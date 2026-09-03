@extends('layouts.admin')

@section('title', 'Pengaturan & Konfigurasi Sistem')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/20">
                    <i data-lucide="settings-2" class="w-6 h-6"></i>
                </div>
                Pengaturan Sistem
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Kelola profil usaha, format dokumen, aturan pajak, dan template struk kasir.</p>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-sm mb-1">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                Terdapat kesalahan dalam pengisian:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 ml-6 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Tab Navigation Sidebar -->
        <div class="lg:col-span-1 space-y-2">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-3 shadow-xs space-y-1">
                <a href="{{ route('settings.index', ['tab' => 'profile']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ $tab === 'profile' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                    <span>Profil Toko & Usaha</span>
                </a>

                <a href="{{ route('settings.index', ['tab' => 'prefixes']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ $tab === 'prefixes' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i data-lucide="hash" class="w-4 h-4 shrink-0"></i>
                    <span>Format Dokumen</span>
                </a>

                <a href="{{ route('settings.index', ['tab' => 'tax']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ $tab === 'tax' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i data-lucide="percent" class="w-4 h-4 shrink-0"></i>
                    <span>Pajak & Mata Uang</span>
                </a>

                <a href="{{ route('settings.index', ['tab' => 'receipt']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ $tab === 'receipt' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i data-lucide="receipt" class="w-4 h-4 shrink-0"></i>
                    <span>Template Struk Kasir</span>
                </a>
            </div>

            <!-- Info Box -->
            <div class="bg-gradient-to-br from-brand-50 to-indigo-50/50 dark:from-brand-950/20 dark:to-slate-900 border border-brand-100 dark:border-brand-900/30 rounded-3xl p-5 text-slate-700 dark:text-slate-300">
                <div class="flex items-center gap-2 font-bold text-xs text-brand-700 dark:text-brand-400 mb-2">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    Informasi Sinkronisasi
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                    Konfigurasi ini digunakan secara global di seluruh transaksi POS, cetak invoice, laporan keuangan, dan surat jalan.
                </p>
            </div>
        </div>

        <!-- Tab Content Area -->
        <div class="lg:col-span-3">
            @if($tab === 'profile')
                <!-- TAB 1: PROFIL TOKO -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 dark:text-white">Profil Toko & Usaha</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Identitas usaha yang akan tercetak di kuitansi, invoice, dan laporan.</p>
                        </div>
                    </div>

                    <form action="{{ route('settings.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <!-- Logo Toko Preview & Upload -->
                        <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                            <div class="relative w-24 h-24 rounded-2xl bg-white dark:bg-slate-900 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden shadow-xs shrink-0">
                                @if($companyLogo)
                                    <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo Toko" class="w-full h-full object-contain p-2" id="logoPreview">
                                @else
                                    <div class="flex flex-col items-center justify-center text-slate-400 p-2 text-center" id="logoPlaceholder">
                                        <i data-lucide="image" class="w-8 h-8 stroke-1"></i>
                                        <span class="text-[10px] font-bold mt-1">Belum Ada</span>
                                    </div>
                                    <img src="" alt="Logo Toko" class="w-full h-full object-contain p-2 hidden" id="logoPreview">
                                @endif
                            </div>
                            <div class="space-y-1.5 text-center sm:text-left flex-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Upload Logo Toko</label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Format PNG, JPG, WEBP, atau SVG. Maksimal 2MB. Resolusi ideal 500x500 px.</p>
                                <input type="file" name="company_logo" id="logoInput" accept="image/*" class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-500 file:text-white hover:file:bg-brand-600 cursor-pointer pt-1">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Nama Usaha -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Nama Toko / Bisnis *</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" required class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                            </div>

                            <!-- Tagline -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Slogan / Tagline</label>
                                <input type="text" name="company_tagline" value="{{ old('company_tagline', $companyTagline) }}" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                            </div>

                            <!-- No Telepon -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">No. Telepon / WhatsApp</label>
                                <input type="text" name="company_phone" value="{{ old('company_phone', $companyPhone) }}" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                            </div>

                            <!-- Email -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Email Resmi</label>
                                <input type="email" name="company_email" value="{{ old('company_email', $companyEmail) }}" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                            </div>

                            <!-- NPWP -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 md:col-span-2">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Nomor Pokok Wajib Pajak (NPWP)</label>
                                <input type="text" name="company_npwp" value="{{ old('company_npwp', $companyTaxNumber) }}" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none" placeholder="00.000.000.0-000.000">
                            </div>

                            <!-- Alamat -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 md:col-span-2">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Alamat Lengkap Toko</label>
                                <textarea name="company_address" rows="3" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none resize-none">{{ old('company_address', $companyAddress) }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition-all flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Profil Toko
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($tab === 'prefixes')
                <!-- TAB 2: FORMAT NOMOR TRANSAKSI -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 dark:text-white">Format Nomor Transaksi & Kode</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atur awalan (prefix) kode otomatis untuk setiap jenis transaksi sistem.</p>
                        </div>
                    </div>

                    <form action="{{ route('settings.prefixes') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Invoice Penjualan -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Prefix Faktur Penjualan (Sales) *</label>
                                <input type="text" name="prefix_invoice" value="{{ old('prefix_invoice', $prefixInvoice) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                                <span class="text-[10px] text-slate-400 font-medium">Contoh: {{ $prefixInvoice }}-20260904-0001</span>
                            </div>

                            <!-- Purchase Order -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Prefix Purchase Order (PO) *</label>
                                <input type="text" name="prefix_po" value="{{ old('prefix_po', $prefixPo) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                                <span class="text-[10px] text-slate-400 font-medium">Contoh: {{ $prefixPo }}-202609-001</span>
                            </div>

                            <!-- Penerimaan Barang (GRN) -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Prefix Good Receipt Note (GRN) *</label>
                                <input type="text" name="prefix_grn" value="{{ old('prefix_grn', $prefixGrn) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                                <span class="text-[10px] text-slate-400 font-medium">Contoh: {{ $prefixGrn }}-202609-001</span>
                            </div>

                            <!-- Retur Penjualan -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Prefix Retur Penjualan (Sale Return) *</label>
                                <input type="text" name="prefix_return_sale" value="{{ old('prefix_return_sale', $prefixReturnSale) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                                <span class="text-[10px] text-slate-400 font-medium">Contoh: {{ $prefixReturnSale }}-202609-001</span>
                            </div>

                            <!-- Retur Pembelian -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Prefix Retur Pembelian (Purchase Return) *</label>
                                <input type="text" name="prefix_return_purchase" value="{{ old('prefix_return_purchase', $prefixReturnPurchase) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                                <span class="text-[10px] text-slate-400 font-medium">Contoh: {{ $prefixReturnPurchase }}-202609-001</span>
                            </div>

                            <!-- Stok Opname -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Prefix Stok Opname (Stock Count) *</label>
                                <input type="text" name="prefix_opname" value="{{ old('prefix_opname', $prefixOpname) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                                <span class="text-[10px] text-slate-400 font-medium">Contoh: {{ $prefixOpname }}-202609-001</span>
                            </div>

                            <!-- Transfer Stok -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 md:col-span-2">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Prefix Transfer Stok Antar Gudang *</label>
                                <input type="text" name="prefix_transfer" value="{{ old('prefix_transfer', $prefixTransfer) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                                <span class="text-[10px] text-slate-400 font-medium">Contoh: {{ $prefixTransfer }}-202609-001</span>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition-all flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Format Prefix
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($tab === 'tax')
                <!-- TAB 3: PAJAK & MATA UANG -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 dark:text-white">Pajak & Mata Uang</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Konfigurasi tarif PPN default serta simbol mata uang aplikasi.</p>
                        </div>
                    </div>

                    <form action="{{ route('settings.tax') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- PPN Rate -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Default PPN (%) *</label>
                                <input type="number" step="0.1" min="0" max="100" name="default_tax_rate" value="{{ old('default_tax_rate', $defaultTaxRate) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                            </div>

                            <!-- Mata Uang Symbol -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Simbol Mata Uang *</label>
                                <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $currencySymbol) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                            </div>

                            <!-- Kode Mata Uang -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Kode Mata Uang ISO *</label>
                                <input type="text" name="currency_code" value="{{ old('currency_code', $currencyCode) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white uppercase bg-transparent focus:ring-0 focus:outline-none">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition-all flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Pengaturan Pajak
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($tab === 'receipt')
                <!-- TAB 4: TEMPLATE STRUK KASIR -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 dark:text-white">Template & Format Struk Kasir</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Konfigurasi pesan catatan header, footer, ukuran kertas printer thermal.</p>
                        </div>
                    </div>

                    <form action="{{ route('settings.receipt') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Ukuran Kertas -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Ukuran Kertas Printer Thermal *</label>
                                <select name="receipt_paper_size" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                                    <option value="58mm" {{ old('receipt_paper_size', $receiptPaperSize) === '58mm' ? 'selected' : '' }}>58mm (Thermal Standar Mini)</option>
                                    <option value="80mm" {{ old('receipt_paper_size', $receiptPaperSize) === '80mm' ? 'selected' : '' }}>80mm (Thermal Kasir Standar)</option>
                                </select>
                            </div>

                            <!-- Tampilkan Logo Toggle -->
                            <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white block">Tampilkan Logo Usaha</span>
                                    <span class="text-[11px] text-slate-400 font-medium">Cetak logo di bagian paling atas struk</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="receipt_show_logo" value="1" {{ old('receipt_show_logo', $receiptShowLogo) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-brand-500"></div>
                                </label>
                            </div>

                            <!-- Header Message -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 md:col-span-2">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Header Struk (Teks Bawah Nama Toko)</label>
                                <textarea name="receipt_header" rows="2" class="w-full border-0 p-0 text-sm font-medium text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none resize-none" placeholder="Selamat Datang...">{{ old('receipt_header', $receiptHeader) }}</textarea>
                            </div>

                            <!-- Footer Message -->
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 md:col-span-2">
                                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Footer Struk (Pesan Penutup / Ucapan)</label>
                                <textarea name="receipt_footer" rows="3" class="w-full border-0 p-0 text-sm font-medium text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none resize-none" placeholder="Terima kasih atas kunjungan Anda...">{{ old('receipt_footer', $receiptFooter) }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition-all flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Template Struk
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const logoInput = document.getElementById('logoInput');
        const logoPreview = document.getElementById('logoPreview');
        const logoPlaceholder = document.getElementById('logoPlaceholder');

        if (logoInput && logoPreview) {
            logoInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        logoPreview.src = event.target.result;
                        logoPreview.classList.remove('hidden');
                        if (logoPlaceholder) logoPlaceholder.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection
