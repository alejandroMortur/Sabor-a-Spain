<?php
namespace App\Controller;

use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface; 
use App\Repository\ProductosRepository;

/**
 * Controlador para gestionar operaciones CRUD de productos
 * 
 * Provee endpoints API REST para listar y filtrar productos con paginación
 */
class ProductoController extends AbstractController
{
    /**
     * Repositorio de Productos inyectado para acceso a datos
     * @var ProductosRepository
     */
    private $productoRepository;

    /**
     * Servicio de paginación inyectado
     * @var PaginatorInterface
     */    
    private $paginator;

    /**
     * Servicio de paginación inyectado
     * @var PaginatorInterface
     */
    public function __construct(ProductosRepository $productoRepository, PaginatorInterface $paginator)
    {
        $this->productoRepository = $productoRepository;
        $this->paginator = $paginator;
    }

    /**
     * Obtiene todos los productos activos paginados
     * 
     * @Route("/api/producto", name="app_producto", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     * 
     * @param Request $request Objeto Request para obtener parámetros
     * @return JsonResponse Respuesta JSON estructurada con formato:
     * {
     *     "productos": array<Producto>,
     *     "total": int,
     *     "pagina": int
     * }
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto', name: 'app_producto', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // 1. Obtiene número de página de la query string (valor por defecto 1)
        $page = $request->query->getInt('page', 1);
    
        // 2. Construye query base con filtro de productos activos
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro para productos activos
            ->setParameter('activo', true) // Prevención de SQL injection
            ->orderBy('p.id', 'ASC') // Ordenamiento por ID ascendente
            ->getQuery();
    
        // 3. Pagina resultados (6 elementos por página)
        $productos = $this->paginator->paginate($query, $page, 6);
        
        // 4. Devuelve respuesta JSON estructurada
        return $this->json([
            'productos' => $productos->getItems(), // Lista de productos
            'total' => $productos->getTotalItemCount(), // Total de elementos
            'pagina' => $page, // Página actual
        ]);
    }
    
    /**
     * Filtra productos por tipo con paginación
     * 
     * @Route("/api/producto/tipe", name="app_producto_tipe", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     *
     * @param Request $request Contiene parámetros:
     *        - filter: string Nombre del tipo a filtrar (opcional)
     *        - page: int Número de página (default: 1)
     * @return JsonResponse Respuesta con estructura:
     * {
     *     "productos": array<Producto>,
     *     "total": int,
     *     "pagina": int
     * }
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/tipe', name: 'app_producto_tipe', methods: ['GET'])]
    public function filter_tipe(Request $request): JsonResponse
    {
        // 1. Obtiene parámetros del request
        $filter = $request->query->getString('filter', '');
        $page = 1;
        
        // 2. Construye query base
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->orderBy('p.id', 'ASC');
    
        // 3. Aplica filtro de tipo si existe    
        if (!empty($filter)) {
            $page = $request->query->getInt('page', 1); // Obtiene página solo si hay filtro
            $query->innerJoin('p.Tipo_producto', 't') // Join con la relación Tipo
                ->andWhere('t.Nombre = :filter')  // Filtro por nombre de tipo
                ->setParameter('filter', $filter); // Bind del parámetro
        }
    
        // Pagina y devolver resultados
        $productos = $this->paginator->paginate($query, $page, 6);
        
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }

    /**
     * Busca productos por nombre con paginación
     * 
     * @Route("/api/producto/name", name="app_producto_name", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     *
     * @param Request $request Contiene parámetros:
     *        - name: string Texto a buscar en nombres (opcional)
     *        - page: int Número de página (default: 1)
     * @return JsonResponse Respuesta con estructura estándar paginada
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/name', name: 'app_producto_name', methods: ['GET'])]
    public function filter_name(Request $request): JsonResponse
    {
        // 1. Obtiene parámetros con valores por defecto
        $page = $request->query->getInt('page', 1);
        $name = $request->query->getString('name', '');
    
        // 2. Query base con filtro activo
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->orderBy('p.id', 'ASC');
    
        // 3. Añade filtro de nombre si se proporciona
        if (!empty($name)) {
            $query->andWhere('p.Nombre LIKE :name')  // Búsqueda parcial
                ->setParameter('name', '%' . $name . '%'); // % para coincidencias parciales
        }
    
        // 4. Pagina y formatear respuesta
        $productos = $this->paginator->paginate($query, $page, 6);
    
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }
    
    /**
     * Obtiene el precio máximo de todos los productos activos
     * 
     * @Route("/api/producto/priceMax", name="app_producto_priceMax", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     * 
     * @return JsonResponse Estructura con formato:
     * {
     *     "precioMaximo": float
     * }
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/priceMax', name: 'app_producto_priceMax', methods: ['GET'])]
    public function max_price(Request $request): JsonResponse
    {
        // 1. Consulta para obtener el precio máxim
        $maxPrecioQuery = $this->productoRepository->createQueryBuilder('p')
            ->select('MAX(p.Precio)') // Función de agregación
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->getQuery();
    
        // 2. Obtiene resultado como escalar
        $maxPrecio = $maxPrecioQuery->getSingleScalarResult();
    
        // 3. Devuelve solo el precio máximo
        return $this->json(['precioMaximo' => $maxPrecio]);
    }

    /**
     * Filtra productos por rango de precio (+/- 100) con paginación
     * 
     * @Route("/api/producto/price", name="app_producto_price", methods={"GET"})
     * @IsGranted("PUBLIC_ACCESS")
     *
     * @param Request $request Contiene parámetros:
     *        - price: float Precio base para el filtro
     *        - page: int Número de página (default: 1)
     * @return JsonResponse Respuesta con estructura estándar paginada
     */
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/producto/price', name: 'app_producto_price', methods: ['GET'])]
    public function filter_price(Request $request): JsonResponse
    {
        // 1. Obtiene parámetros
        $page = $request->query->getInt('page', 1);
        $price = $request->query->getString('price', '');
    
        // 2. Query base
        $query = $this->productoRepository->createQueryBuilder('p')
            ->where('p.Activo = :activo')  // Filtro activo
            ->setParameter('activo', true)
            ->orderBy('p.id', 'ASC');
    
        // 3. Aplica filtro de rango de precio
        if (!empty($price)) {
            $margin = 100; // Margen fijo de +/- 100
            $query->andWhere('p.Precio BETWEEN :minPrice AND :maxPrice')
                ->setParameter('minPrice', $price - $margin) // Conversión implícita a numérico
                ->setParameter('maxPrice', $price + $margin);
        }
    
        // 4. Pagina y devolver
        $productos = $this->paginator->paginate($query, $page, 6);
    
        return $this->json([
            'productos' => $productos->getItems(),
            'total' => $productos->getTotalItemCount(),
            'pagina' => $page,
        ]);
    }
}