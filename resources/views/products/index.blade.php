@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-3 flex-1 w-full">
            
            <!-- Outset Floating-label Search Input -->
            <div class="relative flex-1 min-w-[200px] w-full rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Produk
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik nama, SKU, atau barcode..." 
                           class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Outset Floating-label Category Filter -->
            <div class="relative min-w-[170px] rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Filter Kategori
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="category_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
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

        <button onclick="openCreateProductModal()" class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Produk</span>
        </button>
    </div>

    <!-- Products Table Card (Solid Orange Unified Header) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm overflow-hidden">
        
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
                        <th class="pt-1.5 pb-2.5 px-6">Info Produk & SKU</th>
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
                            <!-- Produk Info -->
                            <td class="py-2.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden shrink-0">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 leading-snug">{{ $product->name }}</div>
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
                                    <span class="font-semibold text-slate-800">{{ $product->baseUnit->name ?? '-' }}</span>
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
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
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
<!-- CREATE & EDIT MODALS -->
@include('products._create_modal')
@include('products._edit_modal')
@endpush

@push('scripts')
<script>
    const availableUnits = @json($units);
    const customerGroups = @json($customerGroups);

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
    }

    // Dynamic Tiered Price Row Helper (Task 2.2) - Outset Floating Standard
    function addTieredRow(type, unitId = '', customerGroupId = '', minQty = '1', maxQty = '', price = '') {
        const container = document.getElementById(`${type}_tiered_container`);
        const index = container.children.length;

        let unitOptions = '<option value="">Pilih Satuan</option>';
        availableUnits.forEach(u => {
            unitOptions += `<option value="${u.id}" ${u.id == unitId ? 'selected' : ''}>${u.name} (${u.short_name})</option>`;
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
        let unitOptions = '<option value="">Pilih Satuan</option>';
        availableUnits.forEach(u => {
            unitOptions += `<option value="${u.id}" ${u.id == unitId ? 'selected' : ''}>${u.name} (${u.short_name})</option>`;
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
        lucide.createIcons();
    }

    // Dynamic Unit Conversion Row (Outset Floating Standard)
    function addConversionRow(type, fromUnitId = '', toUnitId = '', value = '') {
        const container = document.getElementById(`${type}_conversions_container`);
        if (!container) return;

        const index = container.children.length;
        let fromOptions = '<option value="">Satuan Besar (Dari)</option>';
        let toOptions = '<option value="">Satuan Kecil (Ke)</option>';
        
        availableUnits.forEach(u => {
            fromOptions += `<option value="${u.id}" ${u.id == fromUnitId ? 'selected' : ''}>${u.name} (${u.short_name})</option>`;
            toOptions += `<option value="${u.id}" ${u.id == toUnitId ? 'selected' : ''}>${u.name} (${u.short_name})</option>`;
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
                    <input type="number" step="any" name="conversions[${index}][conversion_value]" value="${value}" placeholder="Nilai (40)" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
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

            <button type="button" onclick="this.closest('.p-3').remove()" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition shrink-0" title="Hapus Konversi">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(row);
        lucide.createIcons();
    }

    // Modal Create Handlers
    function openCreateProductModal() {
        document.getElementById('createProductModal').classList.remove('hidden');
        document.getElementById('createProductModal').classList.add('flex');
        switchProductTab('create', 'basic');
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
        document.getElementById('edit_input_category_id').value = product.category_id || '';
        document.getElementById('edit_input_base_unit_id').value = product.base_unit_id || '';
        document.getElementById('edit_input_purchase_price').value = product.purchase_price || '0';
        document.getElementById('edit_input_selling_price').value = product.selling_price || '0';
        document.getElementById('edit_input_min_stock').value = product.min_stock || '5';
        document.getElementById('edit_input_brand').value = product.brand || '';
        document.getElementById('edit_input_is_active').checked = product.is_active ? true : false;

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

        const unitInput = document.getElementById(`${prefix}_input_base_unit_id`);
        if (unitInput) {
            unitInput.addEventListener('change', function() {
                if (this.value === '') setFieldStatus(prefix, 'base_unit_id', 'Satuan dasar wajib dipilih.');
                else setFieldStatus(prefix, 'base_unit_id', null);
            });
        }
    });
</script>
@endpush
