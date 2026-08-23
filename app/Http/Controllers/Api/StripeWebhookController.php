<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $event = null;

        try {
            // Verificar la firma del webhook
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Payload inválido
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(SignatureVerificationException $e) {
            // Firma inválida
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Manejar el evento
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                // Aquí actualizas tu base de datos (ej. marcar orden como pagada)
                Log::info('Pago completado para la sesión: ' . $session->id);
                // Order::where('session_id', $session->id)->update(['status' => 'paid']);
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                Log::error('Pago fallido: ' . $paymentIntent->id);
                break;
            // Manejar otros eventos según necesidad...
            default:
                Log::info('Evento de Stripe no manejado: ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }
}