<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/auth/register",
        summary: "Registrar un nuevo cliente",
        tags: ["Autenticación"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Carlos Gómez"),
                new OA\Property(property: "email", type: "string", format: "email", example: "carlos@example.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "12345678")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Usuario registrado exitosamente"
    )]
    #[OA\Response(
        response: 422,
        description: "Error de validación en los datos enviados"
    )]
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario creado exitosamente',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 201);
    }

    #[OA\Post(
        path: "/api/auth/login",
        summary: "Iniciar sesión y obtener Token JWT",
        tags: ["Autenticación"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "carlos@example.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "12345678")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Login exitoso, devuelve Bearer Token"
    )]
    #[OA\Response(
        response: 401,
        description: "Credenciales inválidas"
    )]
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado. Credenciales inválidas.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/api/auth/me",
        summary: "Obtener datos del usuario autenticado",
        security: [["bearerAuth" => []]],
        tags: ["Autenticación"]
    )]
    #[OA\Response(
        response: 200,
        description: "Datos del usuario actual"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated - Token ausente o inválido"
    )]
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ], 200);
    }

    #[OA\Post(
        path: "/api/auth/logout",
        summary: "Cerrar sesión e invalidar Token",
        security: [["bearerAuth" => []]],
        tags: ["Autenticación"]
    )]
    #[OA\Response(
        response: 200,
        description: "Sesión cerrada correctamente"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated - Token ausente o inválido"
    )]
    public function logout()
    {
        $token = JWTAuth::getToken();
        if ($token) {
            JWTAuth::invalidate($token);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente',
        ], 200);
    }
}