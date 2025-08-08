<form method="POST" action="{{ $workflow->exists ? route('workflows.update', $workflow) : route('workflows.store') }}">
    @csrf
    @if($workflow->exists)
        @method('PUT')
    @endif
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $workflow->name) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control">{{ old('description', $workflow->description) }}</textarea>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="active" class="form-check-input" id="wf-active" value="1" {{ old('active', $workflow->active) ? 'checked' : '' }}>
        <label for="wf-active" class="form-check-label">Active</label>
    </div>
    <div id="workflow-builder" class="mb-3">
        <!-- drag-and-drop builder placeholder -->
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
