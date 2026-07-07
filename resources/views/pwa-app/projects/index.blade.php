@extends('vendor.pwa.layouts.app')
@php($title = 'Projects')

@section('content')
    @forelse($projects as $project)
        <a href="{{ route('app.projects.show', $project) }}" class="card mb-2 p-3 text-decoration-none text-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">
                        {{ $project->name }}
                        @if($project->is_private)<i class="bi bi-lock small text-muted"></i>@endif
                    </div>
                    <div class="small text-muted">{{ $project->open_tasks_count }} open · {{ $project->tasks_count }} total</div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        </a>
    @empty
        <p class="text-muted small">No projects yet.</p>
    @endforelse

    <a href="{{ route('app.projects.create') }}"
       class="btn btn-dark rounded-circle position-fixed d-flex align-items-center justify-content-center"
       style="width:56px;height:56px;right:20px;bottom:76px;box-shadow:0 4px 12px rgba(0,0,0,.25);">
        <i class="bi bi-plus fs-3"></i>
    </a>
@endsection