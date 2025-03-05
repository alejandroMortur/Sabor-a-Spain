<?php

namespace App\Controller;

use App\Entity\Venta;
use App\Entity\Usuario;
use App\Service\PayPalService;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Productos;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador para gestionar pagos con PayPal
 * 
 * Maneja la creación, ejecución y cancelación de pagos
 * Integra validación JWT y gestión de transacciones
 */
final class PaypalController extends AbstractController
{    
    /**
     * Servicio de PayPal inyectado
     * @var PayPalService
     */
    private $payPalService;

    /**
     * Gestor de entidades Doctrine
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Repositorio de productos
     * @var ProductosRepository
     */
    private $productoRepository;

    /**
     * Repositorio de usuarios
     * @var UsuarioRepository
     */
    private UsuarioRepository $userRepository;

    /**
     * Servicio JWT
     * @var JWTTokenManagerInterface
     */
    private JWTTokenManagerInterface $jwtManager;

    /**
     * Constructor que inyecta dependencias
     * @param JWTTokenManagerInterface $jwtManager Servicio JWT
     * @param UsuarioRepository $userRepository Repositorio de usuarios
     * @param PayPalService $payPalService Servicio PayPal
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @param ManagerRegistry $doctrine Registro de Doctrine
     */
    public function __construct(JWTTokenManagerInterface $jwtManager ,UsuarioRepository $userRepository ,PayPalService $payPalService, EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
        $this->payPalService = $payPalService;
        $this->entityManager = $entityManager;
        $this->productoRepository = $doctrine->getRepository(Productos::class);
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
    }

    /**
     * Crea un pago a través de PayPal
     * 
     * @Route("/api/protected/paypal", name="app_paypal", methods={"POST"})
     * 
     * @param Request $request Contiene:
     *        - cart: array Items del carrito
     *        - totalAmount: float Monto total
     *        - currency: string Moneda (USD, EUR, GBP)
     * @return JsonResponse URL de aprobación de PayPal o errores
     */
    #[Route('/api/protected/paypal', name: 'app_paypal', methods: ['POST'])]
    public function createPayment(Request $request): JsonResponse
    {

        // 1. Valida token JWT
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // 2. Decodifica y valida datos de la solicitud
        $data = json_decode($request->getContent(), true);  // Decodifica el JSON recibido
        $cart = $data['cart'] ?? [];
        $totalAmount = $data['totalAmount'] ?? 0;
        $currency = $data['currency'] ?? '';

        // 3. Validacion de datos
        if (!is_numeric($totalAmount) || $totalAmount <= 0) {
            return new JsonResponse(['error' => 'El monto total debe ser un número válido mayor que cero.'], 400);
        }
    
        $validCurrencies = ['USD', 'EUR', 'GBP'];
        if (!in_array($currency, $validCurrencies)) {
            return new JsonResponse(['error' => 'Moneda no válida. Las monedas permitidas son: ' . implode(', ', $validCurrencies)], 400);
        }
    
        // 4. Genera URLs de redirección
        $returnUrl = $this->generateUrl('execute_payment', ['cart' => json_encode($cart)], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('cancel_payment', [], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            // 5. Crea pago en PayPal
            $payment = $this->payPalService->createPayment($totalAmount, $currency, $returnUrl, $cancelUrl);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al crear el pago: ' . $e->getMessage()], 500);
        }
    
        // 6. Obtiene URL de aprobación
        if ($payment) {
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() == 'approval_url') {
                    // Devolver la URL de aprobación de PayPal
                    return new JsonResponse(['approvalUrl' => $link->getHref()]);
                }
            }
        }
    
