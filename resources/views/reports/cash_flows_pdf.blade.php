<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Mutasi Arus Kas & Bank</title>
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
        }
        .badge-in { background-color: #dcfce7; color: #15803d; }
        .badge-out { background-color: #fee2e2; color: #b91c1c; }
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
        <div class="report-title">LAPORAN MUTASI ARUS KAS & BANK</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            @if($account)
                &nbsp;|&nbsp; Rekening: {{ $account->name }}
            @endif
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="18%" class="text-left">No. Bukti Mutasi</th>
                <th width="12%" class="text-center">Tanggal</th>
                <th width="18%" class="text-left">Akun Kas / Bank</th>
                <th width="12%" class="text-center">Tipe</th>
                <th width="22%" class="text-left">Kategori & Keterangan</th>
                <th width="14%" class="text-right">Nominal Mutasi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumIn = 0;
                $sumOut = 0;
            @endphp
            @forelse($cashFlows as $index => $cf)
            @php
                $amount = (float) $cf->amount;
                if ($cf->type === 'in') {
                    $sumIn += $amount;
                } else {
                    $sumOut += $amount;
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left font-bold" style="color: #ea580c;">{{ $cf->cash_flow_number }}</td>
                <td class="text-center text-muted">{{ $cf->transaction_date ? \Carbon\Carbon::parse($cf->transaction_date)->format('d/m/Y') : '-' }}</td>
                <td class="text-left font-bold">{{ $cf->account->name ?? '-' }}</td>
                <td class="text-center">
                    <span class="badge {{ $cf->type === 'in' ? 'badge-in' : 'badge-out' }}">
                        {{ $cf->type === 'in' ? 'MASUK (IN)' : 'KELUAR (OUT)' }}
                    </span>
                </td>
                <td class="text-left text-muted">
                    {{ ($cf->category ? '['.ucfirst($cf->category).'] ' : '') . ($cf->description ?: '-') }}
                </td>
                <td class="text-right font-bold" style="color: {{ $cf->type === 'in' ? '#059669' : '#e11d48' }};">
                    {{ $cf->type === 'in' ? '+' : '-' }}Rp {{ number_format($amount, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 16px; color: #94a3b8;">
                    Tidak ada transaksi arus kas ditemukan pada periode ini.
                </td>
            </tr>
            @endforelse

            @if(count($cashFlows) > 0)
            @php
                $netCash = $sumIn - $sumOut;
            @endphp
            <tr class="summary-row">
                <td colspan="4" class="text-right">TOTAL KAS MASUK (IN):</td>
                <td colspan="3" class="text-right font-bold" style="color: #059669;">+Rp {{ number_format($sumIn, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row">
                <td colspan="4" class="text-right">TOTAL KAS KELUAR (OUT):</td>
                <td colspan="3" class="text-right font-bold" style="color: #e11d48;">-Rp {{ number_format($sumOut, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row" style="background-color: #e2e8f0 !important;">
                <td colspan="4" class="text-right">ARUS KAS BERSIH (NET CASH FLOW):</td>
                <td colspan="3" class="text-right font-bold" style="color: {{ $netCash >= 0 ? '#059669' : '#e11d48' }};">
                    {{ $netCash < 0 ? '-Rp ' : 'Rp ' }}{{ number_format(abs($netCash), 0, ',', '.') }}
                </td>
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
