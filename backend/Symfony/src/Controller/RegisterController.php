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

final class RegisterController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;  // Lexik JWT Manager
    private RefreshTokenGeneratorInterface $refreshTokenGenerator;

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

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $email = $request->get('email');

        if (!$username || !$password || !$email) {
            return new JsonResponse(['error' => 'Faltan datos para el registro'], 400);
        }

        // Crear un nuevo usuario
        $usuario = new Usuario();
        $usuario->setNombre($username);
        $usuario->setEmail($email);
        $usuario->setPassword($this->passwordHasher->hashPassword($usuario, $password));
        $usuario->setRoles(['ROLE_USER']);

        // Guardar el usuario en la base de datos
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Generar el JWT (Access Token)
        $accessToken = $this->jwtManager->create($usuario);

        $ttl = (new \DateTime('+1 year'))->getTimestamp() - time(); // Obtiene la diferencia en segundos
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($usuario, $ttl);

        $usuario->setRefreshToken($refreshToken->getRefreshToken());
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Establecer las cookies con JWT y Refresh Token
        $response = new JsonResponse([
            'message' => 'OK'
        ], 200);

        // Crear las cookies para ambos tokens sin el parámetro 'secure'
        $response->headers->setCookie(
            new Cookie('access_token', $accessToken, time() + (3600*2), '/', null, false, true, false, 'None')
        );
        
        $response->headers->setCookie(
            new Cookie('refresh_token', $refreshToken, time() + ((365 * 24 * 3600)*2), '/', null, false, true, false, 'None')
        );
        
        return $response;
    }
}