<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetalleIngreso extends Model
{
    protected $table = 'detalle_ingresos';
    protected $fillable = [
        'idingreso',
        'idarticulo',
        'cantidad',
        'cantidad_blister',
        'precio'
    ];

    public $timestamps = false;

}
