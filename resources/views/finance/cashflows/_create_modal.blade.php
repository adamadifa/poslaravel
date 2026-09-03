<!-- CASH FLOW MODAL (Income / Expense) -->
<div id="cashFlowModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div id="cfModalIconContainer" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100/60 shadow-2xs">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="cfModalTitle" class="font-extrabold text-base text-slate-900 tracking-tight">Catat Kas Masuk</h3>
                    <p class="text-xs text-slate-400" id="cfModalSubtitle">Pemasukan kas atau modal usaha</p>
                </div>
            </div>
            <button onclick="closeModal('cashFlowModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="cashFlowForm" action="{{ route('cash-flows.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="type" id="cf_type_input" value="income">

            <div class="p-6 space-y-4">
                
                <!-- Account Selection -->
                <div id="cf_group_account_id">
                    <div id="cf_box_account_id" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label id="cf_label_account_id" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Pilih Akun Kas & Bank <span class="text-rose-500">*</span>
                        </label>
                        <select name="account_id" id="cf_account_select" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ $acc->is_default ? 'selected' : '' }}>
                                    {{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p id="cf_error_account_id" class="mt-1 text-[11px] text-slate-400 px-1 hidden"></p>
                </div>

                <!-- Category & Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div id="cf_group_category">
                        <div id="cf_box_category" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="cf_label_category" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Kategori Mutasi <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="category" 
                                id="cf_category_input" 
                                required 
                                list="cf_category_suggestions" 
                                placeholder="Ketik / pilih kategori..." 
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none"
                            >
                            <datalist id="cf_category_suggestions">
                                <option value="Gaji Karyawan">
                                <option value="Listrik & Air">
                                <option value="Sewa Tempat">
                                <option value="Transport & Logistik">
                                <option value="Biaya Operasional Toko">
                                <option value="Modal Tambahan">
                                <option value="Pendapatan Lain-lain">
                            </datalist>
                        </div>
                        <p id="cf_error_category" class="mt-1 text-[11px] text-slate-400 px-1">Kategori mutasi wajib diisi</p>
                    </div>

                    <div id="cf_group_amount">
                        <div id="cf_box_amount" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="cf_label_amount" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Nominal (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                step="any" 
                                min="1" 
                                name="amount" 
                                id="cf_amount_input"
                                required 
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 font-mono-num focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="cf_error_amount" class="mt-1 text-[11px] text-slate-400 px-1">Nominal harus lebih dari Rp 0</p>
                    </div>

                </div>

                <!-- Date & Description -->
                <div class="space-y-3.5">
                    
                    <div id="cf_group_date">
                        <div id="cf_box_date" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="cf_label_date" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Tanggal Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="transaction_date" 
                                id="cf_date_input" 
                                value="{{ date('Y-m-d') }}" 
                                required 
                                class="flatpickr-date w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer"
                            >
                        </div>
                        <p id="cf_error_date" class="mt-1 text-[11px] text-slate-400 px-1 hidden"></p>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Keterangan Tambahan
                        </label>
                        <textarea 
                            name="description" 
                            rows="2" 
                            placeholder="Catatan rincian biaya / pemasukan..." 
                            class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none resize-none"
                        ></textarea>
                    </div>

                </div>

            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('cashFlowModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button id="cfSubmitBtn" type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Simpan Mutasi</span>
                </button>
            </div>

        </form>
    </div>
</div>
