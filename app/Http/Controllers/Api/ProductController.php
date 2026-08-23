<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: "/api/products",
        summary: "Obtener catálogo de productos",
        tags: ["Productos"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de productos obtenida correctamente"
    )]
    public function index()
    {
        $products = Product::all();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
    }

    #[OA\Post(
        path: "/api/products",
        summary: "Crear producto",
        security: [["bearerAuth" => []]],
        tags: ["Productos"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "description", "price", "stock"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Camiseta Deportiva"),
                new OA\Property(property: "description", type: "string", example: "Camiseta de alta calidad para entrenamiento"),
                new OA\Property(property: "price", type: "number", format: "float", example: 25.99),
                new OA\Property(property: "stock", type: "integer", example: 50)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Producto creado correctamente"
    )]
    #[OA\Response(
        response: 422,
        description: "Error de validación en los datos del producto"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated - Requiere Token JWT"
    )]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Producto creado correctamente',
            'data' => $product
        ], 201);
    }
    
    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Obtener un producto específico",
        tags: ["Productos"]
    )]
    #[OA\Response(
        response: 200,
        description: "Producto encontrado exitosamente"
    )]
    #[OA\Response(
        response: 404,
        description: "Producto no encontrado"
    )]
    public function show(int $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 200);
    }

    #[OA\Put(
        path: "/api/products/{id}",
        summary: "Actualizar un producto existente",
        security: [["bearerAuth" => []]],
        tags: ["Productos"]
    )]
    #[OA\Response(
        response: 200,
        description: "Producto actualizado correctamente"
    )]
    #[OA\Response(
        response: 404,
        description: "Producto no encontrado"
    )]
    #[OA\Response(
        response: 422,
        description: "Error de validación"
    )]
    public function update(Request $request, int $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Producto actualizado correctamente',
            'data' => $product
        ], 200);
    }

    #[OA\Delete(
        path: "/api/products/{id}",
        summary: "Eliminar un producto",
        security: [["bearerAuth" => []]],
        tags: ["Productos"]
    )]
    #[OA\Response(
        response: 200,
        description: "Producto eliminado correctamente"
    )]
    #[OA\Response(
        response: 404,
        description: "Producto no encontrado"
    )]
    public function destroy(int $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Producto eliminado correctamente'
        ], 200);
    }
}