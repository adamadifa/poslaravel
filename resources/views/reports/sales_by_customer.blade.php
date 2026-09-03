@extends('layouts.admin')

@section('title', 'Laporan Penjualan per Pelanggan')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Penjualan per Pelanggan (Customer)',
        'subtitle' => 'Pantau kontribusi belanja pelanggan, frekuensi order, dan nilai rata-rata transaksi tiap customer.',
    ])

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.sales.customers') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Cari Pelanggan (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Pelanggan
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Nama pelanggan / nomor HP..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

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

            <!-- Reset Button (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-center">
                @if(request()->hasAny(['search', 'start_date', 'end_date']))
                    <a href="{{ route('reports.sales.customers') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- CUSTOMER TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="users" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Analisis Kontribusi Belanja Pelanggan</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $customerReport->total() }} Pelanggan
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Nama Pelanggan</th>
                        <th class="py-3 px-4 border-b border-white/10">Kontak / Kode</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Jumlah Transaksi</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total Belanja (Omset)</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Rata-rata Belanja (AOV)</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Transaksi Terakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customerReport as $cust)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5 font-bold text-slate-900">
                            {{ $cust->customer_name }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 font-medium">
                            {{ $cust->customer_phone ?? ($cust->customer_code ?? '-') }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-brand-600">
                            {{ $cust->total_orders }} Transaksi
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($cust->total_spent, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($cust->avg_spent, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-5 text-center text-slate-500">
                            {{ $cust->last_order_date ? \Carbon\Carbon::parse($cust->last_order_date)->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="users" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Tidak ada transaksi pelanggan pada periode ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customerReport->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $customerReport->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
