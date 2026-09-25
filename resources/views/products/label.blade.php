<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Barcode Label</title>
<style>body{font-family:Arial,sans-serif;text-align:center;padding:30px}.label{display:inline-block;border:1px dashed #999;padding:18px;min-width:320px}.name{font-weight:bold;font-size:18px}.price{font-size:22px;font-weight:bold;margin-top:8px}@media print{button{display:none}.label{border:0}}</style></head>
<body><div class="label"><div class="name">{{ $product->product_name }}</div><div>{{ $product->product_code }}</div><svg id="barcode"></svg><div class="price">₱{{ number_format((float) $product->selling_price, 2) }}</div></div><br><br><button onclick="window.print()">Print Label</button>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script><script>JsBarcode('#barcode', @json($product->barcode), {format:'CODE128',displayValue:true,height:60,margin:10});</script></body></html>
