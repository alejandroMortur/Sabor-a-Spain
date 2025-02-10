<?php

namespace App\Controller;

use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UsuarioRepository;

final class UsuarioController extends AbstractController
{
    #[Route('/api/usuario', name: 'app_usuario', methods: ['GET'])]
    public function index(UsuarioRepository $usuarioRepository): JsonResponse
    {
        // Obtener todos los productos desde la base de datos
        $usuarios = $usuarioRepository->findAll();

        // Crear un array con los datos de los productos para la respuesta
        $data = [];
    
        foreach ($usuarios as $usuario) {
            $data[] = [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'correo' => $usuario->getEmail(),
                'foto' => $usuario->getFoto(),
                'clave' => $usuario->getPassword(),
                'roles' => $usuario->getRoles(),
            ];
        }

        // Retornar los datos en formato JSON
        return new JsonResponse($data);
    }
}
