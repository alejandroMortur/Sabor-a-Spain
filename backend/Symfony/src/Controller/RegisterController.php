<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface; 
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile; // Import UploadedFile

/**
 * Controlador para el registro de nuevos usuarios
 * 
 * Maneja la creación de cuentas de usuario, incluyendo:
 * - Validación de datos básicos
 * - Subida de imágenes de perfil
 * - Generación de tokens JWT
 * - Configuración de cookies seguras
 */
final class RegisterController extends AbstractController
{
    /**
     * @var UserPasswordHasherInterface Servicio para hashear contraseñas
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @var UserPasswordHasherInterface Servicio para hashear contraseñas
     */
    private JWTTokenManagerInterface $jwtManager;  

    /**
     * @var RefreshTokenGeneratorInterface Generador de refresh tokens
     */
    private RefreshTokenGeneratorInterface $refreshTokenGenerator;

    /**
     * Constructor que inyecta las dependencias necesarias
     * 
     * @param UserPasswordHasherInterface $passwordHasher
     * @param JWTTokenManagerInterface $jwtManager
     * @param RefreshTokenGeneratorInterface $refreshTokenGenerator
     */
    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenGeneratorInterface $refreshTokenGenerator 
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->refreshTokenGenerator = $refreshTokenGenerator;
    }

    /**
     * Registra un nuevo usuario en el sistema
     * 
     * @Route("/register", name="app_register", methods={"POST"})
     * 
     * @param Request $request Objeto Request con los datos del formulario
     * @param EntityManagerInterface $entityManager Gestor de entidades de Doctrine
     * 
     * @return JsonResponse Respuesta JSON con:
     * - message: Estado de la operación
     * - imageUrl: URL de la imagen subida (opcional)
     * 
     * @throws \Exception Si falla la creación de directorios o el movimiento de archivos
     */
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
         // 1. Obtiene y validar datos básicos del formulario
        $username = $request->get('username');
        $password = $request->get('password');
        $email = $request->get('email');

        if (!$username || !$password || !$email) {
            return new JsonResponse(['error' => 'Faltan datos para el registro'], 400);
        }

        // 2. Manejo de la imagen de perfil
        $file = $request->files->get('image');
        $imageUrl = null;

        if ($file instanceof UploadedFile) {

            // 2.1 Validar tipo MIME del archivo
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse(['error' => 'Tipo de archivo no permitido'], 400);
            }

            // 2.2 Genera nombre único y seguro para el archivo
            $filename = uniqid() . '.' . $file->guessExtension();

            // 2.3 Configura directorio de subida
            $uploadDirectory = '/var/www/html/data/userImg/';
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            // 2.4 Mueve el archivo al directorio permanente
            $file->move($uploadDirectory, $filename);
            $imageUrl = $request->getSchemeAndHttpHost() . '/data/userImg/' . $filename;
            
        }

        // 3. Creación de la entidad Usuario
        $usuario = new Usuario();
        $usuario->setNombre($username);
        $usuario->setEmail($email);
        $usuario->setPassword($this->passwordHasher->hashPassword($usuario, $password));
        $usuario->setRoles(['ROLE_USER']);

        if ($imageUrl) {
            $usuario->setFoto($imageUrl);
        }

        // 4. Persistencia en base de datos
        $entityManager->persist($usuario);
        $entityManager->flush();

        // 5. Generación de tokens de autenticación
        // 5.1 Access Token (JWT) válido por 2 horas
        $accessToken = $this->jwtManager->create($usuario);

        // 5.2 Refresh Token válido por 1 año
        $ttl = (new \DateTime('+1 year'))->getTimestamp() - time(); // Obtiene la diferencia en segundos
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($usuario, $ttl);

        // 5.3 Almacena refresh token en el usuario
        $usuario->setRefreshToken($refreshToken->getRefreshToken());
        $entityManager->persist($usuario);
        $entityManager->flush();

        // 6. Configura respuesta HTTP con cookies seguras
        $response = new JsonResponse([
            'message' => 'OK',
            'imageUrl' => $imageUrl  // Aquí se incluye la URL de la imagen generada
        ], 200);        

        // 6.1 Cookie para Access Token (2 horas de vida)
        // Solo enviar sobre HTTPS
        // No accesible desde JavaScript
        // Politica SameSite
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'access_token', $accessToken, time() + (3600 * 2), '/', null, true, true, false, 'None'
        ));
        
        // 6.2 Cookie para Refresh Token (2 años de vida)
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'refresh_token', $refreshToken->getRefreshToken(), time() + ((365 * 24 * 3600) * 2), '/', null, true, true, false, 'None'
        ));        
        
        return $response;
    }
}