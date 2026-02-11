<?php

namespace Jonas\TestPackage\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'user_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}