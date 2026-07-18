<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Labels - Mayank Footware</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .no-print {
            background: #2c3e50; color: #fff; padding: 12px 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .labels-container { padding: 10px; }
        .row {
            display: flex; justify-content: center; gap: 3mm;
            margin-bottom: 5px;
        }
        .label {
            width: 50mm; height: 25mm; background: #fff; border: 1px solid #ddd;
            display: block; padding: 0.5mm 1.5mm; text-align: center;
            overflow: hidden;
        }
        .label .shop-name   { font-size: 2mm; line-height: 1; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3mm; color: #333; }
        .label .product-name { font-size: 1.8mm; line-height: 1; font-weight: bold; margin: 0.3mm 0; max-width: 47mm; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .label .product-detail { font-size: 1.6mm; line-height: 1; color: #555; }
        .label .price       { font-size: 2.4mm; line-height: 1; font-weight: bold; color: #e74c3c; margin: 0.3mm 0; }
        .label .barcode-img { display: block; margin: 0.3mm auto; max-width: 47mm; height: 10mm; }
        .label .barcode-num { font-size: 1.6mm; line-height: 1; font-family: 'Courier New', monospace; color: #555; letter-spacing: 0.2mm; }
        .label .no-product  { font-size: 1.6mm; line-height: 1; color: #999; font-style: italic; }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .labels-container { padding: 0; }
            .row {
                width: 104mm; height: 24mm; margin: 0 auto;
                page-break-after: always; page-break-inside: avoid;
                gap: 3mm;
            }
            .row:last-child { page-break-after: auto; }
            .label { border: none; width: 50mm; height: 24mm; }
            @page { size: 104mm 25mm; margin: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div>
        <strong>Barcode Labels</strong> &nbsp;
        <span style="opacity:.7">{{ $barcodes->count() }} label(s)</span>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <label style="font-size:13px">Copies per label:</label>
        <input type="number" id="copiesInput" value="1" min="1" max="20"
            style="width:60px;padding:4px;border-radius:4px;border:none;text-align:center">
        <button onclick="generateLabels()"
            style="padding:6px 16px;background:#27ae60;color:#fff;border:none;border-radius:4px;cursor:pointer">
            Update
        </button>
        <button onclick="window.print()"
            style="padding:6px 16px;background:#e74c3c;color:#fff;border:none;border-radius:4px;cursor:pointer">
            🖨️ Print Labels
        </button>
        <button onclick="window.close()"
            style="padding:6px 16px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer">
            Close
        </button>
    </div>
</div>

<div class="labels-container" id="labelsContainer">
    @foreach($barcodes->chunk(2) as $rowItems)
    <div class="row">
        @foreach($rowItems as $item)
        <div class="label">
            <div class="shop-name">Mayank Footware</div>
            @if($item->product_id)
                <div class="product-name" title="{{ $item->product->name }}">
                    {{ Str::limit($item->product->name, 25) }}
                </div>
                @if($item->product->size || $item->product->color)
                <div class="product-detail">
                    {{ $item->product->size ? 'Size: '.$item->product->size : '' }}
                    {{ $item->product->size && $item->product->color ? ' | ' : '' }}
                    {{ $item->product->color ?? '' }}
                </div>
                @endif
                <div class="price">&#8377;{{ number_format($item->product->selling_price, 0) }}</div>
            @else
                <div class="no-product">Unassigned</div>
            @endif
            <img class="barcode-img"
                src="{{ $barcodeImages[$item->barcode] ?? '' }}"
                alt="{{ $item->barcode }}">
            <div class="barcode-num">{{ $item->barcode }}</div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

<script>
var labelsData = [];
@foreach($barcodes as $item)
labelsData.push({
    barcode:       {{ json_encode($item->barcode) }},
    barcodeImg:    {{ json_encode($barcodeImages[$item->barcode] ?? '') }},
    productName:   {{ json_encode($item->product_id ? $item->product->name : null) }},
    sellingPrice:  {{ $item->product_id ? $item->product->selling_price : 0 }},
    size:          {{ json_encode($item->product_id ? $item->product->size : null) }},
    color:         {{ json_encode($item->product_id ? $item->product->color : null) }},
});
@endforeach

function labelHtml(d) {
    var detail = '';
    if (d.size)              detail += 'Size: ' + d.size;
    if (d.size && d.color)   detail += ' | ';
    if (d.color)             detail += d.color;

    var name = d.productName
        ? (d.productName.length > 25 ? d.productName.substring(0, 25) + '...' : d.productName)
        : null;

    return '<div class="label">' +
            '<div class="shop-name">Mayank Footware</div>' +
            (name
                ? '<div class="product-name">' + name + '</div>'
                : '<div class="no-product">Unassigned</div>') +
            (detail ? '<div class="product-detail">' + detail + '</div>' : '') +
            (d.sellingPrice ? '<div class="price">&#8377;' + Math.round(d.sellingPrice) + '</div>' : '') +
            '<img class="barcode-img" src="' + d.barcodeImg + '" alt="' + d.barcode + '">' +
            '<div class="barcode-num">' + d.barcode + '</div>' +
        '</div>';
}

function generateLabels() {
    var copies    = parseInt(document.getElementById('copiesInput').value) || 1;
    var container = document.getElementById('labelsContainer');
    container.innerHTML = '';

    var allLabels = [];
    labelsData.forEach(function (d) {
        for (var i = 0; i < copies; i++) allLabels.push(d);
    });

    for (var j = 0; j < allLabels.length; j += 2) {
        var rowHtml = labelHtml(allLabels[j]);
        if (allLabels[j + 1]) rowHtml += labelHtml(allLabels[j + 1]);
        container.innerHTML += '<div class="row">' + rowHtml + '</div>';
    }
}
</script>
</body>
</html>
