<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Cache;

class TaskMetaController extends Controller
{
    public function index()
    {
        // 1. Fetch the data
        $statuses = TaskStatus::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();

        // 2. The "Nuclear" Serialization Purge
        // We encode to JSON and immediately decode to an associative array.
        // This physically strips all PHP class information.
        $pureArray = json_decode($statuses->toJson(), true);

        // 3. Cache the PURE array, not the Collection object
        $data = Cache::remember('tasks_api.meta', 3600, function () use ($pureArray) {
            return ['statuses' => $pureArray];
        });

        return response()->json($data);
    }
}