@extends('layouts.app')
@section('title', 'Add Supplier')
@section('page-title', 'Add Supplier')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-truck me-2"></i>Add New Supplier
            </div>
            <div class="card-body">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company</label>
                            <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
                                value="{{ old('company') }}" placeholder="Company / Firm name">
                            @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}" placeholder="+91 XXXXXXXXXX">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                rows="2" placeholder="Full address...">{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">GST Number</label>
                            <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror"
                                value="{{ old('gst_number') }}" placeholder="22AAAAA0000A1Z5" style="text-transform:uppercase">
                            @error('gst_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Opening Balance (&#8377;)</label>
                            <input type="number" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror"
                                value="{{ old('opening_balance', 0) }}" min="0" step="0.01"
                                placeholder="Amount already owed to supplier">
                            @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Amount already owed to this supplier before using this system.</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                    {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-save me-1"></i>Save Supplier
                        </button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
