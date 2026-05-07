<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(ApiClient::class, 'created_by_client_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
