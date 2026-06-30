<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Entrega extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entregas';

    protected $fillable = [
        'tarea_id',
        'usuario_id',
        'ruta_archivo',
        'fecha_entrega',
        'nota',
        'retroalimentacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'datetime',
            'nota' => 'decimal:2',
        ];
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getArchivoUrlAttribute(): ?string
    {
        if (! $this->ruta_archivo) {
            return null;
        }

        return Storage::disk('local')->url($this->ruta_archivo);
    }
}
