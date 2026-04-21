<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'type',
        'source',
        'message',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
