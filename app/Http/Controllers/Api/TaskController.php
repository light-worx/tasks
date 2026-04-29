<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()
            ->where('api_client_id', $request->user()->id);

        if ($request->filled('assigned_email')) {
            $query->where('assigned_email', $request->assigned_email);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return $query->latest()->paginate(50);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assigned_email' => 'required|email',
            'due_at' => 'nullable|date',
        ]);

        return Task::create([
            ...$validated,
            'api_client_id' => $request->user()->id,
        ]);
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);

        return $task;
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $task->update($request->only([
            'title',
            'description',
            'status',
            'assigned_email',
            'due_at',
            'project_id',
        ]));

        return $task;
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);

        $task->delete();

        return response()->noContent();
    }

    private function authorizeTask(Task $task)
    {
        abort_unless($task->api_client_id === auth()->id(), 403);
    }
}