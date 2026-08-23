<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "E-Commerce API Segura",
    description: "API RESTful para comercio electrónico con Laravel, JWT y Stripe."
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Servidor Local de Desarrollo"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "Authorization",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Ingresa tu token JWT con el formato: Bearer {token}"
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}