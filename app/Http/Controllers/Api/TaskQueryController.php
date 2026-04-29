<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;

class TaskQueryController extends Controller
{
    public function byAssignee(string $email)
    {
        return Task::where('assigned_email', $email)->get();
    }
}