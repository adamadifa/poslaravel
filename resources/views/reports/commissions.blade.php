@extends('layouts.admin')

@section('title', 'Laporan Komisi Staf & Teknisi')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Komisi Staf & Jasa',
        'subtitle' => 'Rekapitulasi komisi pengerjaan jasa/layanan per staf dan teknisi untuk periode penggajian.',
        'exportPdfUrl' => route('reports.commissions.export-pdf', request()->query()),
        'exportExcelUrl' => route('reports.commissions.export-excel', request()->query())
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Komisi Cair -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Komisi Staf (Cair)</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">Rp {{ number_format($totalCommission, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="badge-percent" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                <span>Dari total <strong>{{ $totalServicesCompleted }}</strong> tugas selesai</span>
            </div>
        </div>

        <!-- Card 2: Total Omset Layanan -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Nilai Omset Layanan</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mt-1.5">Rp {{ number_format($totalServiceRevenue, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                Nilai subtotal pesanan jasa yang masuk
            </div>
        </div>

        <!-- Card 3: Layanan Selesai vs Antrian -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Tugas Layanan</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mt-1.5">{{ $totalServicesCompleted + $totalServicesInProgress }} <span class="text-xs font-normal text-slate-500">Tugas</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-[11px]">
                <span class="text-emerald-600 font-semibold">{{ $totalServicesCompleted }} Selesai</span>
                <span class="text-slate-300 dark:text-slate-700">•</span>
                <span class="text-amber-600 font-semibold">{{ $totalServicesInProgress }} Dikerjakan</span>
            </div>
        </div>

        <!-- Card 4: Rata-rata Durasi -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Rata-rata Durasi Kerja</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mt-1.5">{{ round($avgDurationMinutes, 1) }} <span class="text-xs font-normal text-slate-500">Menit</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="timer" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                Waktu pengerjaan riil per transaksi
            </div>
        </div>
    </div>

    <!-- FILTER SECTION -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.commissions') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Dari Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Dari Tanggal
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- Sampai Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Sampai Tanggal
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- Staf / Teknisi (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Staf / Teknisi
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="staff_user_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Staf & Teknisi</option>
                        @foreach($staffUsers as $su)
                            <option value="{{ $su->id }}" {{ $staffUserId == $su->id ? 'selected' : '' }}>
                                {{ $su->name }} ({{ $su->roles->pluck('name')->implode(', ') ?: 'Staff' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Gudang / Cabang (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Cabang / Gudang
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="warehouse" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="warehouse_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Cabang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status Pengerjaan (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Status
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="check-square" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                        <option value="waiting" {{ $status == 'waiting' ? 'selected' : '' }}>Menunggu</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons (Col 1) -->
            <div class="lg:col-span-1 flex items-center gap-2 justify-end">
                <button type="submit" class="h-10 w-full sm:w-auto px-4 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5" title="Terapkan Filter">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span class="lg:hidden">Filter</span>
                </button>
                @if(request()->hasAny(['start_date', 'end_date', 'staff_user_id', 'warehouse_id', 'status']))
                    <a href="{{ route('reports.commissions') }}" class="h-10 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition flex items-center justify-center" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- REKAP PER STAF (SUMMARY CARD SECTION) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-emerald-500"></i>
                    Ringkasan Akumulasi Komisi per Staf
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Total komisi yang berhak diterima masing-masing staf pada periode ini.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3">Nama Staf / Teknisi</th>
                        <th class="px-4 py-3 text-center">Total Tugas</th>
                        <th class="px-4 py-3 text-center">Tugas Selesai</th>
                        <th class="px-4 py-3 text-center">Rata-rata Durasi</th>
                        <th class="px-5 py-3 text-right">Total Komisi Cair (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($staffSummary as $sum)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-black text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($sum->staff?->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <div>{{ $sum->staff?->name ?? 'Belum Ditugaskan' }}</div>
                                <div class="text-[11px] font-normal text-slate-400">{{ $sum->staff?->email ?? '-' }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center font-semibold text-slate-700 dark:text-slate-300">
                            {{ $sum->total_tasks }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                {{ $sum->completed_tasks }} Selesai
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center font-medium text-slate-600 dark:text-slate-400">
                            {{ $sum->avg_duration ? round($sum->avg_duration, 0) . ' Menit' : '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-black text-emerald-600 dark:text-emerald-400 text-sm">
                            Rp {{ number_format($sum->total_commission_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-xs">
                            Belum ada rekapitulasi pengerjaan jasa pada filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RINCIAN DETAIL LOG KOMISI PER TRANSAKSI -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-brand-500"></i>
                    Rincian Riwayat Transaksi Jasa & Komisi
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Detail per faktur, layanan yang dikerjakan, dan komisi staf per item.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3">No. Faktur</th>
                        <th class="px-4 py-3">Waktu Selesai</th>
                        <th class="px-4 py-3">Staf Pelaksana</th>
                        <th class="px-4 py-3">Layanan / Jasa</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Durasi</th>
                        <th class="px-4 py-3 text-right">Harga Jasa</th>
                        <th class="px-5 py-3 text-right">Komisi Staf</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($assignments as $assignment)
                    @php
                        $saleItem = $assignment->saleItem;
                        $sale = $saleItem?->sale;
                        $product = $saleItem?->product;
                        $staff = $assignment->staff;
                        $customer = $sale?->customer;
                    @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3.5">
                            @if($sale)
                                <a href="{{ route('sales.show', $sale->id) }}" class="font-bold text-brand-600 hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                            @else
                                <span class="font-bold text-slate-700 dark:text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            {{ $assignment->completed_at ? $assignment->completed_at->format('d/m/Y H:i') : ($assignment->started_at ? $assignment->started_at->format('d/m/Y H:i') : '-') }}
                        </td>
                        <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">
                            {{ $staff?->name ?? 'Belum Ditugaskan' }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $product?->name ?? '-' }}</div>
                            @if($saleItem?->notes)
                                <div class="text-[11px] text-slate-400">Catatan: {{ $saleItem->notes }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300">
                            {{ $customer?->name ?? 'Pelanggan Umum' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($assignment->status === 'completed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    Selesai
                                </span>
                            @elseif($assignment->status === 'in_progress')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                    Dikerjakan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                    Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center text-slate-600 dark:text-slate-400 font-medium">
                            {{ $assignment->duration_actual_minutes ? $assignment->duration_actual_minutes . ' m' : '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-slate-700 dark:text-slate-300">
                            Rp {{ number_format($saleItem?->subtotal ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                            Rp {{ number_format($assignment->commission_amount ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="clipboard-x" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-xs">Tidak ditemukan riwayat komisi pada rentang filter ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assignments->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
