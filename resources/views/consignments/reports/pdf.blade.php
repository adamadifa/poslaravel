<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Transaksi & Rekonsiliasi Konsinyasi</title>
    <style>
        @page {
            margin: 18px 20px 22px 20px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .kop-container {
            border-bottom: 2px solid #6b21a8;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #581c87;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #7e22ce;
            margin-bottom: 4px;
        }
        .report-meta {
            font-size: 8.5px;
            font-style: italic;
            color: #64748b;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #581c87;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            padding: 6px 4px;
            border: 1px solid #3b0764;
            text-align: center;
        }
        .data-table td {
            padding: 5px 4px;
            font-size: 8.5px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #faf5ff;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-purple { color: #6b21a8; }
        .text-emerald { color: #047857; }
        .text-rose { color: #be123c; }
        .summary-row td {
            background-color: #f3e8ff !important;
            font-weight: bold;
            font-size: 9px;
            color: #3b0764;
            border-top: 1px solid #9333ea !important;
            border-bottom: 3px double #581c87 !important;
            padding: 6px 4px;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-settled { background-color: #dcfce7; color: #15803d; }
        .badge-pending { background-color: #fef3c7; color: #b45309; }
        .footer-note {
            margin-top: 8px;
            font-size: 8px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>
    <!-- Kop Laporan -->
    <div class="kop-container">
        <div class="company-name">{{ strtoupper($storeName) }}</div>
        <div class="report-title">LAPORAN PENJUALAN & REKONSILIASI KONSINYASI (TITIP JUAL)</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            &nbsp;|&nbsp; Supplier: {{ $supplier->name ?? 'Semua Supplier Mitra Penitip' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Tanggal</th>
                <th width="12%">No. Transaksi</th>
                <th width="15%">Supplier Penitip</th>
                <th width="18%">Produk Titipan</th>
                <th width="6%">Qty</th>
                <th width="10%" class="text-right">Harga Jual</th>
                <th width="10%" class="text-right">Omzet Kotor</th>
                <th width="10%" class="text-right">Hak Supplier</th>
                <th width="10%" class="text-right">Komisi Toko</th>
                <th width="6%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumQty = 0;
                $sumGross = 0;
                $sumSupplier = 0;
                $sumStore = 0;
            @endphp
            @forelse($saleItems as $index => $item)
                @php
                    $itemPayable = (float) $item->quantity * (float) $item->consignment_cost;
                    $itemCommission = max(0, (float) $item->subtotal - $itemPayable);

                    $sumQty += (float) $item->quantity;
                    $sumGross += (float) $item->subtotal;
                    $sumSupplier += $itemPayable;
                    $sumStore += $itemCommission;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->sale?->created_at ? $item->sale->created_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center font-bold">{{ $item->sale?->invoice_number ?? '-' }}</td>
                    <td class="text-left font-bold text-purple">{{ $item->consignmentSupplier?->name ?? 'Supplier Umum' }}</td>
                    <td class="text-left">
                        {{ $item->product?->name }}
                        <small class="text-muted" style="display:block; font-size:7.5px;">{{ $item->product?->code }}</small>
                    </td>
                    <td class="text-center font-bold">{{ number_format($item->quantity, 0) }} {{ $item->unit?->name }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right font-bold text-rose">Rp {{ number_format($itemPayable, 0, ',', '.') }}</td>
                    <td class="text-right font-bold text-emerald">Rp {{ number_format($itemCommission, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item->consignment_settlement_id)
                            <span class="badge badge-settled">Settled</span>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 16px; color: #94a3b8;">
                        Tidak ada data transaksi produk konsinyasi pada rentang tanggal yang dipilih.
                    </td>
                </tr>
            @endforelse

            <!-- Summary Row -->
            @if($saleItems->count() > 0)
                <tr class="summary-row">
                    <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
                    <td class="text-center">{{ number_format($sumQty, 0) }}</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($sumGross, 0, ',', '.') }}</td>
                    <td class="text-right text-rose">Rp {{ number_format($sumSupplier, 0, ',', '.') }}</td>
                    <td class="text-right text-emerald">Rp {{ number_format($sumStore, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer-note">
        <div class="footer-left">Dokumen ini dihasilkan secara otomatis oleh Modul Konsinyasi Sistem POS Retail Pro.</div>
        <div class="footer-right">Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>
