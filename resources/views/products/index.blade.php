@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
        
        <!-- Search & Category Filter Form -->
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[220px]">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, SKU, atau barcode..." class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
            </div>

            <select name="category_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            @if(request('search') || request('category_id'))
                <a href="{{ route('products.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                    Reset
                </a>
            @endif
        </form>

        <button class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Produk</span>
        </button>
    </div>

    <!-- Products Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-850 text-slate-400 dark:text-slate-400 uppercase font-bold tracking-wider text-[10px]">
                        <th class="py-3.5 px-5">Produk</th>
                        <th class="py-3.5 px-5">Kategori</th>
                        <th class="py-3.5 px-5">Satuan Dasar</th>
                        <th class="py-3.5 px-5 text-right">Harga Beli (HPP)</th>
                        <th class="py-3.5 px-5 text-right">Harga Jual</th>
                        <th class="py-3.5 px-5 text-center">Stok Total</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium text-slate-700 dark:text-slate-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 shrink-0">
                                        <i data-lucide="package" class="w-4 h-4 text-brand-500"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $product->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono flex items-center gap-2">
                                            <span>SKU: {{ $product->code }}</span>
                                            <span>•</span>
                                            <span>Barcode: {{ $product->barcode ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-semibold text-slate-600 dark:text-slate-300">
                                {{ $product->baseUnit->name ?? '-' }} ({{ $product->baseUnit->short_name ?? '-' }})
                            </td>
                            <td class="py-3.5 px-5 text-right font-mono text-slate-500 dark:text-slate-400">
                                Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-mono font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($product->stocks->sum('quantity'), 0) }}
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada data produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
