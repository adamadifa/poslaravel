<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pesanan Pembelian (PO)</title>
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
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #ea580c;
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
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            padding: 6px 4px;
            border: 1px solid #334155;
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
            background-color: #f8fafc;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .summary-row td {
            background-color: #f1f5f9 !important;
            font-weight: bold;
            font-size: 9px;
            color: #0f172a;
            border-top: 1px solid #94a3b8 !important;
            border-bottom: 3px double #0f172a !important;
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
        .badge-received { background-color: #dcfce7; color: #15803d; }
        .badge-sent { background-color: #e0f2fe; color: #0369a1; }
        .badge-draft { background-color: #f1f5f9; color: #475569; }
        .badge-cancelled { background-color: #fee2e2; color: #b91c1c; }
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
    @php
        $companyName = \App\Models\Setting::get('company_name', \App\Models\Setting::get('app_name', 'POS Retail Pro'));
    @endphp

    <div class="kop-container">
        <div class="company-name">{{ strtoupper($companyName) }}</div>
        <div class="report-title">LAPORAN PESANAN PEMBELIAN (PURCHASE ORDERS)</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            &nbsp;|&nbsp; Supplier: {{ $supplier->name ?? 'Semua Supplier' }}
            &nbsp;|&nbsp; Gudang: {{ $warehouse->name ?? 'Semua Gudang' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="12%">No. PO</th>
                <th width="9%">Tgl Pesan</th>
                <th width="9%">Estimasi Tiba</th>
                <th width="18%">Supplier</th>
                <th width="13%">Gudang Tujuan</th>
                <th width="8%">Status</th>
                <th width="8%" class="text-right">Subtotal</th>
                <th width="5%" class="text-right">Diskon</th>
                <th width="5%" class="text-right">Pajak</th>
                <th width="5%" class="text-right">Ongkir</th>
                <th width="9%" class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumSubtotal = 0;
                $sumDiscount = 0;
                $sumTax = 0;
                $sumShipping = 0;
                $sumGrandTotal = 0;
            @endphp
            @forelse($purchases as $index => $p)
            @php
                $isCancelled = ($p->status === 'cancelled');
                if (!$isCancelled) {
                    $sumSubtotal += (float) $p->subtotal;
                    $sumDiscount += (float) $p->discount_amount;
                    $sumTax += (float) $p->tax_amount;
                    $sumShipping += (float) $p->shipping_cost;
                    $sumGrandTotal += (float) $p->grand_total;
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center font-bold">{{ $p->po_number }}</td>
                <td class="text-center">{{ $p->order_date ? \Carbon\Carbon::parse($p->order_date)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $p->expected_date ? \Carbon\Carbon::parse($p->expected_date)->format('d/m/Y') : '-' }}</td>
                <td class="text-left">{{ $p->supplier->name ?? '-' }}</td>
                <td class="text-left">{{ $p->warehouse->name ?? '-' }}</td>
                <td class="text-center">
                    @if($p->status === 'received')
                        <span class="badge badge-received">Diterima</span>
                    @elseif($p->status === 'sent')
                        <span class="badge badge-sent">Terkirim</span>
                    @elseif($p->status === 'cancelled')
                        <span class="badge badge-cancelled">Batal</span>
                    @else
                        <span class="badge badge-draft">{{ ucfirst($p->status) }}</span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($p->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">{{ $p->discount_amount > 0 ? number_format($p->discount_amount, 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $p->tax_amount > 0 ? number_format($p->tax_amount, 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $p->shipping_cost > 0 ? number_format($p->shipping_cost, 0, ',', '.') : '-' }}</td>
                <td class="text-right font-bold">{{ number_format($p->grand_total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 16px; color: #94a3b8;">
                    Tidak ada data dokumen pembelian yang ditemukan pada periode ini.
                </td>
            </tr>
            @endforelse

            @if($purchases->count() > 0)
            <tr class="summary-row">
                <td colspan="7" class="text-right">TOTAL KESELURUHAN ({{ number_format($totalOrders, 0, ',', '.') }} PO AKTIF):</td>
                <td class="text-right">Rp {{ number_format($sumSubtotal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumDiscount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumTax, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumShipping, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumGrandTotal, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer-note">
        <div class="footer-left">Dokumen ini dihasilkan secara otomatis oleh Sistem POS & Inventori.</div>
        <div class="footer-right">Halaman 1 dari 1</div>
    </div>
</body>
</html>
