<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Propietario
        User::create([
            'name' => 'Roberto García',
            'email' => 'propietario@edu.com',
            'password' => Hash::make('password'),
            'is_propietario' => true,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        // Director
        User::create([
            'name' => 'María López',
            'email' => 'director@edu.com',
            'password' => Hash::make('password'),
            'is_propietario' => false,
            'is_director' => true,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        // Secretaria
        User::create([
            'name' => 'Laura Fernández',
            'email' => 'secretaria@edu.com',
            'password' => Hash::make('password'),
            'is_propietario' => false,
            'is_director' => false,
            'is_secretaria' => true,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        // Profesor
        User::create([
            'name' => 'Prof. Juan Martínez',
            'email' => 'juan@doc.com',
            'password' => Hash::make('password'),
            'is_propietario' => false,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => true,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        // Estudiantes
        User::create([
            'name' => 'Carlos Gómez',
            'email' => 'carlos@est.com',
            'password' => Hash::make('password'),
            'codigo_estudiante' => 'EST-' . (10000 + 0),
            'is_propietario' => false,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        User::create([
            'name' => 'Ana Pérez',
            'email' => 'ana@est.com',
            'password' => Hash::make('password'),
            'codigo_estudiante' => 'EST-' . (10000 + 1),
            'is_propietario' => false,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        User::create([
            'name' => 'David Rojas',
            'email' => 'david@est.com',
            'password' => Hash::make('password'),
            'codigo_estudiante' => 'EST-' . (10000 + 2),
            'is_propietario' => false,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => true,
            'is_activo' => true,
        ]);
    }
}
