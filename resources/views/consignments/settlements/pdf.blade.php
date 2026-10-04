<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Settlement-{{ $settlement->settlement_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #6b21a8;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #6b21a8;
        }
        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 10px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 3px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 6px;
        }
        .items-table th {
            background-color: #f3e8ff;
            color: #581c87;
            text-align: left;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signatures {
            width: 100%;
            margin-top: 30px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            height: 70px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $storeName }}</h1>
        <p>{{ $storeAddress }} | Telp: {{ $storePhone }}</p>
        <h2 style="font-size: 13px; margin-top: 8px; text-transform: uppercase;">FAKTUR REKONSILIASI & BAGI HASIL KONSINYASI</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>No. Settlement</strong></td>
            <td style="width: 35%;">: {{ $settlement->settlement_number }}</td>
            <td style="width: 15%;"><strong>Supplier Penitip</strong></td>
            <td style="width: 35%;">: {{ $settlement->supplier?->name }} ({{ $settlement->supplier?->phone ?? '-' }})</td>
        </tr>
        <tr>
            <td><strong>Periode Penjualan</strong></td>
            <td>: {{ $settlement->start_date->format('d/m/Y') }} s.d. {{ $settlement->end_date->format('d/m/Y') }}</td>
            <td><strong>Status Bayar</strong></td>
            <td>: {{ strtoupper($settlement->payment_status) }}</td>
        </tr>
        <tr>
            <td><strong>Dibuat Oleh</strong></td>
            <td>: {{ $settlement->user?->name ?? 'Admin' }}</td>
            <td><strong>Akun Pembayaran</strong></td>
            <td>: {{ $settlement->paymentAccount?->name ?? 'Kas Toko' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Produk Titipan</th>
                <th class="text-center" style="width: 10%;">Satuan</th>
                <th class="text-center" style="width: 12%;">Qty Terjual</th>
                <th class="text-right" style="width: 15%;">Harga Jual</th>
                <th class="text-right" style="width: 15%;">Harga Setor</th>
                <th class="text-right" style="width: 18%;">Hak Supplier</th>
                <th class="text-right" style="width: 15%;">Laba Toko</th>
            </tr>
        </thead>
        <tbody>
            @foreach($settlement->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name }}</strong><br>
                        <small style="color: #666;">SKU: {{ $item->product?->code }}</small>
                    </td>
                    <td class="text-center">{{ $item->unit?->name }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ number_format($item->quantity_sold, 0) }}</td>
                    <td class="text-right">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->consignment_cost, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: #9f1239;">Rp {{ number_format($item->supplier_payable, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #166534;">Rp {{ number_format($item->store_commission, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #faf5ff; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-center">{{ number_format($settlement->total_sold_quantity, 0) }} Pcs</td>
                <td class="text-right">Rp {{ number_format($settlement->total_gross_sales, 0, ',', '.') }}</td>
                <td></td>
                <td class="text-right" style="color: #9f1239;">Rp {{ number_format($settlement->total_supplier_amount, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #166534;">Rp {{ number_format($settlement->total_store_commission, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Supplier / Penitip Barang,<br><br><br><br>
                ( {{ $settlement->supplier?->name }} )
            </td>
            <td>
                Kasir / Pengelola Toko,<br><br><br><br>
                ( {{ $settlement->user?->name ?? 'Kasir Pro' }} )
            </td>
        </tr>
    </table>

</body>
</html>
