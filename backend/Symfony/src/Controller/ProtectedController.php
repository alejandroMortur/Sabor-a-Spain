<?php

namespace App\Controller;

use App\Entity\Productos;
use App\Repository\UsuarioRepository;
use App\Repository\ProductosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

    #[Route('/api/protected', name: 'app_protected')]
    public function index(): Response
    {
        
    }

    #[Route('/api/protected/admin', name: 'app_protected_admin')]
    public function admin(): Response
    {
        
    }

    // Obtener todos los productos (protegiendo la ruta con validación manual del token)
    #[Route('/api/protected/admin/productos', name: 'get_productos', methods: ['GET'])]
    public function getProductos(Request $request, ProductosRepository $productosRepository): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        $productos = $productosRepository->findAll();
        return $this->json($productos);
    }

    // Obtener un producto por ID
    #[Route('/api/protected/admin/productos/{id}', name: 'get_producto', methods: ['GET'])]
    public function getProducto(int $id, ProductosRepository $productosRepository): JsonResponse
    {
        $producto = $productosRepository->find($id);
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($producto);
    }

    // Crear un nuevo producto
    #[Route('/api/protected/admin/productos', name: 'create_producto', methods: ['POST'])]
    public function createProducto(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $producto = new Productos();
        $producto->setNombre($data['Nombre']);
        $producto->setPrecio($data['Precio']);
        $producto->setStock($data['Stock']);
        $producto->setDescripcion($data['Descripcion']);
        $producto->setImagen($data['Imagen']);

        // Asignar el tipo de producto (requiere un objeto Tipos)
        // $tipoProducto = $entityManager->getRepository(Tipos::class)->find($data['Tipo_producto_id']);
        // $producto->setTipoProducto($tipoProducto);

        $entityManager->persist($producto);
        $entityManager->flush();

        return $this->json($producto, Response::HTTP_CREATED);
    }

    // Actualizar un producto existente
    #[Route('/api/protected/admin/productos/{id}', name: 'update_producto', methods: ['PUT'])]
    public function updateProducto(int $id, Request $request, ProductosRepository $productosRepository, EntityManagerInterface $entityManager): JsonResponse
    {
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

        // Actualizar el tipo de producto si se proporciona
        // if (isset($data['Tipo_producto_id'])) {
        //     $tipoProducto = $entityManager->getRepository(Tipos::class)->find($data['Tipo_producto_id']);
        //     $producto->setTipoProducto($tipoProducto);
        // }

        $entityManager->flush();

        return $this->json($producto);
    }

    // Eliminar un producto
    #[Route('/api/protected/admin/productos/{id}', name: 'delete_producto', methods: ['DELETE'])]
    public function deleteProducto(int $id, ProductosRepository $productosRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $producto = $productosRepository->find($id);
        if (!$producto) {
            return $this->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($producto);
        $entityManager->flush();

        return $this->json(['message' => 'Producto eliminado']);
    }

    // Método para validar el token manualmente
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

            // Buscar si el usuario existe en la base de datos
            $usuario = $this->userRepository->find($payload['id']);
            return $usuario !== null;
        } catch (\Exception $e) {
            return false; // Si hay error en la validación, el token es inválido
        }
    }
}