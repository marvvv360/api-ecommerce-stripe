# Sistema de E-Commerce y Pagos con Stripe API

API RESTful desarrollada en Laravel 12 para la gestión de un catálogo de productos, procesamiento de pagos con Stripe y control de órdenes de compra, utilizando autenticación basada en tokens con JWT y documentación interactiva mediante Swagger UI.

> **Nota de Arquitectura:** Este repositorio funciona de la mano con el cliente oficial [frontend-ecommerce](https://github.com/marvvv360/frontend-ecommerce.git) desarrollado en Next.js, encargándose de proveer toda la lógica de negocio, autenticación segura, pasarela de pago y persistencia de datos.

## Características

- Autenticación JWT: Registro, inicio de sesión, perfil de usuario, renovación de tokens y cierre de sesión seguro (blacklist).
- Gestión de Catálogo (Productos): Creación, lectura, actualización y eliminación de productos con control de stock y precios.
- Pasarela de Pagos (Stripe): Integración con Stripe para procesar cobros de forma segura.
- Control de Órdenes y Multi-Producto: Lógica robusta en el controlador de pagos para validar y registrar arreglos de ítems (`id`, `quantity`, `price`, `name`), asociando los productos adquiridos por cada usuario.
- Documentación Interactiva: Endpoints documentados y accesibles mediante Swagger UI.

---

## Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Extensión OpenSSL de PHP activa

---

## Instalación y Configuración

1. Clonar el repositorio:
   git clone https://github.com/marvvv360/api-ecommerce-stripe.git
   cd api-ecommerce-stripe

2. Instalar dependencias:
   composer install

3. Configurar variables de entorno:
   cp .env.example .env

   Configura la conexión a la base de datos y tu llave secreta de Stripe dentro del archivo .env:
   DB_DATABASE=tu_base_de_datos
   DB_USERNAME=root
   DB_PASSWORD=
   STRIPE_SECRET=sk_test_tu_clave_secreta_aqui

4. Generar la clave de la aplicación:
   php artisan key:generate

5. Generar la clave secreta de JWT:
   php artisan jwt:secret

6. Ejecutar migraciones y seeders:
   php artisan migrate:fresh --seed

7. Generar la documentación de Swagger:
   php artisan l5-swagger:generate

8. Iniciar el servidor local:
   php artisan serve

   La API estará corriendo en http://127.0.0.1:8000 y la documentación en http://127.0.0.1:8000/api/documentation.

---

## Documentación de Endpoints y Ejemplos de Verificación

A continuación se detallan las peticiones principales para verificar el funcionamiento de la API desde Postman, Thunder Client, Swagger UI o el cliente Next.js.

### 1. Autenticación

#### Registrar Usuario
- Método: POST
- URL: http://127.0.0.1:8000/api/auth/register
- Headers: Content-Type: application/json
- Body (JSON):
  {
    "name": "Usuario Test",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }

#### Iniciar Sesión (Login)
- Método: POST
- URL: http://127.0.0.1:8000/api/auth/login
- Headers: Content-Type: application/json
- Body (JSON):
  {
    "email": "test@example.com",
    "password": "password123"
  }
- Respuesta esperada (200 OK):
  {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }

#### Obtener Perfil Autenticado
- Método: GET
- URL: http://127.0.0.1:8000/api/auth/me
- Headers: Authorization: Bearer <TU_TOKEN_JWT>
- Respuesta esperada (200 OK):
  {
    "id": 1,
    "name": "Usuario Test",
    "email": "test@example.com"
  }

#### Renovar Token (Refresh)
- Método: POST
- URL: http://127.0.0.1:8000/api/auth/refresh
- Headers: Authorization: Bearer <TU_TOKEN_JWT>
- Respuesta esperada (200 OK):
  Devuelve una nueva estructura con un nuevo token JWT renovado.

#### Cerrar Sesión (Logout)
- Método: POST
- URL: http://127.0.0.1:8000/api/auth/logout
- Headers: Authorization: Bearer <TU_TOKEN_JWT>
- Respuesta esperada (200 OK):
  {
    "message": "Sesión cerrada correctamente"
  }

---

### 2. Gestión de Productos (/api/products)

#### Crear Producto
- Método: POST
- URL: http://127.0.0.1:8000/api/products
- Headers: Authorization: Bearer <TU_TOKEN_JWT>, Content-Type: application/json
- Body (JSON):
  {
    "name": "Camiseta Deportiva",
    "description": "Camiseta de alta calidad para entrenamiento",
    "price": 25.99,
    "stock": 50
  }
- Respuesta esperada (201 Created):
  {
    "status": "success",
    "message": "Producto creado exitosamente",
    "data": {
      "id": 1,
      "name": "Camiseta Deportiva",
      "description": "Camiseta de alta calidad para entrenamiento",
      "price": "25.99",
      "stock": 50
    }
  }

#### Listar Productos
- Método: GET
- URL: http://127.0.0.1:8000/api/products

#### Ver Producto por ID
- Método: GET
- URL: http://127.0.0.1:8000/api/products/1

---

### 3. Gestión de Órdenes y Pagos con Stripe (/api/payment/create-session u órdenes)

#### Procesar Pago y Crear Orden Multi-Producto
- Método: POST
- URL: http://127.0.0.1:8000/api/payment/create-session (o tu endpoint correspondiente)
- Headers: Authorization: Bearer <TU_TOKEN_JWT>, Content-Type: application/json
- Body (JSON):
  {
    "items": [
      {
        "id": 1,
        "quantity": 2,
        "price": 25.99,
        "name": "Camiseta Deportiva"
      }
    ],
    "amount": 51.98,
    "product_name": "Compra de 2 productos en E-Commerce"
  }
- Respuesta esperada (201 / 200 OK):
  Procesa la sesión de pago con Stripe y registra la orden junto con sus ítems asociados de forma persistente en la base de datos.

#### Listar Órdenes del Usuario Autenticado (Historial)
- Método: GET
- URL: http://127.0.0.1:8000/api/orders
- Headers: Authorization: Bearer <TU_TOKEN_JWT>
- Respuesta esperada (200 OK):
  Listado completo de las órdenes históricas asociadas al usuario autenticado, desglosando los productos y estados de cada transacción.