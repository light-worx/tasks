<?php

namespace App\Http\Controllers\Pwa;

use App\Models\Organisation;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Projects visible to the signed-in person:
     *   - projects they own (owner_email matches)
     *   - non-private projects in the organisation
     *   - private projects that aren't theirs, but where they have at
     *     least one task assigned (so being added to a task grants visibility
     *     even if you didn't create the project)
     */
    public function index(Request $request): View
    {
        $email = $request->pwaPreference->email;

        $projects = Project::where('organisation_id', $this->organisationId())
            ->where(function ($query) use ($email) {
                $query->where('owner_email', $email)
                    ->orWhere('is_private', false)
                    ->orWhereHas('tasks', fn ($q) => $q->where('assigned_email', $email));
            })
            ->withCount([
                'tasks',
                'tasks as open_tasks_count' => fn ($q) => $q->where('status', '!=', 'done'),
            ])
            ->orderBy('name')
            ->get();

        return view('pwa-app.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('pwa-app.projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_private'  => 'sometimes|boolean',
        ]);

        $project = Project::create($data + [
            'organisation_id' => $this->organisationId(),
            'owner_email'     => $request->pwaPreference->email,
            'is_private'      => $request->boolean('is_private'),
        ]);

        return redirect()
            ->route('app.projects.show', $project)
            ->with('status', 'Project created.');
    }

    public function show(Request $request, Project $project): View
    {
        $this->authorizeVisible($request, $project);

        $project->load([
            'tasks' => fn ($q) => $q->orderBy('due_at'),
        ]);

        $canEdit = $project->owner_email === $request->pwaPreference->email;

        return view('pwa-app.projects.show', compact('project', 'canEdit'));
    }

    public function edit(Request $request, Project $project): View
    {
        $this->authorizeOwner($request, $project);

        return view('pwa-app.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeOwner($request, $project);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_private'  => 'sometimes|boolean',
        ]);

        $project->update($data + [
            'is_private' => $request->boolean('is_private'),
        ]);

        return redirect()
            ->route('app.projects.show', $project)
            ->with('status', 'Project updated.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeOwner($request, $project);

        // Guard against silently orphaning tasks — require the project to be
        // empty before it can be removed via the PWA. Bulk cleanup of tasks
        // stays an API/admin operation, not a mobile-UI one.
        if ($project->tasks()->exists()) {
            return back()->with('error', 'Move or delete this project\'s tasks before deleting it.');
        }

        $project->delete();

        return redirect()
            ->route('app.projects')
            ->with('status', 'Project deleted.');
    }

    // ── Authorization helpers ────────────────────────────────────────────────

    /**
     * A project is visible if it isn't private, or the person owns it,
     * or the person has a task assigned within it.
     */
    private function authorizeVisible(Request $request, Project $project): void
    {
        $email = $request->pwaPreference->email;

        $visible = ! $project->is_private
            || $project->owner_email === $email
            || $project->tasks()->where('assigned_email', $email)->exists();

        abort_unless($visible, 403, 'You don\'t have access to this project.');
    }

    private function authorizeOwner(Request $request, Project $project): void
    {
        abort_unless(
            $project->owner_email === $request->pwaPreference->email,
            403,
            'Only the project owner can do this.'
        );
    }

    /**
     * Resolve the single organisation this PWA deployment serves.
     *
     * Assumes one organisation per PWA install (matching the subdomain-per-app
     * routing pattern already in use). Cached per-request to avoid repeat
     * lookups across index/show calls in the same request lifecycle.
     */
    private function organisationId(): int
    {
        static $id;

        return $id ??= Organisation::where('slug', config('pwa.organisation_slug'))
            ->value('id')
            ?? abort(500, 'pwa.organisation_slug is not configured or does not match an organisation.');
    }
}