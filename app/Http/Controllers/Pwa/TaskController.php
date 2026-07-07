<?php
// app/Http/Controllers/Pwa/TaskController.php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Pwa\Concerns\ScopesToOrganisation;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Lightworx\FilamentPwa\Facades\PushNotification;
use Lightworx\FilamentPwa\Models\UserPreference;

class TaskController extends Controller
{
    use ScopesToOrganisation;
    /**
     * "My tasks" — everything assigned to the signed-in person, across
     * all projects. Optional ?status= and ?project_id= filters for the
     * segmented control / project drill-down on the tasks page.
     */
    public function index(Request $request): View
    {
        $email = $request->pwaPreference->email;

        $tasks = Task::where('assigned_email', $email)
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->string('status')))
            ->when($request->filled('project_id'), fn ($q) =>
                $q->where('project_id', $request->integer('project_id')))
            ->with('project:id,name')
            ->orderByRaw('due_at IS NULL, due_at asc')
            ->get();

        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();

        return view('pwa-app.tasks.index', compact('tasks', 'statuses'));
    }

    public function create(Request $request): View
    {
        // Only projects the person can actually file a task against —
        // same visibility rule as ProjectController::index.
        $email = $request->pwaPreference->email;

        $projects = Project::where('organisation_id', $this->organisationId())
            ->where(function ($q) use ($email) {
                $q->where('owner_email', $email)
                  ->orWhere('is_private', false)
                  ->orWhereHas('tasks', fn ($q2) => $q2->where('assigned_email', $email));
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();

        return view('pwa-app.tasks.create', compact('projects', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:5000',
            'project_id'     => 'required|exists:projects,id',
            'due_at'         => 'nullable|date',
            'assigned_email' => 'nullable|email',
        ]);

        $project = Project::findOrFail($data['project_id']);
        $this->authorizeProjectAccess($request, $project);

        $defaultStatus = TaskStatus::where('is_active', true)->orderBy('sort_order')->first();

        $task = Task::create([
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'due_at'          => $data['due_at'] ?? null,
            'project_id'      => $project->id,
            'organisation_id' => $project->organisation_id,
            // Defaults to self-assignment; lets the creator hand it to
            // someone else on the same project in one step.
            'assigned_email'  => $data['assigned_email'] ?? $request->pwaPreference->email,
            'status'          => $defaultStatus->id,
        ]);

        $this->notifyAssignee($task, $request->pwaPreference);

        return redirect()
            ->route('app.tasks.show', $task)
            ->with('status', 'Task created.');
    }

    public function show(Request $request, Task $task): View
    {
        $this->authorizeVisible($request, $task);

        $task->load('project:id,name,owner_email');
        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();

        $canEdit = $this->canEdit($request, $task);

        return view('pwa-app.tasks.show', compact('task', 'statuses', 'canEdit'));
    }

    public function edit(Request $request, Task $task): View
    {
        $this->authorizeEdit($request, $task);

        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();

        return view('pwa-app.tasks.edit', compact('task', 'statuses'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeEdit($request, $task);

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:5000',
            'due_at'         => 'nullable|date',
            'assigned_email' => 'nullable|email',
        ]);

        $previousAssignee = $task->assigned_email;

        $task->update($data);

        // Only notify if the assignee actually changed to someone new
        if (! empty($data['assigned_email']) && $data['assigned_email'] !== $previousAssignee) {
            $this->notifyAssignee($task, $request->pwaPreference);
        }

        return redirect()
            ->route('app.tasks.show', $task)
            ->with('status', 'Task updated.');
    }

    /**
     * Lightweight status-only update for the segmented control on the task
     * detail page — kept separate from update() so a quick status flip
     * doesn't require the full edit form's validation/authorization shape.
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorizeVisible($request, $task);

        // Anyone who can see the task (assignee or project owner) can move
        // its status — editing title/description/assignee is more restricted.
        $data = $request->validate([
            'status' => 'required|exists:task_statuses,id',
        ]);

        $task->update($data);

        // Let the project owner know their task moved, unless they're the
        // one who just moved it.
        $owner = UserPreference::where('email', $task->project->owner_email)->first();

        if ($owner && $owner->email !== $request->pwaPreference->email) {
            PushNotification::toPreference(
                $owner,
                'Task updated',
                "\"{$task->title}\" is now {$task->status}.",
                url: route('app.tasks.show', $task, absolute: false),
                senderName: $request->pwaPreference->name,
            );
        }

        return response()->json(['status' => 'ok', 'task_status' => $task->status]);
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeEdit($request, $task);

        $task->delete();

        return redirect()
            ->route('app.tasks')
            ->with('status', 'Task deleted.');
    }

    // ── Notifications ────────────────────────────────────────────────────────

    private function notifyAssignee(Task $task, UserPreference $actor): void
    {
        if ($task->assigned_email === $actor->email) {
            return; // don't notify yourself for self-assigned tasks
        }

        $assignee = UserPreference::where('email', $task->assigned_email)->first();

        if (! $assignee) {
            return; // not signed up on the PWA yet — nothing to push to
        }

        PushNotification::toPreference(
            $assignee,
            'New task assigned',
            $task->title,
            url: route('app.tasks.show', $task, absolute: false),
            senderName: $actor->name,
        );
    }

    // ── Authorization helpers ────────────────────────────────────────────────

    /**
     * A task is visible if the person is the assignee, or owns the
     * project it belongs to.
     */
    private function authorizeVisible(Request $request, Task $task): void
    {
        $email = $request->pwaPreference->email;

        $visible = $task->assigned_email === $email
            || $task->project->owner_email === $email;

        abort_unless($visible, 403, 'You don\'t have access to this task.');
    }

    /**
     * Editing (title/description/assignee/delete) is restricted to the
     * assignee or the project owner — same rule as visibility for now,
     * kept as a separate method so the two can diverge later without
     * touching call sites.
     */
    private function authorizeEdit(Request $request, Task $task): void
    {
        $this->authorizeVisible($request, $task);
    }

    private function canEdit(Request $request, Task $task): bool
    {
        $email = $request->pwaPreference->email;

        return $task->assigned_email === $email
            || $task->project->owner_email === $email;
    }

    private function authorizeProjectAccess(Request $request, Project $project): void
    {
        $email = $request->pwaPreference->email;

        $allowed = ! $project->is_private
            || $project->owner_email === $email
            || $project->tasks()->where('assigned_email', $email)->exists();

        abort_unless($allowed, 403, 'You don\'t have access to this project.');
    }
}