@extends('layouts.admin')

@section('content')
    <div class="space-y-6">

        <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
            <form method="GET" action="{{ route('products.index') }}"
                class="flex flex-wrap sm:flex-nowrap items-center gap-3 flex-1 w-full">

                <!-- Outset Floating-label Search Input -->
                <div
                    class="relative flex-1 min-w-[200px] w-full rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2.5">
                    <label
                        class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Cari Produk
                    </label>
                    <div class="flex items-center gap-3">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Ketik nama, SKU, atau barcode..."
                            class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <!-- Outset Floating-label Category Filter -->
                <div
                    class="relative min-w-[170px] rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2.5">
                    <label
                        class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Filter Kategori
                    </label>
                    <div class="flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <select name="category_id" id="filter_category_id" onchange="this.form.submit()"
                            class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if(request('search') || request('category_id'))
                    <a href="{{ route('products.index') }}"
                        class="text-xs font-bold text-rose-500 hover:text-rose-600 px-2 shrink-0">
                        Reset
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Tombol Cetak Barcode Universal -->
                <button type="button" onclick="openBarcodePrintModal()"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-brand-500 hover:text-brand-600 text-slate-700 dark:text-slate-200 font-bold text-xs shadow-2xs transition whitespace-nowrap">
                    <i data-lucide="printer" class="w-4 h-4 text-brand-500"></i>
                    <span>Cetak Barcode</span>
                    <span id="bulk_barcode_badge"
                        class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-brand-500 text-white">0</span>
                </button>

                <button onclick="openCreateProductModal()"
                    class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Produk</span>
                </button>
            </div>
        </div>

        <!-- Products Table Card (Solid Orange Unified Header) -->
        <div
            class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">

            <!-- Table Card Header (Solid Orange Theme) -->
            <div
                class="px-6 pt-5 pb-3 bg-brand-500 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center shadow-xs">
                        <i data-lucide="package" class="w-4 h-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-white tracking-tight flex items-center gap-2">
                            <span>Katalog Master Produk</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white">
                                {{ $products->total() }} Total
                            </span>
                        </h3>
                        <p class="text-[11px] text-white/80">Daftar produk, barcode satuan, harga beli/jual, dan stok sistem
                        </p>
                    </div>
                </div>

                @if(request('search') || request('category_id'))
                    <div
                        class="text-[11px] text-white flex items-center gap-1.5 bg-black/15 px-3 py-1.5 rounded-xl self-start sm:self-auto">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-white/90"></i>
                        <span>Filter Aktif:
                            @if(request('search')) <strong>"{{ request('search') }}"</strong> @endif
                            @if(request('category_id')) (Kategori ID: {{ request('category_id') }}) @endif
                        </span>
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-brand-500 text-white/95 uppercase font-extrabold tracking-wider text-[10px]">
                            <th class="pt-1.5 pb-2.5 pl-5 pr-1 w-7">
                                <input type="checkbox" id="select_all_products" onchange="toggleSelectAllProducts(this)"
                                    class="rounded text-brand-500 focus:ring-brand-400 w-3.5 h-3.5 cursor-pointer bg-white">
                            </th>
                            <th class="pt-1.5 pb-2.5 px-4">Info Produk & SKU</th>
                            <th class="pt-1.5 pb-2.5 px-5">Kategori</th>
                            <th class="pt-1.5 pb-2.5 px-5">Satuan Dasar</th>
                            <th class="pt-1.5 pb-2.5 px-5 text-right">Harga Beli (HPP)</th>
                            <th class="pt-1.5 pb-2.5 px-5 text-right">Harga Jual</th>
                            <th class="pt-1.5 pb-2.5 px-5 text-center">Status</th>
                            <th class="pt-1.5 pb-2.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Checkbox Item -->
                                <td class="py-2.5 pl-5 pr-1">
                                    <input type="checkbox"
                                        class="product-row-checkbox rounded text-brand-500 focus:ring-brand-400 w-3.5 h-3.5 cursor-pointer"
                                        value="{{ $product->id }}" data-name="{{ addslashes($product->name) }}"
                                        data-code="{{ $product->code }}"
                                        data-barcode="{{ $product->barcode ?: $product->code }}"
                                        data-unit="{{ $product->baseUnit ? $product->baseUnit->display_name : 'Pcs' }}"
                                        data-price="{{ $product->selling_price }}" onchange="handleProductCheckboxChange()">
                                </td>

                                <!-- Produk Info -->
                                <td class="py-2.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden shrink-0">
                                            @if($product->image_path)
                                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-slate-900 leading-snug">{{ $product->name }}</span>
                                                @if($product->product_type === 'raw_material')
                                                    <span
                                                        class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Bahan
                                                        Baku</span>
                                                @elseif($product->product_type === 'food')
                                                    <span
                                                        class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-orange-50 text-orange-700 border border-orange-200">Food</span>
                                                @elseif($product->product_type === 'beverage')
                                                    <span
                                                        class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Beverage</span>
                                                @elseif($product->product_type === 'service')
                                                    <span
                                                        class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Jasa</span>
                                                @endif
                                                @if($product->recipes && $product->recipes->count() > 0)
                                                    <span
                                                        class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-0.5"
                                                        title="Resep BOM Aktif">
                                                        <i data-lucide="chef-hat" class="w-2.5 h-2.5"></i>
                                                        {{ $product->recipes->count() }} Bahan
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1.5">
                                                <span class="text-brand-600 font-semibold">{{ $product->code }}</span>
                                                <span>•</span>
                                                <span>Barcode: {{ $product->barcode ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="py-2.5 px-5">
                                    @if($product->category)
                                        <span
                                            class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $product->category->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>

                                <!-- Satuan Dasar & Multi Satuan Badge -->
                                <td class="py-2.5 px-5">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-semibold text-slate-800">{{ $product->baseUnit ? $product->baseUnit->display_name : '-' }}</span>
                                        @if($product->conversions->count() > 0)
                                            <span
                                                class="px-1.5 py-0.2 rounded-md text-[9px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                +{{ $product->conversions->count() }} Satuan
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Harga Beli (HPP) -->
                                <td class="py-2.5 px-5 text-right font-mono text-slate-500">
                                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </td>

                                <!-- Harga Jual -->
                                <td class="py-2.5 px-5 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </td>

                                <!-- Status -->
                                <td class="py-2.5 px-5 text-center">
                                    @if($product->is_active)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-2.5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- Cetak Barcode Item Ini -->
                                        <button type="button"
                                            data-id="{{ $product->id }}"
                                            onclick="openProductBarcodeFromRow(productsMap[this.dataset.id])"
                                            class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition"
                                            title="Cetak Label Barcode">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        </button>

                                        @if($product->product_type !== 'raw_material' && $product->product_type !== 'service')
                                            <button type="button" data-pid="{{ $product->id }}" data-pname="{{ e($product->name) }}"
                                                data-pprice="{{ (float) $product->selling_price }}"
                                                data-ptype="{{ $product->product_type }}"
                                                onclick="openRecipeModal(this.dataset.pid, this.dataset.pname, this.dataset.pprice, this.dataset.ptype)"
                                                class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition"
                                                title="Kelola Resep (BOM)">
                                                <i data-lucide="chef-hat" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @endif
                                        <button type="button"
                                            data-id="{{ $product->id }}"
                                            onclick="openEditProductModal(productsMap[this.dataset.id])"
                                            class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 transition"
                                            title="Edit Produk">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <form id="delete-prod-{{ $product->id }}"
                                            action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete-prod-{{ $product->id }}', 'Hapus Produk {{ addslashes($product->name) }}?', 'Produk ini akan dihapus dari katalog.')"
                                                class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="Hapus Produk">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada data produk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
@push('modals')
    <!-- CREATE, EDIT, RECIPE & BARCODE PRINT MODALS -->
    @include('products._create_modal')
    @include('products._edit_modal')
    @include('products._recipe_modal')
    @include('products._barcode_print_modal')
@endpush
@push('scripts')
    <script>
        const availableUnits = @json($units);
        const customerGroups = @json($customerGroups);
        const productsMap = @json($products->keyBy('id'));
    </script>
    @include('products.scripts._unit_helpers')
    @include('products.scripts._dynamic_rows')
    @include('products.scripts._product_crud_modals')
    @include('products.scripts._recipe_modal')
    @include('products.scripts._barcode_printer')
    @include('products.scripts._document_ready')
@endpush