<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class ApiClient extends Authenticatable
{
    use HasApiTokens;

    protected $guarded = ['id'];

    protected $hidden = [
        'client_secret',
    ];

    protected $casts = [
        'organisation_id' => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}