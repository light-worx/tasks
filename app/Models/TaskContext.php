<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskContext extends Model
{
    protected $guarded = ['id'];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'context_id');
    }
}