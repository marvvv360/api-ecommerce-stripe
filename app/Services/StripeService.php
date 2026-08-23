<?php

namespace App\Services;

use Stripe\StripeClient;
use Exception;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret') ?? env('STRIPE_SECRET'));
    }

    /**
     * Procesar un pago directo utilizando un PaymentIntent de Stripe.
     */
    public function createPaymentIntent(float $amount, string $currency, string $paymentMethodId): object
    {
        try {
            return $this->stripe->paymentIntents->create([
                'amount' => (int) ($amount * 100), // Stripe procesa montos en centavos
                'currency' => strtolower($currency),
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);
        } catch (Exception $e) {
            throw new Exception('Error procesando el pago con Stripe: ' . $e->getMessage());
        }
    }
}