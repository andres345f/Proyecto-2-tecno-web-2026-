<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrupoPeriodo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grupo_periodo';

    protected $fillable = [
        'grupo_id',
        'periodo_academico_id',
        'docente_id',
        'cupo_maximo',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'grupo_periodo_id');
    }

    public function matriculasGrupo(): HasMany
    {
        return $this->hasMany(MatriculaGrupo::class, 'grupo_periodo_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'grupo_periodo_id');
    }
}
