@extends('layouts.admin')

@section('title', 'Laporan Hasil Stok Opname')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Hasil Stok Opname',
        'subtitle' => 'Rekapitulasi pelaksanaan penyesuaian fisik stok, selisih kuantitas fisik vs sistem, serta nilai penyesuaian aset.',
    ])

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.stock-opnames') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
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

            <!-- Gudang (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Gudang
                </label>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Opname + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Status
                    </label>
                    <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                @if(request()->hasAny(['start_date', 'end_date', 'warehouse_id', 'status']))
                    <a href="{{ route('reports.stock-opnames') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- OPNAME TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="clipboard-check" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Dokumen Stok Opname</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $opnames->total() }} Dokumen
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Opname & Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-4 border-b border-white/10">Petugas / Approver</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Total Item Dicek</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opnames as $op)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5">
                            <div class="font-mono font-bold text-brand-600">{{ $op->opname_number }}</div>
                            <div class="text-[11px] text-slate-400">{{ $op->opname_date ? \Carbon\Carbon::parse($op->opname_date)->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $op->warehouse->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            <div>Pelaksana: <strong>{{ $op->conductor->name ?? '-' }}</strong></div>
                            @if($op->approver)
                                <div class="text-[10px] text-emerald-600 font-semibold">Disetujui: {{ $op->approver->name }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">
                            {{ $op->items->count() }} Item
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($op->status === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700">Disetujui</span>
                            @elseif($op->status === 'draft')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700">Draft</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-rose-50 text-rose-700">{{ $op->status }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-5 text-right">
                            <a href="{{ route('stock-opnames.show', $op->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-600 font-bold text-xs transition">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="clipboard-check" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Tidak ada riwayat stok opname pada periode ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $opnames->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
