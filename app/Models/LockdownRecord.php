<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockdownRecord extends Model
{
    protected $fillable = [
        'started_at',
        'ended_at',
        'admin_id',
        'reason'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
