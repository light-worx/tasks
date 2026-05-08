<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        return Project::query()
            ->where(
                'organisation_id',
                $client->organisation_id
            )

            ->where(function ($query) use ($request) {

                // public org projects
                $query->where('is_private', false);

                // private projects owned by this email
                if ($request->filled('owner_email')) {

                    $query->orWhere(function ($private) use ($request) {

                        $private
                            ->where('is_private', true)
                            ->where(
                                'owner_email',
                                $request->owner_email
                            );
                    });
                }
            })

            ->withCount([
                'tasks as task_count',
            ])

            ->orderBy('name')

            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
            'owner_email' => 'nullable|email',
        ]);

        return Project::create([

            ...$validated,

            'organisation_id' =>
                $request->user()->organisation_id,

            'created_by_client_id' =>
                $request->user()->id,
        ]);
    }

    public function show(Request $request, Project $project)
    {
        $this->authorizeProject($request, $project);

        $project->loadCount([
            'tasks as task_count',
        ]);

        return $project;
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeProject($request, $project);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
            'owner_email' => 'nullable|email',
        ]);

        $project->update($validated);

        return $project->fresh()->loadCount([
            'tasks as task_count',
        ]);
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorizeProject($request, $project);

        $project->delete();

        return response()->noContent();
    }

    protected function authorizeProject(
        Request $request,
        Project $project
    ): void {

        $client = $request->user();

        // must belong to same organisation
        abort_unless(
            $project->organisation_id === $client->organisation_id,
            403
        );

        // public projects are accessible
        if (! $project->is_private) {
            return;
        }

        // private projects require matching owner_email
        abort_unless(
            $request->filled('owner_email')
            && $project->owner_email === $request->owner_email,
            403
        );
    }
}