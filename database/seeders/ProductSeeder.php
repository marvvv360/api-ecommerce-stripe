<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Gamer Pro 15',
                'description' => 'Procesador Intel i7, 16GB RAM, SSD 1TB, RTX 4060',
                'price' => 1299.99,
                'stock' => 10,
            ],
            [
                'name' => 'Monitor 27" 4K IPS',
                'description' => 'Resolución 3840x2160, HDR400, Tasa de refresco 144Hz',
                'price' => 349.50,
                'stock' => 25,
            ],
            [
                'name' => 'Teclado Mecánico RGB',
                'description' => 'Switches Red lineales, retroiluminación RGB individual',
                'price' => 89.90,
                'stock' => 50,
            ],
            [
                'name' => 'Mouse Inalámbrico Ergológico',
                'description' => 'Sensor óptico 26K DPI, batería recargable, Bluetooth/2.4G',
                'price' => 59.99,
                'stock' => 40,
            ],
            [
                'name' => 'Auriculares Studio Wireless',
                'description' => 'Cancelación de ruido activa, sonido de alta fidelidad, 30h de batería',
                'price' => 199.00,
                'stock' => 15,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}