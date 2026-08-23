<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear un usuario de prueba
        User::create([
            'name' => 'Usuario Test',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Crear productos de ejemplo
        Product::create([
            'name' => 'Camiseta Deportiva',
            'description' => 'Camiseta de alta calidad para entrenamiento',
            'price' => 25.99,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Zapatillas Running',
            'description' => 'Zapatillas cómodas para correr',
            'price' => 79.99,
            'stock' => 20,
        ]);
    }
}