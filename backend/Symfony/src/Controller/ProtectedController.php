<?php
namespace App\Controller;

use App\Entity\Productos;
use App\Entity\Tipos;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use App\Repository\ProductosRepository;
use App\Repository\TiposRepository;
use App\Repository\VentaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * Controlador para operaciones administrativas protegidas
 * 
 * Provee endpoints CRUD para gestión de productos, tipos, usuarios y reportes
 * Requiere autenticación JWT válida con permisos de administrador
 */
final class ProtectedController extends AbstractController
{
    /**
     * Servicio JWT para manejo de tokens
     * @var JWTTokenManagerInterface
     */
    private JWTTokenManagerInterface $jwtManager;

    /**
     * Repositorio de usuarios inyectado
     * @var UsuarioRepository
     */
    private UsuarioRepository $userRepository;

    /**
     * Constructor que inyecta dependencias
     * @param JWTTokenManagerInterface $jwtManager Servicio JWT
     * @param UsuarioRepository $userRepository Repositorio de usuarios
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, UsuarioRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
    }

    /////////////////////////////////////////////////// Rutas para Productos /////////////////////////////////////////////////////////

    /**
     * Obtiene todos los productos (incluyendo inactivos)
     * 
     * @Route("/api/protected/admin/productos/obtener", name="get_productos", methods={"GET"})
     * 
     * @param Request $request Objeto Request
     * @param ProductosRepository $productosRepository Repositorio de productos
     * @return JsonResponse Lista completa de productos con status 200
     */
    #[Route('/api/protected/admin/productos/obtener', name: 'get_productos', methods: ['GET'])]
    public function getProductos(Request $request, ProductosRepository $productosRepository): JsonResponse
    {
        // 1. Valida token JWT
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Construye query sin filtros
        $productos = $productosRepository->createQueryBuilder('p')
            ->getQuery()
            ->getResult();

        return $this->json($productos);
    }


    /**
     * Crea un nuevo producto
     * 
     * @Route("/api/protected/admin/productos/crear", name="create_producto", methods={"POST"})
     * 
     * @param Request $request Objeto Request con datos del producto
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Producto creado con status 201
     */
    #[Route('/api/protected/admin/productos/crear', name: 'create_producto', methods: ['POST'])]
    public function createProducto(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Decodifica datos JSON
        $data = json_decode($request->getContent(), true);

        // 3. Crea y configura entidad
        $producto = new Productos();
        $producto->setNombre($data['Nombre']);
        $producto->setPrecio($data['Precio']);
        $producto->setStock($data['Stock']);
        $producto->setDescripcion($data['Descripcion']);
        $producto->setImagen($data['Imagen']);

        // 4. Persiste y guardar en BD
        $entityManager->persist($producto);
        $entityManager->flush();

        return $this->json($producto, Response::HTTP_CREATED);
    }

    /**
     * Eliminación lógica de producto (soft delete)
     * 
     * @Route("/api/protected/admin/productos/eliminar/{id}", name="delete_producto", methods={"DELETE"})
     * 
     * @param int $id ID del producto
     * @param Request $request Objeto Request
     * @param ProductosRepository $productosRepository Repositorio de productos
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Mensaje de éxito o error 404
     */
    #[Route('/api/protected/admin/productos/eliminar/{id}', name: 'delete_producto', methods: ['DELETE'])]
    public function deleteProducto(int $id, Request $request, ProductosRepository $productosRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        // 2. Busca producto por ID
        $producto = $productosRepository->find($id);
        
        // 3. Si no existe, retorna error
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        // 4. Marcado el producto como inactivo (soft delete)
        $producto->setActivo(false);
        $entityManager->flush();
    
        return $this->json(['message' => 'Producto marcado como eliminado']);
    }

