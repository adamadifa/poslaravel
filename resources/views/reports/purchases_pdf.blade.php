<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pembelian</title>
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
        <h2>LAPORAN PEMBELIAN & PENGADAAN (PO)</h2>
        <p>POS & INVENTORY SYSTEM</p>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Supplier:</strong></td>
            <td width="35%">{{ $supplier->name ?? 'Semua Supplier' }}</td>
            <td width="20%"><strong>Total Dokumen PO:</strong></td>
            <td width="30%">{{ number_format($totalOrders, 0, ',', '.') }} Dokumen</td>
        </tr>
        <tr>
            <td><strong>Gudang Tujuan:</strong></td>
            <td>{{ $warehouse->name ?? 'Semua Gudang' }}</td>
            <td><strong>Tanggal Cetak:</strong></td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No. PO</th>
                <th width="12%">Tgl Pesan</th>
                <th width="20%">Supplier</th>
                <th width="15%">Gudang</th>
                <th width="10%" class="text-center">Status</th>
                <th width="10%" class="text-right">Diskon</th>
                <th width="13%" class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $p->po_number }}</strong></td>
                <td>{{ $p->order_date ? \Carbon\Carbon::parse($p->order_date)->format('d/m/Y') : '-' }}</td>
                <td>{{ $p->supplier->name ?? '-' }}</td>
                <td>{{ $p->warehouse->name ?? '-' }}</td>
                <td class="text-center" style="text-transform: uppercase;">{{ $p->status }}</td>
                <td class="text-right">{{ $p->discount_amount > 0 ? 'Rp ' . number_format($p->discount_amount, 0, ',', '.') : '-' }}</td>
                <td class="text-right"><strong>Rp {{ number_format($p->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada transaksi pembelian pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-box">
        <tr>
            <td>Total Subtotal Pembelian:</td>
            <td class="text-right">Rp {{ number_format($purchases->sum('subtotal'), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Potongan Diskon:</td>
            <td class="text-right" style="color: #c00;">-Rp {{ number_format($purchases->sum('discount_amount'), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Ongkos Kirim:</td>
            <td class="text-right">Rp {{ number_format($purchases->sum('shipping_cost'), 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td>TOTAL PENGADAAN AKTIF:</td>
            <td class="text-right">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis dari Sistem POS pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
