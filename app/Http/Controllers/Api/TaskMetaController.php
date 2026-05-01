<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Cache;

class TaskMetaController extends Controller
{
    public function index()
    {
        return Cache::remember('tasks.meta', 3600, function () {
            return [
                'statuses' => TaskStatus::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(['id', 'label', 'color']),
            ];
        });
    }
}