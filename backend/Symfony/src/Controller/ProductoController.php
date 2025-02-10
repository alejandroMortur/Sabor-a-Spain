<?php

// src/Controller/ProductoController.php
namespace App\Controller;

use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductosRepository;

class ProductoController extends AbstractController
{

    #[Route('/api/producto', name: 'app_producto', methods: ['GET'])]
    public function index(ProductosRepository $productoRepository): JsonResponse
    {
        // Obtener todos los productos desde la base de datos
        $productos = $productoRepository->findAll();

        // Crear un array con los datos de los productos para la respuesta
        $data = [];
        foreach ($productos as $producto) {
            $data[] = [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'precio' => $producto->getPrecio(),
                'descripcion' => $producto->getDescripcion(),
                'stock' => $producto->getStock(),
            ];
        }

        // Retornar los datos en formato JSON
        return new JsonResponse($data);
    }
}