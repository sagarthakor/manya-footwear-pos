@extends('layouts.app')
@section('title', 'GST Report')
@section('page-title', 'GST Report')

@section('content')
{{-- Month Picker --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.gst') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Select Month</label>
                <input type="month" name="month" class="form-control"
                    value="{{ request('month', now()->format('Y-m')) }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-file-earmark-text me-1"></i>Generate GST Report
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($gstData))
@php
    $totalOutputTax = collect($gstData['sales_tax'])->sum('tax_collected');
    $totalInputTax  = collect($gstData['purchase_tax'])->sum('tax_paid');
    $netGst         = $totalOutputTax - $totalInputTax;
@endphp

{{-- Summary Badges --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#229954)">
            <div class="small mb-1" style="opacity:.85">Output Tax (Sales)</div>
            <div class="stat-value fs-5">&#8377;{{ number_format($totalOutputTax, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2980b9)">
            <div class="small mb-1" style="opacity:.85">Input Tax (Purchases)</div>
            <div class="stat-value fs-5">&#8377;{{ number_format($totalInputTax, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,{{ $netGst >= 0 ? '#e74c3c,#c0392b' : '#27ae60,#229954' }})">
            <div class="small mb-1" style="opacity:.85">Net GST Payable</div>
            <div class="stat-value fs-5">&#8377;{{ number_format(abs($netGst), 2) }}
                @if($netGst < 0)<small style="font-size:.7em">(Credit)</small>@endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Sales Tax Collected --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-receipt me-2"></i>Sales Tax Collected (Output GST)
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tax %</th>
                            <th class="text-end">Taxable Amount</th>
                            <th class="text-end">Tax Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gstData['sales_tax'] as $row)
                        <tr>
                            <td><span class="badge bg-primary">{{ $row['tax_percent'] }}%</span></td>
                            <td class="text-end">&#8377;{{ number_format($row['taxable_amount'], 2) }}</td>
                            <td class="text-end fw-semibold text-success">&#8377;{{ number_format($row['tax_collected'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No taxable sales this month</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($gstData['sales_tax']))
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold">Total</td>
                            <td class="text-end fw-bold">&#8377;{{ number_format(collect($gstData['sales_tax'])->sum('taxable_amount'), 2) }}</td>
                            <td class="text-end fw-bold text-success">&#8377;{{ number_format($totalOutputTax, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Purchase Tax Paid --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-truck me-2"></i>Purchase Tax Paid (Input GST)
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tax %</th>
                            <th class="text-end">Taxable Amount</th>
                            <th class="text-end">Tax Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gstData['purchase_tax'] as $row)
                        <tr>
                            <td><span class="badge bg-primary">{{ $row['tax_percent'] }}%</span></td>
                            <td class="text-end">&#8377;{{ number_format($row['taxable_amount'], 2) }}</td>
                            <td class="text-end fw-semibold text-info">&#8377;{{ number_format($row['tax_paid'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No taxable purchases this month</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($gstData['purchase_tax']))
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold">Total</td>
                            <td class="text-end fw-bold">&#8377;{{ number_format(collect($gstData['purchase_tax'])->sum('taxable_amount'), 2) }}</td>
                            <td class="text-end fw-bold text-info">&#8377;{{ number_format($totalInputTax, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Net Payable Summary --}}
<div class="card mt-4">
    <div class="card-body">
        <div class="row justify-content-end">
            <div class="col-md-4">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Output Tax (Sales)</td>
                        <td class="text-end fw-semibold">&#8377;{{ number_format($totalOutputTax, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Input Tax (Purchases)</td>
                        <td class="text-end fw-semibold text-success">-&#8377;{{ number_format($totalInputTax, 2) }}</td>
                    </tr>
                    <tr class="table-active">
                        <td class="fw-bold">Net GST Payable</td>
                        <td class="text-end fw-bold {{ $netGst >= 0 ? 'text-danger' : 'text-success' }}">
                            &#8377;{{ number_format(abs($netGst), 2) }}
                            @if($netGst < 0)<small class="text-muted">(Credit)</small>@endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@else
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-file-earmark-spreadsheet fs-1 d-block mb-3 opacity-50"></i>
        Select a month and click <strong>Generate GST Report</strong>.
    </div>
</div>
@endif
@endsection
