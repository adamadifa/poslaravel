<!-- CREATE / EDIT ACCOUNT MODAL -->
<div id="accountModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="accountModalTitle" class="font-extrabold text-base text-slate-900 tracking-tight">Tambah Akun Kas & Bank</h3>
                    <p class="text-xs text-slate-400">Atur rekening bank, kasir, atau kas kecil operasional</p>
                </div>
            </div>
            <button onclick="closeModal('accountModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="accountForm" action="{{ route('accounts.store') }}" method="POST">
            @csrf
            <div id="account_method_field"></div>
            
            <div class="p-6 space-y-5">
                
                <!-- Row 1: Account Code & Name -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    
                    <!-- Account Code (Col 1) -->
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Kode Akun <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="account_code" 
                            id="acc_code_input" 
                            required 
                            placeholder="ACC-1003" 
                            class="w-full bg-transparent border-0 p-0 text-xs font-mono font-bold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                    <!-- Account Name (Col 2) -->
                    <div class="sm:col-span-2 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nama Akun Keuangan <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="acc_name_input" 
                            required 
                            placeholder="Contoh: Bank BCA, Kas Toko Utama..." 
                            class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Row 2: Type & Opening Balance -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <!-- Type Selection -->
                    <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Tipe Akun <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="type" 
                            id="acc_type_select" 
                            onchange="toggleAccountTypeFields()" 
                            required 
                            class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer"
                        >
                            <option value="cash">Kas Fisik / Kasir (Cash)</option>
                            <option value="bank">Rekening Bank (Bank)</option>
                            <option value="other">Lainnya / E-Wallet (Other)</option>
                        </select>
                    </div>

                    <!-- Opening Balance -->
                    <div id="opening_balance_container" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Saldo Awal (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="any" 
                            min="0" 
                            name="opening_balance" 
                            id="acc_opening_balance_input" 
                            value="0" 
                            required 
                            class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 font-mono-num placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Row 3: Bank Specific Fields (Conditional) -->
                <div id="bank_fields_container" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 hidden">
                    
                    <!-- Bank Name -->
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nama Bank
                        </label>
                        <input 
                            type="text" 
                            name="bank_name" 
                            id="acc_bank_name_input" 
                            placeholder="Contoh: BCA, Mandiri, BRI, BNI" 
                            class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                    <!-- Account Number -->
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nomor Rekening
                        </label>
                        <input 
                            type="text" 
                            name="account_number" 
                            id="acc_number_input" 
                            placeholder="Contoh: 8291029381" 
                            class="w-full bg-transparent border-0 p-0 text-xs font-mono font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Row 4: Description -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Keterangan / Peruntukan
                    </label>
                    <textarea 
                        name="description" 
                        id="acc_desc_input" 
                        rows="2" 
                        placeholder="Catatan tambahan peruntukan akun kas / bank ini..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none resize-none"
                    ></textarea>
                </div>

                <!-- Row 5: Set Default Switch / Checkbox Card -->
                <label for="acc_is_default_input" class="p-3.5 rounded-xl border border-slate-200 hover:border-brand-200 bg-slate-50/50 hover:bg-brand-50/20 transition flex items-center justify-between cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 border border-brand-100">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Jadikan Akun Kas Utama</span>
                            <span class="text-[11px] text-slate-400 block">Akun default penerimaan transaksi tunai POS kasir</span>
                        </div>
                    </div>
                    <input 
                        type="checkbox" 
                        name="is_default" 
                        id="acc_is_default_input" 
                        value="1" 
                        class="rounded-md border-slate-300 text-brand-500 focus:ring-brand-500 w-4 h-4 cursor-pointer"
                    >
                </label>

            </div>

            <!-- Modal Action Buttons -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('accountModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Akun</span>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    function toggleAccountTypeFields() {
        const type = document.getElementById('acc_type_select').value;
        const bankContainer = document.getElementById('bank_fields_container');
        if (type === 'bank') {
            bankContainer.classList.remove('hidden');
        } else {
            bankContainer.classList.add('hidden');
        }
    }

    function openCreateAccountModal() {
        document.getElementById('accountForm').reset();
        document.getElementById('accountForm').action = "{{ route('accounts.store') }}";
        document.getElementById('account_method_field').innerHTML = '';
        document.getElementById('accountModalTitle').innerText = 'Tambah Akun Kas & Bank';
        document.getElementById('opening_balance_container').style.display = 'block';
        toggleAccountTypeFields();
        openModal('accountModal');
    }

    function openEditAccountModal(accId) {
        openCreateAccountModal();
        document.getElementById('accountModalTitle').innerText = 'Edit Akun Kas & Bank';
        document.getElementById('accountForm').action = `/accounts/${accId}`;
        document.getElementById('account_method_field').innerHTML = '@method("PUT")';
        document.getElementById('opening_balance_container').style.display = 'none';

        fetch(`/accounts/${accId}`)
            .then(res => res.json())
            .then(acc => {
                document.getElementById('acc_code_input').value = acc.account_code || '';
                document.getElementById('acc_name_input').value = acc.name || '';
                document.getElementById('acc_type_select').value = acc.type || 'cash';
                document.getElementById('acc_bank_name_input').value = acc.bank_name || '';
                document.getElementById('acc_number_input').value = acc.account_number || '';
                document.getElementById('acc_desc_input').value = acc.description || '';
                document.getElementById('acc_is_default_input').checked = !!acc.is_default;
                toggleAccountTypeFields();
            });
    }
</script>
