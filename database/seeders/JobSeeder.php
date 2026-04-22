<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            [
                'client_name' => 'Carlos Mendez',
                'client_phone' => '+57 301 234 5678',
                'device_type' => 'computador',
                'problem_description' => 'Laptop no enciende',
                'status' => 'recibido',
                'assigned_to' => 2,
                'progress' => 0,
                'notes' => 'Cliente reporta que se apagó sin razón aparente',
            ],
            [
                'client_name' => 'Maria Garcia',
                'client_phone' => '+57 302 345 6789',
                'device_type' => 'celular',
                'problem_description' => 'Pantalla rota',
                'status' => 'en_reparacion',
                'assigned_to' => 2,
                'progress' => 50,
                'price' => 80000,
                'notes' => 'Pantalla pedida, en espera de llegada',
            ],
            [
                'client_name' => 'Juan Rodriguez',
                'client_phone' => '+57 303 456 7890',
                'device_type' => 'camara',
                'problem_description' => 'Instalación de cámaras de seguridad',
                'status' => 'en_diagnostico',
                'assigned_to' => 2,
                'progress' => 25,
                'notes' => 'Visita realizada, presupuesto enviado',
            ],
            [
                'client_name' => 'Ana Perez',
                'client_phone' => '+57 304 567 8901',
                'device_type' => 'computador',
                'problem_description' => 'Formateo e instalación Windows',
                'status' => 'listo',
                'assigned_to' => 2,
                'progress' => 100,
                'price' => 120000,
                'notes' => 'Listo para recoger',
            ],
            [
                'client_name' => 'David Lopez',
                'client_phone' => '+57 305 678 9012',
                'device_type' => 'celular',
                'problem_description' => 'Batería no carga',
                'status' => 'entregado',
                'assigned_to' => 2,
                'progress' => 100,
                'price' => 45000,
                'notes' => 'Cliente retiró hace 2 días, en garantía',
            ],
            [
                'client_name' => 'Rosa Martinez',
                'client_phone' => '+57 306 789 0123',
                'device_type' => 'otro',
                'problem_description' => 'Reparación de impresora',
                'status' => 'recibido',
                'assigned_to' => 2,
                'progress' => 0,
                'notes' => 'Espera en cola',
            ],
        ];

        foreach ($jobs as $job) {
            Job::create($job);
        }
    }
}
