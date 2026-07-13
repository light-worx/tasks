@extends('vendor.pwa.layouts.app')
@php($title = 'Task')

@section('content')
    <div class="card p-3 mb-3">
        <h1 class="h5 mb-1">{{ $task->title }}</h1>
        <div class="small text-muted mb-3">{{ $task->project?->name ?? 'No project' }}</div>

        @if($task->description)
            <p>{{ $task->description }}</p>
        @endif

        <div class="d-flex justify-content-between small text-muted mb-3">
            <span><i class="bi bi-person"></i> {{ $task->assigned_email }}</span>
            @if($task->due_at)
                <span><i class="bi bi-calendar-event"></i>
                    {{ \Carbon\Carbon::parse($task->due_at)->format('D, j M Y') }}
                </span>
            @endif
        </div>

        <label class="form-label small text-muted">Status</label>
        <div class="btn-group w-100 mb-2" role="group">
            @foreach($statuses as $status)
                <button type="button"
                        class="btn btn-sm status-btn {{ $task->status == $status->id ? 'btn-dark' : 'btn-outline-dark' }}"
                        data-status="{{ $status->id }}">
                    {{ $status->label }}
                </button>
            @endforeach
        </div>
    </div>

    @if($canEdit)
        <div class="d-flex gap-2">
            <a href="{{ route('app.tasks.edit', $task) }}" class="btn btn-outline-dark flex-fill">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <button type="button" class="btn btn-outline-danger flex-fill"
                    data-bs-toggle="modal" data-bs-target="#deleteTaskModal">
                <i class="bi bi-trash"></i> Delete
            </button>
        </div>

        <form id="deleteTaskForm" method="POST" action="{{ route('app.tasks.destroy', $task) }}" class="d-none">
            @csrf @method('DELETE')
        </form>

        @include('pwa-app._confirm-modal', [
            'id'      => 'deleteTaskModal',
            'title'   => 'Delete this task?',
            'message' => "\"{$task->title}\" will be permanently removed.",
            'formId'  => 'deleteTaskForm',
        ])
    @endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const status = btn.dataset.status;
        try {
            const res = await fetch("{{ route('app.tasks.status', $task) }}", {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status }),
            });
            if (!res.ok) throw new Error();
            document.querySelectorAll('.status-btn').forEach(b => b.classList.replace('btn-dark', 'btn-outline-dark'));
            btn.classList.replace('btn-outline-dark', 'btn-dark');
            showToast('Status updated');
        } catch {
            showToast('Could not update status', 'error');
        }
    });
});
</script>
@endpush