@extends('vendor.pwa.layouts.app')
@php($title = 'My tasks')

@section('content')
    <div class="row g-2 mb-2">
        <div class="col-6">
            <select class="form-select form-select-sm w-100" onchange="location.href=this.value">
                <option value="{{ route('app.home', request()->except('status')) }}"
                    {{ ! request('status') ? 'selected' : '' }}>All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ route('app.home', array_merge(request()->except('status'), ['status' => $status->id])) }}"
                        {{ request('status') == $status->id ? 'selected' : '' }}>
                        {{ $status->label }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($contexts->isNotEmpty())
        <div class="col-6">
            <select class="form-select form-select-sm w-100" onchange="location.href=this.value">
                <option value="{{ route('app.home', request()->except('context')) }}"
                    {{ ! request('context') ? 'selected' : '' }}>All contexts</option>
                @foreach($contexts as $context)
                    <option value="{{ route('app.home', array_merge(request()->except('context'), ['context' => $context->id])) }}"
                        {{ request('context') == $context->id ? 'selected' : '' }}>
                        {{ '@' . $context->label }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    @if(request('status') || request('context'))
    <div class="d-flex gap-2 mb-3">
        @if(request('status'))
            @php($activeStatus = $statuses->firstWhere('id', request('status')))
            @if($activeStatus)
            <a href="{{ route('app.home', request()->except('status')) }}"
            class="badge rounded-pill text-decoration-none"
            style="background:{{ $activeStatus->colour ?? '#6b7280' }};">
                {{ $activeStatus->label }} <i class="bi bi-x"></i>
            </a>
            @endif
        @endif

        @if(request('context'))
            @php($activeContext = $contexts->firstWhere('id', request('context')))
            @if($activeContext)
            <a href="{{ route('app.home', request()->except('context')) }}"
            class="badge rounded-pill text-bg-light border text-decoration-none">
                {{ '@' . $activeContext->label }} <i class="bi bi-x"></i>
            </a>
            @endif
        @endif
    </div>
    @endif
    {{-- tasks/index.blade.php --}}
    @forelse($tasks as $task)
        <a href="{{ route('app.tasks.show', $task) }}" class="card mb-2 p-3 text-decoration-none text-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div style="min-width:0;">
                    <div class="fw-semibold">{{ $task->title }}</div>
                    @if($task->project)
                        <div class="small text-muted">{{ $task->project->name }}</div>
                    @endif
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-1">
                    <span class="badge rounded-pill"
                        style="background:{{ $task->taskStatus?->colour ?? '#6b7280' }};">
                        {{ $task->taskStatus?->label ?? $task->status }}
                    </span>
                    @if($task->context)
                        <span class="badge rounded-pill text-bg-light border">
                            {{ '@' . $task->context->label }}
                        </span>
                    @endif
                </div>
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
    <a href="{{ route('app.tasks.create') }}" class="btn btn-dark rounded-circle position-fixed d-flex align-items-center justify-content-center" style="width:56px;height:56px;left:50%;transform:translateX(-50%);bottom:32px; box-shadow:0 4px 12px rgba(0,0,0,.35);z-index:1040; border:3px solid white">
        <i class="bi bi-plus fs-3"></i>
    </a>
@endsection