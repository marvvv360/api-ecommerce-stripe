<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\HttpClient\CurlClient; 
use Stripe\ApiRequestor;         
use OpenApi\Attributes as OA;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

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
        'items' => 'required|array|min:1',
        'items.*.id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.name' => 'required|string',
    ]);

    try {
        Stripe::setApiKey(config('services.stripe.secret'));

        $curlClient = new CurlClient([
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        ApiRequestor::setHttpClient($curlClient);

        // Mapear los ítems para Stripe y calcular el total general
        $lineItems = [];
        $totalOrder = 0;

        foreach ($request->items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $totalOrder += $subtotal;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => intval($item['price'] * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => 'http://localhost:3000/orders?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:3000/cart',
        ]);

        $user = $request->user();

        // Registrar la orden y todos sus ítems en la base de datos
        DB::transaction(function () use ($user, $request, $session, $totalOrder) {
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $totalOrder,
                'status' => 'pending', // Cambiará a pagado con el webhook
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        });

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

/**
 * @OA\Post(
 *     path="/api/payment/create-session",
 *     summary="Crear una sesión de pago en Stripe",
 *     tags={"Pagos"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"amount","product_name"},
 *             @OA\Property(property="amount", type="number", example=25.5),
 *             @OA\Property(property="product_name", type="string", example="Suscripción Premium")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Sesión creada exitosamente",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(response=401, description="No autorizado")
 * )
 */