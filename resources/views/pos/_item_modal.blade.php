<!-- MODAL INPUT ITEM (QTY, PILIH SATUAN, & HARGA MANUAL) -->
<div id="itemModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        
        <!-- Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shrink-0 overflow-hidden" id="modal_product_img_box">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-sm text-slate-900 truncate" id="modal_product_name">Nama Produk</h3>
                    <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                        <span id="modal_product_code" class="font-mono bg-slate-100 px-1 py-0.2 rounded border border-slate-200 text-slate-600">PRD-0001</span>
                        <span id="modal_product_stock" class="font-bold text-slate-600">Stok: 0</span>
                    </div>
                </div>
            </div>
            <button onclick="closeModal('itemModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="itemModalForm" onsubmit="handleItemModalSubmit(event)" class="p-5 space-y-4">
            
            <!-- Satuan Jual -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Satuan Jual <span class="text-rose-500">*</span>
                </label>
                <select id="modal_item_unit" onchange="onModalUnitChange()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-brand-600 focus:ring-0 focus:outline-none cursor-pointer">
                    <!-- Dynamic Units -->
                </select>
            </div>

            <!-- Jumlah / Qty (Support Desimal) -->
            <div class="relative rounded-xl border-2 border-brand-500 bg-white transition px-4 pt-3 pb-2.5 shadow-2xs">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-brand-600">
                    Jumlah / Qty <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="adjustModalQty(-1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 flex items-center justify-center text-sm font-black transition cursor-pointer">-</button>
                    <input 
                        type="number" 
                        step="any" 
                        min="0.0001" 
                        id="modal_item_qty" 
                        oninput="calculateModalSubtotal()" 
                        required 
                        placeholder="1" 
                        class="w-full text-center bg-transparent border-0 p-0 text-xl font-black text-slate-900 font-mono-num focus:ring-0 focus:outline-none"
                    >
                    <button type="button" onclick="adjustModalQty(1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 flex items-center justify-center text-sm font-black transition cursor-pointer">+</button>
                </div>
            </div>

            <!-- Harga Satuan (Bisa di-edit manual jika diizinkan) -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <div class="flex items-center justify-between">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Harga Satuan Jual <span class="text-rose-500">*</span>
                    </label>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-brand-500">Rp</span>
                    <input 
                        type="number" 
                        step="any" 
                        min="0" 
                        id="modal_item_price" 
                        oninput="calculateModalSubtotal()" 
                        required 
                        class="w-full bg-transparent border-0 p-0 text-sm font-bold text-slate-900 font-mono-num focus:ring-0 focus:outline-none"
                    >
                    <span id="modal_manual_price_badge" class="hidden px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">
                        Manual Edit
                    </span>
                </div>
            </div>

            <!-- Subtotal Banner -->
            <div class="p-3.5 rounded-xl bg-slate-900 text-white flex items-center justify-between shadow-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Subtotal Item</span>
                    <span class="text-[11px] text-slate-300" id="modal_subtotal_calc_text">1 x Rp 0</span>
                </div>
                <span class="text-lg font-black text-amber-400 font-mono-num" id="modal_item_subtotal_display">Rp 0</span>
            </div>

            <!-- Footer Action -->
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('itemModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span id="modal_submit_btn_text">Masukkan ke Keranjang</span>
                </button>
            </div>
        </form>
    </div>
</div>
