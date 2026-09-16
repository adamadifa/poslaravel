@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-3 flex-1 w-full">
            
            <!-- Outset Floating-label Search Input -->
            <div class="relative flex-1 min-w-[200px] w-full rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Cari Produk
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik nama, SKU, atau barcode..." 
                           class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Outset Floating-label Category Filter -->
            <div class="relative min-w-[170px] rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Filter Kategori
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="category_id" id="filter_category_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white focus:ring-0 focus:outline-none cursor-pointer">
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
                <a href="{{ route('products.index') }}" class="text-xs font-bold text-rose-500 hover:text-rose-600 px-2 shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Tombol Cetak Barcode Universal -->
            <button type="button" onclick="openBarcodePrintModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-brand-500 hover:text-brand-600 text-slate-700 dark:text-slate-200 font-bold text-xs shadow-2xs transition whitespace-nowrap">
                <i data-lucide="printer" class="w-4 h-4 text-brand-500"></i>
                <span>Cetak Barcode</span>
                <span id="bulk_barcode_badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-brand-500 text-white">0</span>
            </button>

            <button onclick="openCreateProductModal()" class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    <!-- Products Table Card (Solid Orange Unified Header) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Table Card Header (Solid Orange Theme) -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-white">
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
                    <p class="text-[11px] text-white/80">Daftar produk, barcode satuan, harga beli/jual, dan stok sistem</p>
                </div>
            </div>

            @if(request('search') || request('category_id'))
                <div class="text-[11px] text-white flex items-center gap-1.5 bg-black/15 px-3 py-1.5 rounded-xl self-start sm:self-auto">
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
                            <input type="checkbox" id="select_all_products" onchange="toggleSelectAllProducts(this)" class="rounded text-brand-500 focus:ring-brand-400 w-3.5 h-3.5 cursor-pointer bg-white">
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
                                       value="{{ $product->id }}"
                                       data-name="{{ addslashes($product->name) }}"
                                       data-code="{{ $product->code }}"
                                       data-barcode="{{ $product->barcode ?: $product->code }}"
                                       data-unit="{{ $product->baseUnit ? $product->baseUnit->display_name : 'Pcs' }}"
                                       data-price="{{ $product->selling_price }}"
                                       onchange="handleProductCheckboxChange()">
                            </td>

                            <!-- Produk Info -->
                            <td class="py-2.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden shrink-0">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-slate-900 leading-snug">{{ $product->name }}</span>
                                            @if($product->product_type === 'raw_material')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Bahan Baku</span>
                                            @elseif($product->product_type === 'food')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-orange-50 text-orange-700 border border-orange-200">Food</span>
                                            @elseif($product->product_type === 'beverage')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Beverage</span>
                                            @elseif($product->product_type === 'service')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Jasa</span>
                                            @endif
                                            @if($product->recipes && $product->recipes->count() > 0)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-0.5" title="Resep BOM Aktif">
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
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Satuan Dasar & Multi Satuan Badge -->
                            <td class="py-2.5 px-5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold text-slate-800">{{ $product->baseUnit ? $product->baseUnit->display_name : '-' }}</span>
                                    @if($product->conversions->count() > 0)
                                        <span class="px-1.5 py-0.2 rounded-md text-[9px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-2.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Cetak Barcode Item Ini -->
                                    <button type="button" onclick="openProductBarcodeFromRow({{ json_encode($product) }})" class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" title="Cetak Label Barcode">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </button>

                                    @if($product->product_type !== 'raw_material' && $product->product_type !== 'service')
                                        <button onclick="openRecipeModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, '{{ $product->product_type }}')" class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Kelola Resep (BOM)">
                                            <i data-lucide="chef-hat" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                    <button onclick="openEditProductModal({{ json_encode($product) }})" class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 transition" title="Edit Produk">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form id="delete-prod-{{ $product->id }}" action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('delete-prod-{{ $product->id }}', 'Hapus Produk {{ addslashes($product->name) }}?', 'Produk ini akan dihapus dari katalog.')" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Produk">
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

    // Helper to format unit display name without duplicate text (e.g. "Dus" instead of "Dus (dus)")
    function formatUnitDisplay(u) {
        if (!u) return '';
        const name = (u.name || '').trim();
        const shortName = (u.short_name || '').trim();
        if (shortName && shortName.toLowerCase() !== name.toLowerCase()) {
            return `${name} (${shortName})`;
        }
        return name;
    }

    // Helper to get only units that are configured for this specific product (Base Unit + Conversions)
    function getProductConfiguredUnits(type, currentUnitId = null) {
        const configuredUnitIds = new Set();
        let baseUnitId = null;

        // 1. Base unit from Tab 1
        const baseUnitSelect = document.getElementById(`${type}_input_base_unit_id`);
        if (baseUnitSelect && baseUnitSelect.value) {
            baseUnitId = parseInt(baseUnitSelect.value);
            configuredUnitIds.add(baseUnitId);
        }

        // 2. Conversion units from Tab 3
        const convContainer = document.getElementById(`${type}_conversions_container`);
        if (convContainer) {
            const fromSelects = convContainer.querySelectorAll('select[name*="[from_unit_id]"]');
            fromSelects.forEach(s => {
                if (s.value) configuredUnitIds.add(parseInt(s.value));
            });
            const toSelects = convContainer.querySelectorAll('select[name*="[to_unit_id]"]');
            toSelects.forEach(s => {
                if (s.value) configuredUnitIds.add(parseInt(s.value));
            });
        }

        // 3. Keep current unit ID if provided (e.g. when editing existing saved row)
        if (currentUnitId) {
            configuredUnitIds.add(parseInt(currentUnitId));
        }

        // If at least one unit is configured, return only the configured units
        if (configuredUnitIds.size > 0) {
            const filtered = availableUnits.filter(u => configuredUnitIds.has(u.id));
            if (filtered.length > 0) {
                return filtered.map(u => ({
                    ...u,
                    is_base: u.id === baseUnitId
                }));
            }
        }

        // Fallback to all available units if no unit configured yet
        return availableUnits.map(u => ({
            ...u,
            is_base: false
        }));
    }

    // Refresh unit dropdowns in Tab 2 (Barcodes) & Tab 4 (Tiered Prices)
    function updateProductUnitDropdowns(type) {
        // Refresh Tiered Prices
        const tieredContainer = document.getElementById(`${type}_tiered_container`);
        if (tieredContainer) {
            const selects = tieredContainer.querySelectorAll('select[name*="[unit_id]"]');
            selects.forEach(sel => {
                const currentVal = $(sel).val();
                const units = getProductConfiguredUnits(type, currentVal);
                let options = '<option value="">Pilih Satuan</option>';
                units.forEach(u => {
                    const tag = u.is_base ? ' (Satuan Dasar)' : '';
                    options += `<option value="${u.id}" ${u.id == currentVal ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
                });
                $(sel).html(options).val(currentVal).trigger('change.select2');
            });
        }

        // Refresh Barcodes
        const barcodeContainer = document.getElementById(`${type}_barcodes_container`);
        if (barcodeContainer) {
            const selects = barcodeContainer.querySelectorAll('select[name*="[unit_id]"]');
            selects.forEach(sel => {
                const currentVal = $(sel).val();
                const units = getProductConfiguredUnits(type, currentVal);
                let options = '<option value="">Pilih Satuan</option>';
                units.forEach(u => {
                    const tag = u.is_base ? ' (Satuan Dasar)' : '';
                    options += `<option value="${u.id}" ${u.id == currentVal ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
                });
                $(sel).html(options).val(currentVal).trigger('change.select2');
            });
        }
    }

    // Tab switcher helper (Pill segmented style)
    function switchProductTab(type, tab) {
        const tabs = ['basic', 'barcodes', 'conversions', 'tiered'];
        tabs.forEach(t => {
            const content = document.getElementById(`${type}_tab_content_${t}`);
            const btn = document.getElementById(`${type}_tab_btn_${t}`);
            if (content && btn) {
                if (t === tab) {
                    content.classList.remove('hidden');
                    btn.classList.add('bg-white', 'text-slate-800', 'shadow-2xs', 'font-bold');
                    btn.classList.remove('text-slate-500', 'font-semibold');
                    const icon = btn.querySelector('i');
                    if (icon) icon.classList.add('text-brand-500');
                } else {
                    content.classList.add('hidden');
                    btn.classList.remove('bg-white', 'text-slate-800', 'shadow-2xs', 'font-bold');
                    btn.classList.add('text-slate-500', 'font-semibold');
                    const icon = btn.querySelector('i');
                    if (icon) icon.classList.remove('text-brand-500');
                }
            }
        });

        // Automatically update unit options when switching to Barcodes or Tiered Pricing tab
        if (tab === 'tiered' || tab === 'barcodes') {
            updateProductUnitDropdowns(type);
        }
    }

    // Dynamic Tiered Price Row Helper (Task 2.2) - Outset Floating Standard
    function addTieredRow(type, unitId = '', customerGroupId = '', minQty = '1', maxQty = '', price = '') {
        const container = document.getElementById(`${type}_tiered_container`);
        const index = container.children.length;

        const units = getProductConfiguredUnits(type, unitId);
        let unitOptions = '<option value="">Pilih Satuan</option>';
        units.forEach(u => {
            const tag = u.is_base ? ' (Satuan Dasar)' : '';
            unitOptions += `<option value="${u.id}" ${u.id == unitId ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
        });

        let groupOptions = '<option value="">Semua Pelanggan (Umum)</option>';
        customerGroups.forEach(g => {
            groupOptions += `<option value="${g.id}" ${g.id == customerGroupId ? 'selected' : ''}>${g.name}</option>`;
        });

        const row = document.createElement('div');
        row.className = 'p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/90 shadow-2xs space-y-3';
        row.innerHTML = `
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 text-[11px] font-black flex items-center justify-center">
                        #${index + 1}
                    </span>
                    <span class="text-xs font-bold text-slate-800">Konfigurasi Tier Grosir</span>
                </div>
                <button type="button" onclick="this.closest('.p-3.5').remove()" class="px-2 py-1 rounded-lg text-rose-500 hover:bg-rose-50 text-[11px] font-bold transition flex items-center gap-1">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Hapus Baris</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <!-- Satuan -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Satuan Jual <span class="text-rose-500">*</span>
                    </label>
                    <select name="tiered_prices[${index}][unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${unitOptions}
                    </select>
                </div>

                <!-- Grup Member -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Target Member
                    </label>
                    <select name="tiered_prices[${index}][customer_group_id]" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${groupOptions}
                    </select>
                </div>

                <!-- Rentang Qty -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Rentang Qty (Min - Max) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                        <input type="number" step="any" name="tiered_prices[${index}][min_qty]" value="${minQty}" placeholder="Min (1)" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                        <span class="text-slate-400 font-bold text-xs">-</span>
                        <input type="number" step="any" name="tiered_prices[${index}][max_qty]" value="${maxQty}" placeholder="Max (∞)" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <!-- Harga Jual Khusus -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Harga Jual Satuan <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-brand-500">Rp</span>
                        <input type="number" step="any" name="tiered_prices[${index}][price]" value="${price}" placeholder="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>
        `;
        container.appendChild(row);
        
        // Initialize Select2 on dynamic tiered selects
        $(row).find('select').each(function() {
            $(this).select2({
                dropdownParent: $(`#${type}ProductModal`),
                width: '100%'
            });
        });

        lucide.createIcons();
    }

    // Image preview helper
    function previewProductImage(input, type) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(`${type}_image_preview_img`);
                const icon = document.getElementById(`${type}_image_preview_icon`);
                if (img && icon) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    icon.classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Dynamic Multi-Barcode Row (Outset Floating Standard)
    function addBarcodeRow(type, barcode = '', unitId = '') {
        const container = document.getElementById(`${type}_barcodes_container`);
        if (!container) return;
        
        const index = container.children.length;
        const units = getProductConfiguredUnits(type, unitId);
        let unitOptions = '<option value="">Pilih Satuan</option>';
        units.forEach(u => {
            const tag = u.is_base ? ' (Satuan Dasar)' : '';
            unitOptions += `<option value="${u.id}" ${u.id == unitId ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
        });

        const row = document.createElement('div');
        row.className = 'p-3 rounded-xl bg-slate-50/70 border border-slate-200/90 shadow-2xs flex flex-wrap sm:flex-nowrap items-center gap-3';
        row.innerHTML = `
            <div class="flex-1 w-full">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Kode Barcode Scanner <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <i data-lucide="scan" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <input type="text" name="barcodes[${index}][barcode]" value="${barcode}" placeholder="Scan / ketik barcode..." required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="w-full sm:w-52">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Satuan Jual <span class="text-rose-500">*</span>
                    </label>
                    <select name="barcodes[${index}][unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${unitOptions}
                    </select>
                </div>
            </div>

            <button type="button" onclick="this.closest('.p-3').remove()" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition shrink-0" title="Hapus Barcode">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(row);

        // Initialize Select2 on barcode select
        $(row).find('select').select2({
            dropdownParent: $(`#${type}ProductModal`),
            placeholder: 'Pilih Satuan',
            width: '100%'
        });

        lucide.createIcons();
    }

    // Dynamic Unit Conversion Row (Outset Floating Standard)
    function addConversionRow(type, fromUnitId = '', toUnitId = '', value = '') {
        const container = document.getElementById(`${type}_conversions_container`);
        if (!container) return;

        const index = container.children.length;
        let fromOptions = '<option value="">Satuan Besar (Dari)</option>';
        let toOptions = '<option value="">Satuan Kecil (Ke)</option>';
        
        // Clean numeric format: remove unnecessary .0000 trailing zeros, max 2 decimal digits
        let formattedValue = '';
        if (value !== '' && value !== null && value !== undefined) {
            const num = parseFloat(value);
            if (!isNaN(num)) {
                // Remove trailing zeros, max 2 decimal places
                formattedValue = Number(Math.round(num * 100) / 100);
            } else {
                formattedValue = value;
            }
        }

        availableUnits.forEach(u => {
            fromOptions += `<option value="${u.id}" ${u.id == fromUnitId ? 'selected' : ''}>${formatUnitDisplay(u)}</option>`;
            toOptions += `<option value="${u.id}" ${u.id == toUnitId ? 'selected' : ''}>${formatUnitDisplay(u)}</option>`;
        });

        const row = document.createElement('div');
        row.className = 'p-3 rounded-xl bg-slate-50/70 border border-slate-200/90 shadow-2xs flex flex-wrap sm:flex-nowrap items-center gap-3';
        row.innerHTML = `
            <div class="flex-1 w-full">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        1 Satuan Besar (Dari) <span class="text-rose-500">*</span>
                    </label>
                    <select name="conversions[${index}][from_unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${fromOptions}
                    </select>
                </div>
            </div>

            <div class="w-full sm:w-36">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Isi / Rasio <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="any" name="conversions[${index}][conversion_value]" value="${formattedValue}" placeholder="Nilai (40)" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <div class="flex-1 w-full">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Satuan Dasar (Ke) <span class="text-rose-500">*</span>
                    </label>
                    <select name="conversions[${index}][to_unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${toOptions}
                    </select>
                </div>
            </div>

            <button type="button" onclick="this.closest('.p-3').remove(); updateProductUnitDropdowns('${type}');" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition shrink-0" title="Hapus Konversi">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(row);

        // Initialize Select2 on conversion selects and update dependent dropdowns on change
        $(row).find('select').each(function() {
            $(this).select2({
                dropdownParent: $(`#${type}ProductModal`),
                width: '100%'
            }).on('change', function() {
                updateProductUnitDropdowns(type);
            });
        });

        lucide.createIcons();
    }

    // Modal Create Handlers
    function openCreateProductModal() {
        document.getElementById('createProductModal').classList.remove('hidden');
        document.getElementById('createProductModal').classList.add('flex');
        switchProductTab('create', 'basic');

        // Initialize Select2 inside Create Modal
        $('#create_input_category_id').select2({
            dropdownParent: $('#createProductModal'),
            placeholder: 'Pilih Kategori',
            allowClear: true,
            width: '100%'
        });

        $('#create_input_base_unit_id').select2({
            dropdownParent: $('#createProductModal'),
            placeholder: 'Pilih Satuan',
            allowClear: true,
            width: '100%'
        }).off('change.unitSync').on('change.unitSync', function() {
            updateProductUnitDropdowns('create');
        });

        $('#create_input_product_type').select2({
            dropdownParent: $('#createProductModal'),
            minimumResultsForSearch: Infinity,
            width: '100%'
        });
    }

    function closeCreateProductModal() {
        document.getElementById('createProductModal').classList.add('hidden');
        document.getElementById('createProductModal').classList.remove('flex');
    }

    // Modal Edit Handlers
    function openEditProductModal(product) {
        const form = document.getElementById('editProductForm');
        form.action = `/products/${product.id}`;
        document.getElementById('edit_form_action').value = form.action;
        document.getElementById('edit_product_id').value = product.id;

        document.getElementById('edit_input_name').value = product.name || '';
        document.getElementById('edit_input_code').value = product.code || '';
        document.getElementById('edit_input_barcode').value = product.barcode || '';
        document.getElementById('edit_input_purchase_price').value = product.purchase_price || '0';
        document.getElementById('edit_input_selling_price').value = product.selling_price || '0';
        document.getElementById('edit_input_min_stock').value = product.min_stock || '5';
        document.getElementById('edit_input_brand').value = product.brand || '';
        document.getElementById('edit_input_is_active').checked = product.is_active ? true : false;

        // Populate and initialize Select2 for Edit Modal
        $('#edit_input_category_id').val(product.category_id || '').select2({
            dropdownParent: $('#editProductModal'),
            placeholder: 'Pilih Kategori',
            allowClear: true,
            width: '100%'
        });

        $('#edit_input_base_unit_id').val(product.base_unit_id || '').select2({
            dropdownParent: $('#editProductModal'),
            placeholder: 'Pilih Satuan',
            allowClear: true,
            width: '100%'
        }).off('change.unitSync').on('change.unitSync', function() {
            updateProductUnitDropdowns('edit');
        });

        $('#edit_input_product_type').val(product.product_type || 'standard').select2({
            dropdownParent: $('#editProductModal'),
            minimumResultsForSearch: Infinity,
            width: '100%'
        });

        // Image preview
        const img = document.getElementById('edit_image_preview_img');
        const icon = document.getElementById('edit_image_preview_icon');
        if (product.image_path) {
            img.src = `/storage/${product.image_path}`;
            img.classList.remove('hidden');
            icon.classList.add('hidden');
        } else {
            img.classList.add('hidden');
            icon.classList.remove('hidden');
        }

        // Render Barcodes
        const barcodeContainer = document.getElementById('edit_barcodes_container');
        barcodeContainer.innerHTML = '';
        if (product.barcodes && product.barcodes.length > 0) {
            product.barcodes.forEach(b => addBarcodeRow('edit', b.barcode, b.unit_id));
        }

        // Render Conversions
        const convContainer = document.getElementById('edit_conversions_container');
        convContainer.innerHTML = '';
        if (product.conversions && product.conversions.length > 0) {
            product.conversions.forEach(c => addConversionRow('edit', c.from_unit_id, c.to_unit_id, c.conversion_value));
        }

        // Render Tiered Prices (Harga Berjenjang)
        const tieredContainer = document.getElementById('edit_tiered_container');
        tieredContainer.innerHTML = '';
        if (product.tiered_prices && product.tiered_prices.length > 0) {
            product.tiered_prices.forEach(t => addTieredRow('edit', t.unit_id, t.customer_group_id, t.min_qty, t.max_qty || '', t.price));
        }

        document.getElementById('editProductModal').classList.remove('hidden');
        document.getElementById('editProductModal').classList.add('flex');
        switchProductTab('edit', 'basic');
    }

    function closeEditProductModal() {
        document.getElementById('editProductModal').classList.add('hidden');
        document.getElementById('editProductModal').classList.remove('flex');
    }

    // Realtime field validation feedback
    function setFieldStatus(prefix, field, errorMsg) {
        const box = document.getElementById(`${prefix}_box_${field}`);
        const label = document.getElementById(`${prefix}_label_${field}`);
        const icon = document.getElementById(`${prefix}_icon_${field}`);
        const err = document.getElementById(`${prefix}_error_${field}`);

        if (!box) return;

        if (errorMsg) {
            box.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/10');
            box.classList.remove('border-slate-200', 'focus-within:border-brand-500');
            if (label) { label.classList.add('text-rose-500'); label.classList.remove('text-slate-700'); }
            if (icon) { icon.classList.add('text-rose-500'); icon.classList.remove('text-slate-400'); }
            if (err) { err.textContent = errorMsg; err.classList.add('text-rose-500'); err.classList.remove('text-slate-400'); }
        } else {
            box.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/10');
            box.classList.add('border-slate-200');
            if (label) { label.classList.remove('text-rose-500'); label.classList.add('text-slate-700'); }
            if (icon) { icon.classList.remove('text-rose-500'); icon.classList.add('text-slate-400'); }
            if (err) {
                err.classList.remove('text-rose-500');
                err.classList.add('text-slate-400');
                if (field === 'name') err.textContent = 'Nama lengkap barang dagangan';
                else if (field === 'base_unit_id') err.textContent = 'Satuan eceran terendah';
                else if (field === 'purchase_price') err.textContent = 'Harga modal per 1 satuan dasar';
                else if (field === 'selling_price') err.textContent = 'Harga jual standar di kasir';
            }
        }
    }

    ['create', 'edit'].forEach(prefix => {
        const nameInput = document.getElementById(`${prefix}_input_name`);
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                if (this.value.trim() === '') setFieldStatus(prefix, 'name', 'Nama produk wajib diisi.');
                else setFieldStatus(prefix, 'name', null);
            });
        }
    });

    // ==========================================
    // RECIPE & BOM MODAL CONTROLLER
    // ==========================================
    let currentRecipeProductId = null;
    let currentRecipeProductSellingPrice = 0;
    let currentRecipeList = [];
    let availableRecipeIngredients = [];
    let availableRecipeUnits = [];

    function openRecipeModal(productId, productName, sellingPrice, productType) {
        currentRecipeProductId = productId;
        currentRecipeProductSellingPrice = parseFloat(sellingPrice) || 0;
        currentRecipeList = [];

        document.getElementById('recipe_modal_product_name').innerText = productName;
        document.getElementById('recipe_modal_product_badge').innerText = productType ? productType.toUpperCase() : 'PRODUK';
        document.getElementById('recipe_modal_selling_price').innerText = 'Rp ' + Math.round(currentRecipeProductSellingPrice).toLocaleString('id-ID');

        const modal = document.getElementById('recipeModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        const tbody = document.getElementById('recipe_table_body');
        tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-slate-400 text-xs">Memuat resep bahan baku...</td></tr>';

        fetch(`/products/${productId}/recipes`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    availableRecipeIngredients = data.available_ingredients || [];
                    availableRecipeUnits = data.units || [];

                    // Populate ingredient dropdown
                    const ingSelect = document.getElementById('recipe_new_ingredient_id');
                    ingSelect.innerHTML = '<option value="">Pilih Bahan Baku...</option>';
                    availableRecipeIngredients.forEach(ing => {
                        const baseUnitName = ing.base_unit ? (ing.base_unit.short_name || ing.base_unit.name) : '';
                        const typeTag = ing.product_type === 'raw_material' ? '[Bahan]' : '';
                        ingSelect.innerHTML += `<option value="${ing.id}" data-price="${ing.purchase_price}" data-unit="${ing.base_unit_id}">${typeTag} ${ing.name} (HPP: Rp ${Math.round(ing.purchase_price).toLocaleString('id-ID')}/${baseUnitName})</option>`;
                    });

                    // Populate unit dropdown
                    const unitSelect = document.getElementById('recipe_new_unit_id');
                    unitSelect.innerHTML = '<option value="">Pilih Satuan...</option>';
                    availableRecipeUnits.forEach(u => {
                        unitSelect.innerHTML += `<option value="${u.id}">${formatUnitDisplay(u)}</option>`;
                    });

                    // Initialize Select2 for recipe modal
                    $('#recipe_new_ingredient_id').select2({
                        dropdownParent: $('#recipeModal'),
                        placeholder: 'Pilih Bahan Baku...',
                        allowClear: true,
                        width: '100%'
                    });

                    $('#recipe_new_unit_id').select2({
                        dropdownParent: $('#recipeModal'),
                        placeholder: 'Pilih Satuan...',
                        allowClear: true,
                        width: '100%'
                    });

                    // Auto-select unit when ingredient is chosen
                    $('#recipe_new_ingredient_id').off('select2:select change').on('select2:select change', function() {
                        const selectedOption = $(this).find('option:selected');
                        const defaultUnitId = selectedOption.data('unit');
                        if (defaultUnitId) {
                            $('#recipe_new_unit_id').val(defaultUnitId).trigger('change');
                        }
                    });

                    // Map existing recipes
                    currentRecipeList = (data.recipes || []).map(r => ({
                        ingredient_product_id: r.ingredient_product_id,
                        ingredient_name: r.ingredient ? r.ingredient.name : 'Bahan',
                        quantity: parseFloat(r.quantity) || 0,
                        unit_id: r.unit_id,
                        unit_name: r.unit ? (r.unit.short_name || r.unit.name) : '',
                        waste_percent: parseFloat(r.waste_percent) || 0,
                        cost_estimate: parseFloat(r.cost_estimate) || 0,
                        notes: r.notes || ''
                    }));

                    renderRecipeTable();
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-rose-500 text-xs">Gagal memuat resep produk.</td></tr>';
            });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeRecipeModal() {
        const modal = document.getElementById('recipeModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function renderRecipeTable() {
        const tbody = document.getElementById('recipe_table_body');
        document.getElementById('recipe_items_count').innerText = currentRecipeList.length;

        if (currentRecipeList.length === 0) {
            tbody.innerHTML = `<tr>
                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                    Belum ada bahan baku pada resep ini. Tambahkan bahan racikan di atas.
                </td>
            </tr>`;
            calculateRecipeTotals();
            return;
        }

        let html = '';
        currentRecipeList.forEach((item, idx) => {
            html += `
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="py-2.5 px-4 font-semibold text-slate-900">
                        ${item.ingredient_name}
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <input type="number" step="any" min="0.001" value="${item.quantity}" onchange="updateRecipeQty(${idx}, this.value)" class="w-20 text-center text-xs font-semibold bg-white border border-slate-200 rounded-md py-1 px-1 focus:border-brand-500 focus:outline-none">
                    </td>
                    <td class="py-2.5 px-3">
                        <select onchange="updateRecipeUnit(${idx}, this.value)" class="text-xs bg-white border border-slate-200 rounded-md py-1 px-1.5 focus:border-brand-500 focus:outline-none">
                            ${availableRecipeUnits.map(u => `<option value="${u.id}" ${u.id == item.unit_id ? 'selected' : ''}>${u.short_name || u.name}</option>`).join('')}
                        </select>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <input type="number" step="any" min="0" max="100" value="${item.waste_percent}" onchange="updateRecipeWaste(${idx}, this.value)" class="w-16 text-center text-xs font-semibold bg-white border border-slate-200 rounded-md py-1 px-1 focus:border-brand-500 focus:outline-none">
                    </td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-800">
                        Rp ${Math.round(item.cost_estimate || 0).toLocaleString('id-ID')}
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <button type="button" onclick="removeRecipeIngredientRow(${idx})" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Bahan">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        calculateRecipeTotals();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function addRecipeIngredientRow() {
        const ingSelect = document.getElementById('recipe_new_ingredient_id');
        const qtyInput = document.getElementById('recipe_new_qty');
        const unitSelect = document.getElementById('recipe_new_unit_id');

        const ingId = parseInt(ingSelect.value);
        const qty = parseFloat(qtyInput.value);
        const unitId = parseInt(unitSelect.value);

        if (!ingId) {
            alert('Silakan pilih bahan baku terlebih dahulu.');
            return;
        }
        if (!qty || qty <= 0) {
            alert('Silakan masukkan takaran yang valid.');
            return;
        }
        if (!unitId) {
            alert('Silakan pilih satuan takaran.');
            return;
        }

        const existing = currentRecipeList.find(r => r.ingredient_product_id === ingId);
        if (existing) {
            alert('Bahan baku ini sudah ada di resep.');
            return;
        }

        const ingData = availableRecipeIngredients.find(i => i.id === ingId);
        const unitData = availableRecipeUnits.find(u => u.id === unitId);

        let costEst = (parseFloat(ingData?.purchase_price) || 0) * qty;

        currentRecipeList.push({
            ingredient_product_id: ingId,
            ingredient_name: ingData ? ingData.name : 'Bahan',
            quantity: qty,
            unit_id: unitId,
            unit_name: unitData ? (unitData.short_name || unitData.name) : '',
            waste_percent: 0,
            cost_estimate: costEst,
            notes: ''
        });

        $('#recipe_new_ingredient_id').val('').trigger('change');
        qtyInput.value = '';
        $('#recipe_new_unit_id').val('').trigger('change');
        renderRecipeTable();
    }

    function updateRecipeQty(idx, val) {
        if (currentRecipeList[idx]) {
            currentRecipeList[idx].quantity = parseFloat(val) || 0;
            const ingData = availableRecipeIngredients.find(i => i.id === currentRecipeList[idx].ingredient_product_id);
            currentRecipeList[idx].cost_estimate = (parseFloat(ingData?.purchase_price) || 0) * currentRecipeList[idx].quantity;
            renderRecipeTable();
        }
    }

    function updateRecipeUnit(idx, val) {
        if (currentRecipeList[idx]) {
            currentRecipeList[idx].unit_id = parseInt(val);
            const unitData = availableRecipeUnits.find(u => u.id === parseInt(val));
            currentRecipeList[idx].unit_name = unitData ? (unitData.short_name || unitData.name) : '';
        }
    }

    function updateRecipeWaste(idx, val) {
        if (currentRecipeList[idx]) {
            currentRecipeList[idx].waste_percent = parseFloat(val) || 0;
            calculateRecipeTotals();
        }
    }

    function removeRecipeIngredientRow(idx) {
        currentRecipeList.splice(idx, 1);
        renderRecipeTable();
    }

    function calculateRecipeTotals() {
        let totalHpp = 0;
        currentRecipeList.forEach(item => {
            totalHpp += (parseFloat(item.cost_estimate) || 0) * (1 + ((parseFloat(item.waste_percent) || 0) / 100));
        });

        document.getElementById('recipe_modal_total_cost').innerText = 'Rp ' + Math.round(totalHpp).toLocaleString('id-ID');

        let margin = 0;
        if (currentRecipeProductSellingPrice > 0) {
            margin = ((currentRecipeProductSellingPrice - totalHpp) / currentRecipeProductSellingPrice) * 100;
        }

        const marginEl = document.getElementById('recipe_modal_margin');
        marginEl.innerText = margin.toFixed(1) + '%';
        if (margin >= 40) {
            marginEl.className = 'text-sm font-extrabold text-emerald-600 font-mono mt-0.5';
        } else if (margin >= 20) {
            marginEl.className = 'text-sm font-extrabold text-amber-600 font-mono mt-0.5';
        } else {
            marginEl.className = 'text-sm font-extrabold text-rose-600 font-mono mt-0.5';
        }
    }

    function saveProductRecipes() {
        if (!currentRecipeProductId) return;

        const btn = document.getElementById('saveRecipeBtn');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i><span>Menyimpan...</span>';

        const payload = {
            recipes: currentRecipeList.map(r => ({
                ingredient_product_id: r.ingredient_product_id,
                quantity: r.quantity,
                unit_id: r.unit_id,
                waste_percent: r.waste_percent,
                notes: r.notes || null
            }))
        };

        fetch(`/products/${currentRecipeProductId}/recipes`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Simpan Resep</span>';
            if (data.status === 'success') {
                alert(data.message);
                closeRecipeModal();
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan resep.');
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Simpan Resep</span>';
            alert('Terjadi kesalahan koneksi saat menyimpan resep.');
        });
    }

    // =========================================================================
    // BARCODE PRINT CONTROLLER (CUSTOM MM DIMENSIONS & REALTIME SVG RENDERING)
    // =========================================================================
    let barcodeQueue = [];
    let currentPreviewZoom = 100;

    const barcodePresets = {
        '40x30': { width: 40, height: 30, cols: 1, gap: 2 },
        '50x30': { width: 50, height: 30, cols: 1, gap: 2 },
        '33x15_3col': { width: 33, height: 15, cols: 3, gap: 2 },
        '100x50': { width: 100, height: 50, cols: 1, gap: 3 },
        'a4_108': { width: 38, height: 18, cols: 5, gap: 2 },
        'a4_103': { width: 64, height: 32, cols: 3, gap: 3 },
    };

    function handleBarcodePresetChange(key) {
        if (key !== 'custom' && barcodePresets[key]) {
            const p = barcodePresets[key];
            document.getElementById('barcode_param_width').value = p.width;
            document.getElementById('barcode_param_height').value = p.height;
            document.getElementById('barcode_param_cols').value = p.cols;
            document.getElementById('barcode_param_gap').value = p.gap;
        }
        renderBarcodePreview();
    }

    function openBarcodePrintModal(initialItems = []) {
        const modal = document.getElementById('barcodePrintModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (initialItems && initialItems.length > 0) {
            initialItems.forEach(item => {
                const existing = barcodeQueue.find(q => q.id === item.id);
                if (existing) {
                    existing.qty += (item.qty || 10);
                } else {
                    barcodeQueue.push({ ...item, qty: item.qty || 10 });
                }
            });
        } else {
            // Check if any row checkboxes were checked
            const checkedBoxes = document.querySelectorAll('.product-row-checkbox:checked');
            if (checkedBoxes.length > 0) {
                checkedBoxes.forEach(cb => {
                    const prod = {
                        id: cb.value + '_base',
                        product_id: cb.value,
                        name: cb.dataset.name,
                        code: cb.dataset.code,
                        barcode: cb.dataset.barcode,
                        unit_name: cb.dataset.unit,
                        price: parseFloat(cb.dataset.price) || 0
                    };
                    if (!barcodeQueue.some(item => item.id === prod.id)) {
                        barcodeQueue.push({ ...prod, qty: 10 });
                    }
                });
            }
        }

        // Initialize Select2 AJAX for product search inside barcode modal
        $('#barcode_product_search_select').select2({
            dropdownParent: $('#barcodePrintModal'),
            placeholder: 'Cari & tambah produk lain ke antrean...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("products.barcode-search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: (data.results || []).map(r => ({
                            id: r.id,
                            text: r.label,
                            itemData: r
                        }))
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        }).off('select2:select').on('select2:select', function (e) {
            const data = e.params.data.itemData;
            if (data) {
                const existing = barcodeQueue.find(q => q.id === data.id);
                if (existing) {
                    existing.qty += 10;
                } else {
                    barcodeQueue.push({ ...data, qty: 10 });
                }
                renderBarcodeQueueList();
                renderBarcodePreview();
            }
            $(this).val(null).trigger('change');
        });

        renderBarcodeQueueList();
        renderBarcodePreview();
        lucide.createIcons();
    }

    function openProductBarcodeFromRow(product) {
        const items = [];
        const baseBarcode = product.barcode || product.code || '';
        const baseUnitName = product.base_unit ? formatUnitDisplay(product.base_unit) : 'Pcs';
        const baseSellingPrice = parseFloat(product.selling_price) || 0;

        // 1. Base unit item
        items.push({
            id: product.id + '_base',
            product_id: product.id,
            name: product.name,
            code: product.code,
            barcode: baseBarcode,
            unit_name: baseUnitName,
            price: baseSellingPrice,
            is_base: true,
            qty: 10
        });

        // 2. Barcodes / Multi-satuan
        const processedUnits = new Set([product.base_unit_id]);

        if (product.barcodes && product.barcodes.length > 0) {
            product.barcodes.forEach(b => {
                if (b.unit_id && !processedUnits.has(b.unit_id)) {
                    processedUnits.add(b.unit_id);
                    const uName = b.unit ? formatUnitDisplay(b.unit) : 'Satuan';
                    let convRatio = 1;
                    if (product.conversions) {
                        const conv = product.conversions.find(c => c.from_unit_id == b.unit_id);
                        if (conv && conv.conversion_value > 0) convRatio = parseFloat(conv.conversion_value);
                    }
                    const pList = product.price_lists ? product.price_lists.find(p => p.unit_id == b.unit_id) : null;
                    const unitPrice = pList && pList.selling_price > 0 ? parseFloat(pList.selling_price) : (baseSellingPrice * convRatio);

                    items.push({
                        id: product.id + '_barcode_' + b.id,
                        product_id: product.id,
                        name: product.name,
                        code: product.code,
                        barcode: b.barcode || baseBarcode,
                        unit_name: uName,
                        price: unitPrice,
                        is_base: false,
                        qty: 10
                    });
                }
            });
        }

        if (product.conversions && product.conversions.length > 0) {
            product.conversions.forEach(c => {
                if (c.from_unit_id && !processedUnits.has(c.from_unit_id)) {
                    processedUnits.add(c.from_unit_id);
                    const uName = c.from_unit ? formatUnitDisplay(c.from_unit) : 'Satuan';
                    const convRatio = parseFloat(c.conversion_value) || 1;
                    const pList = product.price_lists ? product.price_lists.find(p => p.unit_id == c.from_unit_id) : null;
                    const unitPrice = pList && pList.selling_price > 0 ? parseFloat(pList.selling_price) : (baseSellingPrice * convRatio);

                    items.push({
                        id: product.id + '_conv_' + c.id,
                        product_id: product.id,
                        name: product.name,
                        code: product.code,
                        barcode: baseBarcode,
                        unit_name: uName,
                        price: unitPrice,
                        is_base: false,
                        qty: 10
                    });
                }
            });
        }

        openBarcodePrintModal(items);
    }

    function openSingleProductBarcode(productData) {
        openBarcodePrintModal([productData]);
    }

    function closeBarcodeModal() {
        const modal = document.getElementById('barcodePrintModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function renderBarcodeQueueList() {
        const container = document.getElementById('barcode_queue_container');
        const badge = document.getElementById('barcode_queue_badge');
        badge.innerText = `${barcodeQueue.length} Produk`;

        if (barcodeQueue.length === 0) {
            container.innerHTML = `
                <div class="py-6 text-center text-slate-400 text-xs">
                    <p class="font-semibold">Antrean cetak masih kosong.</p>
                    <p class="text-[10px] mt-1">Cari produk di atas atau pilih dari tabel produk.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = barcodeQueue.map((item, idx) => `
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-slate-900 dark:text-white truncate max-w-[170px]" title="${item.name}">${item.name}</span>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-teal-50 text-teal-700 border border-teal-200">${item.unit_name || 'Pcs'}</span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1 mt-0.5">
                        <span class="text-brand-600 font-semibold">${item.barcode || item.code}</span>
                        <span>•</span>
                        <span class="text-slate-700 dark:text-slate-300 font-bold">Rp ${Math.round(item.price).toLocaleString('id-ID')}</span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="updateBarcodeQueueQty(${idx}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-white font-bold text-sm flex items-center justify-center transition active:scale-95 shadow-2xs">-</button>
                    <input type="number" min="1" max="1000" value="${item.qty}" oninput="setBarcodeQueueQtyDirect(${idx}, this.value)" class="w-14 h-7 text-center text-xs font-extrabold rounded-lg border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-1 focus:ring-brand-500 focus:border-brand-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none p-0">
                    <button type="button" onclick="updateBarcodeQueueQty(${idx}, 1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-white font-bold text-sm flex items-center justify-center transition active:scale-95 shadow-2xs">+</button>
                    <button type="button" onclick="removeBarcodeQueueItem(${idx})" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition ml-0.5" title="Hapus dari antrean">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        `).join('');

        lucide.createIcons();
    }

    function updateBarcodeQueueQty(index, delta) {
        if (!barcodeQueue[index]) return;
        barcodeQueue[index].qty = Math.max(1, barcodeQueue[index].qty + delta);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function setBarcodeQueueQtyDirect(index, value) {
        if (!barcodeQueue[index]) return;
        barcodeQueue[index].qty = Math.max(1, parseInt(value) || 1);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function removeBarcodeQueueItem(index) {
        barcodeQueue.splice(index, 1);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function clearBarcodeQueue() {
        barcodeQueue = [];
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function setAllBarcodeQty(qty) {
        barcodeQueue.forEach(item => item.qty = qty);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function setPreviewZoom(zoom) {
        currentPreviewZoom = zoom;
        const viewport = document.getElementById('barcode_preview_viewport');
        viewport.style.transform = `scale(${zoom / 100})`;
        ['75', '100', '125'].forEach(z => {
            const btn = document.getElementById(`btn_zoom_${z}`);
            if (btn) {
                if (parseInt(z) === zoom) {
                    btn.className = 'px-1.5 py-0.5 rounded bg-brand-500 text-white shadow-2xs';
                } else {
                    btn.className = 'px-1.5 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700';
                }
            }
        });
    }

    function toggleSelectAllProducts(master) {
        const checkboxes = document.querySelectorAll('.product-row-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
        handleProductCheckboxChange();
    }

    function handleProductCheckboxChange() {
        const checked = document.querySelectorAll('.product-row-checkbox:checked');
        const badge = document.getElementById('bulk_barcode_badge');
        if (checked.length > 0) {
            badge.innerText = checked.length;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function renderBarcodePreview() {
        const width = parseFloat(document.getElementById('barcode_param_width').value) || 40;
        const height = parseFloat(document.getElementById('barcode_param_height').value) || 30;
        const cols = parseInt(document.getElementById('barcode_param_cols').value) || 1;
        const gap = parseFloat(document.getElementById('barcode_param_gap').value) || 2;

        const showStore = document.getElementById('barcode_toggle_store').checked;
        const storeName = document.getElementById('barcode_store_name').value.trim();
        const showName = document.getElementById('barcode_toggle_name').checked;
        const showPrice = document.getElementById('barcode_toggle_price').checked;
        const showUnit = document.getElementById('barcode_toggle_unit').checked;
        const showCodeText = document.getElementById('barcode_toggle_code_text').checked;

        const fontSizeScale = document.getElementById('barcode_font_size').value || 'md';
        const barHeight = parseInt(document.getElementById('barcode_bar_height').value) || 35;

        // Font sizes mapping
        const fontMap = {
            sm: { store: 8, name: 9, price: 9, code: 8 },
            md: { store: 9, name: 10, price: 11, code: 9 },
            lg: { store: 10, name: 11, price: 12, code: 10 },
        }[fontSizeScale];

        // Flatten all labels according to item.qty
        const labels = [];
        barcodeQueue.forEach(item => {
            const count = Math.max(1, parseInt(item.qty) || 1);
            for (let i = 0; i < count; i++) {
                labels.push(item);
            }
        });

        const totalBadge = document.getElementById('barcode_total_labels_badge');
        totalBadge.innerText = `Total: ${labels.length} Label (${cols} Kolom)`;

        const sheetContainer = document.getElementById('barcode_render_sheet');
        if (labels.length === 0) {
            sheetContainer.innerHTML = `
                <div class="p-8 text-center text-slate-400 text-xs w-full">
                    <p class="font-bold text-slate-500">Belum ada label untuk ditampilkan.</p>
                    <p class="text-[11px] mt-1">Tambahkan produk ke antrean di panel sebelah kiri.</p>
                </div>
            `;
            sheetContainer.style.width = 'auto';
            sheetContainer.style.padding = '10px';
            return;
        }

        // Calculate sheet container width in mm
        const sheetWidthMm = cols * width + (cols - 1) * gap + 4;
        sheetContainer.style.width = `${sheetWidthMm}mm`;
        sheetContainer.style.padding = '2mm';
        sheetContainer.style.gap = `${gap}mm`;

        let html = '';
        labels.forEach((item, idx) => {
            const priceFormatted = Math.round(item.price).toLocaleString('id-ID');
            const unitText = showUnit && item.unit_name ? ` / ${item.unit_name}` : '';

            html += `
                <div class="barcode-preview-card" style="
                    width: ${width}mm; 
                    height: ${height}mm; 
                    box-sizing: border-box; 
                    border: 1px dashed #cbd5e1; 
                    padding: 1.5mm; 
                    background: #ffffff; 
                    display: flex; 
                    flex-direction: column; 
                    align-items: center; 
                    justify-content: space-between; 
                    text-align: center; 
                    overflow: hidden; 
                    position: relative;
                ">
                    ${showStore && storeName ? `<div style="font-size: ${fontMap.store}px; font-weight: 800; text-transform: uppercase; line-height: 1.1; letter-spacing: -0.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; color: #0f172a;">${storeName}</div>` : ''}
                    
                    ${showName ? `<div style="font-size: ${fontMap.name}px; font-weight: 700; line-height: 1.1; max-height: 2.2em; overflow: hidden; color: #1e293b; width: 100%; word-break: break-word;">${item.name}</div>` : ''}
                    
                    <div style="width: 100%; flex: 1 1 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; min-height: 0;">
                        <svg class="barcode-svg-${idx}" style="max-width: 98%; max-height: 100%; display: block; margin: 0 auto;"></svg>
                    </div>

                    ${showPrice ? `<div style="font-size: ${fontMap.price}px; font-weight: 900; line-height: 1; color: #000000; width: 100%;"><span style="font-size: 0.85em; font-weight: 700;">Rp</span> ${priceFormatted}<span style="font-size: 0.8em; font-weight: normal; color: #475569;">${unitText}</span></div>` : ''}
                </div>
            `;
        });

        sheetContainer.innerHTML = html;

        // Render SVG barcodes with JsBarcode
        labels.forEach((item, idx) => {
            const codeVal = item.barcode || item.code || '12345678';
            try {
                JsBarcode(`.barcode-svg-${idx}`, codeVal, {
                    format: "CODE128",
                    width: width < 35 ? 1.0 : 1.3,
                    height: barHeight,
                    displayValue: showCodeText,
                    fontSize: fontMap.code,
                    fontOptions: "bold",
                    margin: 0,
                    textMargin: 1
                });
            } catch (err) {
                console.warn('JsBarcode render fallback for code:', codeVal, err);
            }
        });
    }

    function executeBarcodePrint() {
        const width = parseFloat(document.getElementById('barcode_param_width').value) || 40;
        const height = parseFloat(document.getElementById('barcode_param_height').value) || 30;
        const cols = parseInt(document.getElementById('barcode_param_cols').value) || 1;
        const gap = parseFloat(document.getElementById('barcode_param_gap').value) || 2;

        const showStore = document.getElementById('barcode_toggle_store').checked;
        const storeName = document.getElementById('barcode_store_name').value.trim();
        const showName = document.getElementById('barcode_toggle_name').checked;
        const showPrice = document.getElementById('barcode_toggle_price').checked;
        const showUnit = document.getElementById('barcode_toggle_unit').checked;
        const showCodeText = document.getElementById('barcode_toggle_code_text').checked;

        const fontSizeScale = document.getElementById('barcode_font_size').value || 'md';
        const barHeight = parseInt(document.getElementById('barcode_bar_height').value) || 35;

        const fontMap = {
            sm: { store: '7pt', name: '8pt', price: '8.5pt', code: '7pt' },
            md: { store: '8pt', name: '9pt', price: '9.5pt', code: '8pt' },
            lg: { store: '9pt', name: '10pt', price: '11pt', code: '9pt' },
        }[fontSizeScale];

        const labels = [];
        barcodeQueue.forEach(item => {
            const count = Math.max(1, parseInt(item.qty) || 1);
            for (let i = 0; i < count; i++) {
                labels.push(item);
            }
        });

        if (labels.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Antrean Kosong',
                text: 'Silakan pilih atau tambahkan produk sebelum mencetak barcode.',
                confirmButtonColor: '#f97316'
            });
            return;
        }

        // Calculate page size for print stylesheet
        const pageWidth = cols > 1 ? (cols * width + (cols - 1) * gap) : width;
        const pageHeight = height;

        // Build HTML for isolated print iframe
        const printFrame = document.getElementById('barcode_print_frame');
        const doc = printFrame.contentWindow.document;
        doc.open();
        
        let labelsHtml = '';
        labels.forEach((item, idx) => {
            const priceFormatted = Math.round(item.price).toLocaleString('id-ID');
            const unitText = showUnit && item.unit_name ? ` / ${item.unit_name}` : '';

            labelsHtml += `
                <div class="print-label-item">
                    ${showStore && storeName ? `<div class="lbl-store">${storeName}</div>` : ''}
                    ${showName ? `<div class="lbl-name">${item.name}</div>` : ''}
                    <div class="lbl-svg-wrap">
                        <svg id="p-svg-${idx}"></svg>
                    </div>
                    ${showPrice ? `<div class="lbl-price">Rp ${priceFormatted}${unitText}</div>` : ''}
                </div>
            `;
        });

        const printDocContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Cetak Barcode Label</title>
                <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
                <style>
                    @page {
                        size: ${pageWidth}mm ${pageHeight}mm;
                        margin: 0mm !important;
                    }
                    @media print {
                        html, body {
                            margin: 0 !important;
                            padding: 0 !important;
                            background: #ffffff !important;
                            color: #000000 !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                    }
                    * {
                        box-sizing: border-box;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        background: #fff;
                        display: flex;
                        flex-wrap: wrap;
                        align-content: flex-start;
                        width: ${pageWidth}mm;
                    }
                    .print-label-item {
                        width: ${width}mm;
                        height: ${height}mm;
                        padding: 1mm 1.5mm;
                        box-sizing: border-box;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: space-between;
                        text-align: center;
                        overflow: hidden;
                        page-break-inside: avoid;
                        break-inside: avoid;
                        ${cols > 1 ? `margin-right: ${gap}mm; margin-bottom: ${gap}mm;` : ''}
                    }
                    ${cols > 1 ? `.print-label-item:nth-child(${cols}n) { margin-right: 0 !important; }` : ''}
                    .lbl-store {
                        font-size: ${fontMap.store};
                        font-weight: 800;
                        text-transform: uppercase;
                        line-height: 1.1;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        width: 100%;
                    }
                    .lbl-name {
                        font-size: ${fontMap.name};
                        font-weight: 700;
                        line-height: 1.1;
                        max-height: 2.2em;
                        overflow: hidden;
                        width: 100%;
                        word-break: break-word;
                    }
                    .lbl-svg-wrap {
                        width: 100%;
                        flex: 1 1 auto;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        overflow: hidden;
                        min-height: 0;
                    }
                    .lbl-svg-wrap svg {
                        max-width: 98%;
                        max-height: 100%;
                        display: block;
                        margin: 0 auto;
                    }
                    .lbl-price {
                        font-size: ${fontMap.price};
                        font-weight: 900;
                        line-height: 1;
                        width: 100%;
                    }
                </style>
            </head>
            <body>
                ${labelsHtml}
                <script>
                    window.onload = function() {
                        const labelsData = ${JSON.stringify(labels.map(l => l.barcode || l.code || '12345678'))};
                        labelsData.forEach((code, i) => {
                            try {
                                JsBarcode('#p-svg-' + i, code, {
                                    format: "CODE128",
                                    width: ${width < 35 ? 1.0 : 1.3},
                                    height: ${barHeight},
                                    displayValue: ${showCodeText},
                                    fontSize: ${fontSizeScale === 'sm' ? 8 : (fontSizeScale === 'lg' ? 11 : 9)},
                                    fontOptions: "bold",
                                    margin: 0,
                                    textMargin: 1
                                });
                            } catch(e) {}
                        });
                        setTimeout(function() {
                            window.focus();
                            window.print();
                        }, 250);
                    };
                <\/script>
            </body>
            </html>
        `;

        doc.write(printDocContent);
        doc.close();
    }

    // Document Ready Initializers
    $(document).ready(function() {
        // Filter Kategori on index
        $('#filter_category_id').select2({
            placeholder: 'Semua Kategori',
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            $(this).closest('form').submit();
        });

        // Initialize if Create Modal is rendered open on load (e.g. error bag)
        if (!$('#createProductModal').hasClass('hidden')) {
            $('#create_input_category_id').select2({
                dropdownParent: $('#createProductModal'),
                placeholder: 'Pilih Kategori',
                allowClear: true,
                width: '100%'
            });
            $('#create_input_base_unit_id').select2({
                dropdownParent: $('#createProductModal'),
                placeholder: 'Pilih Satuan',
                allowClear: true,
                width: '100%'
            });
            $('#create_input_product_type').select2({
                dropdownParent: $('#createProductModal'),
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        }

        // Initialize if Edit Modal is rendered open on load
        if (!$('#editProductModal').hasClass('hidden')) {
            $('#edit_input_category_id').select2({
                dropdownParent: $('#editProductModal'),
                placeholder: 'Pilih Kategori',
                allowClear: true,
                width: '100%'
            });
            $('#edit_input_base_unit_id').select2({
                dropdownParent: $('#editProductModal'),
                placeholder: 'Pilih Satuan',
                allowClear: true,
                width: '100%'
            });
            $('#edit_input_product_type').select2({
                dropdownParent: $('#editProductModal'),
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        }

        // Realtime validation listeners for Select2
        ['create', 'edit'].forEach(prefix => {
            $(`#${prefix}_input_base_unit_id`).on('change', function() {
                if ($(this).val() === '' || !$(this).val()) {
                    setFieldStatus(prefix, 'base_unit_id', 'Satuan dasar wajib dipilih.');
                } else {
                    setFieldStatus(prefix, 'base_unit_id', null);
                }
            });
        });
    });
</script>
@endpush
