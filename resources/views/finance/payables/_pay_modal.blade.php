<!-- PAY AP MODAL -->
<div id="payModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Form Pembayaran Hutang Supplier</h3>
                    <p class="text-xs text-slate-400" id="payModalSubtitle">Faktur Penerimaan Barang</p>
                </div>
            </div>
            <button onclick="closeModal('payModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('payables.store') }}" method="POST">
            @csrf
            <input type="hidden" name="purchase_receipt_id" id="modal_receipt_id">

            <div class="p-6 space-y-4">
                
                <!-- Sisa Tagihan Info Banner -->
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-semibold text-amber-800 block">Sisa Hutang Belum Lunas</span>
                        <span class="text-xs text-amber-700 font-medium" id="modal_supplier_name">Supplier</span>
                    </div>
                    <div class="text-lg font-black text-amber-800 font-mono-num" id="modal_remaining_display">
                        Rp 0
                    </div>
                </div>

                <!-- Account & Method -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Bayar dari Akun <span class="text-rose-500">*</span>
                        </label>
                        <select name="account_id" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ $acc->is_default ? 'selected' : '' }}>
                                    {{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Metode Pembayaran <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_method" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="cash">Tunai / Kas (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="check">Cek / Bilyet Giro</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                </div>

                <!-- Amount & Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nominal Dibayar (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="any" 
                            min="1" 
                            name="amount" 
                            id="modal_amount_input" 
                            required 
                            class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 font-mono-num focus:ring-0 focus:outline-none"
                        >
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Tanggal Bayar <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="payment_date" 
                            id="modal_payment_date" 
                            value="{{ date('Y-m-d') }}" 
                            required 
                            class="flatpickr-date w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer"
                        >
                    </div>

                </div>

                <!-- Reference & Notes -->
                <div class="space-y-3.5">
                    
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            No. Referensi / Bukti Transfer
                        </label>
                        <input 
                            type="text" 
                            name="reference_number" 
                            placeholder="Contoh: TRF-8392183 atau No. Giro..." 
                            class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Catatan Pembayaran
                        </label>
                        <input 
                            type="text" 
                            name="notes" 
                            placeholder="Catatan tambahan pelunasan / cicilan..." 
                            class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                </div>

            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('payModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Proses Pembayaran</span>
                </button>
            </div>

        </form>
    </div>
</div>
