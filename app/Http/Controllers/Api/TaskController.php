<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        return Task::where('api_client_id', $request->user()->id)->get();
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

        $task->update($request->all());

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