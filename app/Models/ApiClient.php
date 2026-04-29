<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class ApiClient extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'client_id',
        'client_secret',
        'status',
    ];

    protected $hidden = [
        'client_secret',
    ];

    protected static function booted()
    {
        static::creating(function ($client) {
            $client->client_id = 'cli_' . Str::random(24);
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}