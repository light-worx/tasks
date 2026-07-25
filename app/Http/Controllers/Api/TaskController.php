<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->visibleTasks($request);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return $query
            ->latest()
            ->paginate(50);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assigned_email' => 'required|email',
            'due_at' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|string|exists:task_statuses,id',
            'context_id'     => 'nullable|exists:task_contexts,id',
        ]);

        return Task::create([

            ...$validated,

            'organisation_id' =>
                $request->user()->organisation_id,

            'created_by_client_id' =>
                $request->user()->id,

            'status' =>
                $validated['status'] ?? 'pending',
        ]);
    }

    public function show(Request $request, Task $task)
    {
        $visible = $this->visibleTasks($request)
            ->whereKey($task->id)
            ->first();

        abort_unless($visible, 403);

        return $visible;
    }

    public function update(Request $request, Task $task)
    {
        $visible = $this->visibleTasks($request)
            ->whereKey($task->id)
            ->first();

        abort_unless($visible, 403);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string|exists:task_statuses,id',
            'assigned_email' => 'nullable|email',
            'due_at' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'context_id'     => 'nullable|exists:task_contexts,id',
        ]);

        $visible->update($validated);

        return $visible->fresh();
    }

    public function destroy(Request $request, Task $task)
    {
        $visible = $this->visibleTasks($request)
            ->whereKey($task->id)
            ->first();

        abort_unless($visible, 403);

        $visible->delete();

        return response()->noContent();
    }

    private function visibleTasks(Request $request)
    {
        $client = $request->user();

        $query = Task::query()
            ->where('organisation_id', $client->organisation_id)
            ->where(function ($outer) use ($request, $client) {

                // tasks with no project at all — always visible within the org
                $outer->whereNull('project_id')

                    // or tasks whose project passes the existing visibility rule
                    ->orWhereHas('project', function ($projectQuery) use ($request, $client) {
                        $projectQuery->where('is_private', false);

                        if ($client->can_lookup_assigned_tasks && $request->filled('owner_email')) {
                            $projectQuery->orWhere(function ($private) use ($request) {
                                $private->where('is_private', true)
                                    ->where('owner_email', $request->owner_email);
                            });
                        }
                    });
            });

        // organisation-wide visibility
        if ($client->can_view_all_tasks) {
            return $query;
        }

        // assigned task lookup
        if (
            $client->can_lookup_assigned_tasks &&
            $request->filled('assigned_email')
        ) {

            return $query->where(
                'assigned_email',
                $request->assigned_email
            );
        }

        // default: no access
        return $query->whereRaw('1 = 0');
    }
}