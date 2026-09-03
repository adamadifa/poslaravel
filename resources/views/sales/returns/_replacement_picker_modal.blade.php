<!-- MODAL CARI & PILIH PRODUK PENGGANTI (EXCHANGE) -->
<div id="replacementProductPickerModal" class="fixed inset-0 z-[120] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="package-search" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Pilih Barang Pengganti (Tukar Barang)</h3>
                    <p class="text-xs text-slate-400">Pilih produk dari inventaris untuk diserahkan ke pelanggan</p>
                </div>
            </div>
            <button onclick="closeModal('replacementProductPickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 px-3.5 py-2 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="replacement_product_search" 
                    oninput="filterReplacementProducts()" 
                    placeholder="Ketik nama produk, kode SKU, atau barcode..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    autocomplete="off"
                >
            </div>
        </div>

        <!-- Scrollable Products Grid -->
        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="replacement_product_container">
                <!-- Dynamically populated from available products -->
            </div>
            <div id="replacement_product_empty" class="hidden p-8 text-center text-slate-400">
                <i data-lucide="package-x" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                <p class="font-bold text-xs text-slate-600">Produk Tidak Ditemukan</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Coba cari dengan kata kunci nama atau kode produk yang lain.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/70 flex items-center justify-end">
            <button type="button" onclick="closeModal('replacementProductPickerModal')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                Tutup
            </button>
        </div>

    </div>
</div>
