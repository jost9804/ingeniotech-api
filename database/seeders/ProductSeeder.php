<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Portátil HP 15 — Ryzen 5, 8GB, 512GB SSD',
                'description' => 'Ideal para trabajo y estudio. Rápido, liviano y con garantía.',
                'price' => 1890000,
                'category' => 'Computadores',
                'featured' => true,
            ],
            [
                'name' => 'PC de Escritorio Intel i5 + Monitor 22"',
                'description' => 'Equipo completo listo para usar, ensamblado y configurado.',
                'price' => 2150000,
                'category' => 'Computadores',
            ],
            [
                'name' => 'Samsung Galaxy A15 128GB',
                'description' => 'Pantalla AMOLED, buena cámara y batería para todo el día.',
                'price' => 650000,
                'category' => 'Celulares',
            ],
            [
                'name' => 'Xiaomi Redmi 13C 128GB',
                'description' => 'Gran relación precio-calidad. Nuevo, con garantía.',
                'price' => 520000,
                'category' => 'Celulares',
            ],
            [
                'name' => 'Cámara IP WiFi 1080p Interior',
                'description' => 'Monitorea desde tu celular. Visión nocturna y detección de movimiento.',
                'price' => 95000,
                'category' => 'Cámaras',
                'featured' => true,
            ],
            [
                'name' => 'Kit 4 Cámaras + DVR 1TB',
                'description' => 'Sistema de videovigilancia completo. Incluye instalación opcional.',
                'price' => 850000,
                'category' => 'Cámaras',
            ],
            [
                'name' => 'Disco SSD 480GB SATA',
                'description' => 'Dale nueva vida a tu equipo. Instalación incluida.',
                'price' => 165000,
                'category' => 'Accesorios',
            ],
            [
                'name' => 'Memoria RAM 8GB DDR4',
                'description' => 'Más velocidad y multitarea para tu computador.',
                'price' => 120000,
                'category' => 'Accesorios',
            ],
            [
                'name' => 'Combo Teclado + Mouse Inalámbrico',
                'description' => 'Cómodo y sin cables. Perfecto para tu escritorio.',
                'price' => 75000,
                'category' => 'Accesorios',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
