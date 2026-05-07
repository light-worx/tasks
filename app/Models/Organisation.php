<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function clients()
    {
        return $this->belongsTo(ApiClient::class, 'created_by_client_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
