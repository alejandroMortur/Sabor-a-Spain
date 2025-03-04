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

final class ProtectedController extends AbstractController
{
    private JWTTokenManagerInterface $jwtManager;
    private UsuarioRepository $userRepository;

    public function __construct(JWTTokenManagerInterface $jwtManager, UsuarioRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
    }

    /////////////////////////////////////////////////// Rutas para Productos /////////////////////////////////////////////////////////

    #[Route('/api/protected/admin/productos/obtener', name: 'get_productos', methods: ['GET'])]
    public function getProductos(Request $request, ProductosRepository $productosRepository): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $productos = $productosRepository->findAll();
        return $this->json($productos);
    }

    #[Route('/api/protected/admin/productos/crear', name: 'create_producto', methods: ['POST'])]
    public function createProducto(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $producto = new Productos();
        $producto->setNombre($data['Nombre']);
        $producto->setPrecio($data['Precio']);
        $producto->setStock($data['Stock']);
        $producto->setDescripcion($data['Descripcion']);
        $producto->setImagen($data['Imagen']);

        $entityManager->persist($producto);
        $entityManager->flush();

        return $this->json($producto, Response::HTTP_CREATED);
    }

    #[Route('/api/protected/admin/productos/eliminar/{id}', name: 'delete_producto', methods: ['DELETE'])]
    public function deleteProducto(int $id, Request $request, ProductosRepository $productosRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $producto = $productosRepository->find($id);
        
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($producto);
        $entityManager->flush();

        return $this->json(['message' => 'Producto eliminado correctamente']);
    }

    #[Route('/api/protected/admin/productos/obtener/{id}', name: 'get_producto', methods: ['GET'])]
    public function getProducto(int $id, Request $request, ProductosRepository $productosRepository): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $producto = $productosRepository->find($id);
        
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($producto);
    }

    #[Route('/api/protected/admin/productos/actualizar/{id}', name: 'update_producto', methods: ['PUT'])]
    public function updateProducto(int $id, Request $request, ProductosRepository $productosRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $producto = $productosRepository->find($id);
        
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $producto->setNombre($data['Nombre'] ?? $producto->getNombre());
        $producto->setPrecio($data['Precio'] ?? $producto->getPrecio());
        $producto->setStock($data['Stock'] ?? $producto->getStock());
        $producto->setDescripcion($data['Descripcion'] ?? $producto->getDescripcion());
        $producto->setImagen($data['Imagen'] ?? $producto->getImagen());

        $entityManager->flush();

        return $this->json($producto);
    }

    ////////////////////////////////////////////////////// Rutas para Tipos////////////////////////////////////////////////////////

    #[Route('/api/protected/admin/tipos/obtener', name: 'get_tipos', methods: ['GET'])]
    public function getTipos(Request $request, TiposRepository $tiposRepository): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $tipos = $tiposRepository->findAll();
        return $this->json($tipos);
    }

    #[Route('/api/protected/admin/tipos/crear', name: 'create_tipo', methods: ['POST'])]
    public function createTipo(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $tipo = new Tipos();
        $tipo->setNombre($data['Nombre']);
        $tipo->setDescripcion($data['Descripcion']);
        $tipo->setImagen($data['Imagen'] ?? null);

        $entityManager->persist($tipo);
        $entityManager->flush();

        return $this->json($tipo, Response::HTTP_CREATED);
    }

    #[Route('/api/protected/admin/tipos/eliminar/{id}', name: 'delete_tipo', methods: ['DELETE'])]
    public function deleteTipo(int $id, Request $request, TiposRepository $tiposRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $tipo = $tiposRepository->find($id);
        
        if (!$tipo) {
            return $this->json(['error' => 'Tipo no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($tipo);
        $entityManager->flush();

        return $this->json(['message' => 'Tipo eliminado correctamente']);
    }

    #[Route('/api/protected/admin/tipos/obtener/{id}', name: 'get_tipo', methods: ['GET'])]
    public function getTipo(int $id, Request $request, TiposRepository $tiposRepository): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        $tipo = $tiposRepository->find($id);
        
        if (!$tipo) {
            return $this->json(['error' => 'Tipo no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        return $this->json($tipo);
    }
    
    #[Route('/api/protected/admin/tipos/actualizar/{id}', name: 'update_tipo', methods: ['PUT'])]
    public function updateTipo(int $id, Request $request, TiposRepository $tiposRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        $tipo = $tiposRepository->find($id);
        
        if (!$tipo) {
            return $this->json(['error' => 'Tipo no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        $data = json_decode($request->getContent(), true);
    
        $tipo->setNombre($data['Nombre'] ?? $tipo->getNombre());
        $tipo->setDescripcion($data['Descripcion'] ?? $tipo->getDescripcion());
        $tipo->setImagen($data['Imagen'] ?? $tipo->getImagen());
    
        $entityManager->flush();
    
        return $this->json($tipo);
    }

    //////////////////////////////////////////////// Rutas para Usuarios////////////////////////////////////////////////////////////////

    #[Route('/api/protected/admin/usuarios/obtener', name: 'get_usuarios', methods: ['GET'])]
    public function getUsuarios(Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $usuarios = $usuarioRepository->findAll();
        
        // Aquí se añade el contexto para ignorar 'ventas'
        return $this->json($usuarios, 200, [], ['ignored_attributes' => ['ventas']]);
    }
    #[Route('/api/protected/admin/usuarios/crear', name: 'create_usuario', methods: ['POST'])]
    public function createUsuario(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $usuario = new Usuario();
        $usuario->setEmail($data['email']);
        $usuario->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $usuario->setRoles($data['roles'] ?? ['ROLE_USER']);
        $usuario->setNombre($data['Nombre']);
        $usuario->setFoto($data['foto'] ?? null);

        $entityManager->persist($usuario);
        $entityManager->flush();

        return $this->json($usuario, Response::HTTP_CREATED);
    }

    #[Route('/api/protected/admin/usuarios/eliminar/{id}', name: 'delete_usuario', methods: ['DELETE'])]
    public function deleteUsuario(int $id, Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $usuario = $usuarioRepository->find($id);
        
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($usuario);
        $entityManager->flush();

        return $this->json(['message' => 'Usuario eliminado correctamente']);
    }

    #[Route('/api/protected/admin/usuarios/obtener/{id}', name: 'get_usuario', methods: ['GET'])]
    public function getUsuario(int $id, Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        $usuario = $usuarioRepository->find($id);
        
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        return $this->json($usuario);
    }
    
    #[Route('/api/protected/admin/usuarios/actualizar/{id}', name: 'update_usuario', methods: ['PUT'])]
    public function updateUsuario(int $id, Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }
    
        $usuario = $usuarioRepository->find($id);
        
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        $data = json_decode($request->getContent(), true);
    
        $usuario->setNombre($data['Nombre'] ?? $usuario->getNombre());
        $usuario->setEmail($data['email'] ?? $usuario->getEmail());
        $usuario->setFoto($data['foto'] ?? $usuario->getFoto());
        
        if (isset($data['password'])) {
            $usuario->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        }
        
        if (isset($data['roles'])) {
            $usuario->setRoles($data['roles']);
        }
    
        $entityManager->flush();
    
        return $this->json($usuario);
    }

    //////////////////////////////////////// Api protegida para graficos productos //////////////////////////////////////////

    #[Route('/api/protected/admin/grafico/stock', name: 'grafico_stock', methods: ['GET'])]
    public function obtenerGraficoStock(Request $request, ProductosRepository $productosRepository): JsonResponse
    {

        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // Obtener todos los productos
        $productos = $productosRepository->findAll();

        // Inicializar un array para los resultados
        $result = [];

        // Recorrer los productos y acumular el stock por nombre de producto
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

        // Devolver el array como JSON para usarlo en el gráfico de Angular
        return new JsonResponse($result);
    }

    //////////////////////////////////////// Api protegida para graficos ventas //////////////////////////////////////////

    #[Route('/api/protected/admin/grafico/ventas', name: 'grafico_ventas', methods: ['GET'])]
    public function obtenerVentas(Request $request, VentaRepository $ventaRepository): JsonResponse
    {

        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // Llamar al repositorio para obtener los datos de las ventas agrupados por categoría
        $ventasPorCategoria = $ventaRepository->findVentasPorCategoria(null, null); // Sin fechas

        // Formatear los datos para el gráfico
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
    private function isValidToken(Request $request): bool
    {
        // Obtener el token desde las cookies
        $token = $request->cookies->get('access_token');

        // Si no está en cookies, buscarlo en el encabezado de autorización
        if (!$token) {
            $authHeader = $request->headers->get('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        // Si no hay token, no se puede autenticar
        if (!$token) {
            return false;
        }

        try {
            // Decodificar el token
            $payload = $this->jwtManager->parse($token);

            // Verificar si el payload contiene el ID del usuario
            if (!$payload || !isset($payload['id'])) {
                return false;
            }

            // Buscar si el usuario existe
            $usuario = $this->userRepository->find($payload['id']);
            return $usuario !== null;
        } catch (\Exception $e) {
            // Si hay un error en el proceso de decodificación, consideramos el token inválido
            return false;
        }
    }
}
