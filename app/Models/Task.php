<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public $table = 'tasks';
    protected $guarded = ['id'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'status', 'id');
    }

    public function context()
    {
        return $this->belongsTo(TaskContext::class, 'context_id');
    }
}
