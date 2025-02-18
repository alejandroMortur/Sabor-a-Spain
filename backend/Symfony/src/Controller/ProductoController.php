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
            ->orderBy('p.id', 'ASC') // Ordenar por ID de manera ascendente
            ->getQuery();

        // Paginación de los resultados
        $productos = $this->paginator->paginate(
            $query, // Consulta de Doctrine
            $page,  // Página actual
            6      // Número de elementos por página
        );

        // Responder con los productos paginados (en formato JSON)
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }

    #[Route('/api/producto/tipe', name: 'app_producto_tipe', methods: ['GET'])]
    public function filter_tipe(Request $request): JsonResponse
    {
        // Obtener el número de página desde el request, si no, asignar 1
        $filter = $request->query->getString('filter', ''); // `filter` es el valor que recibimos de Angular
        $page = 1;//Valor por defecto en caso de no filtro
        
        // Crear la consulta base para los productos
        $query = $this->productoRepository->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC');  // Ordenar por ID de manera ascendente
        
        // Si se recibe un filtro (no está vacío), aplicar el filtro
        if (!empty($filter)) {
            $page = $request->query->getInt('page', 1); // `page` es el valor que recibimos de Angular
            $query->innerJoin('p.Tipo_producto', 't')  // Hacer un inner join con la tabla de tipos
                ->where('t.Nombre = :filter')  // Filtrar por el nombre del tipo
                ->setParameter('filter', $filter);  // Asignar el valor del filtro
        }
        
        // Paginación de los resultados
        $productos = $this->paginator->paginate(
            $query, // Consulta de Doctrine
            $page,  // Página actual
            6      // Número de elementos por página
        );
    
        // Responder con los productos paginados (en formato JSON)
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }    

    #[Route('/api/producto/name', name: 'app_producto_name', methods: ['GET'])]
    public function filter_name(Request $request): JsonResponse
    {

        // Obtener el número de página desde el request, si no, asignar 1
        $page = $request->query->getInt('page', 1); // `page` es el valor que recibimos de Angular
        $name = $request->query->getString('name', ''); // `filter` es el valor que recibimos de Angular

        // Crear la consulta base
        $query = $this->productoRepository->createQueryBuilder('p')->orderBy('p.id', 'ASC');

        // Si hay un filtro de nombre, agregar la condición WHERE
        if (!empty($name)) {
            $query->where('p.Nombre LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        // Paginación de los resultados
        $query->setMaxResults(6)  // Número de elementos por página
            ->setFirstResult(($page - 1) * 6); // Calcular el primer elemento para la página actual

        // Obtener los productos
        $productos = $this->paginator->paginate(
            $query, // Consulta de Doctrine
            $page,  // Página actual
            6      // Número de elementos por página
        );

        // Responder con los productos paginados (en formato JSON)
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);

    }  

    #[Route('/api/producto/price', name: 'app_producto_price', methods: ['GET'])]
    public function filter_price(Request $request): JsonResponse
    {

    }  
}