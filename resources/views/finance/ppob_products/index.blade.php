@extends('layouts.admin')

@section('title', 'Katalog Produk PPOB & Pulsa - WarungPro')

@section('content')
    <div class="space-y-6">

        <!-- Top Statistic Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">

            <!-- Total Active Products -->
            <div
                class="p-4 rounded-2xl bg-brand-500 text-white shadow-md shadow-brand-500/20 flex items-center justify-between transition-all hover:bg-brand-600">
                <div class="space-y-1">
                    <span class="text-[11px] font-semibold text-white/90 uppercase tracking-wider">Total Produk Aktif</span>
                    <div class="text-2xl font-black text-white font-mono-num tracking-tight">
                        {{ number_format($totalActive, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-white/80 font-medium">Siap ditransaksikan di kasir</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center shrink-0">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Pulsa Reguler -->
            <div
                class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between transition-all hover:border-slate-300">
                <div class="space-y-1">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pulsa & Data</span>
                    <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                        {{ number_format($totalPulsa, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-blue-600 font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        <span>Tsel, Indosat, XL, dll</span>
                    </div>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/80">
                    <i data-lucide="radio" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Token PLN -->
            <div
                class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between transition-all hover:border-slate-300">
                <div class="space-y-1">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Token Listrik PLN</span>
                    <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                        {{ number_format($totalTokenPln, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-amber-600 font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Token Prabayar</span>
                    </div>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100/80">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- E-Wallet Top Up -->
            <div
                class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between transition-all hover:border-slate-300">
                <div class="space-y-1">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Top Up E-Wallet</span>
                    <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                        {{ number_format($totalEwallet, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Dana, OVO, GoPay, Spay</span>
                    </div>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100/80">
                    <i data-lucide="wallet-cards" class="w-5 h-5"></i>
                </div>
            </div>

        </div>

        <!-- Action & Filter Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">

            <!-- Row 1: Header Title & Action Button -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black tracking-tight text-slate-900">Daftar Katalog Produk PPOB</h2>
                        <p class="text-xs text-slate-400">Atur harga modal server (HPP), harga jual kasir, serta margin
                            keuntungan otomatis</p>
                    </div>
                </div>

                <!-- Add Product Button -->
                <button onclick="openCreatePpobModal()" type="button"
                    class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Produk PPOB</span>
                </button>
            </div>

            <div class="h-px bg-slate-100"></div>

            <!-- Row 2: Search & Filter Form -->
            <form action="{{ route('ppob-products.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Search Keyword (Col 1-5) -->
                <div class="lg:col-span-5 relative">
                    <i data-lucide="search"
                        class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama produk, provider, atau kode..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                </div>

                <!-- Filter Kategori (Col 6-8) -->
                <div class="lg:col-span-3">
                    <select name="category" onchange="this.form.submit()"
                        class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <option value="pulsa" {{ $category === 'pulsa' ? 'selected' : '' }}>Pulsa Reguler</option>
                        <option value="paket_data" {{ $category === 'paket_data' ? 'selected' : '' }}>Paket Data</option>
                        <option value="token_pln" {{ $category === 'token_pln' ? 'selected' : '' }}>Token Listrik PLN</option>
                        <option value="ewallet" {{ $category === 'ewallet' ? 'selected' : '' }}>Top Up E-Wallet</option>
                        <option value="tagihan" {{ $category === 'tagihan' ? 'selected' : '' }}>Tagihan Bulanan</option>
                    </select>
                </div>

                <!-- Filter Provider (Col 9-11) -->
                <div class="lg:col-span-3">
                    <select name="provider" onchange="this.form.submit()"
                        class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition cursor-pointer">
                        <option value="">Semua Provider</option>
                        @foreach($providers as $p)
                            <option value="{{ $p }}" {{ $provider === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Reset Button (Col 12) -->
                <div class="lg:col-span-1 flex items-center justify-end">
                    @if($search || $category || $provider)
                        <a href="{{ route('ppob-products.index') }}"
                            class="p-2 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition"
                            title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- PPOB PRODUCTS TABLE CARD -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">

            <!-- Table Header Bar -->
            <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="smartphone" class="w-5 h-5 text-white"></i>
                    <h3 class="font-black text-sm tracking-tight text-white">Daftar Produk Digital PPOB</h3>
                    <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                        {{ $products->total() }} Produk
                    </span>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                            <th class="py-3 px-5 border-b border-white/10">Kode & Nama Produk</th>
                            <th class="py-3 px-4 border-b border-white/10">Kategori & Provider</th>
                            <th class="py-3 px-4 border-b border-white/10 text-right">Modal Server (HPP)</th>
                            <th class="py-3 px-4 border-b border-white/10 text-right">Harga Jual Kasir</th>
                            <th class="py-3 px-4 border-b border-white/10 text-right">Margin Keuntungan</th>
                            <th class="py-3 px-4 border-b border-white/10">Akun Saldo Terpotong</th>
                            <th class="py-3 px-5 text-right w-32 border-b border-white/10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($products as $prod)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Kode & Nama Produk -->
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-900">{{ $prod->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $prod->code ?? '-' }}</div>
                                </td>

                                <!-- Kategori & Provider -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px]">
                                            {{ $prod->provider ?? 'Umum' }}
                                        </span>
                                        <span class="text-slate-400 text-[10px] capitalize">
                                            ({{ str_replace('_', ' ', $prod->category) }})
                                        </span>
                                    </div>
                                </td>

                                <!-- Modal Server (HPP) -->
                                <td class="py-3.5 px-4 text-right font-mono-num font-semibold text-slate-600">
                                    Rp {{ number_format($prod->cost_price, 0, ',', '.') }}
                                </td>

                                <!-- Harga Jual Kasir -->
                                <td class="py-3.5 px-4 text-right font-mono-num font-black text-slate-900">
                                    Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                                </td>

                                <!-- Margin Keuntungan -->
                                <td class="py-3.5 px-4 text-right">
                                    <span
                                        class="font-mono-num font-bold text-emerald-600 px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200/60">
                                        + Rp {{ number_format($prod->margin, 0, ',', '.') }}
                                    </span>
                                </td>

                                <!-- Akun Saldo Terpotong -->
                                <td class="py-3.5 px-4">
                                    @if($prod->defaultAccount)
                                        <div class="font-bold text-slate-700 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                            <span>{{ $prod->defaultAccount->name }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono-num">
                                            Sisa: Rp {{ number_format($prod->defaultAccount->current_balance, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Akun PPOB Default</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" onclick="openEditPpobModal({{ $prod->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition cursor-pointer"
                                            title="Edit Produk PPOB">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="{{ route('ppob-products.destroy', $prod->id) }}" method="POST"
                                            class="inline" id="delete_ppob_{{ $prod->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete_ppob_{{ $prod->id }}', 'Hapus Produk {{ $prod->name }}?', 'Produk ini tidak akan lagi muncul di pilihan kasir POS.')"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                                title="Hapus Produk">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i data-lucide="smartphone" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                    <p class="font-bold text-sm text-slate-600">Belum Ada Produk PPOB</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Tambah Produk PPOB" di atas untuk
                                        menambahkan daftar pulsa / paket digital.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- CREATE / EDIT MODAL -->
    <div id="ppobProductModal"
        class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
        <div
            class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="ppobModalTitle" class="font-extrabold text-base text-slate-900 tracking-tight">Tambah Produk
                            PPOB</h3>
                        <p class="text-xs text-slate-400">Atur nama paket, HPP modal server, dan harga jual kasir</p>
                    </div>
                </div>
                <button onclick="closeModal('ppobProductModal')" type="button"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="ppobProductForm" action="{{ route('ppob-products.store') }}" method="POST">
                @csrf
                <div id="ppob_method_field"></div>

                <div class="p-6 space-y-4">
                    <!-- Nama & Kode Produk -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label
                                class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Kode</label>
                            <input type="text" name="code" id="ppob_code_input" placeholder="TSEL10"
                                class="w-full bg-transparent border-0 p-0 text-xs font-mono font-bold text-slate-800 focus:ring-0 focus:outline-none">
                        </div>
                        <div
                            class="sm:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label
                                class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Nama
                                Produk <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="ppob_name_input" required
                                placeholder="Contoh: Telkomsel 10.000"
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                        </div>
                    </div>

                    <!-- Kategori & Provider -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label
                                class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Kategori
                                <span class="text-rose-500">*</span></label>
                            <select name="category" id="ppob_category_input" required
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                                <option value="pulsa">Pulsa Reguler</option>
                                <option value="paket_data">Paket Data Internet</option>
                                <option value="token_pln">Token Listrik PLN</option>
                                <option value="ewallet">Top Up E-Wallet</option>
                                <option value="tagihan">Bayar Tagihan Bulanan</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label
                                class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Provider
                                / Operator</label>
                            <input type="text" name="provider" id="ppob_provider_input"
                                placeholder="Contoh: Telkomsel, PLN, DANA"
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                        </div>
                    </div>

                    <!-- Harga Modal vs Harga Jual -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label
                                class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Modal
                                Server / HPP (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" min="0" name="cost_price" id="ppob_cost_input"
                                oninput="recalcModalMargin()" required placeholder="10250"
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold font-mono-num text-slate-700 focus:ring-0 focus:outline-none">
                        </div>
                        <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                            <label
                                class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Harga
                                Jual Kasir (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" min="0" name="selling_price" id="ppob_sell_input"
                                oninput="recalcModalMargin()" required placeholder="12500"
                                class="w-full bg-transparent border-0 p-0 text-xs font-black font-mono-num text-slate-900 focus:ring-0 focus:outline-none">
                        </div>
                    </div>

                    <!-- Kalkulasi Margin Preview -->
                    <div
                        class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium">Margin Keuntungan Bersih:</span>
                        <strong class="text-emerald-600 font-black font-mono-num text-sm" id="ppob_margin_preview">+ Rp
                            0</strong>
                    </div>

                    <!-- Akun Rekening Server PPOB Terkait -->
                    <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                        <label
                            class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Potong
                            Saldo Rekening/Server (Default)</label>
                        <select name="default_account_id" id="ppob_account_input"
                            class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Pilih Otomatis di POS Kasir</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Aktif Checkbox -->
                    <label class="flex items-center gap-2.5 cursor-pointer pt-1">
                        <input type="checkbox" name="is_active" id="ppob_active_input" value="1" checked
                            class="rounded-md border-slate-300 text-brand-500 focus:ring-brand-500 w-4 h-4 cursor-pointer">
                        <span class="text-xs font-bold text-slate-700">Aktifkan produk ini di kasir POS</span>
                    </label>
                </div>

                <!-- Modal Action Buttons -->
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                    <button type="button" onclick="closeModal('ppobProductModal')"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                    <button type="submit"
                        class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function recalcModalMargin() {
                const cost = parseFloat(document.getElementById('ppob_cost_input').value) || 0;
                const sell = parseFloat(document.getElementById('ppob_sell_input').value) || 0;
                const margin = sell - cost;
                const el = document.getElementById('ppob_margin_preview');
                if (el) {
                    el.innerText = `${margin >= 0 ? '+' : ''} Rp ${parseInt(margin).toLocaleString('id-ID')}`;
                    el.className = margin >= 0 ? 'text-emerald-600 font-black font-mono-num text-sm' : 'text-rose-600 font-black font-mono-num text-sm';
                }
            }

            function openCreatePpobModal() {
                document.getElementById('ppobProductForm').reset();
                document.getElementById('ppobProductForm').action = "{{ route('ppob-products.store') }}";
                document.getElementById('ppob_method_field').innerHTML = '';
                document.getElementById('ppobModalTitle').innerText = 'Tambah Produk PPOB';
                document.getElementById('ppob_active_input').checked = true;
                recalcModalMargin();
                openModal('ppobProductModal');
            }

            function openEditPpobModal(id) {
                openCreatePpobModal();
                document.getElementById('ppobModalTitle').innerText = 'Edit Produk PPOB';
                document.getElementById('ppobProductForm').action = `/ppob-products/${id}`;
                document.getElementById('ppob_method_field').innerHTML = '<input type="hidden" name="_method" value="PUT">';

                fetch(`/ppob-products/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('ppob_code_input').value = data.code || '';
                        document.getElementById('ppob_name_input').value = data.name || '';
                        document.getElementById('ppob_category_input').value = data.category || 'pulsa';
                        document.getElementById('ppob_provider_input').value = data.provider || '';
                        document.getElementById('ppob_cost_input').value = data.cost_price || 0;
                        document.getElementById('ppob_sell_input').value = data.selling_price || 0;
                        document.getElementById('ppob_account_input').value = data.default_account_id || '';
                        document.getElementById('ppob_active_input').checked = !!data.is_active;
                        recalcModalMargin();
                    });
            }
        </script>
    @endpush

@endsection