<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Rekonsiliasi Konsinyasi - {{ $settlement->settlement_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #6b21a8;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #6b21a8;
        }
        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 11px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 4px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 7px;
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
            margin-top: 40px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            height: 80px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
        }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-unpaid { background: #ffe4e6; color: #9f1239; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #7e22ce; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Cetak Faktur Settlement
        </button>
    </div>

    <div class="header">
        <h1>{{ $storeName }}</h1>
        <p>{{ $storeAddress }} | Telp: {{ $storePhone }}</p>
        <h2 style="font-size: 14px; margin-top: 10px; text-transform: uppercase;">FAKTUR REKONSILIASI & BAGI HASIL KONSINYASI</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>No. Settlement</strong></td>
            <td style="width: 35%;">: <strong>{{ $settlement->settlement_number }}</strong></td>
            <td style="width: 15%;"><strong>Supplier Penitip</strong></td>
            <td style="width: 35%;">: {{ $settlement->supplier?->name }} ({{ $settlement->supplier?->phone ?? '-' }})</td>
        </tr>
        <tr>
            <td><strong>Periode Penjualan</strong></td>
            <td>: {{ $settlement->start_date->format('d/m/Y') }} s.d. {{ $settlement->end_date->format('d/m/Y') }}</td>
            <td><strong>Status Pelunasan</strong></td>
            <td>: 
                @if($settlement->payment_status === 'paid')
                    <span class="status-badge status-paid">LUNAS ({{ $settlement->payment_date ? $settlement->payment_date->format('d/m/Y') : '-' }})</span>
                @else
                    <span class="status-badge status-unpaid">BELUM DIBAYAR</span>
                @endif
            </td>
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
                <th>Nama Produk Titipan</th>
                <th class="text-center" style="width: 10%;">Satuan</th>
                <th class="text-center" style="width: 12%;">Qty Terjual</th>
                <th class="text-right" style="width: 15%;">Harga Jual POS</th>
                <th class="text-right" style="width: 15%;">Harga Setor (HPP)</th>
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
                <td class="text-right" style="color: #9f1239; font-size: 13px;">Rp {{ number_format($settlement->total_supplier_amount, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #166534; font-size: 13px;">Rp {{ number_format($settlement->total_store_commission, 0, ',', '.') }}</td>
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
