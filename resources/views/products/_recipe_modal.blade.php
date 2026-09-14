<!-- MODAL KELOLA RESEP / BILL OF MATERIALS (BOM) -->
<div id="recipeModal" class="fixed inset-0 z-[110] bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 bg-brand-500 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center shadow-xs">
                    <i data-lucide="chef-hat" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-base text-white tracking-tight" id="recipe_modal_product_name">Resep & Bahan Baku</h3>
                        <span id="recipe_modal_product_badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white uppercase tracking-wider">
                            Menu F&B
                        </span>
                    </div>
                    <p class="text-[11px] text-white/80">Petakan bahan baku, takaran per porsi, dan kalkulasi HPP otomatis</p>
                </div>
            </div>
            <button onclick="closeRecipeModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
            
            <!-- Summary Card: Live Cost & Margin Indicator -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Harga Jual Menu</div>
                    <div class="text-sm font-extrabold text-slate-900 font-mono mt-0.5" id="recipe_modal_selling_price">Rp 0</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total HPP Resep</div>
                    <div class="text-sm font-extrabold text-brand-600 font-mono mt-0.5" id="recipe_modal_total_cost">Rp 0</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Estimasi Gross Margin</div>
                    <div class="text-sm font-extrabold text-emerald-600 font-mono mt-0.5" id="recipe_modal_margin">0%</div>
                </div>
            </div>

            <!-- Form Tambah Bahan Baku Baru -->
            <div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/50 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-brand-500"></i>
                        <span>Tambah Bahan Baku ke Resep</span>
                    </span>
                    <span class="text-[10px] text-slate-400">Pilih bahan & tentukan takaran</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                    <!-- Dropdown Bahan Baku -->
                    <div class="sm:col-span-5">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Bahan Baku</label>
                        <select id="recipe_new_ingredient_id" class="w-full text-xs font-medium bg-white border border-slate-200 rounded-lg px-2.5 py-2 text-slate-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <option value="">Pilih Bahan Baku...</option>
                        </select>
                    </div>

                    <!-- Takaran Qty -->
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Takaran</label>
                        <input type="number" step="any" min="0.001" id="recipe_new_qty" placeholder="18" class="w-full text-xs font-medium bg-white border border-slate-200 rounded-lg px-2.5 py-2 text-slate-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <!-- Satuan Unit -->
                    <div class="sm:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Satuan</label>
                        <select id="recipe_new_unit_id" class="w-full text-xs font-medium bg-white border border-slate-200 rounded-lg px-2.5 py-2 text-slate-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <option value="">Pilih Satuan...</option>
                        </select>
                    </div>

                    <!-- Tombol Tambah Baris -->
                    <div class="sm:col-span-2">
                        <button type="button" onclick="addRecipeIngredientRow()" class="w-full py-2 px-3 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-1 shadow-xs">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Bahan Baku Resep -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Komposisi Bahan Racikan (<span id="recipe_items_count">0</span>)</span>
                    </h4>
                    <span class="text-[11px] text-slate-400">Stok bahan akan otomatis dipotong saat pesanan dibuat</span>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] border-b border-slate-200">
                                <th class="py-2 px-4">Nama Bahan Baku</th>
                                <th class="py-2 px-3 text-center">Takaran</th>
                                <th class="py-2 px-3">Satuan</th>
                                <th class="py-2 px-3 text-center">Waste %</th>
                                <th class="py-2 px-4 text-right">Est. Biaya (HPP)</th>
                                <th class="py-2 px-3 text-center w-12">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="recipe_table_body" class="divide-y divide-slate-100">
                            <!-- Rows injected via JavaScript -->
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                                    Belum ada bahan baku pada resep ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
            <button onclick="closeRecipeModal()" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                Tutup
            </button>
            <button id="saveRecipeBtn" onclick="saveProductRecipes()" type="button" class="px-5 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Simpan Resep</span>
            </button>
        </div>

    </div>
</div>
