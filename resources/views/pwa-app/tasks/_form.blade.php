{{-- resources/views/pwa-app/tasks/_form.blade.php --}}

@php
    $hasBackTabErrors = $errors->hasAny(['due_at', 'assigned_email', 'project_id', 'description']);
@endphp

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ ! $hasBackTabErrors ? 'active' : '' }}"
                data-bs-toggle="tab" data-bs-target="#tab-main" type="button" role="tab">
            Task
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $hasBackTabErrors ? 'active' : '' }}"
                data-bs-toggle="tab" data-bs-target="#tab-more" type="button" role="tab">
            More details
        </button>
    </li>
</ul>

<div class="tab-content mb-3">

    <div class="tab-pane fade {{ ! $hasBackTabErrors ? 'show active' : '' }}" id="tab-main" role="tabpanel">

        <div class="mb-3">
            <label class="form-label small">Title</label>
            <input type="text" name="title" class="form-control" required autofocus
                   value="{{ old('title', $task->title ?? '') }}">
        </div>

        @isset($statuses)
        <div class="mb-3">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select" required>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}"
                        {{ old('status', $task->status ?? '') == $status->id ? 'selected' : '' }}>
                        {{ $status->label }}
                    </option>
                @endforeach
            </select>
        </div>
        @endisset

        @isset($contexts)
            @if($contexts->isNotEmpty())
            <div class="mb-3">
                <label class="form-label small">Context</label>
                <select name="context_id" class="form-select">
                    <option value="">None</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context->id }}"
                            {{ old('context_id', $task->context_id ?? '') == $context->id ? 'selected' : '' }}>
                            {{ '@' . $context->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
        @endisset

    </div>

    <div class="tab-pane fade {{ $hasBackTabErrors ? 'show active' : '' }}" id="tab-more" role="tabpanel">

        <div class="mb-3">
            <label class="form-label small">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $task->description ?? '') }}</textarea>
        </div>

        @if(isset($projects))
        <div class="mb-3">
            <label class="form-label small">Project</label>
            <select name="project_id" class="form-select">
                <option value="">No project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ old('project_id', $task->project_id ?? '') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="mb-3">
            <label class="form-label small">Due date</label>
            <input type="date" name="due_at" class="form-control"
                   value="{{ old('due_at', isset($task->due_at) ? \Carbon\Carbon::parse($task->due_at)->format('Y-m-d') : '') }}">
        </div>

        <div class="mb-3">
            <label class="form-label small">Assign to (optional — defaults to you)</label>
            <input type="email" name="assigned_email" class="form-control"
                   value="{{ old('assigned_email', $task->assigned_email ?? '') }}">
        </div>

    </div>
</div>