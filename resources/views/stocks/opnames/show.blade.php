@extends('layouts.admin')

@section('title', 'Detail Dokumen Stok Opname ' . $stockOpname->opname_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ url()->previous() == route('reports.stock-opnames') ? route('reports.stock-opnames') : route('stock-opnames.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Kembali">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                    <span>Dokumen Stok Opname</span>
                    <span class="font-mono text-brand-600 font-bold text-base px-2.5 py-1 rounded-lg bg-brand-50 border border-brand-200/60">{{ $stockOpname->opname_number }}</span>
                </h1>
            </div>
            <p class="text-xs text-slate-500 mt-1 ml-11">
                Audit fisik persediaan gudang & perbandingan selisih kuantitas sistem
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($stockOpname->status, ['draft', 'in_progress']))
                <form action="{{ route('stock-opnames.approve', $stockOpname->id) }}" method="POST" class="inline" id="approve_opname_{{ $stockOpname->id }}">
                    @csrf
                    <button type="button" onclick="confirmDelete('approve_opname_{{ $stockOpname->id }}', 'Setujui Opname {{ $stockOpname->opname_number }}?', 'Sistem akan otomatis menyesuaikan stok fisik gudang dan memposting kartu mutasi inventaris!')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Setujui & Posting Stok</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Meta Information Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Lokasi Gudang</span>
            <h4 class="font-black text-sm text-slate-900 mt-1">{{ $stockOpname->warehouse->name ?? '-' }}</h4>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $stockOpname->warehouse->code ?? '-' }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal & Status</span>
            <div class="flex items-center gap-2 mt-1">
                <h4 class="font-black text-sm text-slate-900">{{ $stockOpname->opname_date ? $stockOpname->opname_date->format('d/m/Y') : '-' }}</h4>
                @if($stockOpname->status === 'approved' || $stockOpname->status === 'completed')
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700">Disetujui</span>
                @elseif($stockOpname->status === 'draft')
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700">Draft</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-amber-50 text-amber-700">{{ $stockOpname->status }}</span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $stockOpname->notes ?: 'Tidak ada catatan' }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Petugas Audit</span>
            <h4 class="font-black text-sm text-slate-900 mt-1">{{ $stockOpname->conductor->name ?? '-' }}</h4>
            @if($stockOpname->approver)
                <p class="text-[11px] text-emerald-600 font-semibold mt-0.5">Disetujui oleh: {{ $stockOpname->approver->name }}</p>
            @else
                <p class="text-[11px] text-slate-400 mt-0.5">Belum disetujui</p>
            @endif
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Nilai Selisih</span>
            @php
                $totalDiffValue = $stockOpname->items->sum('difference_value');
            @endphp
            <h4 class="font-black text-sm mt-1 {{ $totalDiffValue < 0 ? 'text-rose-600' : ($totalDiffValue > 0 ? 'text-emerald-600' : 'text-slate-900') }}">
                {{ $totalDiffValue < 0 ? '-Rp ' : 'Rp ' }}{{ number_format(abs($totalDiffValue), 0, ',', '.') }}
            </h4>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $stockOpname->items->count() }} Produk Dicek</p>
        </div>
    </div>

    <!-- Opname Items Table Card -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Rincian Hasil Pengecekan Fisik Produk</h3>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-white/20 text-white font-bold text-xs">
                {{ $stockOpname->items->count() }} Item
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No</th>
                        <th class="py-3 px-4 border-b border-white/10">Kode & Nama Produk</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Stok Sistem</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Fisik Riil</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Selisih Qty</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">HPP Modal</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Nilai Selisih</th>
                        <th class="py-3 px-5 border-b border-white/10">Keterangan / Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stockOpname->items as $idx => $item)
                    @php
                        $diffQty = (float) $item->difference_qty;
                        $diffVal = (float) $item->difference_value;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $item->product->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $item->product->code ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-slate-600">
                            {{ number_format($item->system_qty, 2) }} {{ $item->product->baseUnit->name ?? 'Pcs' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">
                            {{ number_format($item->physical_qty, 2) }} {{ $item->product->baseUnit->name ?? 'Pcs' }}
                        </td>
                        <td class="py-3 px-4 text-right font-black font-mono-num {{ $diffQty > 0 ? 'text-emerald-600' : ($diffQty < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                            {{ $diffQty > 0 ? '+' : '' }}{{ number_format($diffQty, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-black font-mono-num {{ $diffVal > 0 ? 'text-emerald-600' : ($diffVal < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                            {{ $diffVal < 0 ? '-Rp ' : ($diffVal > 0 ? '+Rp ' : 'Rp ') }}{{ number_format(abs($diffVal), 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-5 text-slate-600">
                            {{ $item->reason ?: '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <p class="font-bold text-sm">Tidak ada rincian item pada dokumen stok opname ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
