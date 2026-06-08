<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 80mm;
            margin: 0 auto;
            background: #fff;
            color: #000;
            padding: 4mm 3mm;
        }
        .separator        { border-top: 1px dashed #000; margin: 3mm 0; }
        .double-separator { border-top: 2px solid #000; margin: 3mm 0; }
        .shop-name  { font-size: 16px; font-weight: bold; text-align: center; letter-spacing: 1px; }
        .shop-sub   { font-size: 10px; text-align: center; }
        .inv-title  { font-size: 13px; font-weight: bold; text-align: center; margin: 2mm 0; }

        /* Info table (invoice, customer) */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 1mm 0; font-size: 10.5px; vertical-align: top; }
        .info-table .lbl  { width: 38%; }
        .info-table .val  { text-align: right; font-weight: bold; word-break: break-all; }

        /* Items table */
        .items-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .items-table th {
            font-size: 9.5px; text-transform: uppercase;
            border-bottom: 1px dashed #000;
            padding: 1mm 1mm;
        }
        .items-table th.col-item   { width: 44%; text-align: left; }
        .items-table th.col-qty    { width: 9%;  text-align: center; }
        .items-table th.col-rate   { width: 22%; text-align: right; }
        .items-table th.col-amount { width: 25%; text-align: right; }

        .items-table td { padding: 1.5mm 1mm; vertical-align: top; font-size: 10.5px; }
        .items-table td.col-qty    { text-align: center; }
        .items-table td.col-rate   { text-align: right; white-space: nowrap; }
        .items-table td.col-amount { text-align: right; font-weight: bold; white-space: nowrap; }

        .item-name   { font-weight: bold; }
        .item-detail { font-size: 9px; color: #333; }

        /* Totals table */
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 1mm 0; font-size: 10.5px; vertical-align: top; }
        .totals-table .lbl { width: 55%; }
        .totals-table .val { text-align: right; white-space: nowrap; }
        .row-total { font-size: 13px; font-weight: bold; }

        .payment-box { font-size: 11px; font-weight: bold; text-align: center; padding: 2mm; background: #f0f0f0; margin: 2mm 0; }
        .footer      { text-align: center; font-size: 9.5px; margin-top: 3mm; }

        @media print {
            body { width: 80mm; }
            .no-print { display: none; }
            @page { margin: 0; size: 80mm auto; }
        }
    </style>
</head>
<body>

{{-- Shop Header --}}
<div class="shop-name">{{ strtoupper($settings['business_name'] ?? 'MANYA FOOTWEAR') }}</div>
@if(!empty($settings['business_address']))
<div class="shop-sub">{{ $settings['business_address'] }}</div>
@endif
@if(!empty($settings['business_phone']))
<div class="shop-sub">Tel: {{ $settings['business_phone'] }}</div>
@endif
@if(!empty($settings['business_gst']))
<div class="shop-sub">GSTIN: {{ $settings['business_gst'] }}</div>
@endif

<div class="separator"></div>
<div class="inv-title">CASH MEMO</div>

{{-- Invoice Info --}}
<table class="info-table">
    <tr>
        <td class="lbl">Invoice:</td>
        <td class="val">{{ $sale->invoice_number }}</td>
    </tr>
    <tr>
        <td class="lbl">Date:</td>
        <td class="val">{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
    </tr>
    @if($sale->customer)
    <tr>
        <td class="lbl">Customer:</td>
        <td class="val">{{ $sale->customer->name }}</td>
    </tr>
    <tr>
        <td class="lbl">Mobile:</td>
        <td class="val">{{ $sale->customer->mobile }}</td>
    </tr>
    @else
    <tr>
        <td class="lbl">Customer:</td>
        <td class="val">Walk-in</td>
    </tr>
    @endif
    <tr>
        <td class="lbl">Cashier:</td>
        <td class="val">{{ $sale->user->name }}</td>
    </tr>
</table>

<div class="separator"></div>

{{-- Items Table --}}
<table class="items-table">
    <thead>
        <tr>
            <th class="col-item">Item</th>
            <th class="col-qty">Qty</th>
            <th class="col-rate">Rate</th>
            <th class="col-amount">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->items as $item)
        <tr>
            <td>
                <div class="item-name">{{ Str::limit($item->product_name, 18) }}</div>
                @if($item->product_size || $item->product_color)
                <div class="item-detail">
                    {{ $item->product_size ? 'Sz:'.$item->product_size : '' }}
                    {{ $item->product_color ? ' '.$item->product_color : '' }}
                </div>
                @endif
                @if($item->discount > 0)
                <div class="item-detail">Disc: -&#8377;{{ number_format($item->discount, 2) }}</div>
                @endif
            </td>
            <td class="col-qty">{{ $item->quantity }}</td>
            <td class="col-rate">{{ number_format($item->unit_price, 2) }}</td>
            <td class="col-amount">{{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="separator"></div>

{{-- Sub-totals --}}
<table class="totals-table">
    <tr>
        <td class="lbl">Sub Total:</td>
        <td class="val">&#8377;{{ number_format($sale->subtotal, 2) }}</td>
    </tr>
    @if($sale->tax_amount > 0)
    <tr>
        <td class="lbl">GST:</td>
        <td class="val">+&#8377;{{ number_format($sale->tax_amount, 2) }}</td>
    </tr>
    @endif
    @if($sale->discount_amount > 0)
    <tr>
        <td class="lbl">Discount:</td>
        <td class="val">-&#8377;{{ number_format($sale->discount_amount, 2) }}</td>
    </tr>
    @endif
</table>

<div class="double-separator"></div>

{{-- Total + Payment --}}
<table class="totals-table">
    <tr class="row-total">
        <td class="lbl">TOTAL:</td>
        <td class="val">&#8377;{{ number_format($sale->total_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="lbl">Paid ({{ strtoupper($sale->payment_method) }}):</td>
        <td class="val">&#8377;{{ number_format($sale->paid_amount, 2) }}</td>
    </tr>
    @if($sale->change_amount > 0)
    <tr>
        <td class="lbl">Change Returned:</td>
        <td class="val">&#8377;{{ number_format($sale->change_amount, 2) }}</td>
    </tr>
    @endif
</table>

<div class="separator"></div>

<div class="payment-box">
    Payment: {{ strtoupper($sale->payment_method) }}
    &nbsp;|&nbsp;
    Items: {{ $sale->items->sum('quantity') }} pcs
</div>

<div class="separator"></div>

<div class="footer">
    <div><strong>*** {{ $settings['receipt_footer'] ?? 'Thank You! Come Again!' }} ***</strong></div>
    @if(!empty($settings['receipt_exchange_policy']))
    <div>{{ $settings['receipt_exchange_policy'] }}</div>
    @endif
    @if(!empty($settings['business_gst']))
    <div>GSTIN: {{ $settings['business_gst'] }}</div>
    @endif
    <br>
    <div style="font-size:9px">{{ $sale->invoice_number }} | {{ $sale->created_at->format('d/m/Y') }}</div>
</div>

<div class="no-print" style="text-align:center;margin-top:10mm;padding:5mm">
    <button onclick="window.print()"
        style="padding:8px 20px;background:#e74c3c;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px">
        🖨️ Print Receipt
    </button>
    <button onclick="window.close()"
        style="padding:8px 20px;background:#6c757d;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px;margin-left:8px">
        Close
    </button>
</div>

<script>
    window.onload = function() { window.print(); }
</script>
</body>
</html>
