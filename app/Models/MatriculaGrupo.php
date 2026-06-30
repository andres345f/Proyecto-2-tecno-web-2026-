<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriculaGrupo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matriculas_grupo';

    protected $fillable = [
        'matricula_periodo_id',
        'grupo_periodo_id',
        'nota_final',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'nota_final' => 'decimal:2',
        ];
    }

    public function matriculaPeriodo(): BelongsTo
    {
        return $this->belongsTo(MatriculaPeriodo::class);
    }

    public function grupoPeriodo(): BelongsTo
    {
        return $this->belongsTo(GrupoPeriodo::class);
    }
}
