<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Nilai Persediaan Stok</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            text-transform: uppercase;
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
            padding: 3px 0;
            font-size: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 10px;
        }
        .data-table th {
            background-color: #f5f5f5;
            text-align: left;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-box {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 4px 8px;
            font-size: 11px;
        }
        .summary-box tr.total {
            font-weight: bold;
            border-top: 1px solid #333;
            border-bottom: 2px solid #333;
        }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #888;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN NILAI PERSEDIAAN STOK (INVENTORY VALUATION)</h2>
        <p>POS & INVENTORY SYSTEM</p>
        <p>Tanggal Laporan: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Gudang / Lokasi:</strong></td>
            <td width="35%">{{ $warehouse->name ?? 'Semua Lokasi Gudang' }}</td>
            <td width="20%"><strong>Total Variasi Produk:</strong></td>
            <td width="30%">{{ number_format($stocks->count(), 0, ',', '.') }} SKU</td>
        </tr>
        <tr>
            <td><strong>Dicetak Oleh:</strong></td>
            <td>{{ auth()->user()->name ?? 'Administrator' }}</td>
            <td><strong>Total Kuantitas Fisik:</strong></td>
            <td>{{ number_format($totalQty, 0, ',', '.') }} Unit</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode SKU</th>
                <th width="25%">Nama Produk</th>
                <th width="15%">Gudang</th>
                <th width="10%" class="text-right">Sisa Stok</th>
                <th width="15%" class="text-right">HPP Pokok (Rp)</th>
                <th width="15%" class="text-right">Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $index => $s)
            @php
                $cost = (float) ($s->product->purchase_price ?? 0);
                $val = (float) $s->quantity * $cost;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $s->product->code ?? '-' }}</strong></td>
                <td>{{ $s->product->name ?? '-' }}</td>
                <td>{{ $s->warehouse->name ?? '-' }}</td>
                <td class="text-right"><strong>{{ number_format($s->quantity, 0, ',', '.') }}</strong> {{ $s->product->baseUnit->name ?? 'Pcs' }}</td>
                <td class="text-right">Rp {{ number_format($cost, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($val, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data stok persediaan ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-box">
        <tr>
            <td>Total Item Fisik:</td>
            <td class="text-right">{{ number_format($totalQty, 0, ',', '.') }} Unit</td>
        </tr>
        <tr class="total">
            <td>TOTAL NILAI ASET PERSEDIAAN:</td>
            <td class="text-right">Rp {{ number_format($totalValuation, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis dari Sistem POS pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
