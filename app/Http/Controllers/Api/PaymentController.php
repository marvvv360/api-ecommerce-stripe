<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\HttpClient\CurlClient; // <--- 1. Importar CurlClient
use Stripe\ApiRequestor;          // <--- 2. Importar ApiRequestor
use OpenApi\Attributes as OA;

class PaymentController extends Controller
{
    #[OA\Post(
        path: "/api/payment/create-session",
        summary: "Crear una sesión de pago en Stripe",
        security: [["bearerAuth" => []]],
        tags: ["Pagos"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["amount", "product_name"],
            properties: [
                new OA\Property(property: "amount", type: "number", format: "float", example: 25.50, description: "Monto a cobrar"),
                new OA\Property(property: "product_name", type: "string", example: "Suscripción Premium", description: "Nombre del producto o servicio")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Sesión creada exitosamente"
    )]
    #[OA\Response(
        response: 401,
        description: "No autorizado - Token ausente o inválido"
    )]
    #[OA\Response(
        response: 422,
        description: "Error de validation"
    )]
    #[OA\Response(
        response: 500,
        description: "Error interno de Stripe"
    )]
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.50',
            'product_name' => 'required|string|max:255',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // <--- 3. Desactivar la verificación SSL solo para entorno de desarrollo local
            $curlClient = new CurlClient([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            ApiRequestor::setHttpClient($curlClient);

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $request->product_name,
                        ],
                        'unit_amount' => intval($request->amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/api/payment/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/api/payment/cancel'),
            ]);

            return response()->json([
                'status' => 'success',
                'session_url' => $session->url,
                'session_id' => $session->id
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}