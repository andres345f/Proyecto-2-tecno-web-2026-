<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfertaAcademica extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ofertas_academicas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
    ];

    /**
     * Get the materias for this oferta academica via malla curricular.
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'malla_curricular')
            ->withPivot('semestre_orden')
            ->withTimestamps();
    }

    /**
     * Get the malla curricular entries for this oferta academica.
     */
    public function mallaCurricular(): HasMany
    {
        return $this->hasMany(MallaCurricular::class);
    }

    /**
     * Get the planes de pago for this oferta academica.
     */
    public function planesPago(): HasMany
    {
        return $this->hasMany(PlanPago::class);
    }

    /**
     * Get the periodos academicos for this oferta academica.
     */
    public function periodosAcademicos(): HasMany
    {
        return $this->hasMany(PeriodoAcademico::class);
    }
}
