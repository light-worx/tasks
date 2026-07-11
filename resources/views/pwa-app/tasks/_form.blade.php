<div class="mb-3">
    <label class="form-label small">Title</label>
    <input type="text" name="title" class="form-control" required
           value="{{ old('title', $task->title ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label small">Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $task->description ?? '') }}</textarea>
</div>

@if(isset($projects))
<div class="mb-3">
    <label class="form-label small">Project</label>
    <select name="project_id" class="form-select" required>
        @foreach($projects as $project)
            <option value="{{ $project->id }}"
                {{ old('project_id', $task->project_id ?? '') == $project->id ? 'selected' : '' }}>
                {{ $project->name }}
            </option>
        @endforeach
    </select>
</div>
@endif

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