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
use App\Entity\Usuario;

final class AuthController extends AbstractController
{
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
        $email = $request->get('email');
        $password = $request->get('password');

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email y contraseña son obligatorios'], 400);
        }

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        if (!$usuario || !$passwordHasher->isPasswordValid($usuario, $password)) {
            return new JsonResponse(['error' => 'Credenciales inválidas'], 401);
        }

        // Generar tokens
        $accessToken = $jwtManager->create($usuario);
        $ttl = (new \DateTime('+1 year'))->getTimestamp() - time();
        $refreshToken = $refreshTokenGenerator->createForUserWithTtl($usuario, $ttl);

        $usuario->setRefreshToken($refreshToken->getRefreshToken());
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Establecer cookies con los tokens
        $response = new JsonResponse(['message' => 'Login exitoso'], 200);
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'access_token', $accessToken, time() + (3600 * 2), '/', null, false, true, false, 'None'
        ));
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'refresh_token', $refreshToken->getRefreshToken(), time() + ((365 * 24 * 3600) * 2), '/', null, false, true, false, 'None'
        ));

        return $response;
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/logout', name: 'app_auth_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['message' => 'Logout exitoso'], 200);

        // Eliminar cookies de autenticación
        $response->headers->clearCookie('access_token', '/');
        $response->headers->clearCookie('refresh_token', '/');

        return $response;
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/refresh', name: 'app_auth_refresh', methods: ['POST'])]
    public function refresh(
        Request $request, 
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ): JsonResponse 
    {
        $refreshToken = $request->cookies->get('refresh_token');

        if (!$refreshToken) {
            return new JsonResponse(['error' => 'No se encontró refresh token'], 400);
        }

        $storedToken = $refreshTokenManager->get($refreshToken);

        if (!$storedToken) {
            return new JsonResponse(['error' => 'Refresh token inválido'], 401);
        }

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $storedToken->getUsername()]);

        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 401);
        }

        $newAccessToken = $jwtManager->create($usuario);
        $response = new JsonResponse(['message' => 'Token actualizado'], 200);

        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'access_token', $newAccessToken, time() + (3600 * 2), '/', null, false, true, false, 'None'
        ));

        return $response;
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/status', name: 'app_auth_status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        $usuario = $this->getUser();

        if (!$usuario) {
            return new JsonResponse(['authenticated' => false, 'message' => 'No autenticado'], 401);
        }

        return new JsonResponse([
            'authenticated' => true,
            'user' => [
                'id' => $usuario->getId(),
                'email' => $usuario->getEmail(),
                'roles' => $usuario->getRoles()
            ]
        ]);
    }
}