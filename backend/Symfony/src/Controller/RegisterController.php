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
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;  // Importa la interfaz  
use Symfony\Component\HttpFoundation\Cookie;

final class RegisterController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;  // Lexik JWT Manager
    private RefreshTokenManagerInterface $refreshTokenManager;// Gesdinet Refresh Token Manager

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenManagerInterface $refreshTokenManager
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if (!$username || !$password) {
            return new JsonResponse(['error' => 'Faltan datos para el registro'], 400);
        }

        // Crear un nuevo usuario
        $usuario = new Usuario();
        $usuario->setNombre($username);
        $usuario->setEmail($username);
        $usuario->setPassword($this->passwordHasher->hashPassword($usuario, $password));
        $usuario->setRoles(['ROLE_USER']);

        // Guardar el usuario en la base de datos
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Generar el JWT (Access Token)
        $accessToken = $this->jwtManager->create($usuario);

        // Generar el Refresh Token
        $refreshToken = $this->refreshTokenManager->create($usuario);

        // Establecer las cookies con JWT y Refresh Token
        $response = new JsonResponse([
            'message' => 'Usuario registrado correctamente'
        ], 200);

        // Crear las cookies para ambos tokens sin el parámetro 'secure'
        $response->headers->setCookie(
            new Cookie('access_token', $accessToken, 0, '/', null, false, true, false, 'Strict')
        );
        $response->headers->setCookie(
            $expiryTime = time() + 3600;  // Verifica si la hora está correcta
            new Cookie('refresh_token', $refreshToken, $expiryTime, '/', null, false, true, false, 'Strict');
        );

        return $response;
    }
}