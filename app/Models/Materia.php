<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materia extends Model
{
    use HasFactory, SoftDeletes;

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
     * Get the malla curricular entries for this materia.
     */
    public function mallaCurricular(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MallaCurricular::class, 'materia_id');
    }

    /**
     * Get the ofertas academicas that include this materia via malla curricular.
     */
    public function ofertasAcademicas(): BelongsToMany
    {
        return $this->belongsToMany(OfertaAcademica::class, 'malla_curricular')
            ->withPivot('semestre_orden')
            ->withTimestamps();
    }
}
