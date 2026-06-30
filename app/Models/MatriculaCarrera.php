<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatriculaCarrera extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matriculas_carrera';

    protected $fillable = [
        'usuario_id',
        'oferta_academica_id',
        'fecha_matricula',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_matricula' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ofertaAcademica(): BelongsTo
    {
        return $this->belongsTo(OfertaAcademica::class);
    }

    public function matriculasPeriodo(): HasMany
    {
        return $this->hasMany(MatriculaPeriodo::class);
    }
}
