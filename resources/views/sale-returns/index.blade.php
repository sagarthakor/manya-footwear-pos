@extends('layouts.app')
@section('title','Sale Returns')
@section('page-title','Sale Returns')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center py-3">
    <span><i class="bi bi-arrow-return-left me-2"></i>Sale Returns</span>
    <a href="{{ route('sale-returns.create') }}" class="btn btn-sm btn-danger"><i class="bi bi-plus"></i> Process Return</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Return No</th><th>Invoice</th><th>Customer</th><th>Date</th><th>Amount</th><th>Type</th><th>Method</th><th>By</th></tr></thead>
      <tbody>
        @forelse($returns as $ret)
        <tr>
          <td class="fw-semibold">{{ $ret->return_number }}</td>
          <td><a href="{{ route('sales.show',$ret->sale) }}">{{ $ret->sale->invoice_number }}</a></td>
          <td>{{ $ret->sale->customer?->name ?? 'Walk-in' }}</td>
          <td>{{ $ret->return_date->format('d M Y') }}</td>
          <td class="fw-bold">&#8377;{{ number_format($ret->return_amount,2) }}</td>
          <td><span class="badge bg-{{ $ret->return_type==='refund'?'danger':'warning' }}">{{ ucfirst($ret->return_type) }}</span></td>
          <td><span class="badge bg-secondary">{{ strtoupper($ret->refund_method) }}</span></td>
          <td class="small">{{ $ret->user->name }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted py-4">No returns found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($returns->hasPages())<div class="card-footer">{{ $returns->links() }}</div>@endif
</div>
@endsection
