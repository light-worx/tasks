<div class="mb-3">
    <label class="form-label small">Name</label>
    <input type="text" name="name" class="form-control" required
           value="{{ old('name', $project->name ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label small">Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $project->description ?? '') }}</textarea>
</div>

<div class="form-check mb-3">
    <input type="checkbox" name="is_private" value="1" class="form-check-input" id="isPrivate"
           {{ old('is_private', $project->is_private ?? false) ? 'checked' : '' }}>
    <label class="form-check-label small" for="isPrivate">Private — only visible to people assigned a task</label>
</div>