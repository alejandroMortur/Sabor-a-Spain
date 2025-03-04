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
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto', name: 'app_producto', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
    
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->orderBy('p.id', 'ASC')
            ->getQuery();
    
        $productos = $this->paginator->paginate($query, $page, 6);
    
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }
    
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/tipe', name: 'app_producto_tipe', methods: ['GET'])]
    public function filter_tipe(Request $request): JsonResponse
    {
        $filter = $request->query->getString('filter', '');
        $page = 1;
        
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->orderBy('p.id', 'ASC');
    
        if (!empty($filter)) {
            $page = $request->query->getInt('page', 1);
            $query->innerJoin('p.Tipo_producto', 't')
                ->andWhere('t.Nombre = :filter')  // Usar andWhere para combinar condiciones
                ->setParameter('filter', $filter);
        }
    
        $productos = $this->paginator->paginate($query, $page, 6);
        
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }
    
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/name', name: 'app_producto_name', methods: ['GET'])]
    public function filter_name(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $name = $request->query->getString('name', '');
    
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->orderBy('p.id', 'ASC');
    
        if (!empty($name)) {
            $query->andWhere('p.Nombre LIKE :name')  // Combinar con andWhere
                ->setParameter('name', '%' . $name . '%');
        }
    
        $productos = $this->paginator->paginate($query, $page, 6);
    
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }
    
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/priceMax', name: 'app_producto_priceMax', methods: ['GET'])]
    public function max_price(Request $request): JsonResponse
    {
        $maxPrecioQuery = $this->productoRepository->createQueryBuilder('p')
            ->select('MAX(p.Precio)')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->getQuery();
    
        $maxPrecio = $maxPrecioQuery->getSingleScalarResult();
    
        return $this->json(['precioMaximo' => $maxPrecio]);
    }
    
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/price', name: 'app_producto_price', methods: ['GET'])]
    public function filter_price(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $price = $request->query->getString('price', '');
    
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->orderBy('p.id', 'ASC');
    
        if (!empty($price)) {
            $margin = 100;
            $query->andWhere('p.Precio BETWEEN :minPrice AND :maxPrice')
                ->setParameter('minPrice', $price - $margin)
                ->setParameter('maxPrice', $price + $margin);
        }
    
        $productos = $this->paginator->paginate($query, $page, 6);
    
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }
}