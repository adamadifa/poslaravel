<!-- Spotlight / Command Palette Modal (macOS Style Navigation) -->
<div id="commandPaletteBackdrop" class="fixed inset-0 z-[99999] bg-slate-950/60 backdrop-blur-md hidden transition-opacity duration-200 flex items-start justify-center pt-14 sm:pt-20 px-4 sm:px-6">
    
    <!-- Command Palette Container -->
    <div id="commandPaletteContainer" class="w-full max-w-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl rounded-2xl shadow-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden ring-1 ring-black/5 flex flex-col max-h-[82vh] sm:max-h-[78vh] transition-all">
        
        <!-- Search Input Header -->
        <div class="px-5 py-4 border-b border-slate-200/70 dark:border-slate-800 flex items-center gap-3.5 bg-slate-50/70 dark:bg-slate-800/40">
            <div class="w-9 h-9 rounded-xl bg-brand-500/10 dark:bg-brand-500/20 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                <i data-lucide="search" class="w-5 h-5"></i>
            </div>
            
            <input 
                type="text" 
                id="commandPaletteInput" 
                placeholder="Ketik nama menu, laporan, atau aksi cepat..." 
                autocomplete="off"
                spellcheck="false"
                class="flex-1 bg-transparent border-0 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm sm:text-base font-semibold focus:outline-none focus:ring-0 p-0"
            >
            
            <div class="flex items-center gap-2">
                <button type="button" id="commandPaletteClearBtn" class="hidden text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-200/50 dark:hover:bg-slate-700/50 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
                <kbd class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2 py-1 rounded-lg shadow-2xs">
                    ESC
                </kbd>
            </div>
        </div>

        <!-- Filter Category Pills (Smooth Scrollable Segment) -->
        <div class="px-4 sm:px-5 py-2.5 bg-slate-100/40 dark:bg-slate-900/60 border-b border-slate-200/60 dark:border-slate-800 flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <button type="button" data-palette-filter="all" class="palette-filter-chip active px-3 py-1 rounded-full text-xs font-bold bg-brand-500 text-white shadow-2xs transition-all shrink-0 cursor-pointer">
                Semua Menu
            </button>
            <button type="button" data-palette-filter="pos" class="palette-filter-chip px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition-all shrink-0 cursor-pointer">
                Kasir & POS
            </button>
            <button type="button" data-palette-filter="consignment" class="palette-filter-chip px-3 py-1 rounded-full text-xs font-semibold text-purple-700 dark:text-purple-400 hover:bg-purple-100/70 dark:hover:bg-purple-950/40 transition-all shrink-0 cursor-pointer">
                Konsinyasi
            </button>
            <button type="button" data-palette-filter="inventory" class="palette-filter-chip px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition-all shrink-0 cursor-pointer">
                Stok & Barang
            </button>
            <button type="button" data-palette-filter="finance" class="palette-filter-chip px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition-all shrink-0 cursor-pointer">
                Keuangan & Kas
            </button>
            <button type="button" data-palette-filter="reports" class="palette-filter-chip px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition-all shrink-0 cursor-pointer">
                Laporan & Rekap
            </button>
            <button type="button" data-palette-filter="master" class="palette-filter-chip px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition-all shrink-0 cursor-pointer">
                Master Data
            </button>
        </div>

        <!-- Results List Area -->
        <div id="commandPaletteResults" class="p-3 sm:p-4 overflow-y-auto space-y-4 flex-1 [scrollbar-width:thin]">
            
            <!-- GROUP: Aksi Cepat / Quick Actions -->
            <div class="palette-group space-y-1" data-group="quick">
                <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center justify-between">
                    <span>⚡ Aksi Cepat & Shortcut</span>
                </div>
                
                @can('sales.pos')
                <a href="{{ route('pos.index') }}" data-category="pos" data-keywords="kasir pos jual transaksi checkout f12 penjualan umum baru bayar" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Buka Kasir POS (F12)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Mulai transaksi kasir dan checkout penjualan langsung</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">F12</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('products.view')
                <a href="{{ route('products.index') }}" data-category="inventory" data-keywords="produk master barang baru barcode sku tambah stok item item" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="package" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Kelola Master Produk</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Daftar produk, barcode scanner, multi-satuan & harga</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Produk</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('consignments.receive')
                <a href="{{ route('consignments.receipts.index') }}" data-category="consignment" data-keywords="konsinyasi titip jual penerimaan barang titipan supplier penitip umkm cnr" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="package-plus" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Penerimaan Konsinyasi (Titip Jual)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Terima stok titipan supplier tanpa hutang awal</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300">Titip Jual</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('consignments.settle')
                <a href="{{ route('consignments.settlements.index') }}" data-category="consignment" data-keywords="settlement konsinyasi bagi hasil rekonsiliasi setor kas supplier lunas bayar cns" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="hand-coins" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Settlement & Bagi Hasil Konsinyasi</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Hitung otomatis omzet terjual & pelunasan hak supplier</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300">Settlement</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan
            </div>

            <!-- GROUP: Transaksi & Operasional Kasir -->
            <div class="palette-group space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60" data-group="pos">
                <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    <span>🛒 Transaksi & Operasional Penjualan</span>
                </div>

                @can('sales.view')
                <a href="{{ route('sales.index') }}" data-category="pos" data-keywords="riwayat penjualan transaksi faktur nota invoice struk kasir riwayat" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Riwayat Transaksi Penjualan</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Cari invoice, cetak ulang struk, dan status pembayaran</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Penjualan</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('sales.return')
                <a href="{{ route('sale-returns.index') }}" data-category="pos" data-keywords="retur penjualan pembeli tukar barang kembalian nota kredit" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Retur Penjualan</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pengembalian barang dari pelanggan</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Retur POS</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('tables.view')
                <a href="{{ route('tables.index') }}" data-category="pos" data-keywords="meja resto fnb denah dining table resto cafe dine in takeaway" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Denah & Meja Resto (F&B)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Monitoring okupansi meja, pesanan aktif, & reservasi</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">F&B</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('kitchen.view')
                <a href="{{ route('kitchen.index') }}" data-category="pos" data-keywords="kds kitchen layar dapur masak koki pesanan resto" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="flame" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Layar Dapur / Kitchen Display (KDS)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Antrian tiket pesanan masak dapur secara real-time</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">KDS</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('service_queue.view')
                <a href="{{ route('service-queue.index') }}" data-category="pos" data-keywords="antrian servis jasa service bengkel salon klinik treatment" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Monitoring Antrian Servis & Jasa</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Status pengerjaan servis mekanik/staf & antrian</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">Jasa</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('finance.accounts')
                <a href="{{ route('ppob-products.index') }}" data-category="pos" data-keywords="ppob pulsa data token listrik pln digiflazz paket internet" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="smartphone" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Katalog Produk PPOB & Pulsa</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Server deposit pulsa, paket data, & token PLN</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">PPOB</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan
            </div>

            <!-- GROUP: Konsinyasi (Titip Jual) -->
            <div class="palette-group space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60" data-group="consignment">
                <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                    <span>📦 Konsinyasi & Titip Jual (UMKM)</span>
                </div>

                @can('consignments.receive')
                <a href="{{ route('consignments.receipts.index') }}" data-category="consignment" data-keywords="penerimaan konsinyasi titip jual barang masuk supplier cnr" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="package-plus" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Daftar Penerimaan Konsinyasi (CNR)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Riwayat bukti tanda terima barang titipan</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">CNR</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('consignments.settle')
                <a href="{{ route('consignments.settlements.index') }}" data-category="consignment" data-keywords="settlement konsinyasi rekonsiliasi bagi hasil pelunasan cns" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="hand-coins" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Settlement & Bagi Hasil Konsinyasi</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Faktur rekonsiliasi dan bukti bayar ke supplier</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">CNS</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('consignments.return')
                <a href="{{ route('consignments.returns.index') }}" data-category="consignment" data-keywords="retur konsinyasi titip jual sisa barang cnrt kembalikan supplier" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="undo-2" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Retur Barang Konsinyasi</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pengembalian barang titipan tidak laku/rusak</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Retur Titipan</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('consignments.reports')
                <a href="{{ route('consignments.reports.index') }}" data-category="consignment" data-keywords="laporan konsinyasi analitik omzet laba komisi sisa stok supplier" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Laporan & Analitik Konsinyasi</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Analisis omzet bagi hasil, margin komisi, & stok titipan</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Laporan</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan
            </div>

            <!-- GROUP: Inventori & Pembelian -->
            <div class="palette-group space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60" data-group="inventory">
                <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    <span>📦 Inventori, Stok & Pembelian</span>
                </div>

                @can('purchases.view')
                <a href="{{ route('purchase-orders.index') }}" data-category="inventory" data-keywords="po purchase order pesanan pembelian supplier order" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Purchase Order (PO)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pesanan pembelian barang ke pemasok</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">PO</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('purchases.receive')
                <a href="{{ route('purchase-receipts.index') }}" data-category="inventory" data-keywords="grn goods receipt penerimaan barang masuk faktur beli supplier" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="package-check" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Penerimaan Barang (GRN)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Terima barang masuk dan catat hutang pembelian</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">GRN</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('stocks.view')
                <a href="{{ route('stocks.index') }}" data-category="inventory" data-keywords="kartu stok fifo mutasi pergerakan masuk keluar batch expiry" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="boxes" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Kartu Stok (FIFO & Mutasi)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Riwayat lengkap mutasi stok fisik di gudang</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Stok</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('stocks.opname')
                <a href="{{ route('stock-opnames.index') }}" data-category="inventory" data-keywords="opname stok fisik hitung selisih inventaris gudang" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Stok Opname (Penghitungan Fisik)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Verifikasi selisih stok sistem vs fisik</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Opname</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('stocks.transfer')
                <a href="{{ route('stock-transfers.index') }}" data-category="inventory" data-keywords="transfer stok mutasi cabang antar gudang kirim terima" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Transfer Antar Gudang</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pemindahan stok antar gudang atau cabang</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Transfer</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('stocks.view')
                <a href="{{ route('stocks.alerts') }}" data-category="inventory" data-keywords="peringatan stok tipis habis minimum alert restock" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Peringatan Stok Tipis / Habis</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Daftar item yang mencapai batas minimum stok</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">Alert</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan
            </div>

            <!-- GROUP: Keuangan & Kas -->
            <div class="palette-group space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60" data-group="finance">
                <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    <span>💰 Keuangan, Kas & Pembukuan</span>
                </div>

                @can('finance.accounts')
                <a href="{{ route('accounts.index') }}" data-category="finance" data-keywords="kas bank rekening saldo akun keuangan laci bca mandiri bri" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Akun Kas & Bank</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Manajemen saldo kas fisik, rekening bank, & saldo digital</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Kas/Bank</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('finance.payable')
                <a href="{{ route('payables.index') }}" data-category="finance" data-keywords="hutang usaha ap payables bayar supplier jatuh tempo tagihan" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="receipt-text" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Hutang Usaha / Pembelian (AP)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Tagihan belum lunas dari supplier pemasok</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">AP</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('finance.receivable')
                <a href="{{ route('receivables.index') }}" data-category="finance" data-keywords="piutang ar receivables tagihan pelanggan tempo kredit" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="coins" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Piutang Penjualan (AR)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Penagihan kredit pelanggan & member</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">AR</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('finance.cashflow')
                <a href="{{ route('cash-flows.index') }}" data-category="finance" data-keywords="arus kas cash flow pemasukan pengeluaran operasional biaya listrik gaji" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="arrow-down-up" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Arus Kas Masuk & Keluar (Cash Flow)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pencatatan beban operasional dan pemasukan lain</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Cash Flow</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('finance.transfer')
                <a href="{{ route('account-transfers.index') }}" data-category="finance" data-keywords="transfer kas bank setor tunai tarik tunai antar rekening" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Transfer Antar Kas & Bank</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pindah dana antar akun kas atau mutasi bank</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Transfer Kas</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan
            </div>

            <!-- GROUP: Laporan & Rekapitulasi -->
            <div class="palette-group space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60" data-group="reports">
                <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    <span>📊 Laporan, Analitik & Rekapitulasi</span>
                </div>

                @can('reports.sales')
                <a href="{{ route('reports.sales') }}" data-category="reports" data-keywords="laporan penjualan omzet transaksi kasir harian bulanan" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Laporan Penjualan & Omzet</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Rekap transaksi, metode bayar, & diskon</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Laporan</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('reports.finance')
                <a href="{{ route('reports.profit-loss') }}" data-category="reports" data-keywords="laporan laba rugi profit loss pnl omzet hpp margin keuntungan bersih kotor" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Laporan Laba & Rugi (P&L)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Analisis omzet kotor, HPP pokok, & laba bersih</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">P&L</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('reports.shifts')
                <a href="{{ route('reports.cashier-shifts') }}" data-category="reports" data-keywords="laporan shift kasir laci kas uang modal setoran selisih" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Laporan Shift & Kas Kasir</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Rekonsiliasi uang fisik laci dan penutupan shift kasir</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Shift</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('reports.purchases')
                <a href="{{ route('reports.purchases') }}" data-category="reports" data-keywords="laporan pembelian supplier belanja stok pengadaan" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Laporan Pembelian Barang</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Rekap pengadaan stok dari pemasok</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Pembelian</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan
            </div>

            <!-- GROUP: Master Data & Pengaturan -->
            <div class="palette-group space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60" data-group="master">
                <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    <span>⚙️ Master Data & Pengaturan Toko</span>
                </div>

                @can('customers.view')
                <a href="{{ route('customers.index') }}" data-category="master" data-keywords="pelanggan member customer kontak telepon poin diskon" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Master Pelanggan & Member</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Basis data pelanggan, grup harga, & poin reward</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Pelanggan</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('suppliers.view')
                <a href="{{ route('suppliers.index') }}" data-category="master" data-keywords="supplier pemasok vendor pabrik distributor kontak" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="truck" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Master Pemasok (Supplier)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Daftar distributor & mitra pemasok barang</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Supplier</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('categories.view')
                <a href="{{ route('categories.index') }}" data-category="master" data-keywords="kategori kategori produk kelompok grup barang" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="folder-tree" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Kategori Produk</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pengelompokan barang dan hierarki kategori</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Kategori</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('units.view')
                <a href="{{ route('units.index') }}" data-category="master" data-keywords="satuan unit pcs pak dus renceng kg liter" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="scale" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Satuan Produk (Units)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Konfigurasi satuan dasar dan konversi satuan</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Satuan</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('warehouses.view')
                <a href="{{ route('warehouses.index') }}" data-category="master" data-keywords="gudang cabang lokasi outlet warehouse" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="warehouse" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Gudang & Cabang Toko</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Manajemen multi-gudang dan lokasi penyimpanan</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Gudang</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('users.view')
                <a href="{{ route('users.index') }}" data-category="master" data-keywords="staf pengguna user kasir admin akun login password" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Staf & Pengguna Sistem</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Manajemen akun kasir, supervisor, dan admin</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">User</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('roles.manage')
                <a href="{{ route('roles.index') }}" data-category="master" data-keywords="hak akses role permission peran otorisasi kasir admin" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Hak Akses & Peran (Roles)</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Pengaturan matriks izin dan otoritas pengguna</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Roles</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('settings.manage')
                <a href="{{ route('settings.index') }}" data-category="master" data-keywords="pengaturan setting toko nama logo printer struk profil aplikasi" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Pengaturan Toko & Bisnis</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Identitas usaha, printer kasir, pajak, & opsi sistem</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Setting</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan

                @can('audit.view')
                <a href="{{ route('audit-trails.index') }}" data-category="master" data-keywords="audit trail log riwayat aktivitas jejak pengguna ubah data" class="palette-item group flex items-center justify-between px-3 py-2.5 rounded-xl border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="item-icon-box w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 transition-colors">
                            <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="item-title font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate">Log Aktivitas & Audit Trail</div>
                            <div class="item-desc text-[11px] text-slate-400 dark:text-slate-500 truncate">Rekam jejak setiap perubahan data sistem</div>
                        </div>
                    </div>
                    <div class="item-action-badge flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[10px] font-semibold text-slate-400">Audit Log</span>
                        <i data-lucide="corner-down-left" class="w-3.5 h-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
                @endcan
            </div>

            <!-- Empty State -->
            <div id="commandPaletteEmptyState" class="hidden py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="search-x" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">Menu tidak ditemukan</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Coba gunakan kata kunci lain seperti <span class="font-semibold text-slate-600 dark:text-slate-300">"pos", "konsinyasi", "laporan", "stok", "kas"</span>.</p>
            </div>
        </div>

        <!-- Footer / Keyboard Shortcuts Hint (Mac Spotlight Style) -->
        <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/90 border-t border-slate-200/70 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-slate-600 dark:text-slate-300 text-[10px] shadow-2xs">↑</kbd>
                    <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-slate-600 dark:text-slate-300 text-[10px] shadow-2xs">↓</kbd>
                    <span>Navigasi</span>
                </div>
                <div class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-slate-600 dark:text-slate-300 text-[10px] shadow-2xs">↵</kbd>
                    <span>Buka</span>
                </div>
                <div class="flex items-center gap-1 hidden sm:flex">
                    <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold text-slate-600 dark:text-slate-300 text-[10px] shadow-2xs">ESC</kbd>
                    <span>Tutup</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 font-medium text-slate-500 dark:text-slate-400 text-[10px] sm:text-[11px]">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span>Spotlight Menu</span>
            </div>
        </div>
    </div>
