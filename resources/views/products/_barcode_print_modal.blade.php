<!-- MODAL CETAK LABEL BARCODE (UNIVERSAL & CUSTOM DIMENSIONS) -->
<div id="barcodePrintModal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto animate-fadeIn">
    <div class="relative w-full max-w-6xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden my-auto flex flex-col max-h-[92vh]">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-brand-500 to-amber-500 text-white flex items-center justify-between shadow-xs shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center shadow-xs">
                    <i data-lucide="printer" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight">Cetak Label Barcode Produk</h3>
                    <p class="text-xs text-white/90">Kustom ukuran kertas stiker label & cetak langsung ke printer</p>
                </div>
            </div>
            <button type="button" onclick="closeBarcodeModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Body (Two-Column Layout: Controls & Queue on Left, Live Preview on Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 flex-1 overflow-hidden">
            
            <!-- LEFT PANEL: SETTINGS & QUEUE (5 Cols) -->
            <div class="lg:col-span-5 border-r border-slate-200 dark:border-slate-800 p-4 sm:p-5 overflow-y-auto space-y-4 bg-slate-50/50 dark:bg-slate-900/50">
                
                <!-- 1. PENGATURAN KERTAS & UKURAN (PRESET + CUSTOM) -->
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sliders" class="w-4 h-4 text-brand-500"></i>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Format & Ukuran Kertas</span>
                        </div>
                        <span class="text-[10px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/50 px-2 py-0.5 rounded-md">
                            Satuan: Milimeter (mm)
                        </span>
                    </div>

                    <!-- Preset Selector -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Preset Ukuran Stiker
                        </label>
                        <select id="barcode_preset_select" onchange="handleBarcodePresetChange(this.value)" class="w-full text-xs font-semibold rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white py-2 px-3 focus:ring-brand-500 focus:border-brand-500 cursor-pointer">
                            <option value="40x30">40 x 30 mm (Thermal Roll 1 Kolom - Populer)</option>
                            <option value="50x30">50 x 30 mm (Thermal Roll 1 Kolom - Sedang)</option>
                            <option value="33x15_3col">33 x 15 mm (Thermal Roll 3 Kolom - Aksesoris/Kecil)</option>
                            <option value="100x50">100 x 50 mm (Thermal Roll 1 Kolom - Besar)</option>
                            <option value="a4_108">Kertas A4 Tom & Jerry 108 (5 Kolom x 8 Baris)</option>
                            <option value="a4_103">Kertas A4 Tom & Jerry 103 (3 Kolom x 4 Baris)</option>
                            <option value="custom">-- Kustom Ukuran Sendiri (Bebas) --</option>
                        </select>
                    </div>

                    <!-- Dimension Inputs (Width, Height, Columns, Gap) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Lebar (mm)</label>
                            <input type="number" id="barcode_param_width" value="40" min="15" max="300" oninput="renderBarcodePreview()" class="w-full text-xs font-bold rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white py-1.5 px-2 focus:ring-brand-500 focus:border-brand-500 text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Tinggi (mm)</label>
                            <input type="number" id="barcode_param_height" value="30" min="10" max="300" oninput="renderBarcodePreview()" class="w-full text-xs font-bold rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white py-1.5 px-2 focus:ring-brand-500 focus:border-brand-500 text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Kolom</label>
                            <input type="number" id="barcode_param_cols" value="1" min="1" max="10" oninput="renderBarcodePreview()" class="w-full text-xs font-bold rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white py-1.5 px-2 focus:ring-brand-500 focus:border-brand-500 text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Celah/Gap (mm)</label>
                            <input type="number" id="barcode_param_gap" value="2" min="0" max="50" oninput="renderBarcodePreview()" class="w-full text-xs font-bold rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white py-1.5 px-2 focus:ring-brand-500 focus:border-brand-500 text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                    </div>
                </div>

                <!-- 2. KONTEN & DESAIN LABEL -->
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
                    <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                        <i data-lucide="layout-template" class="w-4 h-4 text-brand-500"></i>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Informasi Pada Label</span>
                    </div>

                    <!-- Store Name Header input -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Nama Toko / Header</label>
                            <label class="inline-flex items-center gap-1 cursor-pointer">
                                <input type="checkbox" id="barcode_toggle_store" checked onchange="renderBarcodePreview()" class="rounded text-brand-500 focus:ring-brand-400 w-3.5 h-3.5">
                                <span class="text-[10px] text-slate-500">Tampilkan</span>
                            </label>
                        </div>
                        <input type="text" id="barcode_store_name" value="{{ $storeName ?? 'Toko Retail' }}" oninput="renderBarcodePreview()" placeholder="Nama Toko Anda" class="w-full text-xs font-semibold rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white py-1.5 px-3 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <!-- Element Checkboxes -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700 cursor-pointer">
                            <input type="checkbox" id="barcode_toggle_name" checked onchange="renderBarcodePreview()" class="rounded text-brand-500 focus:ring-brand-400">
                            <span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]">Nama Produk</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700 cursor-pointer">
                            <input type="checkbox" id="barcode_toggle_price" checked onchange="renderBarcodePreview()" class="rounded text-brand-500 focus:ring-brand-400">
                            <span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]">Harga Jual</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700 cursor-pointer">
                            <input type="checkbox" id="barcode_toggle_unit" checked onchange="renderBarcodePreview()" class="rounded text-brand-500 focus:ring-brand-400">
                            <span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]">Satuan Jual</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700 cursor-pointer">
                            <input type="checkbox" id="barcode_toggle_code_text" checked onchange="renderBarcodePreview()" class="rounded text-brand-500 focus:ring-brand-400">
                            <span class="font-bold text-slate-700 dark:text-slate-300 text-[11px]">Nomor Barcode</span>
                        </label>
                    </div>

                    <!-- Font size & Barcode Scale -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Ukuran Teks</label>
                            <select id="barcode_font_size" onchange="renderBarcodePreview()" class="w-full text-xs font-semibold rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white py-1.5 px-2 cursor-pointer">
                                <option value="sm">Kecil (Kompak)</option>
                                <option value="md" selected>Sedang (Standar)</option>
                                <option value="lg">Besar (Jelas)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Tinggi Garis Barcode</label>
                            <select id="barcode_bar_height" onchange="renderBarcodePreview()" class="w-full text-xs font-semibold rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white py-1.5 px-2 cursor-pointer">
                                <option value="25">Ramping (25px)</option>
                                <option value="35" selected>Standar (35px)</option>
                                <option value="50">Tinggi (50px)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. DAFTAR ANTREAN PRODUK UNTUK DICETAK -->
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="list-plus" class="w-4 h-4 text-brand-500"></i>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Antrean Produk</span>
                            <span id="barcode_queue_badge" class="px-2 py-0.2 rounded-full text-[10px] font-extrabold bg-brand-100 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300">
                                0 Produk
                            </span>
                        </div>
                        <button type="button" onclick="clearBarcodeQueue()" class="text-[11px] font-bold text-rose-500 hover:text-rose-600 transition">
                            Kosongkan
                        </button>
                    </div>

                    <!-- Search to add product to queue -->
                    <div>
                        <select id="barcode_product_search_select" class="w-full">
                            <option value="">Cari & tambah produk lain ke antrean...</option>
                        </select>
                    </div>

                    <!-- Queue List Container -->
                    <div id="barcode_queue_container" class="space-y-2 max-h-56 overflow-y-auto pr-1 divide-y divide-slate-100 dark:divide-slate-800">
                        <!-- Dynamic items -->
                    </div>

                    <!-- Quick Bulk Qty Modifier -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <span class="text-[11px] font-bold text-slate-500">Set Qty Semua:</span>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="setAllBarcodeQty(1)" class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-[11px] font-bold text-slate-700 dark:text-slate-300">1</button>
                            <button type="button" onclick="setAllBarcodeQty(5)" class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-[11px] font-bold text-slate-700 dark:text-slate-300">5</button>
                            <button type="button" onclick="setAllBarcodeQty(10)" class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-[11px] font-bold text-slate-700 dark:text-slate-300">10</button>
                            <button type="button" onclick="setAllBarcodeQty(20)" class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-[11px] font-bold text-slate-700 dark:text-slate-300">20</button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL: LIVE WYSIWYG SHEET PREVIEW (7 Cols) -->
            <div class="lg:col-span-7 p-4 sm:p-5 flex flex-col bg-slate-100/70 dark:bg-slate-950/70 overflow-hidden">
                
                <!-- Preview Header & Zoom Bar -->
                <div class="flex items-center justify-between pb-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Live Preview Kertas Stiker</span>
                        <span id="barcode_total_labels_badge" class="px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 font-extrabold text-[11px] shadow-2xs border border-slate-200/80 dark:border-slate-700">
                            Total: 0 Label
                        </span>
                    </div>

                    <!-- Zoom Scale Slider -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-semibold text-slate-400">Zoom:</span>
                        <div class="flex items-center bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-0.5 text-[10px] font-bold">
                            <button type="button" id="btn_zoom_75" onclick="setPreviewZoom(75)" class="px-1.5 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700">75%</button>
                            <button type="button" id="btn_zoom_100" onclick="setPreviewZoom(100)" class="px-1.5 py-0.5 rounded bg-brand-500 text-white shadow-2xs">100%</button>
                            <button type="button" id="btn_zoom_125" onclick="setPreviewZoom(125)" class="px-1.5 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700">125%</button>
                        </div>
                    </div>
                </div>

                <!-- Preview Canvas Container (Scrollable Sheet Area) -->
                <div class="flex-1 overflow-auto bg-slate-300/40 dark:bg-slate-900/90 rounded-xl border border-slate-300 dark:border-slate-800 p-4 sm:p-6 flex items-start justify-center min-h-[350px]">
                    
                    <div id="barcode_preview_viewport" class="transition-transform origin-top flex flex-col items-center">
                        <!-- Dynamic Label Sheet Container (Rendered by JS with real mm units) -->
                        <div id="barcode_render_sheet" class="flex flex-wrap items-start justify-start bg-white text-black shadow-md transition-all">
                            <!-- Label items rendered dynamically -->
                        </div>
                    </div>

                </div>

                <!-- Action Footer Inside Modal -->
                <div class="pt-4 flex items-center justify-between shrink-0">
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-brand-500"></i>
                        <span>Ukuran kertas pada dialog printer akan otomatis menyesuaikan kustom mm Anda.</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" onclick="closeBarcodeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition">
                            Tutup
                        </button>
                        <button type="button" onclick="executeBarcodePrint()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-extrabold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            <span>Cetak Label Sekarang</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<!-- HIDDEN PRINT IFRAME FOR 100% ISOLATED DIRECT PRINTING -->
<iframe id="barcode_print_frame" class="hidden w-0 h-0 border-0 absolute -left-[9999px] -top-[9999px]"></iframe>
