<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $fillable = [
        'tipo_movimiento',
        'monto_movimiento',
        'monto_final_caja',
        'detalle_estado'
    ];
}
