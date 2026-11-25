{{-- Shared by create and edit. Expects: $category, $action, $cancel --}}
<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if ($category->exists)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Category name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
               class="form-control @error('name') is-invalid @enderror" required autofocus>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Optional notes about what this category covers">{{ old('description', $category->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-navy">
            <i class="bi bi-check-lg me-1"></i> {{ $category->exists ? 'Save Changes' : 'Add Category' }}
        </button>
        <a href="{{ $cancel }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>