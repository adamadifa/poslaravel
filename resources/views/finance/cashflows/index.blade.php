@extends('layouts.admin')

@section('title', 'Arus Kas (Cash Flow) - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Top Total Financial Summary Cards (Natural, Clean Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Net Cash Flow (Hero Solid Orange Card) -->
        <div class="p-5 rounded-2xl bg-brand-500 text-white shadow-md shadow-brand-500/20 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-white/90">Arus Kas Bersih (Net Flow)</span>
                <div class="text-2xl font-black text-white font-mono-num tracking-tight">
                    {{ $netFlow >= 0 ? '+' : '-' }}Rp {{ number_format(abs($netFlow), 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-white/80 font-medium">Selisih total kas masuk & keluar</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center shrink-0">
                <i data-lucide="scale" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Cash In (Income) -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-500">Total Kas Masuk (Income)</span>
                <div class="text-2xl font-black text-emerald-600 font-mono-num tracking-tight">
                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 font-medium">Penjualan, piutang, modal, dll</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100/80">
                <i data-lucide="arrow-down-left" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Cash Out (Expense) -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-500">Total Kas Keluar (Expense)</span>
                <div class="text-2xl font-black text-rose-600 font-mono-num tracking-tight">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 font-medium">Hutang, operasional, gaji, listrik, dll</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100/80">
                <i data-lucide="arrow-up-right" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Buttons -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="arrow-down-up" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Buku Arus Kas Masuk & Keluar</h2>
                    <p class="text-xs text-slate-400">Pencatatan mutasi kas, biaya operasional, dan histori transaksi keuangan</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button 
                    onclick="openCashFlowModal('income')" 
                    type="button" 
                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>+ Kas Masuk</span>
                </button>
                <button 
                    onclick="openCashFlowModal('expense')" 
                    type="button" 
                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition cursor-pointer"
                >
                    <i data-lucide="minus-circle" class="w-4 h-4"></i>
                    <span>- Kas Keluar / Biaya</span>
                </button>
            </div>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Filter Form -->
        <form action="{{ route('cash-flows.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor / Keterangan
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: CF-2026 atau bayar listrik..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Type Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Jenis Mutasi
                </label>
                <select name="type" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Mutasi</option>
                    <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Kas Masuk (+) [Income]</option>
                    <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Kas Keluar (-) [Expense]</option>
                </select>
            </div>

            <!-- Account Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Akun Kas & Bank
                </label>
                <select name="account_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Akun</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Reset (Col 2) -->
            <div class="lg:col-span-2 flex items-center justify-end">
                @if(request()->hasAny(['search', 'type', 'account_id', 'start_date', 'end_date']))
                    <a href="{{ route('cash-flows.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- CASH FLOWS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="arrow-down-up" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Riwayat Mutasi Arus Kas</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $cashFlows->total() }} Mutasi
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Bukti Kas</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Akun Kas/Bank</th>
                        <th class="py-3 px-4 border-b border-white/10">Kategori & Keterangan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Jenis</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($cashFlows as $cf)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Bukti -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-brand-600 font-mono tracking-tight">{{ $cf->cash_flow_number }}</div>
                                <div class="text-[10px] text-slate-400">Oleh: {{ $cf->creator?->name ?? 'Sistem' }}</div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $cf->transaction_date->format('d/m/Y') }}
                            </td>

                            <!-- Akun -->
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $cf->account?->name }}
                            </td>

                            <!-- Kategori & Keterangan -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $cf->category }}</div>
                                @if($cf->description)
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $cf->description }}</div>
                                @endif
                            </td>

                            <!-- Jenis -->
                            <td class="py-3.5 px-4 text-center">
                                @if($cf->type === 'income')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i> Kas Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i> Kas Keluar
                                    </span>
                                @endif
                            </td>

                            <!-- Nominal -->
                            <td class="py-3.5 px-5 text-right font-black font-mono-num text-sm {{ $cf->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $cf->type === 'income' ? '+' : '-' }}Rp {{ number_format($cf->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="arrow-down-up" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Transaksi Arus Kas</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "+ Kas Masuk" atau "- Kas Keluar" di atas untuk mencatat mutasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cashFlows->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $cashFlows->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
    @include('finance.cashflows._create_modal')
