<?php

namespace App\Controller;

use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class UsuarioController extends AbstractController
{
    #[Route('/api/usuario', name: 'app_usuario', methods: ['GET'])]
    public function index(UsuarioRepository $usuarioRepository): JsonResponse
    {
        // Asegurarse de que el usuario esté autenticado
        if (!$this->getUser()) {
            throw new AccessDeniedException('You are not authenticated');
        }

        // Obtener todos los usuarios desde la base de datos
        $usuarios = $usuarioRepository->findAll();

        // Crear un array con los datos de los usuarios para la respuesta
        $data = [];
    
        foreach ($usuarios as $usuario) {
            $data[] = [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'correo' => $usuario->getEmail(),
                'foto' => $usuario->getFoto(),
                'roles' => $usuario->getRoles(),
            ];
        }

        // Retornar los datos en formato JSON
        return new JsonResponse($data);
    }
}