    /**
     * Obtiene un producto específico por ID
     * 
     * @Route("/api/protected/admin/productos/obtener/{id}", name="get_producto", methods={"GET"})
     * 
     * @param int $id ID del producto
     * @param Request $request Objeto Request
     * @param ProductosRepository $productosRepository Repositorio de productos
     * @return JsonResponse Datos del producto o error 404
     */
    #[Route('/api/protected/admin/productos/obtener/{id}', name: 'get_producto', methods: ['GET'])]
    public function getProducto(int $id, Request $request, ProductosRepository $productosRepository): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Construye query con filtro por ID
        $producto = $productosRepository->createQueryBuilder('p')
        ->where('p.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
        
        // 3. Verifica existencia
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($producto);
    }

    /**
     * Actualiza un producto existente
     * 
     * @Route("/api/protected/admin/productos/actualizar/{id}", name="update_producto", methods={"PUT"})
     * 
     * @param int $id ID del producto
     * @param Request $request Objeto Request con datos actualizados
     * @param ProductosRepository $productosRepository Repositorio de productos
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Producto actualizado o error 404
     */
    #[Route('/api/protected/admin/productos/actualizar/{id}', name: 'update_producto', methods: ['PUT'])]
    public function updateProducto(int $id, Request $request, ProductosRepository $productosRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Busca producto
        $producto = $productosRepository->find($id);
        
        // 3. Verifica existencia
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // 4. Decodifica datos y aplicar cambios
        $data = json_decode($request->getContent(), true);
        $producto->setNombre($data['Nombre'] ?? $producto->getNombre());
        $producto->setPrecio($data['Precio'] ?? $producto->getPrecio());
        $producto->setStock($data['Stock'] ?? $producto->getStock());
        $producto->setDescripcion($data['Descripcion'] ?? $producto->getDescripcion());
        $producto->setImagen($data['Imagen'] ?? $producto->getImagen());

        // 5. Guarda cambios
        $entityManager->flush();

        return $this->json($producto);
    }

    ////////////////////////////////////////////////////// Rutas para Tipos////////////////////////////////////////////////////////

    /**
     * Obtiene todos los tipos de productos
     * 
     * @Route("/api/protected/admin/tipos/obtener", name="get_tipos", methods={"GET"})
     * 
     * @param Request $request Objeto Request
     * @param TiposRepository $tiposRepository Repositorio de tipos
     * @return JsonResponse Lista de tipos con status 200
     */
    #[Route('/api/protected/admin/tipos/obtener', name: 'get_tipos', methods: ['GET'])]
    public function getTipos(Request $request, TiposRepository $tiposRepository): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Obtene todos los tipos
        $tipos = $tiposRepository->createQueryBuilder('t')
        ->getQuery()
        ->getResult();

