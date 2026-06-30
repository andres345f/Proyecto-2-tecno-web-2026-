<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatriculaPeriodo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matriculas_periodo';

    protected $fillable = [
        'matricula_carrera_id',
        'periodo_academico_id',
        'plan_pago_id',
        'fecha_matricula',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_matricula' => 'datetime',
        ];
    }

    public function matriculaCarrera(): BelongsTo
    {
        return $this->belongsTo(MatriculaCarrera::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function planPago(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    public function matriculasGrupo(): HasMany
    {
        return $this->hasMany(MatriculaGrupo::class);
    }
}
