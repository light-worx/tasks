@extends('vendor.pwa.layouts.app')
@php($title = 'My tasks')

@section('content')
    <div class="d-flex gap-2 overflow-auto pb-2 mb-3" style="white-space:nowrap;">
        <a href="{{ route('app.tasks') }}"
           class="btn btn-sm {{ request('status') ? 'btn-outline-dark' : 'btn-dark' }}">All</a>
        @foreach($statuses as $status)
            <a href="{{ route('app.tasks', ['status' => $status->id]) }}"
               class="btn btn-sm {{ request('status') == $status->id ? 'btn-dark' : 'btn-outline-dark' }}">
                {{ $status->label }}
            </a>
        @endforeach
    </div>

    @forelse($tasks as $task)
        <a href="{{ route('app.tasks.show', $task) }}" class="card mb-2 p-3 text-decoration-none text-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-semibold">{{ $task->title }}</div>
                    <div class="small text-muted">{{ $task->project->name }}</div>
                </div>
                <span class="badge rounded-pill"
                      style="background:{{ $task->taskStatus?->colour ?? '#6b7280' }};">
                    {{ $task->taskStatus?->label ?? $task->status }}
                </span>
            </div>
            @if($task->due_at)
                <div class="small text-muted mt-1">
                    <i class="bi bi-calendar-event"></i>
                    {{ \Carbon\Carbon::parse($task->due_at)->format('D, j M') }}
                </div>
            @endif
        </a>
    @empty
        <p class="text-muted small">No tasks here yet.</p>
    @endforelse

    <a href="{{ route('app.tasks.create') }}"
       class="btn btn-dark rounded-circle position-fixed d-flex align-items-center justify-content-center"
       style="width:56px;height:56px;right:20px;bottom:76px;box-shadow:0 4px 12px rgba(0,0,0,.25);">
        <i class="bi bi-plus fs-3"></i>
    </a>
@endsection