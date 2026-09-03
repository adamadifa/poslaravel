@extends('layouts.admin')

@section('title', 'Laporan Piutang Pelanggan (AR)')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Piutang Pelanggan (AR)',
        'subtitle' => 'Pemantauan sisa saldo piutang penjualan belum lunas, batas tempo, dan analisa umur piutang (Aging Schedule).',
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Sisa Piutang Aktif</p>
            <h3 class="text-xl font-black text-rose-600 mt-1.5">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Hak penerimaan kas dari pelanggan</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Nilai Tagihan Penjualan</p>
            <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Dari transaksi kredit belum lunas</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Sudah Diterima (DP / Cicilan)</p>
            <h3 class="text-xl font-black text-emerald-600 mt-1.5">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Kas yang telah masuk ke rekening</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Piutang Kritis (>30 Hari)</p>
            @php
                $overdue = $agingSummary['31_60'] + $agingSummary['61_90'] + $agingSummary['90_plus'];
            @endphp
            <h3 class="text-xl font-black {{ $overdue > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1.5">
                Rp {{ number_format($overdue, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-slate-500 mt-2">{{ $overdue > 0 ? 'Perlu follow-up penagihan' : 'Semua tagihan < 30 hari' }}</p>
        </div>
    </div>

    <!-- AGING SCHEDULE CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-center">
            <span class="text-[10px] font-extrabold uppercase text-slate-500">0 - 30 Hari</span>
            <p class="font-black text-sm text-slate-900 mt-1">Rp {{ number_format($agingSummary['0_30'], 0, ',', '.') }}</p>
        </div>
        <div class="p-3.5 rounded-xl bg-amber-50/60 border border-amber-200 text-center">
            <span class="text-[10px] font-extrabold uppercase text-amber-700">31 - 60 Hari</span>
            <p class="font-black text-sm text-amber-800 mt-1">Rp {{ number_format($agingSummary['31_60'], 0, ',', '.') }}</p>
        </div>
        <div class="p-3.5 rounded-xl bg-orange-50/60 border border-orange-200 text-center">
            <span class="text-[10px] font-extrabold uppercase text-orange-700">61 - 90 Hari</span>
            <p class="font-black text-sm text-orange-800 mt-1">Rp {{ number_format($agingSummary['61_90'], 0, ',', '.') }}</p>
        </div>
        <div class="p-3.5 rounded-xl bg-rose-50/60 border border-rose-200 text-center">
            <span class="text-[10px] font-extrabold uppercase text-rose-700">> 90 Hari</span>
            <p class="font-black text-sm text-rose-800 mt-1">Rp {{ number_format($agingSummary['90_plus'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.receivables') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-10 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Filter Pelanggan
                </label>
                <select name="customer_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->phone ?? 'No Phone' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex items-center justify-center">
                @if(request()->has('customer_id'))
                    <a href="{{ route('reports.receivables') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- RECEIVABLES TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="coins" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Tagihan Piutang Penjualan</h3>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-white/20 text-white font-bold text-xs">
                {{ $receivablesData->count() }} Tagihan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Invoice</th>
                        <th class="py-3 px-4 border-b border-white/10">Pelanggan</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal Transaksi</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Umur Piutang</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Nilai Transaksi</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Sisa Piutang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receivablesData as $r)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5 font-mono font-bold text-brand-600">
                            {{ $r->invoice_number }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $r->customer_name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $r->customer_phone }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $r->sale_date ? \Carbon\Carbon::parse($r->sale_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $r->days_outstanding > 60 ? 'bg-rose-50 text-rose-700' : ($r->days_outstanding > 30 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $r->days_outstanding }} Hari ({{ $r->aging_group }})
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-emerald-600 font-semibold">
                            Rp {{ number_format($r->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-5 text-right font-bold text-rose-600">
                            Rp {{ number_format($r->outstanding_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i data-lucide="check-circle-2" class="w-10 h-10 text-emerald-400 mx-auto mb-2"></i>
                            <p class="font-bold text-sm text-slate-700">Tidak ada piutang customer aktif!</p>
                            <p class="text-xs text-slate-400 mt-0.5">Semua tagihan penjualan telah lunas dibayarkan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
