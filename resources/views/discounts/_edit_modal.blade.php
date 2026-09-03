<!-- MODAL EDIT DISCOUNT -->
<div id="editDiscountModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs {{ $errors->hasBag('default') && old('_method') === 'PUT' ? 'flex' : 'hidden' }} items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Edit Promo & Diskon</h3>
                    <p class="text-[11px] text-slate-400">Perbarui parameter skema diskon, periode, dan target produk</p>
                </div>
            </div>
            <button onclick="closeEditDiscountModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editDiscountForm" action="{{ old('edit_form_action', '#') }}" method="POST" novalidate class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_form_action" id="edit_form_action" value="{{ old('edit_form_action') }}">
            <input type="hidden" name="discount_id" id="edit_discount_id" value="{{ old('discount_id') }}">

            <!-- Baris 1: Nama Promo & Kode Voucher (2 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div class="sm:col-span-2">
                    <div class="relative rounded-xl border {{ $errors->has('name') && old('_method') === 'PUT' ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nama Promo / Diskon <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="tag" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <input type="text" name="name" id="edit_input_name" value="{{ old('_method') === 'PUT' ? old('name') : '' }}" required placeholder="Contoh: Flash Sale Akhir Bulan" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Kode Promo (Opsional)
                        </label>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="ticket" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <input type="text" name="code" id="edit_input_code" value="{{ old('_method') === 'PUT' ? old('code') : '' }}" placeholder="PROMO10" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 uppercase placeholder-slate-400 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Skema Tipe Diskon & Nilai (2 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Tipe Promo Diskon <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="layers" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <select name="type" id="edit_discount_type" onchange="toggleDiscountFields('edit')" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                <option value="percentage_item">Diskon Persentase per Item (%)</option>
                                <option value="fixed_item">Potongan Nominal per Item (Rp)</option>
                                <option value="percentage_invoice">Diskon Persentase Total Transaksi (%)</option>
                                <option value="fixed_invoice">Potongan Nominal Total Transaksi (Rp)</option>
                                <option value="buy_x_get_y">Buy X Get Y (Beli X Dapat Gratis/Bonus Y)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Nilai Diskon Input Container -->
                <div id="edit_value_wrapper">
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label id="edit_value_label" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nilai Diskon (%) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span id="edit_value_prefix" class="text-xs font-bold text-slate-400 hidden">Rp</span>
                            <input type="number" step="any" name="value" id="edit_input_value" value="{{ old('_method') === 'PUT' ? old('value') : '10' }}" placeholder="10" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                            <span id="edit_value_suffix" class="text-xs font-bold text-slate-400">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOGO (Buy X Get Y) Specific Fields -->
            <div id="edit_bogo_fields" class="hidden p-4 rounded-xl bg-blue-50/60 border border-blue-200/80 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-blue-900">
                    <i data-lucide="gift" class="w-4 h-4 text-blue-600"></i>
                    <span>Aturan Beli X Gratis / Diskon Y</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="relative rounded-xl border border-slate-200 bg-white transition px-3 pt-2.5 pb-1.5">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">Beli Sebanyak (X)</label>
                        <input type="number" step="any" name="buy_qty" id="edit_input_buy_qty" placeholder="2" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                    <div class="relative rounded-xl border border-slate-200 bg-white transition px-3 pt-2.5 pb-1.5">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">Gratis Sebanyak (Y)</label>
                        <input type="number" step="any" name="get_qty" id="edit_input_get_qty" placeholder="1" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                    <div class="relative rounded-xl border border-slate-200 bg-white transition px-3 pt-2.5 pb-1.5">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">Produk Bonus (Opsional)</label>
                        <select name="reward_product_id" id="edit_input_reward_product_id" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Sama dengan produk dibeli</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Syarat Minimal Belanja & Maksimal Diskon (2 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Min. Belanja Transaksi (Opsional)
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-brand-500">Rp</span>
                            <input type="number" step="any" name="min_order_amount" id="edit_input_min_order_amount" placeholder="0" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Maksimal Potongan Diskon (Opsional)
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-brand-500">Rp</span>
                            <input type="number" step="any" name="max_discount_amount" id="edit_input_max_discount_amount" placeholder="0" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segmentasi Member & Periode Promo (3 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Target Member Pelanggan
                        </label>
                        <select name="customer_group_id" id="edit_input_customer_group_id" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Semua Pelanggan (Umum)</option>
                            @foreach($customerGroups as $grp)
                                <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Tanggal Mulai
                        </label>
                        <input type="date" name="start_date" id="edit_input_start_date" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    </div>
                </div>

                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Tanggal Berakhir
                        </label>
                        <input type="date" name="end_date" id="edit_input_end_date" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Pilih Produk Promo (Enhanced with Live Search & Select All) -->
            <div id="edit_products_selector_wrapper">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 bg-white transition p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-slate-700">
                            Pilih Produk Berlaku <span class="font-normal text-slate-400">(Kosongkan jika berlaku untuk SEMUA produk)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span id="edit_selected_products_count" class="text-[10px] font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200/60">
                                0 Dipilih
                            </span>
                            <button type="button" onclick="toggleSelectAllProducts('edit', true)" class="text-[10px] font-semibold text-brand-600 hover:underline">
                                Pilih Semua
                            </button>
                            <span class="text-slate-300 text-xs">•</span>
                            <button type="button" onclick="toggleSelectAllProducts('edit', false)" class="text-[10px] font-semibold text-rose-500 hover:underline">
                                Hapus Pilihan
                            </button>
                        </div>
                    </div>

                    <!-- Live Filter Search Box -->
                    <div class="relative rounded-lg border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 px-3 py-1.5 transition flex items-center gap-2">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <input 
                            type="text" 
                            id="edit_product_search_input" 
                            oninput="filterProductList('edit')" 
                            placeholder="Cari ribuan produk berdasarkan nama, kode SKU, atau barcode..." 
                            class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                    <!-- Scrollable Product List -->
                    <div class="max-h-48 overflow-y-auto divide-y divide-slate-100 rounded-lg border border-slate-100 p-1 bg-white" id="edit_product_list_container">
                        @foreach($products as $prod)
                            <label class="edit-product-item flex items-center justify-between py-2 px-2.5 hover:bg-slate-50 rounded-lg cursor-pointer transition" data-name="{{ strtolower($prod->name) }}" data-code="{{ strtolower($prod->code) }}" data-barcode="{{ strtolower($prod->barcode) }}">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}" onchange="updateSelectedCount('edit')" class="edit-product-checkbox rounded text-brand-500 focus:ring-brand-500 border-slate-300 w-4 h-4 cursor-pointer">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800">{{ $prod->name }}</span>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                            <span class="font-mono bg-slate-100 px-1 py-0.2 rounded border border-slate-200 text-slate-600">{{ $prod->code }}</span>
                                            @if($prod->barcode)
                                                <span>• Barcode: {{ $prod->barcode }}</span>
                                            @endif
                                            @if($prod->brand)
                                                <span>• Brand: {{ $prod->brand }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-slate-800 shrink-0">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Status Aktif & Combinable Switch -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/50 border border-slate-200/90">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Status Promo Aktif</span>
                        <span class="block text-[10px] text-slate-400">Diterapkan otomatis di kasir POS</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="edit_input_is_active" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/50 border border-slate-200/90">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Dapat Digabung (Combinable)</span>
                        <span class="block text-[10px] text-slate-400">Bisa diakumulasi dengan promo lain</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_combinable" id="edit_input_is_combinable" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500"></div>
                    </label>
                </div>
            </div>

            <!-- Modal Footer / Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditDiscountModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
