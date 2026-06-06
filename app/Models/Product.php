<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'categoria',
        'price',
        'stock',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];
}