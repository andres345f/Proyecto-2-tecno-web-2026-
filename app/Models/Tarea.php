<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tareas';

    protected $fillable = [
        'grupo_periodo_id',
        'titulo',
        'descripcion',
        'fecha_vencimiento',
        'puntaje_maximo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'datetime',
            'puntaje_maximo' => 'decimal:2',
        ];
    }

    public function grupoPeriodo(): BelongsTo
    {
        return $this->belongsTo(GrupoPeriodo::class);
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class);
    }
}
