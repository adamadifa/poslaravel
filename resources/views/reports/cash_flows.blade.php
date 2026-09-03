@extends('layouts.admin')

@section('title', 'Laporan Mutasi Arus Kas & Bank')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Mutasi Arus Kas & Bank',
        'subtitle' => 'Pencatatan mutasi kas masuk, pengeluaran kas operasional, dan arus kas bersih pada tiap rekening kas/bank.',
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Kas Masuk (Cash In)</p>
            <h3 class="text-xl font-black text-emerald-600 mt-1.5">Rp {{ number_format($totalCashIn, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Penerimaan kas penjualan & setoran</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Kas Keluar (Cash Out)</p>
            <h3 class="text-xl font-black text-rose-600 mt-1.5">Rp {{ number_format($totalCashOut, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Beban operasional & pembayaran supplier</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Arus Kas Bersih (Net Cash Flow)</p>
            <h3 class="text-xl font-black {{ $netCashFlow >= 0 ? 'text-brand-600' : 'text-rose-600' }} mt-1.5">
                {{ $netCashFlow < 0 ? '-Rp ' : 'Rp ' }}{{ number_format(abs($netCashFlow), 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-slate-500 mt-2">Total Kas Masuk - Total Kas Keluar</p>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.cash-flows') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
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

            <!-- Akun Kas / Bank (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Akun Kas & Bank
                </label>
                <select name="account_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Akun Kas / Bank</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ $acc->account_number ?? 'Kas' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Tipe Mutasi + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Tipe Mutasi
                    </label>
                    <select name="type" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Tipe</option>
                        <option value="in" {{ $type === 'in' ? 'selected' : '' }}>Kas Masuk (IN)</option>
                        <option value="out" {{ $type === 'out' ? 'selected' : '' }}>Kas Keluar (OUT)</option>
                    </select>
                </div>

                @if(request()->hasAny(['start_date', 'end_date', 'account_id', 'type']))
                    <a href="{{ route('reports.cash-flows') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- CASH FLOW TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="arrow-down-up" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Riwayat Mutasi Arus Kas</h3>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-white/20 text-white font-bold text-xs">
                {{ $cashFlows->total() }} Mutasi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Bukti / Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Akun Kas / Bank</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Tipe</th>
                        <th class="py-3 px-4 border-b border-white/10">Kategori</th>
                        <th class="py-3 px-4 border-b border-white/10">Keterangan</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Nominal Mutasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashFlows as $cf)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5">
                            <div class="font-mono font-bold text-brand-600">{{ $cf->cash_flow_number }}</div>
                            <div class="text-[10px] text-slate-400">{{ $cf->transaction_date ? \Carbon\Carbon::parse($cf->transaction_date)->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $cf->account->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($cf->type === 'in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700">Masuk (IN)</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-rose-50 text-rose-700">Keluar (OUT)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium">
                            {{ ucfirst($cf->category ?: 'Umum') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $cf->description ?: '-' }}
                        </td>
                        <td class="py-3 px-5 text-right font-black font-mono-num {{ $cf->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $cf->type === 'in' ? '+' : '-' }}Rp {{ number_format($cf->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i data-lucide="arrow-down-up" class="w-10 h-10 text-slate-300 mb-2 mx-auto"></i>
                            <p class="font-bold text-sm text-slate-600">Tidak ada riwayat arus kas pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cashFlows->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $cashFlows->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
