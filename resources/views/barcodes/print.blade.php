<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Labels - Mayank Footware</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .no-print { background: #2c3e50; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; }
        .labels-container { padding: 10px; display: flex; flex-wrap: wrap; gap: 5px; justify-content: flex-start; }
        .label {
            width: 60mm; height: 30mm;
            background: #fff; border: 1px solid #ddd;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 0.5mm 2mm; text-align: center;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .label .shop-name   { font-size: 2mm; line-height: 1; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3mm; color: #333; }
        .label .product-name { font-size: 1.8mm; line-height: 1; font-weight: bold; margin: 0.3mm 0; max-width: 56mm; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .label .product-detail { font-size: 1.6mm; line-height: 1; color: #555; }
        .label .price       { font-size: 2.6mm; line-height: 1; font-weight: bold; color: #e74c3c; margin: 0.3mm 0; }
        .label .barcode-img { max-width: 56mm; height: 11mm; }
        .label .barcode-num { font-size: 1.6mm; line-height: 1; font-family: 'Courier New', monospace; color: #555; letter-spacing: 0.2mm; }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .labels-container { padding: 0; display: block; }
            .label {
                border: none; width: 60mm; height: 30mm;
                margin: 0 auto; page-break-after: always; page-break-inside: avoid;
            }
            .label:last-child { page-break-after: auto; }
            @page { size: 60mm 30mm; margin: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div>
        <strong>Barcode Labels</strong> &nbsp;
        <span style="opacity:.7">{{ $products->count() }} product(s)</span>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <label style="font-size:13px">Copies per product:</label>
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
    @foreach($products as $product)
    <div class="label">
        <div class="shop-name">Mayank Footware</div>
        <div class="product-name" title="{{ $product->name }}">{{ Str::limit($product->name, 25) }}</div>
        @if($product->size || $product->color)
        <div class="product-detail">
            {{ $product->size ? 'Size: '.$product->size : '' }}
            {{ $product->size && $product->color ? ' | ' : '' }}
            {{ $product->color ?? '' }}
        </div>
        @endif
        <div class="price">&#8377;{{ number_format($product->selling_price, 0) }}</div>
        <img class="barcode-img"
            src="{{ route('barcode.image', $product->barcode) }}"
            alt="{{ $product->barcode }}">
        <div class="barcode-num">{{ $product->barcode }}</div>
    </div>
    @endforeach
</div>

<script>
var productsData = [];
@foreach($products as $product)
productsData.push({
    id: {{ $product->id }},
    name: {{ json_encode($product->name) }},
    barcode: {{ json_encode($product->barcode) }},
    selling_price: {{ $product->selling_price }},
    size: {{ json_encode($product->size) }},
    color: {{ json_encode($product->color) }}
});
@endforeach

function generateLabels() {
    var copies = parseInt(document.getElementById('copiesInput').value) || 1;
    var container = document.getElementById('labelsContainer');
    container.innerHTML = '';

    productsData.forEach(function(p) {
        for (var i = 0; i < copies; i++) {
            var detail = '';
            if (p.size) detail += 'Size: ' + p.size;
            if (p.size && p.color) detail += ' | ';
            if (p.color) detail += p.color;

            var name = p.name.length > 25 ? p.name.substring(0, 25) + '...' : p.name;
            var priceInt = Math.round(p.selling_price);

            container.innerHTML +=
                '<div class="label">' +
                    '<div class="shop-name">Mayank Footware</div>' +
                    '<div class="product-name" title="' + p.name + '">' + name + '</div>' +
                    (detail ? '<div class="product-detail">' + detail + '</div>' : '') +
                    '<div class="price">&#8377;' + priceInt + '</div>' +
                    '<img class="barcode-img" src="/barcode/image/' + p.barcode + '" alt="' + p.barcode + '">' +
                    '<div class="barcode-num">' + p.barcode + '</div>' +
                '</div>';
        }
    });
}
</script>
</body>
</html>
