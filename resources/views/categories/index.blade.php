@extends('layouts.app')
@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-plus me-2"></i>Add Category</div>
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="e.g. Men's Shoes" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"
                            placeholder="Optional description...">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-save me-1"></i>Save Category
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-tags me-2"></i>All Categories</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td class="fw-semibold">{{ $cat->name }}</td>
                            <td class="text-muted small">{{ $cat->slug }}</td>
                            <td><span class="badge bg-secondary">{{ $cat->products_count }}</span></td>
                            <td>
                                <span class="badge bg-{{ $cat->is_active ? 'success' : 'danger' }}">
                                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                @if($cat->products_count === 0)
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No categories found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
