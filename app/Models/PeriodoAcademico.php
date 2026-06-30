<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodoAcademico extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'periodos_academicos';

    protected $fillable = [
        'oferta_academica_id',
        'nombre',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'fecha_inicio_inscripcion',
        'fecha_fin_inscripcion',
        'fecha_inicio_cierre',
        'fecha_fin_cierre',
        'fecha_inicio_retiro',
        'fecha_fin_retiro',
        'numero_maximo_materias',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_inicio_inscripcion' => 'date',
            'fecha_fin_inscripcion' => 'date',
            'fecha_inicio_cierre' => 'date',
            'fecha_fin_cierre' => 'date',
            'fecha_inicio_retiro' => 'date',
            'fecha_fin_retiro' => 'date',
        ];
    }

    public function ofertaAcademica(): BelongsTo
    {
        return $this->belongsTo(OfertaAcademica::class);
    }

    public function grupoPeriodos(): HasMany
    {
        return $this->hasMany(GrupoPeriodo::class, 'periodo_academico_id');
    }
}
