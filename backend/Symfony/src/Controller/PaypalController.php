<?php

namespace App\Controller;

use App\Service\PayPalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PaypalController extends AbstractController
{    private $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    #[Route('/api/protected/paypal', name: 'app_paypal', methods: ['POST'])]
    public function createPayment(Request $request): JsonResponse
    {
        // Obtener y validar el monto total del pago
        $totalAmount = $request->get('totalAmount', 100);
    
        if (!is_numeric($totalAmount) || $totalAmount <= 0) {
            return new JsonResponse(['error' => 'El monto total debe ser un número válido mayor que cero.'], 400);
        }
    
        // Obtener y validar la moneda
        $currency = $request->get('currency', 'USD');
        $validCurrencies = ['USD', 'EUR', 'GBP'];
    
        if (!in_array($currency, $validCurrencies)) {
            return new JsonResponse(['error' => 'Moneda no válida. Las monedas permitidas son: ' . implode(', ', $validCurrencies)], 400);
        }
    
        // Generar URLs absolutas
        $returnUrl = $this->generateUrl('execute_payment', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('cancel_payment', [], UrlGeneratorInterface::ABSOLUTE_URL);
    
        try {
            // Crear el pago a través del servicio PayPal
            $payment = $this->payPalService->createPayment($totalAmount, $currency, $returnUrl, $cancelUrl);
        } catch (\Exception $e) {
            // Manejar cualquier error durante la creación del pago
            return new JsonResponse(['error' => 'Error al crear el pago: ' . $e->getMessage()], 500);
        }
    
        // Si el pago fue creado correctamente, devolver la URL de aprobación de PayPal
        if ($payment) {
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() == 'approval_url') {
                    return new JsonResponse(['approvalUrl' => $link->getHref()]);
                }
            }
        }
    
        // Si hubo un error, devolver un error
        return new JsonResponse(['error' => 'Error al crear el pago'], 400);
    }

    #[Route('/api/protected/paypal/execute', name: 'execute_payment', methods: ['GET'])]
    public function executePayment(Request $request): JsonResponse
    {
        // Obtener los parámetros de la URL
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        if (!$paymentId || !$payerId) {
            return new JsonResponse(['error' => 'Faltan parámetros para ejecutar el pago'], 400);
        }

        // Ejecutar el pago
        $payment = $this->payPalService->executePayment($paymentId, $payerId);

        if ($payment) {
            // Si el pago se ha ejecutado con éxito
            return new JsonResponse(['status' => 'success', 'message' => 'Pago realizado correctamente']);
        }

        // Si algo falla, devolver un error
        return new JsonResponse(['error' => 'Error al ejecutar el pago'], 400);  
    }

    #[Route('/api/protected/paypal/cancel', name: 'cancel_payment', methods: ['GET'])]
    public function cancelPayment(): JsonResponse
    {
        // Acción cuando el pago es cancelado por el usuario
        return new JsonResponse(['message' => 'El pago fue cancelado']);
    }
}