        return $this->json($tipos);
    }

    /**
     * Crea un nuevo tipo de producto
     * 
     * @Route("/api/protected/admin/tipos/crear", name="create_tipo", methods={"POST"})
     * 
     * @param Request $request Objeto Request con datos del tipo
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Tipo creado con status 201
     */
    #[Route('/api/protected/admin/tipos/crear', name: 'create_tipo', methods: ['POST'])]
    public function createTipo(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Decodifica datos
        $data = json_decode($request->getContent(), true);

        // 3. Crea y configura entidad
        $tipo = new Tipos();
        $tipo->setNombre($data['Nombre']);
        $tipo->setDescripcion($data['Descripcion']);
        $tipo->setImagen($data['Imagen'] ?? null);

        // 4. Persisti y guardar
        $entityManager->persist($tipo);
        $entityManager->flush();

        return $this->json($tipo, Response::HTTP_CREATED);
    }

    /**
     * Elimina lógicamente un tipo (soft delete)
     * 
     * @Route("/api/protected/admin/tipos/eliminar/{id}", name="delete_tipo", methods={"DELETE"})
     * 
     * @param int $id ID del tipo
     * @param Request $request Objeto Request
     * @param TiposRepository $tiposRepository Repositorio de tipos
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Mensaje de éxito o error 404
     */
    #[Route('/api/protected/admin/tipos/eliminar/{id}', name: 'delete_tipo', methods: ['DELETE'])]
    public function deleteTipo(int $id, Request $request, TiposRepository $tiposRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token JWT
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Busca tipo por ID
        $tipo = $tiposRepository->find($id);
        
        // 3. Verifica existencia
        if (!$tipo) {
            return $this->json(['error' => 'Tipo no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // 4. Marcado como inactivo (soft delete)
        $tipo->setActivo(false);
        $entityManager->flush();

        return $this->json(['message' => 'Tipo marcado como eliminado']);
    }

    /**
     * Obtiene un tipo específico por ID
     * 
     * @Route("/api/protected/admin/tipos/obtener/{id}", name="get_tipo", methods={"GET"})
     * 
     * @param int $id ID del tipo
     * @param Request $request Objeto Request
     * @param TiposRepository $tiposRepository Repositorio de tipos
     * @return JsonResponse Datos del tipo o error 404
     */
    #[Route('/api/protected/admin/tipos/obtener/{id}', name: 'get_tipo', methods: ['GET'])]
    public function getTipo(int $id, Request $request, TiposRepository $tiposRepository): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Construye query con filtro por ID
        $tipo = $tiposRepository->createQueryBuilder('t')
        ->where('t.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
        
        // 3. Verifica existencia
        if (!$tipo) {
            return $this->json(['error' => 'Tipo no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        return $this->json($tipo);
    }
    
    /**
     * Actualiza un tipo existente
     * 
     * @Route("/api/protected/admin/tipos/actualizar/{id}", name="update_tipo", methods={"PUT"})
     * 
     * @param int $id ID del tipo
     * @param Request $request Objeto Request con datos actualizados
     * @param TiposRepository $tiposRepository Repositorio de tipos
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Tipo actualizado o error 404
     */
    #[Route('/api/protected/admin/tipos/actualizar/{id}', name: 'update_tipo', methods: ['PUT'])]
    public function updateTipo(int $id, Request $request, TiposRepository $tiposRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        // 2. Busca tipo por ID
        $tipo = $tiposRepository->find($id);
        
        // 3. Verifica existencia
        if (!$tipo) {
            return $this->json(['error' => 'Tipo no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        // 4. Decodifica y aplica cambios
        $data = json_decode($request->getContent(), true);
        $tipo->setNombre($data['Nombre'] ?? $tipo->getNombre());
        $tipo->setDescripcion($data['Descripcion'] ?? $tipo->getDescripcion());
        $tipo->setImagen($data['Imagen'] ?? $tipo->getImagen());
    
        // 5. Guarda cambios
        $entityManager->flush();
    
        return $this->json($tipo);
    }

    //////////////////////////////////////////////// Rutas para Usuarios////////////////////////////////////////////////////////////////

    /**
     * Obtiene todos los usuarios registrados
     * 
     * @Route("/api/protected/admin/usuarios/obtener", name="get_usuarios", methods={"GET"})
     * 
     * @param Request $request Objeto Request
     * @param UsuarioRepository $usuarioRepository Repositorio de usuarios
     * @return JsonResponse Lista de usuarios excluyendo relaciones de ventas
     */
    #[Route('/api/protected/admin/usuarios/obtener', name: 'get_usuarios', methods: ['GET'])]
    public function getUsuarios(Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Obtiene todos los usuarios
        $usuarios = $usuarioRepository->createQueryBuilder('u')
        ->getQuery()
        ->getResult();

         // 3. Excluye la relación ventas para evitar referencias circulares
        return $this->json($usuarios, 200, [], ['ignored_attributes' => ['ventas']]);
    }

    /**
     * Crea un nuevo usuario
     * 
     * @Route("/api/protected/admin/usuarios/crear", name="create_usuario", methods={"POST"})
     * 
     * @param Request $request Objeto Request con datos del usuario
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Usuario creado con status 201
     */
    #[Route('/api/protected/admin/usuarios/crear', name: 'create_usuario', methods: ['POST'])]
    public function createUsuario(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Decodifica y valida datos
        $data = json_decode($request->getContent(), true);

        // 3. Crea y configura entidad
        $usuario = new Usuario();
        $usuario->setEmail($data['email']);
        $usuario->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $usuario->setRoles($data['roles'] ?? ['ROLE_USER']);
        $usuario->setNombre($data['Nombre']);
        $usuario->setFoto($data['foto'] ?? null);

        // 4. Persiste y guarda
        $entityManager->persist($usuario);
        $entityManager->flush();

        return $this->json($usuario, Response::HTTP_CREATED);
    }

    /**
     * Elimina lógicamente un usuario (soft delete)
     * 
     * @Route("/api/protected/admin/usuarios/eliminar/{id}", name="delete_usuario", methods={"DELETE"})
     * 
     * @param int $id ID del usuario
     * @param Request $request Objeto Request
     * @param UsuarioRepository $usuarioRepository Repositorio de usuarios
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Mensaje de éxito o error 404
     */
    #[Route('/api/protected/admin/usuarios/eliminar/{id}', name: 'delete_usuario', methods: ['DELETE'])]
    public function deleteUsuario(int $id, Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token JWT
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        // 2. Busca usuario por ID
        $usuario = $usuarioRepository->find($id);
        
        // 3. Verifica existencia del usuario
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        // 4. Marcado como inactivo (soft delete)
        $usuario->setActivo(false);
        $entityManager->flush();
    
        return $this->json(['message' => 'Usuario marcado como eliminado']);
    }

    /**
     * Obtiene un usuario específico por ID
     * 
     * @Route("/api/protected/admin/usuarios/obtener/{id}", name="get_usuario", methods={"GET"})
     * 
     * @param int $id ID del usuario
     * @param Request $request Objeto Request
     * @param UsuarioRepository $usuarioRepository Repositorio de usuarios
     * @return JsonResponse Datos del usuario o error 404
     */
    #[Route('/api/protected/admin/usuarios/obtener/{id}', name: 'get_usuario', methods: ['GET'])]
    public function getUsuario(int $id, Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        // 2. Construye query con filtro por ID
        $usuario = $usuarioRepository->createQueryBuilder('u')
        ->where('u.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
        
        // 3. Verifica existencia
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        return $this->json($usuario);
    }
    
    /**
     * Actualiza un usuario existente
     * 
     * @Route("/api/protected/admin/usuarios/actualizar/{id}", name="update_usuario", methods={"PUT"})
     * 
     * @param int $id ID del usuario
     * @param Request $request Objeto Request con datos actualizados
     * @param UsuarioRepository $usuarioRepository Repositorio de usuarios
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return JsonResponse Usuario actualizado o error 404
     */
    #[Route('/api/protected/admin/usuarios/actualizar/{id}', name: 'update_usuario', methods: ['PUT'])]
    public function updateUsuario(int $id, Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        // 2. Busca usuario por ID
        $usuario = $usuarioRepository->find($id);
        
        // 3. Verifica existencia
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        // 4. Decodifica datos JSON
        $data = json_decode($request->getContent(), true);
        
        // 5. Actualiza campos básicos
        $usuario->setNombre($data['Nombre'] ?? $usuario->getNombre());
        $usuario->setEmail($data['email'] ?? $usuario->getEmail());
        $usuario->setFoto($data['foto'] ?? $usuario->getFoto());
        
        // 6. Actualiza contraseña si se proporciona
        if (isset($data['password'])) {
            $usuario->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        }
        
        // 7. Actualiza roles si se proporcionan
        if (isset($data['roles'])) {
            $usuario->setRoles($data['roles']);
        }
    
        // 8. Guarda cambios en la base de datos
        $entityManager->flush();
    
        return $this->json($usuario);
    }

    //////////////////////////////////////// Api protegida para graficos productos //////////////////////////////////////////

    /**
     * Genera datos para gráfico de stock acumulado por producto
     * 
     * @Route("/api/protected/admin/grafico/stock", name="grafico_stock", methods={"GET"})
     * 
     * @param Request $request Objeto Request
     * @param ProductosRepository $productosRepository Repositorio de productos
     * @return JsonResponse Datos formateados para gráfico con estructura {name: string, y: number}[]
     */
    #[Route('/api/protected/admin/grafico/stock', name: 'grafico_stock', methods: ['GET'])]
    public function obtenerGraficoStock(Request $request, ProductosRepository $productosRepository): JsonResponse
    {

        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Obtiene todos los productos activos e inactivos
        $productos = $productosRepository->findAll();

        // Inicializa un array para los resultados
        $result = [];

        // 3. Procesa acumulación de stock
        foreach ($productos as $producto) {
            // Verificamos si ya existe el producto en el array
            $productoExistente = false;
            foreach ($result as &$item) {
                if ($item['name'] === $producto->getNombre()) {
                    // Si el producto ya está en el array, sumamos su stock
                    $item['y'] += $producto->getStock();
                    $productoExistente = true;
                    break;
                }
            }

            // Si no existe el producto en el array, lo agregamos
            if (!$productoExistente) {
                $result[] = [
                    'name' => $producto->getNombre(),
                    'y' => $producto->getStock(),
                ];
            }
        }

        return new JsonResponse($result);
    }

    //////////////////////////////////////// Api protegida para graficos ventas //////////////////////////////////////////

    /**
     * Genera datos para gráfico de ventas por categoría
     * 
     * @Route("/api/protected/admin/grafico/ventas", name="grafico_ventas", methods={"GET"})
     * 
     * @param Request $request Objeto Request
     * @param VentaRepository $ventaRepository Repositorio de ventas
     * @return JsonResponse Datos formateados para gráfico con estructura {label: string, y: number}[]
     */
    #[Route('/api/protected/admin/grafico/ventas', name: 'grafico_ventas', methods: ['GET'])]
    public function obtenerVentas(Request $request, VentaRepository $ventaRepository): JsonResponse
    {

        // 1. Valida token
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Obtiene datos de ventas agrupados por categoría
        $ventasPorCategoria = $ventaRepository->findVentasPorCategoria(null, null); // Sin fechas

        // 3. Formatea los datos para el gráfico
        $dataPoints = [];
        foreach ($ventasPorCategoria as $venta) {
            $dataPoints[] = [
                'label' => $venta['categoria'],
                'y' => (float)$venta['total_ventas']
            ];
        }

        // Devolver los datos en formato JSON
        return new JsonResponse($dataPoints);
    }

    //////////////////////////////////////// Método para validar los JWT /////////7/////////////////////////////////////////

    /**
     * Valida la autenticidad y vigencia del token JWT
     * 
     * @param Request $request Objeto Request con el token
     * @return bool True si el token es válido y corresponde a un usuario existente
     */
    private function isValidToken(Request $request): bool
    {

        // 1. Busca token en cookies
        $token = $request->cookies->get('access_token');

        // 2. Si no está en cookies, buscar en headers
        if (!$token) {
            $authHeader = $request->headers->get('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        // 3. Valida presencia del token
        if (!$token) {
            return false;
        }

        try {
            // 4. Decodifica token
            $payload = $this->jwtManager->parse($token);

            // 5. Valida estructura básica del payload
            if (!$payload || !isset($payload['id'])) {
                return false;
            }

            // 6. Verifica existencia de usuario en base de datos
            $usuario = $this->userRepository->find($payload['id']);
            return $usuario !== null;
        } catch (\Exception $e) {
            
            // Si hay un error en el proceso de decodificación, consideramos el token inválido
            return false;
        }
    }
}
