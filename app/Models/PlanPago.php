<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPago extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'planes_pago';

    protected $fillable = [
        'oferta_academica_id',
        'nombre',
        'tipo',
        'monto_matricula',
        'monto_cuota',
        'cantidad_cuotas',
    ];

    protected function casts(): array
    {
        return [
            'monto_matricula' => 'decimal:2',
            'monto_cuota' => 'decimal:2',
            'cantidad_cuotas' => 'integer',
        ];
    }

    public function ofertaAcademica(): BelongsTo
    {
        return $this->belongsTo(OfertaAcademica::class);
    }
}
