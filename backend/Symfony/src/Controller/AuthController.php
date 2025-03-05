<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use DateTime;
use DateTimeZone;

/**
 * Controlador para manejar autenticación y gestión de tokens JWT
 * 
 * Provee endpoints para login, logout, refresco de tokens y verificación de estado
 */
final class AuthController extends AbstractController
{
    /**
     * Autentica un usuario y genera tokens JWT
     * 
     * @Route("/auth", name="app_auth", methods={"POST"})
     * @IsGranted("PUBLIC_ACCESS")
     * 
     * @param Request $request Debe contener:
     *        - email: string
     *        - password: string
     * @return JsonResponse Con tokens en cookies y datos del usuario
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth', name: 'app_auth', methods: ['POST'])]
    public function login(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher, 
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenGeneratorInterface $refreshTokenGenerator
    ): JsonResponse 
    {
        // 1. Valida datos de entrada
        $email = $request->get('email');
        $password = $request->get('password');

        if (empty($email) || empty($password)) {
            return new JsonResponse(['error' => 'Email y contraseña son obligatorios'], 400);
        }

        // 2. Busca usuario en base de datos
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        // 3. Verifica credenciales
        if (!$usuario || !$passwordHasher->isPasswordValid($usuario, $password)) {
            return new JsonResponse(['error' => 'Credenciales inválidas'], 401);
        }

        // 4. Genera tokens
        $fecha_hoy = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp = $fecha_hoy->getTimestamp();
        $exp = $timestamp + 3600; // 1 hora después de la emisión
        
        $accessToken = $jwtManager->create($usuario, ['iat' => $timestamp, 'exp' => $exp]);
        
        // Refresh token válido por 30 días
        $ttl = 3600 * 24 * 30;  
        $refreshToken = $refreshTokenGenerator->createForUserWithTtl($usuario, $ttl);        

        // 5. Guarda refresh token en BD
        $usuario->setRefreshToken($refreshToken->getRefreshToken());
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Obtiene la URL de la imagen del usuario
        $imageUrl = $usuario->getFoto() ?? "https://localhost:8443/data/imagenes/user.png"; // Usar una imagen por defecto si no hay foto

        // Obtiene los roles del usuario
        $roles = $usuario->getRoles();

        // 6. Prepara respuesta con cookies seguras
        $response = new JsonResponse([
            'message' => 'Login exitoso',
            'roles' => $roles,  // Incluir los roles del usuario
            'imageUrl' => $imageUrl
        ], 200);
        
        // Configurar cookies con flags de seguridad
        // Solo HTTPS
        // HttpOnly
        // dura 2 horas
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'access_token', $accessToken, time() + (3600 * 2), '/', null, true, true, false, 'None'
        ));
        
        // dura 2 años
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'refresh_token', $refreshToken->getRefreshToken(), time() + ((365 * 24 * 3600) * 2), '/', null, true, true, false, 'None'
        ));        

        return $response;
    }

    /**
     * Cierra la sesión del usuario y elimina tokens
     * 
     * @Route("/auth/logout", name="app_auth_logout", methods={"POST"})
     * @IsGranted("PUBLIC_ACCESS")
     * 
     * @return JsonResponse Elimina cookies de tokens
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/logout', name: 'app_auth_logout', methods: ['POST'])]
    public function logout(
        Request $request,
        EntityManagerInterface $entityManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ): JsonResponse 
    {
        // 1. Elimina refresh token de la base de datos
        $refreshToken = $request->cookies->get('refresh_token');
        if ($refreshToken) {
            $storedToken = $refreshTokenManager->get($refreshToken);
            if ($storedToken) {
                $entityManager->remove($storedToken);
                $entityManager->flush();
            }
        }

        // 2. Limpia cookies del cliente
        $response = new JsonResponse(['message' => 'Logout exitoso'], 204);
        $response->headers->clearCookie('access_token', '/');
        $response->headers->clearCookie('refresh_token', '/');

        return $response;
    }

    /**
     * Renueva el access token usando el refresh token
     * 
     * @Route("/auth/refresh", name="app_auth_refresh", methods={"POST"})
     * @IsGranted("PUBLIC_ACCESS")
     * 
     * @return JsonResponse Nuevo access token en cookie
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/refresh', name: 'app_auth_refresh', methods: ['POST'])]
    public function refresh(
        Request $request, 
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ): JsonResponse 
    {
        // 1. Valida refresh token
        $refreshToken = $request->cookies->get('refresh_token');
    
        if (!$refreshToken) {
            return new JsonResponse(['error' => 'No se encontró refresh token'], 400);
        }
    
        // Obtiene el refresh token almacenado en la base de datos
        $storedToken = $refreshTokenManager->get($refreshToken);
    
        // Verifica si el refresh token es válido
        if (!$storedToken || $storedToken->getValid() < new \DateTime()) {
            // Si el refresh token está expirado o no es válido, devolver un error para redirigir al login
            return new JsonResponse(
                ['error' => 'Refresh token expirado. Por favor, inicie sesión nuevamente.'],
                401 // Error de no autorizado
            );
        }
    
        // Si el refresh token es válido, podemos proceder con la renovación del access token
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $storedToken->getUsername()]);
    
        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 401);
        }
    
        // Genera un nuevo access token
        $newAccessToken = $jwtManager->create($usuario);
    
        // Devuelve la nueva cookie con el access token
        $response = new JsonResponse(['message' => 'Token actualizado'], 200);
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'access_token', 
            $newAccessToken, 
            time() + (3600 * 2), // 2 horas de validez para el access token
            '/', 
            null, 
            true,  // secure
            true,  // httpOnly
            false, // SameSite
            'None'  
        ));
    
        return $response;
    }    

    /**
     * Verifica el estado de autenticación del usuario
     * 
     * @Route("/auth/status", name="app_auth_status", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     * 
     * @return JsonResponse Información del usuario autenticado
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/status', name: 'app_auth_status', methods: ['GET'])]
    public function getStatus(
        Request $request,
        JWTTokenManagerInterface $jwtManager, // Use JWTTokenManagerInterface
        UsuarioRepository $userRepository
    ): JsonResponse {
        // 1. Obtiene token de múltiples fuentes
        $token = $request->cookies->get('access_token');
        
        if (!$token) {
            $authHeader = $request->headers->get('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1]; // Extraer el token del encabezado "Bearer"
            }
        }
        
        // 2. Valida el token
        if (!$token) {
            return new JsonResponse(
                ['authenticated' => false, 'message' => 'No autenticado: El token no está presente'],
                401
            );
        }
        
        try {
            // 3. Decodifica y valida token
            $payload = $jwtManager->parse($token); // Usar el método 'parse' para decodificar el token
        
            // Verificar que el payload contiene el campo 'id'
            if (!$payload || !isset($payload['id'])) {
                return new JsonResponse(
                    ['authenticated' => false, 'message' => 'Usuario no encontrado en el token'],
                    401
                );
            }

            // 4. Busca usuario en BD
            $usuario = $userRepository->find($payload['id']);
        
            // Si el usuario no existe, devolver un error
            if (!$usuario) {
                return new JsonResponse(
                    ['authenticated' => false, 'message' => 'Usuario no encontrado en la base de datos'],
                    401
                );
            }
        
            // 5. Devuelve datos del usuario
            return new JsonResponse([
                'authenticated' => true,
                'user' => [
                    'id' => $usuario->getId(),
                    'email' => $usuario->getEmail(),
                    'roles' => $usuario->getRoles(),
                ],
            ]);
        } catch (\Exception $e) {
            // Si ocurre un error al decodificar el token, devolver un mensaje de error
            return new JsonResponse(
                ['authenticated' => false, 'message' => 'Token inválido: ' . $e->getMessage()],
                401
            );
        }
    }
}