@extends('layouts.admin')

@section('title', 'Laporan Hutang Usaha Supplier (AP)')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Hutang Usaha Supplier (AP)',
        'subtitle' => 'Pemantauan sisa saldo hutang dagang, penerimaan barang belum lunas, dan analisa umur hutang (Aging Schedule).',
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Sisa Hutang Aktif</p>
            <h3 class="text-xl font-black text-rose-600 mt-1.5">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Kewajiban pembayaran ke supplier</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Nilai Tagihan Pembelian</p>
            <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalPayable, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Dari seluruh GRN / PO belum lunas</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Sudah Dibayarkan</p>
            <h3 class="text-xl font-black text-emerald-600 mt-1.5">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Cicilan/pelunasan kas yang telah disetor</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Hutang Jatuh Tempo (>30 Hari)</p>
            @php
                $overdue = $agingSummary['31_60'] + $agingSummary['61_90'] + $agingSummary['90_plus'];
            @endphp
            <h3 class="text-xl font-black {{ $overdue > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1.5">
                Rp {{ number_format($overdue, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-slate-500 mt-2">{{ $overdue > 0 ? 'Perlu prioritas pembayaran' : 'Semua tagihan < 30 hari' }}</p>
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
        <form method="GET" action="{{ route('reports.payables') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-10 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Filter Supplier
                </label>
                <select name="supplier_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ $supplierId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex items-center justify-center">
                @if(request()->has('supplier_id'))
                    <a href="{{ route('reports.payables') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- PAYABLES TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="receipt-text" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Tagihan Hutang Pembelian</h3>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-white/20 text-white font-bold text-xs">
                {{ $payablesData->count() }} Tagihan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Penerimaan / PO</th>
                        <th class="py-3 px-4 border-b border-white/10">Supplier</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal Masuk</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Umur Hutang</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Nilai Tagihan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Sisa Hutang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payablesData as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5 font-mono font-bold text-brand-600">
                            {{ $p->receipt_number }}
                            <div class="text-[10px] text-slate-400 font-sans font-normal">PO: {{ $p->po_number }}</div>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $p->supplier_name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $p->receipt_date ? \Carbon\Carbon::parse($p->receipt_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $p->days_outstanding > 60 ? 'bg-rose-50 text-rose-700' : ($p->days_outstanding > 30 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $p->days_outstanding }} Hari ({{ $p->aging_group }})
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($p->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-emerald-600 font-semibold">
                            Rp {{ number_format($p->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-5 text-right font-bold text-rose-600">
                            Rp {{ number_format($p->outstanding_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i data-lucide="check-circle-2" class="w-10 h-10 text-emerald-400 mx-auto mb-2"></i>
                            <p class="font-bold text-sm text-slate-700">Tidak ada tagihan hutang belum lunas!</p>
                            <p class="text-xs text-slate-400 mt-0.5">Seluruh pembelian barang supplier telah terbayar penuh.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
