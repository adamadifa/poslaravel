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

                <a href="{{ route('settings.index', ['tab' => 'business_type']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ $tab === 'business_type' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i data-lucide="layers" class="w-4 h-4 shrink-0"></i>
                    <span>Jenis Usaha (Retail / FNB / Jasa)</span>
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

                <a href="{{ route('settings.index', ['tab' => 'agent']) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ $tab === 'agent' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i data-lucide="landmark" class="w-4 h-4 shrink-0"></i>
                    <span>Biaya Admin Agen & Bank</span>
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

            @elseif($tab === 'business_type')
                <!-- TAB 2: JENIS USAHA & MODEL OPERASIONAL -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 dark:text-white">Jenis Usaha & Model Bisnis</h2>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">Tentukan jenis usaha POS Anda: Retail, FNB (Resto/Kafe), Jasa/Service, atau Gabungan (Hybrid).</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-950/40 text-brand-600 dark:text-brand-400 text-[11px] font-bold border border-brand-200/60 dark:border-brand-800">
                            Multi-Business Mode
                        </span>
                    </div>

                    <form action="{{ route('settings.business-type') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Business Type Selector Cards -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-3">Pilih Model Bisnis Utama:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <!-- Retail -->
                                <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ $businessType === 'retail' ? 'border-brand-500 bg-brand-50/20 dark:bg-brand-950/20 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                    <input type="radio" name="business_type" value="retail" class="sr-only" {{ $businessType === 'retail' ? 'checked' : '' }} onchange="toggleBusinessPanels(this.value)">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center mb-3">
                                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-white">Retail / Ritel</span>
                                    <p class="text-[11px] text-slate-400 mt-1">Minimarket, toko kelontong, butik baju, apotek fisik.</p>
                                </label>

                                <!-- FNB -->
                                <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ $businessType === 'fnb' ? 'border-brand-500 bg-brand-50/20 dark:bg-brand-950/20 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                    <input type="radio" name="business_type" value="fnb" class="sr-only" {{ $businessType === 'fnb' ? 'checked' : '' }} onchange="toggleBusinessPanels(this.value)">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center mb-3">
                                        <i data-lucide="utensils" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-white">F&B (Kuliner)</span>
                                    <p class="text-[11px] text-slate-400 mt-1">Restoran, cafe, kedai kopi, fast food, bakery.</p>
                                </label>

                                <!-- Jasa / Service -->
                                <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ $businessType === 'service' ? 'border-brand-500 bg-brand-50/20 dark:bg-brand-950/20 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                    <input type="radio" name="business_type" value="service" class="sr-only" {{ $businessType === 'service' ? 'checked' : '' }} onchange="toggleBusinessPanels(this.value)">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center mb-3">
                                        <i data-lucide="scissors" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-white">Jasa / Service</span>
                                    <p class="text-[11px] text-slate-400 mt-1">Barbershop, salon, klinik estetika, bengkel, cuci mobil, laundry.</p>
                                </label>

                                <!-- Hybrid -->
                                <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ $businessType === 'hybrid' ? 'border-brand-500 bg-brand-50/20 dark:bg-brand-950/20 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                    <input type="radio" name="business_type" value="hybrid" class="sr-only" {{ $businessType === 'hybrid' ? 'checked' : '' }} onchange="toggleBusinessPanels(this.value)">
                                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 flex items-center justify-center mb-3">
                                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-800 dark:text-white">Hybrid (Semua)</span>
                                    <p class="text-[11px] text-slate-400 mt-1">Gabungan ritel barang, menu resto/kafe, dan layanan jasa sekaligus.</p>
                                </label>
                            </div>
                        </div>

                        <!-- FNB SUB-PANEL -->
                        <div id="fnbPanel" class="p-5 rounded-2xl border border-amber-200/80 bg-amber-50/30 dark:bg-slate-800/60 dark:border-slate-700 space-y-4 {{ in_array($businessType, ['fnb', 'hybrid']) ? '' : 'hidden' }}">
                            <div class="flex items-center gap-2 text-xs font-bold text-amber-900 dark:text-amber-400">
                                <i data-lucide="utensils" class="w-4 h-4"></i>
                                <span>Fitur & Pengaturan Operasional F&B (Resto / Kafe)</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <!-- Table Management -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Manajemen Meja & Denah</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Atur status meja (Indoor, Outdoor, VIP)</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="fnb_enable_table_management" value="1" {{ $fnbEnableTableManagement ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                                    </label>
                                </div>

                                <!-- Kitchen Display System (KDS) -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Kitchen Display System (KDS)</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Layar monitor order dapur & bar real-time</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="fnb_enable_kitchen_display" value="1" {{ $fnbEnableKitchenDisplay ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                                    </label>
                                </div>

                                <!-- Modifiers / Add-ons -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Modifiers & Opsi Tambahan</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Level pedas, topping ekstra, ukuran cup</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="fnb_enable_modifiers" value="1" {{ $fnbEnableModifiers ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                                    </label>
                                </div>

                                <!-- Reservasi Meja -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Reservasi Meja</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Pencatatan booking meja sebelum hari H</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="fnb_enable_reservation" value="1" {{ $fnbEnableReservation ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                                    </label>
                                </div>

                                <!-- Nomor Antrian Take Away -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Nomor Antrian Take Away</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Generate nomor antrian otomatis (A-001)</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="fnb_enable_queue_number" value="1" {{ $fnbEnableQueueNumber ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                                    </label>
                                </div>

                                <!-- Service Charge (%) -->
                                <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                                    <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Biaya Layanan / Service Charge (%)</label>
                                    <input type="number" step="0.1" name="fnb_service_charge_percent" value="{{ old('fnb_service_charge_percent', $fnbServiceChargePercent) }}" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- JASA SUB-PANEL -->
                        <div id="servicePanel" class="p-5 rounded-2xl border border-emerald-200/80 bg-emerald-50/30 dark:bg-slate-800/60 dark:border-slate-700 space-y-4 {{ in_array($businessType, ['service', 'hybrid']) ? '' : 'hidden' }}">
                            <div class="flex items-center gap-2 text-xs font-bold text-emerald-900 dark:text-emerald-400">
                                <i data-lucide="scissors" class="w-4 h-4"></i>
                                <span>Fitur & Pengaturan Operasional Jasa / Service</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <!-- Booking & Appointment -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Booking & Appointment Jadwal</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Kalender reservasi waktu layanan per staff</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="service_enable_booking" value="1" {{ $serviceEnableBooking ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>

                                <!-- Assignment Teknisi / Terapis -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Assignment Staff & Komisi</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Penugasan teknisi/terapis + hitung komisi</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="service_enable_technician_assignment" value="1" {{ $serviceEnableTechnicianAssignment ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>

                                <!-- Antrian & Status Tracking -->
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-white block">Dashboard Antrian Layanan</span>
                                        <span class="text-[11px] text-slate-400 font-medium">Monitor antrian (Menunggu, Dikerjakan, Selesai)</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="service_auto_queue" value="1" {{ $serviceAutoQueue ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>

                                <!-- Slot Waktu Booking -->
                                <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                                    <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Interval Slot Kalender (Menit)</label>
                                    <select name="service_booking_slot_minutes" class="w-full border-0 p-0 text-sm font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none cursor-pointer">
                                        <option value="15" {{ $serviceBookingSlotMinutes == '15' ? 'selected' : '' }}>15 Menit</option>
                                        <option value="30" {{ $serviceBookingSlotMinutes == '30' ? 'selected' : '' }}>30 Menit (Standar)</option>
                                        <option value="45" {{ $serviceBookingSlotMinutes == '45' ? 'selected' : '' }}>45 Menit</option>
                                        <option value="60" {{ $serviceBookingSlotMinutes == '60' ? 'selected' : '' }}>60 Menit (1 Jam)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition-all flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Jenis Usaha
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
            @elseif($tab === 'agent')
                <!-- TAB 5: BIAYA ADMIN AGEN & BANK -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 dark:text-white">Biaya Admin Agen & Perbankan</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tentukan standar biaya jasa transfer dan tarik tunai. Nilai ini akan otomatis terkunci di kasir POS.</p>
                        </div>
                    </div>

                    <form action="{{ route('settings.agent') }}" method="POST" class="space-y-8">
                        @csrf

                        <!-- 1. Biaya Standar / Default (Fallback jika tidak masuk range) -->
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-1 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-brand-50 dark:bg-brand-950/50 text-brand-600 flex items-center justify-center text-xs font-black">1</span>
                                Biaya Admin Default (Fallback)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Biaya admin yang digunakan jika nominal transaksi di kasir tidak masuk ke dalam salah satu range nominal di bawah.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Biaya Admin Transfer Uang -->
                                <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                    <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Default Biaya Admin - Transfer Bank (Rp) *</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-600">Rp</span>
                                        <input type="number" step="any" min="0" name="agent_transfer_admin_fee" value="{{ old('agent_transfer_admin_fee', $agentTransferAdminFee) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none font-mono-num" placeholder="5000">
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 block">Contoh: Rp 5.000 jika transaksi tidak ada kecocokan di tabel range.</span>
                                </div>

                                <!-- Biaya Admin Tarik Tunai -->
                                <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                    <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Default Biaya Admin - Tarik Tunai (Rp) *</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-600">Rp</span>
                                        <input type="number" step="any" min="0" name="agent_withdraw_admin_fee" value="{{ old('agent_withdraw_admin_fee', $agentWithdrawAdminFee) }}" required class="w-full border-0 p-0 text-sm font-bold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none font-mono-num" placeholder="5000">
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 block">Contoh: Rp 5.000 jika transaksi tidak ada kecocokan di tabel range.</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Skema Bertingkat Transfer Uang -->
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 flex items-center justify-center text-xs font-black">2</span>
                                        Range Biaya Admin - Transfer Uang
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atur biaya admin transfer bank bertingkat sesuai nominal transfer nasabah.</p>
                                </div>
                                <button type="button" onclick="addTransferTierRow()" class="px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Tambah Baris Range</span>
                                </button>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-[11px] font-bold text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                        <tr>
                                            <th class="py-3 px-4">Dari Nominal (Min Rp)</th>
                                            <th class="py-3 px-4">Sampai Nominal (Maks Rp)</th>
                                            <th class="py-3 px-4">Biaya Admin Kasir (Rp)</th>
                                            <th class="py-3 px-4 w-12 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transferTierTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                        @forelse($agentTransferTiers as $idx => $tier)
                                            <tr class="tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[{{ $idx }}][min]" value="{{ $tier['min'] ?? 0 }}" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[{{ $idx }}][max]" value="{{ $tier['max'] ?? 0 }}" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                        <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[{{ $idx }}][fee]" value="{{ $tier['fee'] ?? 0 }}" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4 text-center">
                                                    <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <!-- Baris default contoh jika belum ada -->
                                            <tr class="tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[0][min]" value="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[0][max]" value="1000000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                        <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[0][fee]" value="5000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4 text-center">
                                                    <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[1][min]" value="1000001" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[1][max]" value="5000000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                        <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                                                        <input type="number" step="any" min="0" name="transfer_tiers[1][fee]" value="7500" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4 text-center">
                                                    <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 3. Skema Bertingkat Tarik Tunai -->
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center text-xs font-black">3</span>
                                        Range Biaya Admin - Tarik Tunai
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atur biaya admin tarik tunai (gesek EDC / transfer masuk) bertingkat sesuai nominal yang diambil.</p>
                                </div>
                                <button type="button" onclick="addWithdrawTierRow()" class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Tambah Baris Range</span>
                                </button>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-[11px] font-bold text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                        <tr>
                                            <th class="py-3 px-4">Dari Nominal (Min Rp)</th>
                                            <th class="py-3 px-4">Sampai Nominal (Maks Rp)</th>
                                            <th class="py-3 px-4">Biaya Admin Kasir (Rp)</th>
                                            <th class="py-3 px-4 w-12 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="withdrawTierTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                        @forelse($agentWithdrawTiers as $idx => $tier)
                                            <tr class="tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[{{ $idx }}][min]" value="{{ $tier['min'] ?? 0 }}" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[{{ $idx }}][max]" value="{{ $tier['max'] ?? 0 }}" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                        <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[{{ $idx }}][fee]" value="{{ $tier['fee'] ?? 0 }}" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4 text-center">
                                                    <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <!-- Baris default contoh jika belum ada -->
                                            <tr class="tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[0][min]" value="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[0][max]" value="1000000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                        <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[0][fee]" value="5000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4 text-center">
                                                    <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[1][min]" value="1000001" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[11px] font-bold text-slate-400">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[1][max]" value="5000000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4">
                                                    <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                                        <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                                                        <input type="number" step="any" min="0" name="withdraw_tiers[1][fee]" value="7500" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                                                    </div>
                                                </td>
                                                <td class="p-2.5 px-4 text-center">
                                                    <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Keamanan & Informasi -->
                        <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3">
                            <i data-lucide="shield-check" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                            <div class="text-xs text-amber-900 dark:text-amber-300 space-y-1">
                                <p class="font-bold">Otomasi Perhitungan & Proteksi di POS Kasir:</p>
                                <p class="leading-relaxed text-amber-800/90 dark:text-amber-400/90">
                                    Saat kasir memasukkan nominal pokok di layar POS, biaya admin akan <strong>terkalkulasi otomatis</strong> mengikuti tabel range nominal di atas dan posisinya <strong>terkunci (readonly)</strong> sehingga kasir tidak bisa memanipulasi biaya admin.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition-all flex items-center gap-2 cursor-pointer">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Seluruh Pengaturan Admin Agen
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleBusinessPanels(type) {
        const fnbPanel = document.getElementById('fnbPanel');
        const servicePanel = document.getElementById('servicePanel');

        if (fnbPanel) {
            if (type === 'fnb' || type === 'hybrid') {
                fnbPanel.classList.remove('hidden');
            } else {
                fnbPanel.classList.add('hidden');
            }
        }

        if (servicePanel) {
            if (type === 'service' || type === 'hybrid') {
                servicePanel.classList.remove('hidden');
            } else {
                servicePanel.classList.add('hidden');
            }
        }
    }

    function removeTierRow(button) {
        const row = button.closest('tr');
        if (row) {
            row.remove();
        }
    }

    function addTransferTierRow() {
        const tbody = document.getElementById('transferTierTableBody');
        const count = tbody.querySelectorAll('tr').length;
        const index = Date.now(); // unique index

        const tr = document.createElement('tr');
        tr.className = 'tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition';
        tr.innerHTML = `
            <td class="p-2.5 px-4">
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-bold text-slate-400">Rp</span>
                    <input type="number" step="any" min="0" name="transfer_tiers[${index}][min]" value="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                </div>
            </td>
            <td class="p-2.5 px-4">
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-bold text-slate-400">Rp</span>
                    <input type="number" step="any" min="0" name="transfer_tiers[${index}][max]" value="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                </div>
            </td>
            <td class="p-2.5 px-4">
                <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                    <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                    <input type="number" step="any" min="0" name="transfer_tiers[${index}][fee]" value="5000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                </div>
            </td>
            <td class="p-2.5 px-4 text-center">
                <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        lucide.createIcons();
    }

    function addWithdrawTierRow() {
        const tbody = document.getElementById('withdrawTierTableBody');
        const count = tbody.querySelectorAll('tr').length;
        const index = Date.now(); // unique index

        const tr = document.createElement('tr');
        tr.className = 'tier-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition';
        tr.innerHTML = `
            <td class="p-2.5 px-4">
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-bold text-slate-400">Rp</span>
                    <input type="number" step="any" min="0" name="withdraw_tiers[${index}][min]" value="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                </div>
            </td>
            <td class="p-2.5 px-4">
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-bold text-slate-400">Rp</span>
                    <input type="number" step="any" min="0" name="withdraw_tiers[${index}][max]" value="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 focus:outline-none font-mono-num">
                </div>
            </td>
            <td class="p-2.5 px-4">
                <div class="flex items-center gap-1.5 bg-emerald-50/60 dark:bg-emerald-950/40 px-2.5 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                    <span class="text-[11px] font-bold text-emerald-600">Rp</span>
                    <input type="number" step="any" min="0" name="withdraw_tiers[${index}][fee]" value="5000" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 focus:ring-0 focus:outline-none font-mono-num">
                </div>
            </td>
            <td class="p-2.5 px-4 text-center">
                <button type="button" onclick="removeTierRow(this)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        lucide.createIcons();
    }

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
