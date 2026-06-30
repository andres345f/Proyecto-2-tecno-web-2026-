<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MallaCurricular extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'malla_curricular';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'oferta_academica_id',
        'materia_id',
        'semestre_orden',
    ];

    /**
     * Get the oferta academica that owns this malla entry.
     */
    public function ofertaAcademica(): BelongsTo
    {
        return $this->belongsTo(OfertaAcademica::class);
    }

    /**
     * Get the materia that owns this malla entry.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Get the prerequisites for this curricular entry.
     */
    public function prerrequisitos(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            MallaCurricular::class,
            'materia_prerequisito',
            'malla_curricular_id',
            'prerequisito_malla_id'
        );
    }

    /**
     * Get the curricular entries that require this entry as a prerequisite.
     */
    public function esPrerequisitoDe(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            MallaCurricular::class,
            'materia_prerequisito',
            'prerequisito_malla_id',
            'malla_curricular_id'
        );
    }
}
