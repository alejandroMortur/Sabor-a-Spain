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

    public function __construct(string $clientId, string $secret, bool $sandbox)
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($clientId, $secret)
        );
    
        // Configuración de entorno de PayPal (sandbox o producción)
        $this->apiContext->setConfig([
            'mode' => $sandbox ? 'sandbox' : 'live',
        ]);
    }

    public function createPayment($totalAmount, $currency, $returnUrl, $cancelUrl)
    {
        // Crear objeto Payer
        $payer = new Payer();
        $payer->setPaymentMethod('paypal'); // Establecer método de pago

        // Crear objeto Amount
        $amount = new Amount();
        $amount->setTotal($totalAmount); // Establecer el total
        $amount->setCurrency($currency); // Establecer la moneda

        // Crear objeto Transaction
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setDescription("Pago de prueba");

        // Asegurarse de que setTransactions recibe un array de transacciones
        $transactions = [$transaction];  // Debería ser un array de transacciones

        // Crear objeto RedirectUrls
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl)
                     ->setCancelUrl($cancelUrl);

        // Crear objeto Payment
        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions($transactions) // Asegúrate de pasar un array de transacciones
                ->setRedirectUrls($redirectUrls);

        try {
            // Intentar crear el pago en PayPal
            $payment->create($this->apiContext);
            return $payment;  // Devolver el pago creado
        } catch (\PayPal\Exception\PayPalConnectionException $e) {
            // Si hay un error de conexión, devolver el mensaje
            throw new \Exception("Error al crear el pago: " . $e->getMessage());
        }
    }

    public function executePayment($paymentId, $payerId)
    {
        $payment = Payment::get($paymentId, $this->apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        try {
            // Ejecutar el pago
            $payment->execute($execution, $this->apiContext);
            return $payment;
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            return null;
        }
    }
}


