{{-- resources/views/pwa-app/home.blade.php --}}
@extends('vendor.pwa.layouts.app')
@php($title = 'Home')

@section('content')
    <h1 class="h5 mb-3">Hi, {{ $pwaPreference->name ?? $pwaPreference->email }}</h1>

    <div class="row g-2 mb-3">
        <div class="col-4">
            <a href="{{ route('app.tasks') }}" class="card text-center text-decoration-none py-3">
                <div class="fs-4 fw-bold">{{ $openCount }}</div>
                <div class="small text-muted">Open</div>
            </a>
        </div>
        <div class="col-4">
            <a href="{{ route('app.tasks', ['status' => 'overdue']) }}" class="card text-center text-decoration-none py-3">
                <div class="fs-4 fw-bold text-danger">{{ $overdueCount }}</div>
                <div class="small text-muted">Overdue</div>
            </a>
        </div>
        <div class="col-4">
            <a href="{{ route('app.projects') }}" class="card text-center text-decoration-none py-3">
                <div class="fs-4 fw-bold">{{ $projectsCount }}</div>
                <div class="small text-muted">Projects</div>
            </a>
        </div>
    </div>

    <h2 class="h6 text-muted mb-2">Coming up</h2>

    @forelse($upcomingTasks as $task)
        <a href="{{ route('app.tasks.show', $task) }}" class="card mb-2 p-3 text-decoration-none text-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-semibold">{{ $task->title }}</div>
                    <div class="small text-muted mb-3">{{ $task->project?->name ?? 'No project' }}</div>
                </div>
                <span class="badge rounded-pill text-bg-light">
                    {{ \Carbon\Carbon::parse($task->due_at)->diffForHumans() }}
                </span>
            </div>
        </a>
    @empty
        <p class="text-muted small">Nothing due soon — you're all caught up.</p>
    @endforelse

    <a href="{{ route('app.tasks.create') }}"
       class="btn btn-dark rounded-circle position-fixed d-flex align-items-center justify-content-center"
       style="width:56px;height:56px;right:20px;bottom:76px;box-shadow:0 4px 12px rgba(0,0,0,.25);">
        <i class="bi bi-plus fs-3"></i>
    </a>
@endsection