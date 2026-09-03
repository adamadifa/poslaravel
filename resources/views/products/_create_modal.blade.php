<!-- MODAL CREATE PRODUCT -->
<div id="createProductModal"
    class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs {{ $errors->hasBag('default') && !old('_method') ? 'flex' : 'hidden' }} items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div
        class="bg-white border border-slate-200/90 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">

        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div
                    class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Tambah Produk Baru</h3>
                    <p class="text-[11px] text-slate-400">Lengkapi data produk, satuan dasar, harga, dan multi-barcode
                    </p>
                </div>
            </div>
            <button onclick="closeCreateProductModal()" type="button"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form Tabs Navigation (Pill Segmented Style) -->
        <div class="px-6 py-2.5 border-b border-slate-100 bg-white shrink-0">
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl">
                <button type="button" onclick="switchProductTab('create', 'basic')" id="create_tab_btn_basic"
                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 bg-white text-slate-800 shadow-2xs">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-brand-500"></i>
                    <span>Info & Harga</span>
                </button>
                <button type="button" onclick="switchProductTab('create', 'barcodes')" id="create_tab_btn_barcodes"
                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-800 transition flex items-center justify-center gap-1.5">
                    <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                    <span>Multi-Barcode</span>
                </button>
                <button type="button" onclick="switchProductTab('create', 'conversions')"
                    id="create_tab_btn_conversions"
                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-800 transition flex items-center justify-center gap-1.5">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Multi-Satuan</span>
                </button>
                <button type="button" onclick="switchProductTab('create', 'tiered')" id="create_tab_btn_tiered"
                    class="flex-1 py-1.5 px-3 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-800 transition flex items-center justify-center gap-1.5">
                    <i data-lucide="badge-percent" class="w-3.5 h-3.5"></i>
                    <span>Harga Grosir / Tier</span>
                </button>
            </div>
        </div>

        <form id="createProductForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
            novalidate class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            @csrf

            <!-- TAB 1: BASIC INFO & PRICING -->
            <div id="create_tab_content_basic" class="space-y-4">

                <!-- Section 1: Informasi Utama -->
                <div class="space-y-3.5">
                    <!-- Nama Produk -->
                    <div id="create_group_name">
                        <div id="create_box_name"
                            class="relative rounded-xl border {{ $errors->has('name') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                            <label id="create_label_name"
                                class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                                Nama Produk <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-2.5">
                                <i id="create_icon_name" data-lucide="package"
                                    class="w-4 h-4 {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                                <input type="text" name="name" id="create_input_name"
                                    value="{{ old('_method') ? '' : old('name') }}" required
                                    placeholder="Contoh: Indomie Goreng Spesial 85g"
                                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                            </div>
                        </div>
                        <p id="create_error_name"
                            class="mt-1 text-[11px] font-medium {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                            {{ $errors->has('name') && !old('_method') ? $errors->first('name') : 'Nama lengkap barang dagangan' }}
                        </p>
                    </div>

                    <!-- Kode SKU & Barcode Utama (2 Columns) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <div
                                class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                                <label
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                    Kode SKU (Opsional)
                                </label>
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="hash" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                    <input type="text" name="code" value="{{ old('_method') ? '' : old('code') }}"
                                        placeholder="Auto: PRD-00001"
                                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400 px-1">Kosongkan untuk otomatis dari sistem</p>
                        </div>

                        <div>
                            <div
                                class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                                <label
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                    Barcode EAN-13 (Opsional)
                                </label>
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="scan" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                    <input type="text" name="barcode" value="{{ old('_method') ? '' : old('barcode') }}"
                                        placeholder="Scan / ketik barcode..."
                                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400 px-1">Barcode satuan dasar scanner</p>
                        </div>
                    </div>

                    <!-- Kategori & Satuan Dasar (2 Columns) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <div
                                class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                                <label
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                    Kategori Produk
                                </label>
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="tag" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                    <select name="category_id"
                                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ (old('_method') ? '' : old('category_id')) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400 px-1">Klasifikasi kategori barang</p>
                        </div>

                        <div id="create_group_base_unit_id">
                            <div id="create_box_base_unit_id"
                                class="relative rounded-xl border {{ $errors->has('base_unit_id') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                                <label id="create_label_base_unit_id"
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('base_unit_id') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                                    Satuan Terkecil <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2.5">
                                    <i id="create_icon_base_unit_id" data-lucide="scale"
                                        class="w-4 h-4 {{ $errors->has('base_unit_id') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                                    <select name="base_unit_id" id="create_input_base_unit_id" required
                                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                        <option value="">Pilih Satuan</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ (old('_method') ? '' : old('base_unit_id')) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }} ({{ $unit->short_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <p id="create_error_base_unit_id"
                                class="mt-1 text-[11px] font-medium {{ $errors->has('base_unit_id') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                                {{ $errors->has('base_unit_id') && !old('_method') ? $errors->first('base_unit_id') : 'Satuan eceran terendah (Pcs, Botol)' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Penetapan Harga & Stok -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">

                        <!-- Harga Beli / HPP -->
                        <div id="create_group_purchase_price">
                            <div id="create_box_purchase_price"
                                class="relative rounded-xl border {{ $errors->has('purchase_price') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                                <label id="create_label_purchase_price"
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('purchase_price') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                                    Harga Beli (HPP) <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-400 shrink-0">Rp</span>
                                    <input type="number" step="any" name="purchase_price"
                                        id="create_input_purchase_price"
                                        value="{{ old('_method') ? '' : old('purchase_price', '0') }}" required
                                        placeholder="0"
                                        class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <p id="create_error_purchase_price"
                                class="mt-1 text-[11px] font-medium {{ $errors->has('purchase_price') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                                {{ $errors->has('purchase_price') && !old('_method') ? $errors->first('purchase_price') : 'Harga modal per 1 satuan' }}
                            </p>
                        </div>

                        <!-- Harga Jual -->
                        <div id="create_group_selling_price">
                            <div id="create_box_selling_price"
                                class="relative rounded-xl border {{ $errors->has('selling_price') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                                <label id="create_label_selling_price"
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('selling_price') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                                    Harga Jual <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-brand-500 shrink-0">Rp</span>
                                    <input type="number" step="any" name="selling_price" id="create_input_selling_price"
                                        value="{{ old('_method') ? '' : old('selling_price', '0') }}" required
                                        placeholder="0"
                                        class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <p id="create_error_selling_price"
                                class="mt-1 text-[11px] font-medium {{ $errors->has('selling_price') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                                {{ $errors->has('selling_price') && !old('_method') ? $errors->first('selling_price') : 'Harga jual standar kasir' }}
                            </p>
                        </div>

                        <!-- Min Stock -->
                        <div>
                            <div
                                class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                                <label
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                    Min. Stok Peringatan
                                </label>
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                    <input type="number" step="any" name="min_stock"
                                        value="{{ old('_method') ? '' : old('min_stock', '5') }}" placeholder="5"
                                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400 px-1">Batas minimum notifikasi stok</p>
                        </div>

                        <!-- Brand / Merek -->
                        <div>
                            <div
                                class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                                <label
                                    class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                    Brand / Merek (Opsional)
                                </label>
                                <div class="flex items-center gap-2.5">
                                    <i data-lucide="badge-check" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                    <input type="text" name="brand" value="{{ old('_method') ? '' : old('brand') }}"
                                        placeholder="Indofood, Unilever"
                                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400 px-1">Merek atau produsen</p>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Media & Status -->
                <div class="pt-2 border-t border-slate-100 space-y-3">
                    <!-- Foto Produk Compact -->
                    <div
                        class="rounded-xl border border-slate-200/90 bg-slate-50/50 p-2.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center text-slate-400 overflow-hidden shrink-0 border border-slate-200 shadow-2xs"
                                id="create_image_preview_box">
                                <i data-lucide="image" class="w-4 h-4" id="create_image_preview_icon"></i>
                                <img id="create_image_preview_img" class="w-full h-full object-cover hidden"
                                    alt="Preview">
                            </div>
                            <div class="min-w-0">
                                <span class="block text-xs font-bold text-slate-800 truncate">Foto Produk
                                    (Opsional)</span>
                                <span class="block text-[10px] text-slate-400">JPG, PNG, WEBP maks 2MB</span>
                            </div>
                        </div>
                        <label
                            class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold cursor-pointer transition shrink-0 shadow-2xs">
                            <span>Pilih Foto</span>
                            <input type="file" name="image" accept="image/*"
                                onchange="previewProductImage(this, 'create')" class="sr-only">
                        </label>
                    </div>

                    <!-- Status Aktif Switch Compact -->
                    <div
                        class="flex items-center justify-between p-3 rounded-xl bg-slate-50/50 border border-slate-200/90">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i data-lucide="toggle-right" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Status Produk Aktif</span>
                                <span class="block text-[10px] text-slate-400">Dapat dicari dan ditransaksikan di
                                    kasir</span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ (old('_method') ? '' : old('is_active', '1')) ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500">
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- TAB 2: MULTI BARCODE -->
            <div id="create_tab_content_barcodes" class="hidden space-y-3">
                <div class="flex items-center justify-between bg-amber-50/80 border border-amber-200/70 p-3 rounded-xl">
                    <div class="flex items-center gap-2 text-xs text-amber-800">
                        <i data-lucide="scan-line" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span>Barcode berbeda per satuan jual (contoh: Barcode 1 Dus vs 1 Pcs).</span>
                    </div>
                    <button type="button" onclick="addBarcodeRow('create')"
                        class="px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition flex items-center gap-1 shrink-0">
                        <i data-lucide="plus" class="w-3 h-3"></i>
                        <span>Tambah</span>
                    </button>
                </div>

                <div id="create_barcodes_container" class="space-y-2">
                    <!-- Dynamic Barcode Rows -->
                </div>
            </div>

            <!-- TAB 3: UNIT CONVERSIONS -->
            <div id="create_tab_content_conversions" class="hidden space-y-3">
                <div class="flex items-center justify-between bg-teal-50/80 border border-teal-200/70 p-3 rounded-xl">
                    <div class="flex items-center gap-2 text-xs text-teal-800">
                        <i data-lucide="layers" class="w-4 h-4 text-teal-600 shrink-0"></i>
                        <span>Rasio konversi multi-satuan (contoh: 1 Dus = 40 Pcs, 1 Pak = 10 Pcs).</span>
                    </div>
                    <button type="button" onclick="addConversionRow('create')"
                        class="px-2.5 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition flex items-center gap-1 shrink-0">
                        <i data-lucide="plus" class="w-3 h-3"></i>
                        <span>Tambah</span>
                    </button>
                </div>

                <div id="create_conversions_container" class="space-y-2">
                    <!-- Dynamic Conversion Rows -->
                </div>
            </div>

            <!-- TAB 4: TIERED PRICING (Harga Grosir / Berjenjang) -->
            <div id="create_tab_content_tiered" class="hidden space-y-3">
                <div class="flex items-center justify-between bg-blue-50/80 border border-blue-200/70 p-3 rounded-xl">
                    <div class="flex items-center gap-2 text-xs text-blue-800">
                        <i data-lucide="badge-percent" class="w-4 h-4 text-blue-600 shrink-0"></i>
                        <span>Harga bertingkat berdasarkan kuantitas beli atau grup member (contoh: beli ≥ 24 Pcs harga
                            Rp 2.500).</span>
                    </div>
                    <button type="button" onclick="addTieredRow('create')"
                        class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1 shrink-0">
                        <i data-lucide="plus" class="w-3 h-3"></i>
                        <span>Tambah Tier</span>
                    </button>
                </div>

                <div id="create_tiered_container" class="space-y-2">
                    <!-- Dynamic Tiered Rows -->
                </div>
            </div>

            <!-- Modal Footer / Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateProductModal()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Produk</span>
                </button>
            </div>
        </form>
    </div>
</div>