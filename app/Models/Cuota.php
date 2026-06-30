<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cuota extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cuotas';

    protected $fillable = [
        'matricula_periodo_id',
        'descripcion',
        'monto',
        'fecha_vencimiento',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function matriculaPeriodo(): BelongsTo
    {
        return $this->belongsTo(MatriculaPeriodo::class);
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class);
    }
}
