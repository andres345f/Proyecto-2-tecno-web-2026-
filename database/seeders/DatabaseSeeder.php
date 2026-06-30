<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            RoleSeeder::class,
            MateriaSeeder::class,
            OfertaAcademicaSeeder::class,
            MallaCurricularSeeder::class,
            PeriodoAcademicoSeeder::class,
            PlanPagoSeeder::class,
            AulaSeeder::class,
            GrupoSeeder::class,
            HorarioSeeder::class,
            MatriculaCarreraSeeder::class,
            MatriculaPeriodoSeeder::class,
            MatriculaGrupoSeeder::class,
            TareaSeeder::class,
            EntregaSeeder::class,
            CuotaSeeder::class,
            PagoSeeder::class,
        ]);
    }
}
