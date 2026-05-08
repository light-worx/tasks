<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller {
    public function index(Request $request)
    {
        $client = $request->user();

        return Project::query()
            ->where(
                'organisation_id',
                $client->organisation_id
            )
            ->where(function ($query) use ($request) {
                $query->where('is_private', false);
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
}