</div>

<!-- Command Palette Interactive JavaScript -->
<script>
    (function () {
        const backdrop = document.getElementById('commandPaletteBackdrop');
        const container = document.getElementById('commandPaletteContainer');
        const input = document.getElementById('commandPaletteInput');
        const clearBtn = document.getElementById('commandPaletteClearBtn');
        const results = document.getElementById('commandPaletteResults');
        const emptyState = document.getElementById('commandPaletteEmptyState');
        const filterChips = document.querySelectorAll('.palette-filter-chip');
        const navSearchTrigger = document.getElementById('navSearchTrigger');
        const navSearchMobileTrigger = document.getElementById('navSearchMobileTrigger');

        let activeFilter = 'all';
        let selectedIndex = -1;

        // Open Palette
        function openPalette() {
            if (!backdrop) return;
            backdrop.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (input) {
                input.value = '';
                input.focus();
            }
            activeFilter = 'all';
            updateFilterChips();
            filterItems();
            lucide.createIcons();
        }

        // Close Palette
        function closePalette() {
            if (!backdrop) return;
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
            selectedIndex = -1;
            highlightSelectedItem();
        }

        // Event listeners for navbar triggers
        if (navSearchTrigger) {
            navSearchTrigger.addEventListener('click', function (e) {
                e.preventDefault();
                openPalette();
            });
        }
        if (navSearchMobileTrigger) {
            navSearchMobileTrigger.addEventListener('click', function (e) {
                e.preventDefault();
                openPalette();
            });
        }

        // Backdrop click to close
        if (backdrop) {
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) {
                    closePalette();
                }
            });
        }

        // Global Keyboard Shortcut: Cmd+K / Ctrl+K, / key
        document.addEventListener('keydown', function (e) {
            const isTypingInInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) || document.activeElement.isContentEditable;
            
            // Cmd + K or Ctrl + K
            if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                if (backdrop.classList.contains('hidden')) {
                    openPalette();
                } else {
                    closePalette();
                }
                return;
            }

            // Quick shortcut: Pressing "/" outside inputs
            if (e.key === '/' && !isTypingInInput && backdrop.classList.contains('hidden')) {
                e.preventDefault();
                openPalette();
                return;
            }

            // If Palette is open:
            if (backdrop && !backdrop.classList.contains('hidden')) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closePalette();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    navigateItems(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    navigateItems(-1);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    triggerSelectedItem();
                }
            }
        });

        // Filter chips click
        filterChips.forEach(chip => {
            chip.addEventListener('click', function () {
                activeFilter = this.getAttribute('data-palette-filter');
                updateFilterChips();
                filterItems();
                this.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
            });
        });

        function updateFilterChips() {
            filterChips.forEach(chip => {
                const filter = chip.getAttribute('data-palette-filter');
                if (filter === activeFilter) {
                    chip.className = 'palette-filter-chip active px-3 py-1 rounded-full text-xs font-bold bg-brand-500 text-white shadow-2xs transition-all shrink-0 cursor-pointer';
                } else {
                    chip.className = 'palette-filter-chip px-3 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition-all shrink-0 cursor-pointer';
                }
            });
        }

        // Live Filtering
        if (input) {
            input.addEventListener('input', function () {
                if (clearBtn) {
                    if (this.value.trim().length > 0) {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                }
                filterItems();
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                clearBtn.classList.add('hidden');
                input.focus();
                filterItems();
            });
        }

        function filterItems() {
            const query = input.value.toLowerCase().trim();
            const items = results.querySelectorAll('.palette-item');
            const groups = results.querySelectorAll('.palette-group');
            let visibleCount = 0;

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                const keywords = (item.getAttribute('data-keywords') || '').toLowerCase();
                const category = item.getAttribute('data-category') || 'all';

                const matchesQuery = !query || text.includes(query) || keywords.includes(query);
                const matchesFilter = activeFilter === 'all' || category === activeFilter;

                if (matchesQuery && matchesFilter) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            // Hide groups if all children are hidden
            groups.forEach(group => {
                const visibleInGroup = group.querySelectorAll('.palette-item:not(.hidden)').length;
                if (visibleInGroup === 0) {
                    group.classList.add('hidden');
                } else {
                    group.classList.remove('hidden');
                }
            });

            if (emptyState) {
                if (visibleCount === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }

            // Reset keyboard selection to first item
            selectedIndex = visibleCount > 0 ? 0 : -1;
            highlightSelectedItem();
        }

        function getVisibleItems() {
            return Array.from(results.querySelectorAll('.palette-item:not(.hidden)'));
        }

        function navigateItems(direction) {
            const visibleItems = getVisibleItems();
            if (visibleItems.length === 0) return;

            selectedIndex += direction;
            if (selectedIndex < 0) {
                selectedIndex = visibleItems.length - 1;
            } else if (selectedIndex >= visibleItems.length) {
                selectedIndex = 0;
            }

            highlightSelectedItem();
        }

        function highlightSelectedItem() {
            const visibleItems = getVisibleItems();
            visibleItems.forEach((item, idx) => {
                const iconBox = item.querySelector('.item-icon-box');
                const title = item.querySelector('.item-title');
                const desc = item.querySelector('.item-desc');
                const returnArrow = item.querySelector('i[data-lucide="corner-down-left"]');

                if (idx === selectedIndex) {
                    // Sleek Mac Spotlight selected state
                    item.classList.add('bg-brand-50/90', 'dark:bg-brand-500/15', 'border-brand-200/90', 'dark:border-brand-500/30', 'shadow-2xs');
                    item.classList.remove('border-transparent');

                    if (iconBox) {
                        iconBox.classList.add('bg-brand-500', 'text-white', 'shadow-xs');
                    }
                    if (title) {
                        title.classList.add('text-brand-900', 'dark:text-brand-100');
                        title.classList.remove('text-slate-800', 'dark:text-slate-100');
                    }
                    if (desc) {
                        desc.classList.add('text-brand-600/80', 'dark:text-brand-300/80');
                        desc.classList.remove('text-slate-400', 'dark:text-slate-500');
                    }
                    if (returnArrow) {
                        returnArrow.classList.add('opacity-100', 'text-brand-500', 'dark:text-brand-400');
                        returnArrow.classList.remove('opacity-0', 'text-slate-400');
                    }

                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.classList.remove('bg-brand-50/90', 'dark:bg-brand-500/15', 'border-brand-200/90', 'dark:border-brand-500/30', 'shadow-2xs');
                    item.classList.add('border-transparent');

                    if (iconBox) {
                        iconBox.classList.remove('bg-brand-500', 'text-white', 'shadow-xs');
                    }
                    if (title) {
                        title.classList.remove('text-brand-900', 'dark:text-brand-100');
                        title.classList.add('text-slate-800', 'dark:text-slate-100');
                    }
                    if (desc) {
                        desc.classList.remove('text-brand-600/80', 'dark:text-brand-300/80');
                        desc.classList.add('text-slate-400', 'dark:text-slate-500');
                    }
                    if (returnArrow) {
                        returnArrow.classList.remove('opacity-100', 'text-brand-500', 'dark:text-brand-400');
                        returnArrow.classList.add('opacity-0', 'text-slate-400');
                    }
                }
            });
        }

        function triggerSelectedItem() {
            const visibleItems = getVisibleItems();
            if (selectedIndex >= 0 && selectedIndex < visibleItems.length) {
                const target = visibleItems[selectedIndex];
                if (target && target.href) {
                    window.location.href = target.href;
                }
            }
        }
    })();
</script>
