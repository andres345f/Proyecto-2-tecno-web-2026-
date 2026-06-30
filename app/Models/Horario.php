<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'grupo_periodo_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'aula_id',
    ];

    public function grupoPeriodo(): BelongsTo
    {
        return $this->belongsTo(GrupoPeriodo::class);
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }
}
