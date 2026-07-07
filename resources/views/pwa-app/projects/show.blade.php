@extends('vendor.pwa.layouts.app')
@php($title = $project->name)

@section('content')
    <div class="card p-3 mb-3">
        <h1 class="h5 mb-1">{{ $project->name }}</h1>
        @if($project->description)
            <p class="small text-muted mb-0">{{ $project->description }}</p>
        @endif
    </div>

    @if($canEdit)
        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('app.projects.edit', $project) }}" class="btn btn-outline-dark flex-fill btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <form method="POST" action="{{ route('app.projects.destroy', $project) }}" class="flex-fill"
                  onsubmit="return confirm('Delete this project?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger w-100 btn-sm"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    @endif

    <h2 class="h6 text-muted mb-2">Tasks</h2>
    @forelse($project->tasks as $task)
        <a href="{{ route('app.tasks.show', $task) }}" class="card mb-2 p-3 text-decoration-none text-body">
            <div class="d-flex justify-content-between align-items-center">
                <span>{{ $task->title }}</span>
                <span class="badge rounded-pill" style="background:{{ $task->taskStatus?->colour ?? '#6b7280' }};">
                    {{ $task->taskStatus?->label ?? $task->status }}
                </span>
            </div>
        </a>
    @empty
        <p class="text-muted small">No tasks in this project yet.</p>
    @endforelse
@endsection