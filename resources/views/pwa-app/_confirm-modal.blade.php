{{-- resources/views/pwa-app/_confirm-modal.blade.php --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-body text-center pt-4 pb-3">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                </div>
                <h5 class="mb-2">{{ $title }}</h5>
                <p class="text-muted small mb-4">{{ $message }}</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger"
                            onclick="document.getElementById('{{ $formId }}').submit()">
                        Delete
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>