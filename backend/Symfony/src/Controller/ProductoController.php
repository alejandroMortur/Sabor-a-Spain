<?php

// src/Controller/ProductoController.php
namespace App\Controller;

use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface; 
use App\Repository\ProductosRepository;

class ProductoController extends AbstractController
{

    private $productoRepository;
    private $paginator;

    public function __construct(ProductosRepository $productoRepository, PaginatorInterface $paginator)
    {
        $this->productoRepository = $productoRepository;
        $this->paginator = $paginator;
    }

    #[Route('/api/producto', name: 'app_producto', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // Obtener el número de página desde el request, si no, asignar 1
        $page = $request->query->getInt('page', 1); // `page` es el valor que recibimos de Angular

        // Obtener los productos
        $query = $this->productoRepository->createQueryBuilder('p')
            ->getQuery();

        // Paginación de los resultados
        $productos = $this->paginator->paginate(
            $query, // Consulta de Doctrine
            $page,  // Página actual
            10      // Número de elementos por página
        );

        // Responder con los productos paginados (en formato JSON)
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }
}