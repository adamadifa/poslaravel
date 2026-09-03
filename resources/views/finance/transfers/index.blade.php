@extends('layouts.admin')

@section('title', 'Transfer Antar Kas & Bank - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Top Total Financial Summary Cards (Natural, Clean Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Total Transfers (Hero Solid Orange Card) -->
        <div class="p-5 rounded-2xl bg-brand-500 text-white shadow-md shadow-brand-500/20 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-white/90">Total Akumulasi Mutasi Transfer</span>
                <div class="text-2xl font-black text-white font-mono-num tracking-tight">
                    Rp {{ number_format($totalTransferred, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-white/80 font-medium">Pemindahan saldo internal antar kas & rekening bank</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center shrink-0">
                <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Info Card -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-500">Mutasi Buku Kas Otomatis</span>
                <div class="text-sm font-bold text-slate-800 leading-snug">
                    Setoran Tunai Toko & Tarik Kas Operasional
                </div>
                <div class="text-[11px] text-slate-400 font-medium">Biaya admin transfer antar bank tercatat otomatis</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 border border-brand-100/80">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Transfer Saldo Antar Kas & Bank</h2>
                    <p class="text-xs text-slate-400">Pindahkan saldo kas toko ke rekening bank (setoran) atau tarik kas kecil</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openTransferModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Transfer Saldo Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Filter Form -->
        <form action="{{ route('account-transfers.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor / Referensi
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: TRF-ACC-2026 atau catatan..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- From Account (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Dari Akun Asal
                </label>
                <select name="from_account_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Akun Asal</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('from_account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- To Account (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Ke Akun Tujuan
                </label>
                <select name="to_account_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Akun Tujuan</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('to_account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Reset (Col 2) -->
            <div class="lg:col-span-2 flex items-center justify-end">
                @if(request()->hasAny(['search', 'from_account_id', 'to_account_id', 'start_date', 'end_date']))
                    <a href="{{ route('account-transfers.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- TRANSFERS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="arrow-left-right" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Mutasi Transfer Kas & Bank</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $transfers->total() }} Mutasi
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Transfer</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Rute Akun (Asal $\rightarrow$ Tujuan)</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Biaya Admin</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Nominal Transfer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transfers as $trf)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Transfer -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-brand-600 font-mono tracking-tight">{{ $trf->transfer_number }}</div>
                                <div class="text-[10px] text-slate-400">Oleh: {{ $trf->creator?->name ?? 'Admin' }}</div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $trf->transfer_date->format('d/m/Y') }}
                            </td>

                            <!-- Rute Akun -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2 font-bold text-slate-800">
                                    <span>{{ $trf->fromAccount?->name }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                                    <span class="text-brand-700">{{ $trf->toAccount?->name }}</span>
                                </div>
                                @if($trf->notes)
                                    <div class="text-[10px] text-slate-400 italic mt-0.5">{{ $trf->notes }}</div>
                                @endif
                            </td>

                            <!-- Biaya Admin -->
                            <td class="py-3.5 px-4 text-right font-mono-num font-semibold text-slate-500">
                                {{ $trf->transfer_fee > 0 ? 'Rp ' . number_format($trf->transfer_fee, 0, ',', '.') : '-' }}
                            </td>

                            <!-- Nominal Transfer -->
                            <td class="py-3.5 px-5 text-right font-black font-mono-num text-sm text-slate-900">
                                Rp {{ number_format($trf->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i data-lucide="arrow-left-right" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Mutasi Transfer Kas / Bank</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Transfer Saldo Baru" di atas untuk memindahkan dana antar rekening.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
    @include('finance.transfers._create_modal')
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
                err.textContent = field === 'amount' ? 'Nominal transfer harus lebih dari Rp 0' : '';
                err.className = 'mt-1 text-[11px] text-slate-400 px-1';
                if (field !== 'amount') err.classList.add('hidden');
            }
        }
    }

    function openTransferModal() {
        // Reset fields & validation state
        setFieldStatus('trf', 'from_account', null);
        setFieldStatus('trf', 'to_account', null);
        setFieldStatus('trf', 'amount', null);

        openModal('transferModal');

        if (window.flatpickr) {
            flatpickr("#trf_date_input", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const fromSelect = document.getElementById('trf_from_account_select');
        const toSelect = document.getElementById('trf_to_account_select');
        const amtInput = document.getElementById('trf_amount_input');
        const form = document.getElementById('transferForm');

        const validateAccounts = () => {
            if (!fromSelect || !toSelect) return true;
            if (fromSelect.value && toSelect.value && fromSelect.value === toSelect.value) {
                setFieldStatus('trf', 'to_account', 'Akun tujuan tidak boleh sama dengan akun asal');
                return false;
            }
            setFieldStatus('trf', 'to_account', null);
            return true;
        };

        const validateAmt = () => {
            if (!amtInput) return true;
            const val = parseFloat(amtInput.value || 0);
            if (val <= 0 || isNaN(val)) {
                setFieldStatus('trf', 'amount', 'Nominal transfer harus lebih dari Rp 0');
                return false;
            }
            setFieldStatus('trf', 'amount', null);
            return true;
        };

        if (fromSelect) fromSelect.addEventListener('change', validateAccounts);
        if (toSelect) toSelect.addEventListener('change', validateAccounts);

        if (amtInput) {
            amtInput.addEventListener('input', validateAmt);
            amtInput.addEventListener('blur', validateAmt);
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                const v1 = validateAccounts();
                const v2 = validateAmt();
                if (!v1 || !v2) {
                    e.preventDefault();
                    if (!v1) toSelect.focus();
                    else if (!v2) amtInput.focus();
                }
            });
        }
    });
</script>
@endpush
