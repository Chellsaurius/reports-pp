<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesRequest extends Model
{
    protected $fillable = [
        'payload',
        'result',
        'ip'
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array'
    ];
}