@endpush

@push('scripts')
<script>
    // Helper to toggle realtime validation appearance
    function setFieldStatus(prefix, field, errorMsg) {
        const box = document.getElementById(`${prefix}_box_${field}`);
        const label = document.getElementById(`${prefix}_label_${field}`);
        const err = document.getElementById(`${prefix}_error_${field}`);

        if (!box || !label) return;

        if (errorMsg) {
            box.className = 'relative rounded-xl border border-rose-500 ring-2 ring-rose-500/10 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs';
            label.className = 'absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-rose-500';
            if (err) {
                err.textContent = errorMsg;
                err.className = 'mt-1 text-[11px] font-medium text-rose-500 px-1';
                err.classList.remove('hidden');
            }
        } else {
            box.className = 'relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs';
            label.className = 'absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700';
            if (err) {
                err.textContent = field === 'category' ? 'Kategori mutasi wajib diisi' : (field === 'amount' ? 'Nominal harus lebih dari Rp 0' : '');
                err.className = 'mt-1 text-[11px] text-slate-400 px-1';
                if (field === 'account_id' || field === 'date') err.classList.add('hidden');
            }
        }
    }

    function openCashFlowModal(type) {
        document.getElementById('cf_type_input').value = type;
        const isIncome = (type === 'income');

        document.getElementById('cfModalTitle').innerText = isIncome ? 'Catat Kas Masuk (+)' : 'Catat Kas Keluar / Biaya (-)';
        document.getElementById('cfModalSubtitle').innerText = isIncome ? 'Pemasukan di luar penjualan toko' : 'Pengeluaran beban operasional dan biaya harian';

        const iconBox = document.getElementById('cfModalIconContainer');
        const submitBtn = document.getElementById('cfSubmitBtn');

        if (isIncome) {
            iconBox.className = "w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100/60 shadow-2xs";
            submitBtn.className = "px-7 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/25 transition flex items-center gap-2 cursor-pointer";
        } else {
            iconBox.className = "w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100/60 shadow-2xs";
            submitBtn.className = "px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer";
        }

        // Reset fields & validation state
        setFieldStatus('cf', 'category', null);
        setFieldStatus('cf', 'amount', null);

        openModal('cashFlowModal');

        if (window.flatpickr) {
            flatpickr("#cf_date_input", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const catInput = document.getElementById('cf_category_input');
        const amtInput = document.getElementById('cf_amount_input');
        const form = document.getElementById('cashFlowForm');

        const validateCat = () => {
            if (!catInput) return true;
            const val = catInput.value.trim();
            if (!val) {
                setFieldStatus('cf', 'category', 'Kategori mutasi wajib diisi');
                return false;
            }
            setFieldStatus('cf', 'category', null);
            return true;
        };

        const validateAmt = () => {
            if (!amtInput) return true;
            const val = parseFloat(amtInput.value || 0);
            if (val <= 0 || isNaN(val)) {
                setFieldStatus('cf', 'amount', 'Nominal mutasi harus lebih dari Rp 0');
                return false;
            }
            setFieldStatus('cf', 'amount', null);
            return true;
        };

        if (catInput) {
            catInput.addEventListener('input', validateCat);
            catInput.addEventListener('blur', validateCat);
        }

        if (amtInput) {
            amtInput.addEventListener('input', validateAmt);
            amtInput.addEventListener('blur', validateAmt);
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                const v1 = validateCat();
                const v2 = validateAmt();
                if (!v1 || !v2) {
                    e.preventDefault();
                    if (!v1) catInput.focus();
                    else if (!v2) amtInput.focus();
                }
            });
        }
    });
</script>
@endpush
