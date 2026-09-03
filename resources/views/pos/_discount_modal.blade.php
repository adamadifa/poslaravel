<!-- MODAL DISKON TRANSAKSI / VOUCHER (F9) -->
<div id="discountModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="tag" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Diskon / Voucher Transaksi (F9)</h3>
                    <p class="text-[11px] text-slate-400">Terapkan kode promo voucher atau potongan nominal manual</p>
                </div>
            </div>
            <button onclick="closeModal('discountModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="discountForm" onsubmit="handleApplyDiscountModal(event)" class="p-6 space-y-4">
            
            <!-- Kode Voucher Promo -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Kode Promo Voucher (Opsional)
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="ticket" class="w-4 h-4 text-slate-400"></i>
                    <input type="text" id="discount_promo_code" placeholder="Contoh: PROMO10" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 uppercase focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Potongan Manual Nominal (Rp) -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Potongan Manual Kasir (Rp)
                </label>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-brand-500">Rp</span>
                    <input type="number" step="any" min="0" id="discount_manual_amount" placeholder="0" class="w-full bg-transparent border-0 p-0 text-sm font-bold text-slate-900 focus:ring-0 focus:outline-none font-mono-num">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('discountModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Terapkan Diskon</span>
                </button>
            </div>
        </form>
    </div>
</div>