        return new JsonResponse(['error' => 'Error al crear el pago'], 400);
    }
    

    /**
     * Ejecuta un pago aprobado de PayPal
     * 
     * @Route("/api/protected/paypal/execute", name="execute_payment", methods={"GET"})
     * 
     * @param Request $request Contiene parámetros de PayPal:
     *        - paymentId: string ID del pago
     *        - PayerID: string ID del pagador
     *        - cart: string JSON con items del carrito
     * @return Response HTML con script para cerrar ventana y notificar éxito
     */
    #[Route('/api/protected/paypal/execute', name: 'execute_payment', methods: ['GET'])]
    public function executePayment(Request $request): Response
    {
        // 1. Valida token JWT
        if (!$this->isValidToken($request)) {
            return new Response('Acceso no autorizado', 401);
        }
    
        // 2. Obtiene parámetros de PayPal
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');
        $cart = json_decode($request->get('cart'), true);
    
        // 3. Valida parámetros obligatorios
        if (!$paymentId || !$payerId) {
            return new Response('Faltan parámetros para ejecutar el pago', 400);
        }
    
        if (!$cart) {
            return new Response('Carrito no válido o no recibido', 400);
        }
    
        // 4. Obtiene usuario desde refresh token
        $user = $this->getUserFromRefreshToken($request);
        if (!$user) {
            return new Response('Usuario no encontrado o token inválido', 400);
        }
    
        $payment = $this->payPalService->executePayment($paymentId, $payerId);
    
        if ($payment) {
            // 5. Ejecuta pago en PayPal
            $totalAmount = $payment->getTransactions()[0]->getAmount()->getTotal();
    
            // 6. Inicia transacción de base de datos
            $this->entityManager->beginTransaction();
    
            try {
                foreach ($cart as $item) {
                    $producto = $this->productoRepository->find($item['id']);
    
                    if ($producto) {

                        // 7. Valida stock
                        if ($producto->getStock() <= 0) {
                            throw new \Exception('No hay stock para: ' . $producto->getNombre());
                        }
    
                        // 8. Registra venta
                        $venta = new Venta();
                        $venta->setCantidad(1);
                        $venta->setFecha(new \DateTime());
                        $venta->setTotal($producto->getPrecio());
                        $venta->setCodProducto($producto);
                        $venta->setCodUsuario($user);
    
                        // 9. Actualiza stock
                        $this->entityManager->persist($venta);
                        $producto->setStock($producto->getStock() - 1);
                        $this->entityManager->persist($producto);
                    }
                }
    
                // 10. Confirma transacción
                $this->entityManager->flush();
                $this->entityManager->commit();
    
                // 11. Preparar respuesta para frontend (script cerrar ventana)
                return new Response(
                    '<html><script>
                        // Enviar un mensaje a la ventana principal para notificar el éxito
                        window.opener.postMessage({ action: "paymentSuccess", totalAmount: "' . $totalAmount . '", transactionId: "' . $payment->getId() . '" }, "*");
                        // Cerrar la ventana emergente
                        window.close();
                    </script></html>',
                    200,
                    ['Content-Type' => 'text/html']
                );
            } catch (\Exception $e) {
                $this->entityManager->rollback();
                return new Response('Error en el proceso: ' . $e->getMessage(), 500);
            }
        }
    
        return new Response('Error en el pago', 400);
    }    
    
    /**
     * Maneja la cancelación de pagos
     * 
     * @Route("/api/protected/paypal/cancel", name="cancel_payment", methods={"GET"})
     * 
     * @param Request $request Objeto Request
     * @return JsonResponse Mensaje de cancelación
     */
    #[Route('/api/protected/paypal/cancel', name: 'cancel_payment', methods: ['GET'])]
    public function cancelPayment(Request $request): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        return new JsonResponse(['message' => 'El pago fue cancelado']);
    }

    //////////////////////////////////////// Método para validar los JWT /////////7/////////////////////////////////////////

    /**
     * Valida el token JWT
     * 
     * @param Request $request Objeto Request
     * @return bool True si el token es válido
     */
    private function isValidToken(Request $request): bool
    {
        // 1. Busca token en cookies
        $token = $request->cookies->get('access_token');

        // 2. Busca en headers si no está en cookies
        if (!$token) {
            $authHeader = $request->headers->get('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        // 3. Valida existencia del token
        if (!$token) {
            return false;
        }

        try {
            // 4. Decodifica token
            $payload = $this->jwtManager->parse($token);

            // 5. Valida estructura del payload
            if (!$payload || !isset($payload['id'])) {
                return false;
            }

            // 6. Verifica existencia de usuario
            $usuario = $this->userRepository->find($payload['id']);
            return $usuario !== null;
        } catch (\Exception $e) {
            // Si hay un error en el proceso de decodificación, consideramos el token inválido
            return false;
        }
    }

    ////////////////////////// obtener token de refresco del usuario /////////////////////////////////

    /**
     * Obtiene usuario desde refresh token
     * 
     * @param Request $request Objeto Request
     * @return Usuario|null Usuario encontrado o null
     */
    private function getUserFromRefreshToken(Request $request): ?Usuario
    {
        // 1. Obtiene token de refresco
        $refreshToken = $request->cookies->get('refresh_token');
    
        // Si no está en las cookies, devolver null
        if (!$refreshToken) {
            return null;
        }
    
        // 2. Busca usuario en base de datos
        $usuario = $this->userRepository->findOneBy(['RefreshToken' => $refreshToken]);
    
        return $usuario;
    }

}