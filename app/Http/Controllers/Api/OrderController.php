<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class OrderController extends Controller
{
    #[OA\Get(
        path: "/api/orders",
        summary: "Listar las órdenes del usuario autenticado",
        security: [["bearerAuth" => []]],
        tags: ["Órdenes"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de órdenes obtenida correctamente"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated - Requiere Token JWT"
    )]
    public function index()
    {
        // Obtener solo las órdenes del usuario logueado
        $orders = Order::where('user_id', Auth::id())->with('items.product')->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ], 200);
    }

    #[OA\Post(
        path: "/api/orders",
        summary: "Crear orden y procesar pago",
        security: [["bearerAuth" => []]],
        tags: ["Órdenes"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["items", "payment_method_id"],
            properties: [
                new OA\Property(
                    property: "items",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "product_id", type: "integer", example: 1),
                            new OA\Property(property: "quantity", type: "integer", example: 2)
                        ]
                    )
                ),
                new OA\Property(property: "payment_method_id", type: "string", example: "pm_card_visa")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Orden creada y pago procesado con éxito"
    )]
    #[OA\Response(
        response: 400,
        description: "Error al procesar el pago con Stripe"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated - Requiere Token JWT"
    )]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Calcular el total real basado en los productos de la base de datos
            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $total += $product->price * $item['quantity'];
            }

            // Procesar el pago directamente con Stripe PaymentIntent
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Solución para ignorar el certificado SSL en entorno local de desarrollo
            \Stripe\ApiRequestor::setHttpClient(new \Stripe\HttpClient\CurlClient([CURLOPT_SSL_VERIFYPEER => false]));
            
            $paymentIntent = PaymentIntent::create([
                'amount' => intval($total * 100), // En centavos
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never'
                ],
            ]);

            // Crear la orden en la base de datos si el pago es exitoso
            $order = Order::create([
                'user_id' => Auth::id(),
                'total' => $total,
                'status' => 'completed',
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Orden procesada y pagada correctamente',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el pago: ' . $e->getMessage()
            ], 400);
        }
    }
}