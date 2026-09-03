<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penjualan</title>
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
        <h2>LAPORAN PENJUALAN</h2>
        <p>POS & INVENTORY SYSTEM</p>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Gudang / Cabang:</strong></td>
            <td width="35%">{{ $warehouse->name ?? 'Semua Cabang' }}</td>
            <td width="20%"><strong>Total Transaksi:</strong></td>
            <td width="30%">{{ number_format($totalTransactions, 0, ',', '.') }} Transaksi</td>
        </tr>
        <tr>
            <td><strong>Dicetak Oleh:</strong></td>
            <td>{{ auth()->user()->name ?? 'Administrator' }}</td>
            <td><strong>Tanggal Cetak:</strong></td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No. Invoice</th>
                <th width="12%">Waktu</th>
                <th width="15%">Kasir</th>
                <th width="18%">Pelanggan</th>
                <th width="10%">Metode</th>
                <th width="12%" class="text-right">Diskon</th>
                <th width="13%" class="text-right">Total Bersih</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $s)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $s->invoice_number }}</strong></td>
                <td>{{ $s->sale_date ? \Carbon\Carbon::parse($s->sale_date)->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $s->user->name ?? '-' }}</td>
                <td>{{ $s->customer->name ?? 'Pelanggan Umum' }}</td>
                <td class="text-center" style="text-transform: uppercase;">{{ $s->payment_method ?? 'CASH' }}</td>
                <td class="text-right">{{ $s->discount_amount > 0 ? 'Rp ' . number_format($s->discount_amount, 0, ',', '.') : '-' }}</td>
                <td class="text-right"><strong>Rp {{ number_format($s->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada transaksi penjualan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-box">
        <tr>
            <td>Total Penjualan Kotor:</td>
            <td class="text-right">Rp {{ number_format($sales->sum('subtotal'), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Potongan Diskon:</td>
            <td class="text-right" style="color: #c00;">-Rp {{ number_format($sales->sum('discount_amount'), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Pajak (PPN):</td>
            <td class="text-right">Rp {{ number_format($sales->sum('tax_amount'), 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td>TOTAL PENDAPATAN BERSIH:</td>
            <td class="text-right">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis dari Sistem POS pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
