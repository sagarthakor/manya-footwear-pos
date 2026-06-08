@extends('layouts.app')
@section('title', 'Brands')
@section('page-title', 'Brands')

@section('content')
<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-award me-2"></i>Brands</span>
        <a href="{{ route('brands.create') }}" class="btn btn-sm btn-danger">
            <i class="bi bi-plus"></i> Add Brand
        </a>
    </div>
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('brands.index') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search by name..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-search"></i> Search
                </button>
                @if(request('search'))
                <a href="{{ route('brands.index') }}" class="btn btn-sm btn-outline-danger ms-1">
                    <i class="bi bi-x"></i> Clear
                </a>
                @endif
            </div>
        </form>
    </div>
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
                @forelse($brands as $brand)
                <tr>
                    <td class="fw-semibold">{{ $brand->name }}</td>
                    <td class="text-muted small">{{ $brand->slug }}</td>
                    <td><span class="badge bg-secondary">{{ $brand->products_count }}</span></td>
                    <td>
                        <span class="badge bg-{{ $brand->is_active ? 'success' : 'danger' }}">
                            {{ $brand->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('brands.edit', $brand) }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <form method="POST" action="{{ route('brands.destroy', $brand) }}" class="d-inline"
                            onsubmit="return confirm('Delete brand \'{{ addslashes($brand->name) }}\'? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-award fs-3 d-block mb-2 opacity-50"></i>
                        No brands found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($brands->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Showing {{ $brands->firstItem() }}–{{ $brands->lastItem() }} of {{ $brands->total() }} brands
        </small>
        {{ $brands->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
