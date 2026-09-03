<!-- TRANSFER MODAL -->
<div id="transferModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Form Transfer Saldo Kas & Bank</h3>
                    <p class="text-xs text-slate-400">Pindahkan dana antar rekening bank atau kas internal</p>
                </div>
            </div>
            <button onclick="closeModal('transferModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="transferForm" action="{{ route('account-transfers.store') }}" method="POST" novalidate>
            @csrf

            <div class="p-6 space-y-4">
                
                <!-- From & To Accounts -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div id="trf_group_from_account">
                        <div id="trf_box_from_account" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="trf_label_from_account" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Dari Akun Asal <span class="text-rose-500">*</span>
                            </label>
                            <select name="from_account_id" id="trf_from_account_select" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">
                                        {{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p id="trf_error_from_account" class="mt-1 text-[11px] text-slate-400 px-1 hidden"></p>
                    </div>

                    <div id="trf_group_to_account">
                        <div id="trf_box_to_account" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="trf_label_to_account" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Ke Akun Tujuan <span class="text-rose-500">*</span>
                            </label>
                            <select name="to_account_id" id="trf_to_account_select" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ !$loop->first ? 'selected' : '' }}>
                                        {{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p id="trf_error_to_account" class="mt-1 text-[11px] text-slate-400 px-1 hidden"></p>
                    </div>

                </div>

                <!-- Amount & Fee -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div id="trf_group_amount">
                        <div id="trf_box_amount" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="trf_label_amount" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Nominal Transfer (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                step="any" 
                                min="1" 
                                name="amount" 
                                id="trf_amount_input"
                                required 
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 font-mono-num focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="trf_error_amount" class="mt-1 text-[11px] text-slate-400 px-1">Nominal transfer harus lebih dari Rp 0</p>
                    </div>

                    <div id="trf_group_fee">
                        <div id="trf_box_fee" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="trf_label_fee" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Biaya Admin (Opsional)
                            </label>
                            <input 
                                type="number" 
                                step="any" 
                                min="0" 
                                name="transfer_fee" 
                                id="trf_fee_input"
                                value="0" 
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 font-mono-num focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="trf_error_fee" class="mt-1 text-[11px] text-slate-400 px-1 hidden"></p>
                    </div>

                </div>

                <!-- Date & Reference -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Tanggal Transfer <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="transfer_date" 
                            id="trf_date_input" 
                            value="{{ date('Y-m-d') }}" 
                            required 
                            class="flatpickr-date w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer"
                        >
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            No. Referensi / Bukti
                        </label>
                        <input 
                            type="text" 
                            name="reference_number" 
                            placeholder="Contoh: TRF-839218 atau No. Setoran" 
                            class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                </div>

                <!-- Notes -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Catatan Pemindahan Saldo
                    </label>
                    <input 
                        type="text" 
                        name="notes" 
                        placeholder="Contoh: Setor omset harian kasir ke Bank BCA..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>

            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('transferModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Proses Transfer</span>
                </button>
            </div>

        </form>
    </div>
</div>
