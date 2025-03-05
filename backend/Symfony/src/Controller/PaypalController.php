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


final class PaypalController extends AbstractController
{    
    private $payPalService;
    private $entityManager;
    private $productoRepository;
    private UsuarioRepository $userRepository;
    private JWTTokenManagerInterface $jwtManager;

    // Inyectamos EntityManagerInterface y el repositorio de Productos
    public function __construct(JWTTokenManagerInterface $jwtManager ,UsuarioRepository $userRepository ,PayPalService $payPalService, EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
        $this->payPalService = $payPalService;
        $this->entityManager = $entityManager;
        $this->productoRepository = $doctrine->getRepository(Productos::class);
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/protected/paypal', name: 'app_paypal', methods: ['POST'])]
    public function createPayment(Request $request): JsonResponse
    {

        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        // Obtener datos del carrito y la moneda
        $data = json_decode($request->getContent(), true);  // Decodifica el JSON recibido
    
        $cart = $data['cart'] ?? [];
        $totalAmount = $data['totalAmount'] ?? 0;
        $currency = $data['currency'] ?? '';
    
        dump($totalAmount);  // Verificar el valor recibido
    
        if (!is_numeric($totalAmount) || $totalAmount <= 0) {
            return new JsonResponse(['error' => 'El monto total debe ser un número válido mayor que cero.'], 400);
        }
    
        $validCurrencies = ['USD', 'EUR', 'GBP'];
        if (!in_array($currency, $validCurrencies)) {
            return new JsonResponse(['error' => 'Moneda no válida. Las monedas permitidas son: ' . implode(', ', $validCurrencies)], 400);
        }
    
        // Generar URLs para redirección de PayPal
        $returnUrl = $this->generateUrl('execute_payment', ['cart' => json_encode($cart)], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('cancel_payment', [], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            // Crear el pago a través del servicio PayPal
            $payment = $this->payPalService->createPayment($totalAmount, $currency, $returnUrl, $cancelUrl);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al crear el pago: ' . $e->getMessage()], 500);
        }
    
        // Si el pago fue creado correctamente, devolver la URL de aprobación de PayPal
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
    
    #[Route('/api/protected/paypal/execute', name: 'execute_payment', methods: ['GET'])]
    public function executePayment(Request $request): Response
    {
        if (!$this->isValidToken($request)) {
            return new Response('Acceso no autorizado', 401);
        }
    
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');
        $cart = json_decode($request->get('cart'), true);
    
        if (!$paymentId || !$payerId) {
            return new Response('Faltan parámetros para ejecutar el pago', 400);
        }
    
        if (!$cart) {
            return new Response('Carrito no válido o no recibido', 400);
        }
    
        $user = $this->getUserFromRefreshToken($request);
        if (!$user) {
            return new Response('Usuario no encontrado o token inválido', 400);
        }
    
        $payment = $this->payPalService->executePayment($paymentId, $payerId);
    
        if ($payment) {
            $totalAmount = $payment->getTransactions()[0]->getAmount()->getTotal();
    
            $this->entityManager->beginTransaction();
    
            try {
                foreach ($cart as $item) {
                    $producto = $this->productoRepository->find($item['id']);
    
                    if ($producto) {
                        if ($producto->getStock() <= 0) {
                            throw new \Exception('No hay stock para: ' . $producto->getNombre());
                        }
    
                        $venta = new Venta();
                        $venta->setCantidad(1);
                        $venta->setFecha(new \DateTime());
                        $venta->setTotal($producto->getPrecio());
                        $venta->setCodProducto($producto);
                        $venta->setCodUsuario($user);
    
                        $this->entityManager->persist($venta);
                        $producto->setStock($producto->getStock() - 1);
                        $this->entityManager->persist($producto);
                    }
                }
    
                $this->entityManager->flush();
                $this->entityManager->commit();
    
                // Preparar la URL de redirección con parámetros
                $homeUrl = 'http://localhost:4201/home';
                $urlWithParams = sprintf(
                    '%s?status=success&totalAmount=%s&transactionId=%s',
                    $homeUrl,
                    urlencode($totalAmount),
                    urlencode($payment->getId())
                );
    
                // Devolver el HTML con JavaScript para cerrar la ventana emergente y redirigir
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
    
    #[Route('/api/protected/paypal/cancel', name: 'cancel_payment', methods: ['GET'])]
    public function cancelPayment(Request $request): JsonResponse
    {
        if (!$this->isValidToken($request)) {
            return new JsonResponse(['message' => 'Acceso no autorizado'], 401);
        }

        return new JsonResponse(['message' => 'El pago fue cancelado']);
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

    ////////// obtener token de refresco del usuario /////////////////////////////////
    private function getUserFromRefreshToken(Request $request): ?Usuario
    {
        // Obtener el refresh_token desde las cookies
        $refreshToken = $request->cookies->get('refresh_token');
    
        // Si no está en las cookies, devolver null
        if (!$refreshToken) {
            return null;
        }
    
        // Buscar al usuario con el refresh_token en la base de datos
        $usuario = $this->userRepository->findOneBy(['RefreshToken' => $refreshToken]);
    
        return $usuario;
    }

}

