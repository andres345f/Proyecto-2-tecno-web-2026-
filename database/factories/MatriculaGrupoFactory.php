<?php

namespace Database\Factories;

use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use App\Models\GrupoPeriodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatriculaGrupo>
 */
class MatriculaGrupoFactory extends Factory
{
    protected $model = MatriculaGrupo::class;

    public function definition(): array
    {
        return [
            'matricula_periodo_id' => MatriculaPeriodo::factory(),
            'grupo_periodo_id' => GrupoPeriodo::factory(),
            'nota_final' => null,
            'estado' => 'inscrito',
        ];
    }
}
