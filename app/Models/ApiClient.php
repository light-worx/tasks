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

    protected static function booted()
    {
        static::creating(function ($client) {

            $client->client_id ??= 'cli_' . Str::random(24);
            if (!$client->client_secret) {
                $client->client_secret = Hash::make(Str::random(64));
            }
        });
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}