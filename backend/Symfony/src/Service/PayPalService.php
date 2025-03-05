<?php

// App/Service/PayPalService.php
namespace App\Service;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PayPalService
{
    private $apiContext;

    /**
     * Constructor: Configura las credenciales y entorno de PayPal
     * @param string $clientId    ID de cliente de la API de PayPal
     * @param string $secret      Clave secreta de la API de PayPal
     * @param bool   $sandbox     Modo sandbox (true) o producción (false)
     */
    public function __construct(string $clientId, string $secret, bool $sandbox)
    {
        // 1. Crea contexto de API con credenciales
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($clientId, $secret)
        );
    
        // 2. Configura ambiente (sandbox/live)
        $this->apiContext->setConfig([
            'mode' => $sandbox ? 'sandbox' : 'live',  // Define el entorno
        ]);
    }

    /**
     * Crea un pago en PayPal
     * @param float  $totalAmount  Monto total del pago
     * @param string $currency     Moneda (ej: 'EUR', 'USD')
     * @param string $returnUrl    URL de redirección tras éxito
     * @param string $cancelUrl    URL de redirección tras cancelación
     * @return Payment             Objeto Payment de PayPal
     * @throws \Exception          Si falla la creación del pago
     */
    public function createPayment($totalAmount, $currency, $returnUrl, $cancelUrl)
    {
        // 3. Configura método de pago
        $payer = new Payer();
        $payer->setPaymentMethod('paypal'); // Método: PayPal

        // 4. Configura monto y moneda
        $amount = new Amount();
        $amount->setTotal($totalAmount); // Total a cobrar
        $amount->setCurrency($currency); // Tipo de moneda

        // 5. Crea transacción
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setDescription("Pago de prueba");

        // 6. Prepara lista de transacciones (requiere array aunque sea una sola)
        $transactions = [$transaction]; 

        // 7. Configura URLs de redirección post-pago
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl)  // URL éxito de pago
                     ->setCancelUrl($cancelUrl); // URL fallo/cancelación

        // 8. Construye el objeto pago final
        $payment = new Payment();
        $payment->setIntent('sale') // Transacción de venta inmediata
                ->setPayer($payer) // Asocia método de pago
                ->setTransactions($transactions) // Añade transacción(es)
                ->setRedirectUrls($redirectUrls); // Vincula URLs de redirección

        try {
            // 9. Intenta crear el pago en los servidores de PayPal
            $payment->create($this->apiContext);
            return $payment;  // Devuelve objeto con URLs de aprobación
        } catch (\PayPal\Exception\PayPalConnectionException $e) {
            // 10. Maneja errores de conexión/autenticación
            throw new \Exception("Error al crear el pago: " . $e->getMessage());
        }
    }

    /**
     * Ejecuta un pago previamente aprobado por el usuario
     * @param string $paymentId ID único del pago de PayPal
     * @param string $payerId   ID único del pagador de PayPal
     * @return Payment|null     Objeto Payment completado o null en error
     */
    public function executePayment($paymentId, $payerId)
    {
        // 11. Obtiene el pago existente usando su ID
        $payment = Payment::get($paymentId, $this->apiContext);

        // 12. Prepara la ejecución con el ID del pagador
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId); // Identificador único de usuario

        try {
            // 13. Ejecuta la transacción financiera
            $payment->execute($execution, $this->apiContext);
            return $payment; // Devuelve pago completado con detalles
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // 14. Maneja errores en transacción (fondos insuficientes, etc)
            return null;
        }
    }
}