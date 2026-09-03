<!-- CREATE SALE RETURN MODAL -->
<div id="createReturnModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-6xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[92vh]">
        
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Buat Retur Penjualan</h3>
                    <p class="text-xs text-slate-400">Pilih faktur invoice penjualan POS, tentukan kuantiti barang diretur, dan atur metode refund</p>
                </div>
            </div>
            <button onclick="closeModal('createReturnModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createReturnForm" action="{{ route('sale-returns.store') }}" method="POST" novalidate onsubmit="return validateSaleReturnForm(event)" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <input type="hidden" name="sale_id" id="sr_sale_id">

            <div class="flex-1 p-6 overflow-y-auto space-y-6">
                
                <!-- Step 1: Interactive Invoice Picker Box -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-50 to-orange-50/30 border border-slate-200/90 space-y-3 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">
                                Faktur Invoice Penjualan (Struk POS) <span class="text-rose-500">*</span>
                            </span>
                            <p class="text-[11px] text-slate-400">Pilih faktur penjualan yang akan diajukan pengembalian produk</p>
                        </div>
                        <button type="button" onclick="openInvoicePickerModal()" class="px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer shrink-0 self-start sm:self-auto">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            <span>Cari & Pilih Faktur Invoice</span>
                        </button>
                    </div>

                    <!-- Selected Invoice Card -->
                    <div id="sr_invoice_selected_card" class="hidden p-4 rounded-xl bg-white border border-emerald-300 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <i data-lucide="receipt-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-xs text-emerald-950 font-mono tracking-tight" id="sr_info_inv_num">INV-XXXX</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800" id="sr_info_inv_date">01/01/2026</span>
                                </div>
                                <div class="text-[11px] text-slate-600 mt-0.5" id="sr_info_inv_cust">Pelanggan: Pelanggan Umum • Kasir: Admin</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 self-end sm:self-auto">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Total Transaksi</span>
                                <span class="font-black text-sm text-emerald-700 font-mono-num" id="sr_info_inv_total">Rp 0</span>
                            </div>
                            <button type="button" onclick="openInvoicePickerModal()" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-brand-600 hover:border-brand-300 text-xs font-bold transition cursor-pointer">
                                Ganti
                            </button>
                        </div>
                    </div>

                    <!-- Not Selected Warning / Prompt -->
                    <div id="sr_invoice_unselected_prompt" class="p-3.5 rounded-xl bg-white/80 border border-dashed border-slate-300 flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="info" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span>Belum ada faktur invoice yang dipilih. Klik tombol <strong>"Cari & Pilih Faktur Invoice"</strong>.</span>
                        </div>
                    </div>

                    <p id="sr_error_sale_id" class="hidden text-[11px] font-medium text-rose-500 px-1">
                        Faktur invoice penjualan wajib dipilih terlebih dahulu.
                    </p>
                </div>

                <!-- Step 2: Return Parameters (Outset floating styling) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    
                    <div id="sr_group_refund_method">
                        <div id="sr_box_refund_method" class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Metode Refund <span class="text-rose-500">*</span>
                            </label>
                            <select name="refund_method" id="sr_refund_method_select" onchange="toggleRefundAccountField()" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                <option value="cash">Pengembalian Tunai (Cash)</option>
                                <option value="credit_deduction">Potong Piutang Penjualan</option>
                                <option value="exchange">Tukar Barang Saja</option>
                            </select>
                        </div>
                        <p id="sr_error_refund_method" class="hidden mt-1 text-[11px] font-medium text-rose-500 px-1"></p>
                    </div>

                    <div id="sr_account_container">
                        <div id="sr_box_account_id" class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Potong dari Kas/Bank <span class="text-rose-500">*</span>
                            </label>
                            <select name="account_id" id="sr_account_select" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ $acc->is_default ? 'selected' : '' }}>
                                        {{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p id="sr_error_account_id" class="hidden mt-1 text-[11px] font-medium text-rose-500 px-1"></p>
                    </div>

                    <div id="sr_group_return_date">
                        <div id="sr_box_return_date" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="sr_label_return_date" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Tanggal Retur <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="return_date" 
                                id="sr_date_input" 
                                value="{{ date('Y-m-d') }}" 
                                required 
                                class="flatpickr-date w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer"
                            >
                        </div>
                        <p id="sr_error_return_date" class="hidden mt-1 text-[11px] font-medium text-rose-500 px-1"></p>
                    </div>

                    <div id="sr_group_reason">
                        <div id="sr_box_reason" class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                            <label id="sr_label_reason" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                                Alasan Retur <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="reason" 
                                id="sr_input_reason"
                                required 
                                placeholder="Contoh: Barang cacat, salah ukuran..." 
                                class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="sr_error_reason" class="hidden mt-1 text-[11px] font-medium text-rose-500 px-1">Alasan retur wajib diisi</p>
                    </div>

                </div>

                <!-- Step 3: Items Return Table -->
                <div class="border border-slate-200/90 rounded-2xl bg-white overflow-hidden shadow-2xs">
                    <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="package-minus" class="w-4 h-4 text-brand-500"></i>
                            <span class="font-extrabold text-xs text-slate-900">Pilih Kuantiti Barang yang Diretur</span>
                        </div>
                        <span class="text-[11px] text-slate-400">Atur kuantiti 0 jika barang tidak diretur</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/70 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                                    <th class="py-3 px-4">Nama Produk</th>
                                    <th class="py-3 px-3 text-right">Qty Beli</th>
                                    <th class="py-3 px-3 text-right">Harga Satuan</th>
                                    <th class="py-3 px-3 w-36 text-center">Qty Retur</th>
                                    <th class="py-3 px-4 text-right">Subtotal Refund</th>
                                </tr>
                            </thead>
                            <tbody id="sr_items_tbody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically from invoice -->
                            </tbody>
                        </table>
                    </div>

                    <div id="sr_items_empty" class="p-8 text-center text-slate-400">
                        <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                        <p class="font-bold text-xs text-slate-600">Silakan Pilih Faktur Invoice Terlebih Dahulu</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Daftar produk yang dibeli akan otomatis muncul di sini setelah invoice dipilih.</p>
                    </div>
                </div>

                <!-- Step 4: Replacement Items Table (Visible only when Tukar Barang / exchange is selected) -->
                <div id="sr_replacement_card" class="hidden border border-brand-200/90 rounded-2xl bg-white overflow-hidden shadow-2xs">
                    <div class="px-5 py-3.5 bg-brand-50/70 border-b border-brand-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="package-plus" class="w-4 h-4 text-brand-600"></i>
                            <div>
                                <span class="font-extrabold text-xs text-brand-950 block">Barang Pengganti yang Diserahkan (Tukar Barang)</span>
                                <p class="text-[11px] text-brand-700/80">Stok barang ini akan otomatis dipotong (OUT) dari inventaris/kartu stok</p>
                            </div>
                        </div>
                        <button type="button" onclick="openReplacementProductPickerModal()" class="px-3.5 py-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer shrink-0">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Barang Pengganti</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/70 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                                    <th class="py-3 px-4">Nama Produk Pengganti</th>
                                    <th class="py-3 px-3 text-right">Harga Satuan</th>
                                    <th class="py-3 px-3 w-36 text-center">Qty Diberikan</th>
                                    <th class="py-3 px-4 text-right">Subtotal</th>
                                    <th class="py-3 px-3 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="sr_rep_items_tbody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <div id="sr_rep_items_empty" class="p-6 text-center text-slate-400">
                        <i data-lucide="package-open" class="w-7 h-7 mx-auto mb-1 text-slate-300"></i>
                        <p class="font-bold text-xs text-slate-600">Belum Ada Barang Pengganti Ditambahkan</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol <strong>"Tambah Barang Pengganti"</strong> di atas untuk memilih barang pengganti dari stok toko.</p>
                    </div>
                </div>

                <p id="sr_error_items" class="hidden text-[11px] font-medium text-rose-500 px-1 text-center">
                    Minimal harus ada 1 produk dengan jumlah kuantiti retur lebih dari 0.
                </p>
                <p id="sr_error_replacement" class="hidden text-[11px] font-medium text-rose-500 px-1 text-center">
                    Untuk metode Tukar Barang, silakan tambahkan minimal 1 barang pengganti dengan kuantiti lebih dari 0.
                </p>

                <!-- Premium Structured Summary Footer Card -->
                <div class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-4.5 shadow-2xs">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        <!-- Left Info / Context -->
                        <div class="flex items-start sm:items-center gap-3">
                            <div id="sr_summary_icon_wrap" class="w-10 h-10 rounded-xl bg-brand-100/80 text-brand-600 flex items-center justify-center shrink-0 border border-brand-200/60 shadow-2xs">
                                <i id="sr_summary_icon" data-lucide="calculator" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold text-slate-900 tracking-tight" id="sr_summary_title">Total Nilai Pengembalian Dana (Refund)</span>
                                    <span id="sr_summary_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">Tunai</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5" id="sr_summary_desc">Stok barang retur akan otomatis dipulihkan (IN) ke gudang utama kasir</p>
                            </div>
                        </div>

                        <!-- Right Calculation / Badges & Amounts -->
                        <div class="flex flex-wrap items-center gap-3 sm:gap-4 self-end md:self-auto bg-white px-4 py-2.5 rounded-xl border border-slate-200/90 shadow-2xs">
                            
                            <!-- Subtotals (Visible during exchange) -->
                            <div id="sr_breakdown_wrap" class="hidden flex items-center gap-3 text-xs border-r border-slate-200 pr-4">
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 font-semibold uppercase block">Nilai Retur</span>
                                    <span id="sr_subtotal_return_val" class="font-bold text-slate-700 font-mono-num">Rp 0</span>
                                </div>
                                <div class="text-slate-300 font-black">−</div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 font-semibold uppercase block">Nilai Pengganti</span>
                                    <span id="sr_subtotal_rep_val" class="font-bold text-brand-600 font-mono-num">Rp 0</span>
                                </div>
                            </div>

                            <!-- Final Total -->
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 font-semibold uppercase block" id="sr_final_amount_label">Total Pengembalian</span>
                                <div class="flex items-center gap-2">
                                    <span id="sr_total_refund_display" class="text-xl sm:text-2xl font-black text-rose-600 font-mono-num">Rp 0</span>
                                    <span id="sr_status_badge" class="hidden px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wide"></span>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('createReturnModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span id="sr_submit_btn_text">Proses Retur & Refund</span>
                </button>
            </div>

        </form>
    </div>
</div>
