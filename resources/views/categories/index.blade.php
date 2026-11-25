@extends('layouts.app')

@section('title', 'Violation Categories')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <p class="text-muted mb-0">
            The official list of violations officers can choose from. Deactivated categories are hidden from the report form
            but stay on existing reports.
        </p>
        <a href="{{ route('categories.create') }}" class="btn btn-navy text-nowrap">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Reports</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="{{ $category->is_active ? '' : 'text-muted' }}">
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td class="small">{{ $category->description ?? '—' }}</td>
                            <td class="text-center">{{ $category->reports_count }}</td>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('categories.toggle', $category) }}" class="d-inline"
                                      @if ($category->is_active)
                                          onsubmit="return confirm('Deactivate this category? Officers will no longer be able to select it.');"
                                      @endif>
                                    @csrf
                                    @method('PATCH')
                                    @if ($category->is_active)
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-slash-circle"></i> Deactivate
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check-circle"></i> Activate
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                No categories yet. Click "Add Category" to enter the first violation from the official list.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection