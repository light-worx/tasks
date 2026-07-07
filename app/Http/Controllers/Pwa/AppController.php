<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Pwa\Concerns\ScopesToOrganisation;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AppController extends Controller
{

    use ScopesToOrganisation;

    public function home(Request $request): View
    {
        $email = $request->pwaPreference->email;

        $openCount = Task::where('assigned_email', $email)
            ->whereHas('taskStatus', fn ($q) => $q->where('label', '!=', 'Done'))
            ->count();

        $overdueCount = Task::where('assigned_email', $email)
            ->whereNotNull('due_at')->where('due_at', '<', now())
            ->count();

        $projectsCount = Project::where('organisation_id', $this->organisationId())
            ->where(function ($q) use ($email) {
                $q->where('owner_email', $email)
                ->orWhere('is_private', false)
                ->orWhereHas('tasks', fn ($q2) => $q2->where('assigned_email', $email));
            })->count();

        $upcomingTasks = Task::where('assigned_email', $email)
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->with('project:id,name')
            ->take(5)
            ->get();

        return view('pwa-app.home', compact('openCount', 'overdueCount', 'projectsCount', 'upcomingTasks'));
    }
}