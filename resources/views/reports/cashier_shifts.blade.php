@extends('layouts.admin')

@section('title', 'Laporan Rekap Shift Kasir')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Rekap Shift Kasir (POS)',
        'subtitle' => 'Audit pembukaan/penutupan shift kasir, saldo awal kas kecil, akumulasi penjualan, dan verifikasi selisih fisik uang kas.',
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Penjualan Shift</p>
            <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalShiftSales, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Dari seluruh sesi kasir aktif</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Sesi Shift Kasir</p>
            <h3 class="text-xl font-black text-blue-600 mt-1.5">{{ number_format($totalShiftCount, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Sesi</span></h3>
            <p class="text-[11px] text-slate-500 mt-2">Sesi buka & tutup kasir</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Selisih Fisik Kas (Difference)</p>
            <h3 class="text-xl font-black {{ $totalCashDifference < 0 ? 'text-rose-600' : ($totalCashDifference > 0 ? 'text-emerald-600' : 'text-slate-900') }} mt-1.5">
                {{ $totalCashDifference < 0 ? '-Rp ' : ($totalCashDifference > 0 ? '+Rp ' : 'Rp ') }}{{ number_format(abs($totalCashDifference), 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-slate-500 mt-2">{{ $totalCashDifference != 0 ? 'Terdapat perbedaan kas saat penutupan' : 'Fisik kas 100% seimbang' }}</p>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.cashier-shifts') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Dari Tanggal (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Dari Tanggal
                </label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Sampai Tanggal (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Sampai Tanggal
                </label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Kasir (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Kasir / User
                </label>
                <select name="user_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Kasir</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c->id }}" {{ $userId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Gudang + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Gudang / Cabang
                    </label>
                    <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Cabang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(request()->hasAny(['start_date', 'end_date', 'user_id', 'warehouse_id']))
                    <a href="{{ route('reports.cashier-shifts') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- CASHIER SHIFTS TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="user-check" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Rekap Sesi Shift Kasir</h3>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-white/20 text-white font-bold text-xs">
                {{ $shifts->total() }} Sesi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Kasir & Cabang</th>
                        <th class="py-3 px-4 border-b border-white/10">Waktu Buka / Tutup</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Modal Awal</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Penjualan Kas</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Fisik Kas Tutup</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Selisih Fisik</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shifts as $sh)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5">
                            <div class="font-bold text-slate-900">{{ $sh->user->name ?? 'Kasir' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $sh->warehouse->name ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            <div>Buka: {{ $sh->opened_at ? $sh->opened_at->format('d/m/Y H:i') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">Tutup: {{ $sh->closed_at ? $sh->closed_at->format('d/m/Y H:i') : 'Masih Terbuka' }}</div>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($sh->starting_cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($sh->total_sales, 0, ',', '.') }}
                            <div class="text-[10px] font-normal text-slate-400">{{ $sh->total_transactions }} Struk</div>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-800 font-medium">
                            {{ $sh->closing_cash !== null ? 'Rp ' . number_format($sh->closing_cash, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-black font-mono-num {{ $sh->cash_difference < 0 ? 'text-rose-600' : ($sh->cash_difference > 0 ? 'text-emerald-600' : 'text-slate-400') }}">
                            @if($sh->cash_difference !== null)
                                {{ $sh->cash_difference < 0 ? '-Rp ' : ($sh->cash_difference > 0 ? '+Rp ' : 'Rp ') }}{{ number_format(abs($sh->cash_difference), 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-3 px-5 text-center">
                            @if($sh->status === 'closed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700">Ditutup</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700">Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i data-lucide="user-check" class="w-10 h-10 text-slate-300 mb-2 mx-auto"></i>
                            <p class="font-bold text-sm text-slate-600">Tidak ada sesi shift kasir pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
