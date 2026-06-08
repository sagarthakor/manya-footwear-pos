@extends('layouts.app')
@section('title', 'Access Denied')
@section('page-title', 'Access Denied')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 text-center py-5">
        <div style="font-size:5rem">🚫</div>
        <h2 class="mt-3 fw-bold text-danger">403 — Access Denied</h2>
        <p class="text-muted mt-2">
            You do not have permission to access this page.<br>
            Your role: <strong class="text-primary">{{ auth()->user()?->role_label }}</strong>
        </p>
        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-danger">
                <i class="bi bi-house me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
