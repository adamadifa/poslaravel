<!-- MODAL CHECKOUT & PAYMENT -->
<div id="paymentModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[95vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Proses Pembayaran (F12)</h3>
                    <p class="text-[11px] text-slate-400">Pilih metode bayar, input nominal uang, dan hitung kembalian</p>
                </div>
            </div>
            <button onclick="closeModal('paymentModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="paymentForm" onsubmit="handleProcessCheckout(event)" class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            
            <!-- Total Tagihan Banner -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Tagihan Final</span>
                    <span class="text-xs text-slate-300 font-medium" id="pay_item_summary_text">0 Item</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl sm:text-3xl font-black text-amber-400 font-mono-num" id="pay_grand_total_display">Rp 0</span>
                </div>
            </div>

            <!-- Tipe Layanan / Pesanan Selector -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-700">Tipe Layanan / Pesanan</label>
                    <span class="text-[10px] text-slate-400 font-medium">Default: Take Away</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <label id="pay_service_card_takeaway" class="pay-service-card flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-brand-500 bg-brand-50/70 text-brand-700 font-bold text-xs cursor-pointer transition shadow-2xs">
                        <input type="radio" name="pay_service_type" value="takeaway" checked onchange="onPaymentServiceTypeChange('takeaway')" class="sr-only">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-brand-600"></i>
                        <span>Take Away</span>
                    </label>
                    <label id="pay_service_card_dine_in" class="pay-service-card flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-700 font-bold text-xs cursor-pointer transition shadow-2xs">
                        <input type="radio" name="pay_service_type" value="dine_in" onchange="onPaymentServiceTypeChange('dine_in')" class="sr-only">
                        <i data-lucide="utensils" class="w-4 h-4 text-slate-500"></i>
                        <span>Makan di Tempat</span>
                    </label>
                    <label id="pay_service_card_delivery" class="pay-service-card flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-700 font-bold text-xs cursor-pointer transition shadow-2xs">
                        <input type="radio" name="pay_service_type" value="delivery" onchange="onPaymentServiceTypeChange('delivery')" class="sr-only">
                        <i data-lucide="truck" class="w-4 h-4 text-slate-500"></i>
                        <span>Delivery</span>
                    </label>
                </div>

                <!-- Opsi Meja jika Makan di Tempat dipilih -->
                <div id="pay_dine_in_table_section" class="hidden mt-2 p-2.5 rounded-xl bg-amber-50/80 border border-amber-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs">
                        <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-[11px]">
                            <i data-lucide="utensils-crossed" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block">Meja Terpilih:</span>
                            <strong class="text-slate-900 font-bold" id="pay_table_selected_text">Tanpa Meja (Bebas)</strong>
                        </div>
                    </div>
                    <button type="button" onclick="openTableModal()" class="px-3 py-1.5 rounded-lg bg-white hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-300 transition shadow-2xs cursor-pointer flex items-center gap-1">
                        <i data-lucide="layout-grid" class="w-3 h-3"></i>
                        <span>Pilih Meja (F3)</span>
                    </button>
                </div>
            </div>

            <!-- Metode Pembayaran Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Metode Pembayaran</label>
                <div class="grid grid-cols-4 gap-2">
                    <label class="payment-method-card flex flex-col items-center justify-center p-3 rounded-xl border border-brand-500 bg-brand-50/60 text-brand-700 cursor-pointer transition shadow-2xs">
                        <input type="radio" name="payment_method" value="cash" checked onchange="onPaymentMethodChange('cash')" class="sr-only">
                        <i data-lucide="banknote" class="w-5 h-5 mb-1 text-emerald-600"></i>
                        <span class="text-xs font-bold">Tunai</span>
                    </label>
                    <label class="payment-method-card flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-700 cursor-pointer transition shadow-2xs">
                        <input type="radio" name="payment_method" value="qris" onchange="onPaymentMethodChange('qris')" class="sr-only">
                        <i data-lucide="qr-code" class="w-5 h-5 mb-1 text-brand-500"></i>
                        <span class="text-xs font-bold">QRIS</span>
                    </label>
                    <label class="payment-method-card flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-700 cursor-pointer transition shadow-2xs">
                        <input type="radio" name="payment_method" value="transfer" onchange="onPaymentMethodChange('transfer')" class="sr-only">
                        <i data-lucide="credit-card" class="w-5 h-5 mb-1 text-blue-500"></i>
                        <span class="text-xs font-bold">Transfer</span>
                    </label>
                    <label class="payment-method-card flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-700 cursor-pointer transition shadow-2xs">
                        <input type="radio" name="payment_method" value="credit" onchange="onPaymentMethodChange('credit')" class="sr-only">
                        <i data-lucide="user-check" class="w-5 h-5 mb-1 text-amber-500"></i>
                        <span class="text-xs font-bold">Piutang</span>
                    </label>
                </div>
            </div>

            <!-- Cash Input & Quick Cash Buttons -->
            <div id="cash_input_section" class="space-y-3">
                <div class="relative rounded-xl border-2 border-brand-500 bg-white transition px-4 pt-3 pb-2.5 shadow-2xs">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-brand-600">
                        Nominal Uang Diterima (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-brand-500">Rp</span>
                        <input type="text" inputmode="numeric" id="pay_cash_received_input" oninput="onPayCashInput(this)" placeholder="0" class="w-full bg-transparent border-0 p-0 text-xl font-black text-slate-900 font-mono-num focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <!-- Quick Cash Preset Pills -->
                <div class="flex flex-wrap items-center gap-1.5" id="quick_cash_pills_container">
                    <button type="button" onclick="setCashAmount('exact')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Uang Pas</button>
                    <button type="button" onclick="setCashAmount(10000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">10.000</button>
                    <button type="button" onclick="setCashAmount(20000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">20.000</button>
                    <button type="button" onclick="setCashAmount(50000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">50.000</button>
                    <button type="button" onclick="setCashAmount(100000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">100.000</button>
                </div>

                <!-- Kembalian Card -->
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Uang Kembalian</span>
                        <span class="text-[10px] text-emerald-600 font-medium" id="pay_change_status">Uang pas / belum cukup</span>
                    </div>
                    <span class="text-xl sm:text-2xl font-black text-emerald-600 font-mono-num" id="pay_change_amount_display">Rp 0</span>
                </div>
            </div>

            <!-- Non-Cash Reference Input (QRIS / Transfer / EDC) -->
            <div id="non_cash_input_section" class="hidden space-y-3">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Nomor Referensi Transaksi / Approval Code (Opsional)
                    </label>
                    <input type="text" id="pay_reference_number_input" placeholder="Contoh: REF-990812903" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Catatan Transaksi -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Catatan Struk (Opsional)</label>
                <input type="text" id="pay_notes_input" placeholder="Misal: Pesanan Meja 2 / Titipan..." class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 focus:ring-0 focus:outline-none">
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('paymentModal')" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" id="btn_submit_payment" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Selesaikan & Cetak Struk</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL HOLD & RECALL TRANSAKSI -->
<div id="holdModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60">
                    <i data-lucide="pause-circle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Simpan Sementara (Hold)</h3>
                    <p class="text-[11px] text-slate-400">Tahan keranjang untuk melayani pelanggan berikutnya</p>
                </div>
            </div>
            <button onclick="closeModal('holdModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="holdForm" onsubmit="handleHoldCart(event)" class="p-6 space-y-4">
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Label Identitas Antrian / Meja <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="hold_reference_label" required placeholder="Contoh: Meja 4 / Bpk. Rahmat" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('holdModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-600/25 transition">Simpan Hold</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL LIST RECALL HELD TRANSACTIONS -->
<div id="recallModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-200/60">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Daftar Transaksi Tertahan (Recall)</h3>
                    <p class="text-[11px] text-slate-400">Pilih keranjang yang ingin dilanjutkan proses pembayarannya</p>
                </div>
            </div>
            <button onclick="closeModal('recallModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto divide-y divide-slate-100" id="held_transactions_container">
            <!-- Dynamic list -->
        </div>
    </div>
</div>

<!-- MODAL STRUK SUKSES / PRINT PREVIEW (58mm / 80mm) -->
<div id="receiptModal" class="fixed inset-0 z-[120] bg-slate-900/70 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-sm w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4 text-brand-500"></i>
                <span class="font-bold text-xs text-slate-800">Preview Struk Kasir</span>
            </div>
            <button onclick="closeModal('receiptModal')" type="button" class="text-slate-400 hover:text-slate-600 p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Thermal Receipt Preview Paper Area -->
        <div class="p-6 overflow-y-auto bg-slate-100 flex justify-center">
            <div id="thermal_receipt_paper" class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 w-full max-w-[280px] font-mono text-[11px] text-slate-800 leading-tight space-y-2">
                <!-- Receipt Content rendered dynamically -->
            </div>
        </div>

        <!-- Print Action Buttons -->
        <div class="p-4 border-t border-slate-100 bg-white flex items-center justify-between gap-2">
            <button type="button" onclick="closeModal('receiptModal')" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Tutup</button>
            <div class="flex items-center gap-2">
                <!-- Cetak Bluetooth Langsung (ESC/POS) -->
                <button type="button" onclick="printViaBluetooth()" id="btnBtPrintModal" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/25 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="bluetooth" class="w-3.5 h-3.5"></i>
                    <span>Cetak Bluetooth</span>
                </button>
                <!-- Cetak Browser Biasa (Ctrl+P) -->
                <button type="button" onclick="printReceipt()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Browser (P)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KONEKSI & PENGATURAN PRINTER BLUETOOTH -->
<div id="bluetoothPrinterModal" class="fixed inset-0 z-[130] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-blue-50/80 via-sky-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm shadow-blue-600/30">
                    <i data-lucide="printer" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-black text-base text-slate-900 tracking-tight">Printer Bluetooth (ESC/POS)</h3>
                    <p class="text-[11px] text-slate-500">Cetak struk kasir & agen instan tanpa dialog print browser</p>
                </div>
            </div>
            <button onclick="closeModal('bluetoothPrinterModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 space-y-5">
            <!-- Status Card -->
            <div class="p-4 rounded-2xl border transition" id="bt_printer_status_card" style="background-color: #f8fafc; border-color: #e2e8f0;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-3 h-3 rounded-full bg-slate-400" id="bt_status_circle"></div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Status Koneksi</span>
                            <strong class="text-sm font-black text-slate-800 block" id="bt_status_device_name">Belum Terhubung</strong>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200/70 text-slate-600" id="bt_status_badge">Offline</span>
                </div>
            </div>

            <!-- Ukuran Kertas Thermal -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">Ukuran Kertas Printer Thermal:</label>
                <div class="grid grid-cols-2 gap-2.5">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-semibold text-slate-800">
                        <input type="radio" name="bt_paper_width" value="58" checked onchange="changePrinterPaperWidth('58')" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block font-bold">58 mm</span>
                            <span class="text-[10px] text-slate-400 font-normal">Mini Mobile / POS (32 char)</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-semibold text-slate-800">
                        <input type="radio" name="bt_paper_width" value="80" onchange="changePrinterPaperWidth('80')" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block font-bold">80 mm</span>
                            <span class="text-[10px] text-slate-400 font-normal">Thermal Desktop (44 char)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Opsi Direct Print Otomatis -->
            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-800 block">Direct Auto-Print Setelah Bayar</span>
                    <span class="text-[10px] text-slate-500 block">Langsung cetak struk otomatis tanpa harus klik cetak lagi</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="bt_auto_print_toggle" onchange="toggleBtAutoPrint(this.checked)" class="sr-only peer">
                    <div class="w-10 h-5.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>

            <!-- Petunjuk Penggunaan -->
            <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900 space-y-1">
                <div class="font-bold flex items-center gap-1 text-blue-700">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    <span>Cara Kerja Penyimpanan Printer:</span>
                </div>
                <ol class="list-decimal list-inside text-[11px] text-blue-800/90 space-y-0.5 pl-0.5">
                    <li>Printer yang pernah dihubungkan akan <strong>tersimpan di browser</strong>.</li>
                    <li>Sistem akan otomatis menyambungkan kembali (Auto-Reconnect) saat halaman POS dimuat.</li>
                    <li>Jika <strong>Direct Auto-Print</strong> aktif, setiap transaksi selesai struk langsung keluar tanpa klik apa-apa!</li>
                </ol>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-2 shrink-0">
            <button type="button" onclick="testPrintBluetooth()" id="btnBtTestPrint" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition cursor-pointer flex items-center gap-1.5 hidden">
                <i data-lucide="play" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Tes Cetak</span>
            </button>
            <div class="flex items-center gap-2 ml-auto">
                <button type="button" onclick="disconnectBluetoothPrinter()" id="btnBtDisconnect" class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition cursor-pointer hidden">
                    Putuskan
                </button>
                <button type="button" onclick="connectBluetoothPrinter()" id="btnBtConnect" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/25 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="bluetooth" class="w-4 h-4"></i>
                    <span>Cari & Hubungkan Printer</span>
                </button>
            </div>
        </div>
    </div>
</div>
