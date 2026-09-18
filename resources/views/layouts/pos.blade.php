<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kasir / POS' }} - POS Retail Pro</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Compiled Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Theme Initializer -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            user-select: none;
        }
        .font-mono-num {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .dark ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        /* Dedicated Thermal Printer Print Styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body * {
                visibility: hidden !important;
            }
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            #receiptModal,
            #receiptModal * {
                visibility: visible !important;
            }
            #receiptModal {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                box-shadow: none !important;
                display: block !important;
            }
            #receiptModal > div {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
            }
            #receiptModal .border-b,
            #receiptModal .border-t,
            #receiptModal button {
                display: none !important;
            }
            #receiptModal .bg-slate-100 {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            #thermal_receipt_paper {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: 280px !important;
                font-family: 'JetBrains Mono', monospace, Courier !important;
                font-size: 11px !important;
                color: #000000 !important;
                line-height: 1.3 !important;
                background: #ffffff !important;
                display: block !important;
            }
            #thermal_receipt_paper img,
            #receiptModal img {
                display: block !important;
                visibility: visible !important;
                margin: 0 auto 6px auto !important;
                max-height: 56px !important;
                max-width: 140px !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain !important;
                -webkit-filter: grayscale(100%) contrast(150%) !important;
                filter: grayscale(100%) contrast(150%) !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                margin: 0;
                size: auto;
            }
        }

        /* Premium SweetAlert2 Global Theme Overrides */
        .swal2-container.swal2-backdrop-show {
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            background: rgba(15, 23, 42, 0.6) !important;
        }
        /* Completely clear backdrop when showing toast notifications */
        .swal2-toast-shown .swal2-container,
        .swal2-container:has(.swal2-toast),
        .swal2-container.swal2-top-end:not(:has(.swal2-modal)) {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            pointer-events: none !important;
        }
        .swal2-toast {
            pointer-events: auto !important;
        }
        .swal2-popup:not(.swal2-toast) {
            border-radius: 1.75rem !important;
            padding: 2rem 1.75rem !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background: #ffffff !important;
            max-width: 24rem !important;
        }
        .dark .swal2-popup {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .swal2-title {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em !important;
            margin-bottom: 0.5rem !important;
            padding: 0 !important;
        }
        .dark .swal2-title {
            color: #f8fafc !important;
        }
        .swal2-html-container {
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            color: #64748b !important;
            line-height: 1.5 !important;
            margin: 0 0 1.5rem 0 !important;
        }
        .dark .swal2-html-container {
            color: #94a3b8 !important;
        }
        /* Custom SweetAlert Icons */
        .swal2-icon {
            transform: scale(0.9) !important;
            margin: 0.5rem auto 1.25rem !important;
            border-width: 3px !important;
        }
        .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
            background: #fffbeb !important;
        }
        .swal2-icon.swal2-error {
            border-color: #f43f5e !important;
            color: #f43f5e !important;
            background: #fff1f2 !important;
        }
        .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #10b981 !important;
            background: #ecfdf5 !important;
        }
        .swal2-icon.swal2-info {
            border-color: #3b82f6 !important;
            color: #3b82f6 !important;
            background: #eff6ff !important;
        }
        /* Modern Buttons */
        .swal2-actions {
            margin-top: 0.5rem !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }
        .swal2-styled {
            border-radius: 0.875rem !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            padding: 0.65rem 1.5rem !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .swal2-confirm {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px -2px rgba(249, 115, 22, 0.4) !important;
            flex: 1 !important;
            border: none !important;
        }
        .swal2-confirm:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 18px -2px rgba(249, 115, 22, 0.5) !important;
        }
        .swal2-cancel {
            background: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
            flex: 1 !important;
        }
        .swal2-cancel:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
        }
        /* Toast Notifications */
        .swal2-toast.toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), 0 8px 10px -6px rgba(16, 185, 129, 0.2) !important;
        }
        .swal2-toast.toast-error {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(244, 63, 94, 0.4), 0 8px 10px -6px rgba(244, 63, 94, 0.2) !important;
        }
        .swal2-toast.toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4), 0 8px 10px -6px rgba(245, 158, 11, 0.2) !important;
        }
        .swal2-toast.toast-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4), 0 8px 10px -6px rgba(59, 130, 246, 0.2) !important;
        }
        .swal2-toast .swal2-title {
            color: #ffffff !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em !important;
        }
        .swal2-toast .swal2-icon {
            border-color: rgba(255, 255, 255, 0.8) !important;
            color: #ffffff !important;
            margin: 0 !important;
            background: transparent !important;
        }
        .swal2-toast .swal2-close {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .swal2-toast .swal2-timer-progress-bar {
            background: rgba(255, 255, 255, 0.4) !important;
        }
    </style>
    <script>
        // Set Default SweetAlert2 Options Globally
        const POSSwal = Swal.mixin({
            confirmButtonText: 'Oke, Mengerti',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            scrollbarPadding: false,
            heightAuto: false
        });

        function showPosAlert(icon, title, text = '', timer = null) {
            return POSSwal.fire({
                icon: icon,
                title: title,
                text: text,
                timer: timer,
                showConfirmButton: timer ? false : true
            });
        }

        function showPosToast(type, message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                iconColor: '#ffffff',
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                showCloseButton: true,
                customClass: {
                    popup: `rounded-2xl p-3.5 toast-${type}`
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        }
    </script>
    @stack('styles')
</head>
<body class="h-screen w-screen overflow-hidden text-slate-800 antialiased bg-slate-100 flex flex-col transition-colors duration-200">

    <!-- TOP POS HEADER BAR -->
    <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between shrink-0 select-none shadow-2xs z-30 transition-colors duration-200">
        <!-- Left: Brand Logo & Store Info -->
        <div class="flex items-center gap-4">
            <!-- Brand & Mode -->
            <div class="flex items-center gap-2.5">
                @if(!empty($appLogoSetting))
                    <img src="{{ asset('storage/' . $appLogoSetting) }}" alt="{{ $appNameSetting ?? 'Logo' }}" class="w-9 h-9 rounded-xl object-contain bg-white border border-slate-200 p-1 shadow-xs shrink-0">
                @else
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-500 flex items-center justify-center text-white font-black text-sm shadow-sm shadow-brand-500/20">
                        <i data-lucide="zap" class="w-4.5 h-4.5 fill-white stroke-white"></i>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <span class="font-bold text-base sm:text-lg tracking-tight text-slate-900">{{ $appNameSetting ?? 'WarungPro' }}</span>
                    <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                    </span>
                </div>
            </div>

            <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

            <!-- Store / Branch Info -->
            <div class="hidden sm:flex items-center gap-1.5 text-slate-600 font-semibold bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs">
                <i data-lucide="store" class="w-3.5 h-3.5 text-slate-400"></i>
                <span id="posWarehouseDisplayName" class="text-slate-700">{{ $defaultWarehouse->name ?? 'Cabang Utama' }}</span>
            </div>
        </div>

        <!-- Right Side: Structured Action Clusters -->
        <div class="flex items-center gap-2.5">
            
            <!-- 1. Agen Bank & PPOB Pill (Sleek & Integrated) -->
            <div class="relative" id="agentMenuDropdownWrapper">
                <button 
                    type="button" 
                    id="btnAgentCompactDropdown"
                    onclick="toggleAgentHeaderDropdown()" 
                    title="Layanan Agen Bank, PPOB & Pantau Saldo (F8)" 
                    class="h-9 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold transition flex items-center gap-2 whitespace-nowrap shadow-2xs cursor-pointer group"
                >
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <i data-lucide="wallet-cards" class="w-4 h-4 text-blue-600"></i>
                    <span class="font-bold text-slate-800 hidden sm:inline">Agen & PPOB</span>
                    <span class="font-mono-num text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200/60 px-2 py-0.5 rounded-lg" id="pos_header_combined_balance">Rp 0</span>
                    <kbd class="hidden md:inline px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[9px] font-mono font-bold text-slate-400 shadow-2xs">F8</kbd>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform group-hover:translate-y-0.5"></i>
                </button>

                <!-- Agen Dropdown Popover Menu -->
                <div id="agentHeaderDropdown" class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-2 hidden z-50 text-slate-800 transition-all">
                    <!-- Quick Balance Overview Header -->
                    <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Saldo Digital</span>
                        <div class="mt-1.5 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Bank Agen:
                                </span>
                                <strong class="font-mono-num font-bold text-blue-700" id="pos_dropdown_bank_balance">Rp 0</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Server PPOB:
                                </span>
                                <strong class="font-mono-num font-bold text-emerald-700" id="pos_dropdown_ppob_balance">Rp 0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Action Links -->
                    <div class="p-1.5 space-y-1">
                        <button 
                            type="button" 
                            onclick="openAgentServiceModal('transfer'); closeAgentHeaderDropdown();" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition flex items-center justify-between cursor-pointer"
                        >
                            <span class="flex items-center gap-2">
                                <i data-lucide="arrow-up-right" class="w-4 h-4 text-blue-600"></i>
                                <span>Transfer / Kirim Uang</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">Kas In</span>
                        </button>

                        <button 
                            type="button" 
                            onclick="openAgentServiceModal('withdraw'); closeAgentHeaderDropdown();" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition flex items-center justify-between cursor-pointer"
                        >
                            <span class="flex items-center gap-2">
                                <i data-lucide="arrow-down-left" class="w-4 h-4 text-emerald-600"></i>
                                <span>Tarik Tunai Nasabah</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">Kas Out</span>
                        </button>

                        <button 
                            type="button" 
                            onclick="openAgentServiceModal('ppob'); closeAgentHeaderDropdown();" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition flex items-center justify-between cursor-pointer"
                        >
                            <span class="flex items-center gap-2">
                                <i data-lucide="zap" class="w-4 h-4 text-amber-600"></i>
                                <span>Pulsa, PLN & E-Wallet</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">Produk</span>
                        </button>
                    </div>

                    <div class="h-px bg-slate-100 my-1"></div>

                    <div class="p-1.5">
                        <button 
                            type="button" 
                            onclick="openModal('agentBalancesModal'); closeAgentHeaderDropdown();" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition flex items-center gap-2 cursor-pointer"
                        >
                            <i data-lucide="wallet" class="w-4 h-4 text-slate-500"></i>
                            <span>Rincian Seluruh Rekening & Mutasi</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. Menu Operasional Shift (Kas Keluar + Tutup Shift consolidated) -->
            <div class="relative" id="shiftMenuDropdownWrapper">
                <button 
                    type="button" 
                    id="btnShiftCompactDropdown"
                    onclick="toggleShiftHeaderDropdown()" 
                    title="Menu Operasional Kas & Shift Kasir" 
                    class="h-9 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold transition flex items-center gap-2 whitespace-nowrap shadow-2xs cursor-pointer group"
                >
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span class="font-bold text-slate-800">Shift</span>
                    <span id="header_shift_expense_badge" class="hidden px-1.5 py-0.5 rounded-md bg-rose-500 text-white text-[10px] font-black font-mono-num shadow-2xs">-Rp 0</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform group-hover:translate-y-0.5"></i>
                </button>

                <!-- Shift Operations Dropdown -->
                <div id="shiftHeaderDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-1.5 hidden z-50 text-slate-800 transition-all">
                    <div class="px-3.5 py-2 border-b border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Manajemen Kasir</span>
                    </div>

                    <div class="p-1 space-y-1">
                        <button 
                            type="button" 
                            onclick="openShiftExpenseModal(); closeShiftHeaderDropdown();" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-amber-50 hover:text-amber-800 transition flex items-center justify-between cursor-pointer"
                        >
                            <span class="flex items-center gap-2">
                                <i data-lucide="receipt" class="w-4 h-4 text-amber-600"></i>
                                <span>Kas Keluar / Biaya</span>
                            </span>
                            <kbd class="px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded text-[9px] font-mono font-bold">F4</kbd>
                        </button>

                        <button 
                            type="button" 
                            onclick="openCloseShiftDialog(); closeShiftHeaderDropdown();" 
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition flex items-center justify-between cursor-pointer"
                        >
                            <span class="flex items-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4 text-rose-600"></i>
                                <span>Tutup Shift Kasir</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-rose-400"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="h-5 w-px bg-slate-200"></div>

            <!-- 3. Cashier Profile Badge -->
            <div class="h-9 pl-2 pr-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2 text-xs">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-brand-500 to-amber-500 text-white flex items-center justify-center font-black text-[11px] shadow-2xs">
                    {{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}
                </div>
                <span class="font-bold text-slate-800 max-w-[130px] truncate" id="posCashierName">{{ auth()->user()->name ?? 'Kasir' }}</span>
            </div>

            <!-- 4. Quick Action Icons (Bluetooth Printer, Shortcuts, Back Office, Dark Mode) -->
            <div class="flex items-center gap-1.5">
                <!-- Bluetooth Thermal Printer Button -->
                <button 
                    type="button" 
                    id="btnBluetoothPrinter" 
                    onclick="openBluetoothPrinterModal()" 
                    title="Koneksi Printer Bluetooth (ESC/POS)" 
                    class="h-9 px-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 hover:border-slate-300 transition shadow-2xs cursor-pointer flex items-center gap-1.5 text-xs font-semibold"
                >
                    <span class="relative flex h-2 w-2">
                        <span id="bt_indicator_ping" class="hidden absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                        <span id="bt_indicator_dot" class="relative inline-flex rounded-full h-2 w-2 bg-slate-400"></span>
                    </span>
                    <i data-lucide="printer" class="w-4 h-4 text-slate-500" id="bt_printer_icon"></i>
                    <span id="bt_printer_status_label" class="hidden sm:inline text-[11px] font-bold text-slate-600">Printer BT</span>
                </button>

                <!-- Shortcuts Help Icon Button -->
                <button 
                    type="button" 
                    onclick="openModal('keyboardShortcutsModal')" 
                    title="Bantuan Shortcut Keyboard (?)" 
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300 transition shadow-2xs cursor-pointer"
                >
                    <i data-lucide="keyboard" class="w-4 h-4 text-slate-500"></i>
                </button>

                <!-- Back Office Link Button -->
                <a 
                    href="{{ url('/') }}" 
                    title="Kembali ke Back Office / Dashboard" 
                    class="h-9 px-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300 text-xs font-semibold transition shadow-2xs flex items-center gap-1.5"
                >
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-500"></i>
                    <span class="hidden xl:inline">Back Office</span>
                </a>

                <!-- Dark / Light Mode Toggle Button -->
                <button 
                    type="button"
                    id="posThemeToggleBtn" 
                    title="Ganti Mode (Dark / Light)" 
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300 transition shadow-2xs cursor-pointer shrink-0"
                >
                    <i data-lucide="moon" class="w-4 h-4 text-slate-600"></i>
                </button>
            </div>

        </div>
    </header>

    <!-- POS MAIN WORKSPACE -->
    <main class="flex-1 flex overflow-hidden">
        @yield('content')
    </main>

    <!-- MODAL BANTUAN SHORTCUT KEYBOARD KASIR -->
    <div id="keyboardShortcutsModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xs">
                        <i data-lucide="keyboard" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 tracking-tight">Daftar Shortcut Keyboard POS</h3>
                        <p class="text-[11px] text-slate-400">Gunakan tombol fungsi untuk transaksi lebih cepat</p>
                    </div>
                </div>
                <button onclick="closeModal('keyboardShortcutsModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-6 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Fokus Scan / Cari Produk</span>
                        <kbd class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 shadow-2xs">F1</kbd>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Pilih Pelanggan / Member</span>
                        <kbd class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 shadow-2xs">F2</kbd>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Catat Kas Keluar / Biaya</span>
                        <kbd class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-amber-800 shadow-2xs">F4</kbd>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Tahan Transaksi (Hold)</span>
                        <kbd class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 shadow-2xs">F7</kbd>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-200 bg-blue-50/60 border-blue-200/80 flex items-center justify-between">
                        <span class="text-blue-900 font-semibold">Layanan Agen & PPOB</span>
                        <kbd class="px-2 py-1 bg-blue-600 text-white rounded-lg text-xs font-mono font-bold shadow-2xs">F8</kbd>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Diskon Transaksi Global</span>
                        <kbd class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 shadow-2xs">F9</kbd>
                    </div>
                    <div class="p-3 rounded-xl border border-brand-200 bg-brand-50/60 flex items-center justify-between sm:col-span-2">
                        <span class="text-brand-900 font-bold">Bayar / Selesaikan Transaksi</span>
                        <kbd class="px-3 py-1 bg-brand-500 text-white rounded-lg text-xs font-mono font-black shadow-2xs">F12</kbd>
                    </div>
                </div>
            </div>

            <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-end">
                <button type="button" onclick="closeModal('keyboardShortcutsModal')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Initialize Lucide Icons, Dropdown & Theme Logic -->
    <!-- Initialize Lucide Icons, Dropdown & Theme Logic -->
    <script>
        // Agent Dropdown Handlers
        function toggleAgentHeaderDropdown() {
            const dd = document.getElementById('agentHeaderDropdown');
            if (dd) dd.classList.toggle('hidden');
            closeShiftHeaderDropdown();
            closeUserHeaderDropdown();
        }

        function closeAgentHeaderDropdown() {
            const dd = document.getElementById('agentHeaderDropdown');
            if (dd) dd.classList.add('hidden');
        }

        // Shift Operations Dropdown Handlers
        function toggleShiftHeaderDropdown() {
            const dd = document.getElementById('shiftHeaderDropdown');
            if (dd) dd.classList.toggle('hidden');
            closeAgentHeaderDropdown();
            closeUserHeaderDropdown();
        }

        function closeShiftHeaderDropdown() {
            const dd = document.getElementById('shiftHeaderDropdown');
            if (dd) dd.classList.add('hidden');
        }

        // User & Back Office Dropdown Handlers
        function toggleUserHeaderDropdown() {
            const dd = document.getElementById('userHeaderDropdown');
            if (dd) dd.classList.toggle('hidden');
            closeAgentHeaderDropdown();
            closeShiftHeaderDropdown();
        }

        function closeUserHeaderDropdown() {
            const dd = document.getElementById('userHeaderDropdown');
            if (dd) dd.classList.add('hidden');
        }

        // Global Outside Click Listener
        document.addEventListener('click', function (e) {
            const agentWrapper = document.getElementById('agentMenuDropdownWrapper');
            if (agentWrapper && !agentWrapper.contains(e.target)) {
                closeAgentHeaderDropdown();
            }

            const shiftWrapper = document.getElementById('shiftMenuDropdownWrapper');
            if (shiftWrapper && !shiftWrapper.contains(e.target)) {
                closeShiftHeaderDropdown();
            }

            const userWrapper = document.getElementById('userMenuDropdownWrapper');
            if (userWrapper && !userWrapper.contains(e.target)) {
                closeUserHeaderDropdown();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                lucide.createIcons();
            }

            const posThemeToggleBtn = document.getElementById('posThemeToggleBtn');
            if (posThemeToggleBtn) {
                posThemeToggleBtn.addEventListener('click', function () {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    if (window.lucide) lucide.createIcons();
                });
            }
        });
    </script>
    <script src="{{ asset('js/escpos-bluetooth.js') }}"></script>
    @stack('scripts')
</body>
</html>
