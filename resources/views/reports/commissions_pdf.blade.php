<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Komisi Staf & Layanan</title>
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
            color: #059669;
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
        .badge-completed { background-color: #dcfce7; color: #15803d; }
        .badge-in-progress { background-color: #fef3c7; color: #b45309; }
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
    <!-- KOP LAPORAN -->
    <div class="kop-container">
        <div class="company-name">{{ \App\Models\Setting::get('company_name', \App\Models\Setting::get('app_name', 'POS PRO')) }}</div>
        <div class="report-title">LAPORAN REKAP KOMISI STAF & TEKNISI LAYANAN</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} &nbsp;|&nbsp;
            Cabang: {{ $warehouse ? $warehouse->name : 'Semua Cabang' }} &nbsp;|&nbsp;
            Staf: {{ $staff ? $staff->name : 'Semua Staf / Teknisi' }} &nbsp;|&nbsp;
            Status: {{ $status ? ucfirst($status) : 'Semua Status' }} &nbsp;|&nbsp;
            Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- TABEL DATA KOMISI -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 85px;">No. Faktur</th>
                <th style="width: 90px;">Waktu Selesai</th>
                <th style="width: 100px;">Staf / Teknisi</th>
                <th>Layanan / Jasa</th>
                <th style="width: 95px;">Pelanggan</th>
                <th style="width: 80px;">Cabang</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 45px;">Durasi</th>
                <th style="width: 75px;" class="text-right">Harga Jasa</th>
                <th style="width: 80px;" class="text-right">Komisi Staf</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalServiceVal = 0;
                $totalCommVal = 0;
            @endphp
            @forelse($assignments as $idx => $assignment)
                @php
                    $saleItem = $assignment->saleItem;
                    $sale = $saleItem?->sale;
                    $product = $saleItem?->product;
                    $staffUser = $assignment->staff;
                    $customer = $sale?->customer;
                    $wh = $sale?->warehouse;
                    $subtotal = (float)($saleItem?->subtotal ?? 0);
                    $comm = (float)($assignment->commission_amount ?? 0);

                    if ($assignment->status === 'completed') {
                        $totalServiceVal += $subtotal;
                        $totalCommVal += $comm;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold text-center">{{ $sale?->invoice_number ?? '-' }}</td>
                    <td class="text-center text-muted">
                        {{ $assignment->completed_at ? $assignment->completed_at->format('d/m/Y H:i') : ($assignment->started_at ? $assignment->started_at->format('d/m/Y H:i') : '-') }}
                    </td>
                    <td class="font-bold">{{ $staffUser?->name ?? 'Belum Ditugaskan' }}</td>
                    <td>
                        <div class="font-bold">{{ $product?->name ?? '-' }}</div>
                        @if($saleItem?->notes)
                            <div class="text-muted" style="font-size: 7.5px;">Note: {{ $saleItem->notes }}</div>
                        @endif
                    </td>
                    <td>{{ $customer?->name ?? 'Pelanggan Umum' }}</td>
                    <td>{{ $wh?->name ?? '-' }}</td>
                    <td class="text-center">
                        @if($assignment->status === 'completed')
                            <span class="badge badge-completed">Selesai</span>
                        @elseif($assignment->status === 'in_progress')
                            <span class="badge badge-in-progress">Dikerjakan</span>
                        @else
                            <span class="badge badge-in-progress">Menunggu</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $assignment->duration_actual_minutes ? $assignment->duration_actual_minutes . ' m' : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #059669;">Rp {{ number_format($comm, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada catatan komisi staf pada filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($assignments) > 0)
        <tfoot>
            <tr class="summary-row">
                <td colspan="9" class="text-right" style="padding-right: 8px;">TOTAL KOMISI CAIR (STATUS SELESAI):</td>
                <td class="text-right">Rp {{ number_format($totalServiceVal, 0, ',', '.') }}</td>
                <td class="text-right font-bold" style="color: #059669;">Rp {{ number_format($totalCommVal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- FOOTER INFO -->
    <div class="footer-note">
        <div class="footer-left">
            Total {{ $totalCompleted }} layanan selesai berhasil diselesaikan oleh staf/teknisi.
        </div>
        <div class="footer-right">
            Dicetak oleh: {{ auth()->user()->name ?? 'System' }} | Halaman 1
        </div>
    </div>
</body>
</html>
