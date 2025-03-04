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

        if (empty($email) || empty($password)) {
            return new JsonResponse(['error' => 'Email y contraseña son obligatorios'], 400);
        }

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        if (!$usuario || !$passwordHasher->isPasswordValid($usuario, $password)) {
            return new JsonResponse(['error' => 'Credenciales inválidas'], 401);
        }

        $fecha_hoy = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $timestamp = $fecha_hoy->getTimestamp();
        
        // Definir expiración del token, por ejemplo, 1 hora
        $exp = $timestamp + 3600; // 1 hora después de la emisión
        
        $accessToken = $jwtManager->create($usuario, ['iat' => $timestamp, 'exp' => $exp]);
        

        $ttl = 3600 * 24 * 30;  // 30 días, por ejemplo
        $refreshToken = $refreshTokenGenerator->createForUserWithTtl($usuario, $ttl);        

        // Guardar el Refresh Token en la base de datos
        $usuario->setRefreshToken($refreshToken->getRefreshToken());
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Obtener la URL de la imagen del usuario
        $imageUrl = $usuario->getFoto() ?? "https://localhost:8443/data/imagenes/user.png"; // Usar una imagen por defecto si no hay foto

        // Obtener los roles del usuario
        $roles = $usuario->getRoles();

        $response = new JsonResponse([
            'message' => 'Login exitoso',
            'roles' => $roles,  // Incluir los roles del usuario
            'imageUrl' => $imageUrl
        ], 200);
        
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'access_token', $accessToken, time() + (3600 * 2), '/', null, true, true, false, 'None'
        ));
        
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'refresh_token', $refreshToken->getRefreshToken(), time() + ((365 * 24 * 3600) * 2), '/', null, true, true, false, 'None'
        ));        

        return $response;
    }

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/logout', name: 'app_auth_logout', methods: ['POST'])]
    public function logout(
        Request $request,
        EntityManagerInterface $entityManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ): JsonResponse 
    {
        $refreshToken = $request->cookies->get('refresh_token');
        if ($refreshToken) {
            $storedToken = $refreshTokenManager->get($refreshToken);
            if ($storedToken) {
                $entityManager->remove($storedToken);
                $entityManager->flush();
            }
        }

        $response = new JsonResponse(['message' => 'Logout exitoso'], 204);
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
    
        // Obtener el refresh token almacenado en la base de datos
        $storedToken = $refreshTokenManager->get($refreshToken);
    
        // Verificar si el refresh token es válido
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
    
        // Generar un nuevo access token
        $newAccessToken = $jwtManager->create($usuario);
    
        // Devuelve la nueva cookie con el access token
        $response = new JsonResponse(['message' => 'Token actualizado'], 200);
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'access_token', 
            $newAccessToken, 
            time() + (3600 * 2), // 2 horas de validez para el access token
            '/', 
            null, 
            true,  // secure: asegurarse de que sea enviado solo sobre HTTPS
            true,  // httpOnly: evitar acceso mediante JavaScript
            false, // SameSite: evitar que la cookie sea enviada en solicitudes cruzadas
            'None'  // puedes ajustarlo según el comportamiento que desees
        ));
    
        return $response;
    }    
    
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/auth/status', name: 'app_auth_status', methods: ['GET'])]
    public function getStatus(
        Request $request,
        JWTTokenManagerInterface $jwtManager, // Use JWTTokenManagerInterface
        UsuarioRepository $userRepository
    ): JsonResponse {
        // Obtener el token JWT de las cookies
        $token = $request->cookies->get('access_token');
        
        // Si no se encuentra en las cookies, intentar obtenerlo del encabezado de autorización
        if (!$token) {
            $authHeader = $request->headers->get('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1]; // Extraer el token del encabezado "Bearer"
            }
        }
        
        // Si no se encuentra el token, devolver un error
        if (!$token) {
            return new JsonResponse(
                ['authenticated' => false, 'message' => 'No autenticado: El token no está presente'],
                401
            );
        }
        
        try {
            // Decodificar el token JWT utilizando el método adecuado para este caso
            $payload = $jwtManager->parse($token); // Usar el método 'parse' para decodificar el token
        
            // Verificar que el payload contiene el campo 'id'
            if (!$payload || !isset($payload['id'])) {
                return new JsonResponse(
                    ['authenticated' => false, 'message' => 'Usuario no encontrado en el token'],
                    401
                );
            }
        
            // Buscar el usuario en la base de datos usando el 'id' del token
            $usuario = $userRepository->find($payload['id']);
        
            // Si el usuario no existe, devolver un error
            if (!$usuario) {
                return new JsonResponse(
                    ['authenticated' => false, 'message' => 'Usuario no encontrado en la base de datos'],
                    401
                );
            }
        
            // Si todo está bien, devolver la información del usuario
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