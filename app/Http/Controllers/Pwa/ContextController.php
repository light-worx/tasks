<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Pwa\Concerns\ScopesToOrganisation;
use App\Models\TaskContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ContextController extends Controller
{
    use ScopesToOrganisation;

    public function index(Request $request): View
    {
        $contexts = TaskContext::where('owner_email', $request->pwaPreference->email)
            ->withCount('tasks')
            ->orderBy('sort_order')
            ->get();

        return view('pwa-app.contexts.index', compact('contexts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label'  => 'required|string|max:100',
            'colour' => 'nullable|string|max:20',
        ]);

        TaskContext::create($data + [
            'organisation_id' => $this->organisationId(),
            'owner_email'     => $request->pwaPreference->email,
            'sort_order'      => TaskContext::where('owner_email', $request->pwaPreference->email)->count(),
        ]);

        return back()->with('status', 'Context added.');
    }

    public function update(Request $request, TaskContext $context): RedirectResponse
    {
        $this->authorizeOwner($request, $context);

        $context->update($request->validate([
            'label'     => 'required|string|max:100',
            'colour'    => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]));

        return back()->with('status', 'Context updated.');
    }

    public function destroy(Request $request, TaskContext $context): RedirectResponse
    {
        $this->authorizeOwner($request, $context);
        $context->delete(); // tasks keep their title/status, just lose the tag (nullOnDelete)

        return back()->with('status', 'Context removed.');
    }

    private function authorizeOwner(Request $request, TaskContext $context): void
    {
        abort_unless($context->owner_email === $request->pwaPreference->email, 403);
    }
}