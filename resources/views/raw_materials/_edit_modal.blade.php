<!-- MODAL EDIT RAW MATERIAL -->
<div id="editRawMaterialModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100/60">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Edit Data Bahan Baku</h3>
                    <p class="text-[11px] text-slate-400">Perbarui informasi takaran, harga HPP, dan minimum stok</p>
                </div>
            </div>
            <button onclick="closeEditRawMaterialModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editRawMaterialForm" method="POST" class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_raw_id" name="id">

            <!-- Nama Bahan Baku -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Nama Bahan Baku <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2.5">
                    <i data-lucide="box" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="text" name="name" id="edit_raw_name" required placeholder="Contoh: Biji Kopi Espresso Blend" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Kode SKU & Kategori -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Kode SKU
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="hash" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <input type="text" name="code" id="edit_raw_code" placeholder="RAW-00001" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Kategori Bahan
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="tag" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <select name="category_id" id="edit_raw_category_id" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Satuan Dasar & Harga Beli / HPP -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Satuan Dasar Takaran <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="scale" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <select name="base_unit_id" id="edit_raw_base_unit_id" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Pilih Satuan</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Harga Beli (HPP per Satuan) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-400 shrink-0">Rp</span>
                        <input type="number" step="any" name="purchase_price" id="edit_raw_purchase_price" required placeholder="0" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Min Stock Alert & Merk -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Batas Minimum Stok (Alert)
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <input type="number" step="any" name="min_stock" id="edit_raw_min_stock" placeholder="0" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Merk / Brand Bahan (Opsional)
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="bookmark" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <input type="text" name="brand" id="edit_raw_brand" placeholder="Merk bahan" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Keterangan -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Catatan Bahan Baku
                </label>
                <textarea name="description" id="edit_raw_description" rows="2" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none resize-none"></textarea>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" value="1" id="edit_raw_is_active" class="w-4 h-4 text-brand-500 rounded border-slate-300 focus:ring-brand-500">
                <label for="edit_raw_is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Bahan Baku Aktif & Siap Digunakan di Resep</label>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button onclick="closeEditRawMaterialModal()" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs shadow-md shadow-brand-500/20 transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
