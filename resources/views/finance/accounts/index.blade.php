@extends('layouts.admin')

@section('title', 'Akun Kas & Bank - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Top Total Financial Balance Cards (Clean, Natural & Elegant Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Total All Liquid Assets (Primary Solid Orange Accent Card) -->
        <div class="p-5 rounded-2xl bg-brand-500 text-white shadow-md shadow-brand-500/20 flex items-center justify-between transition-all hover:bg-brand-600">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-white/90">Total Kas & Bank Likuid</span>
                <div class="text-2xl font-black text-white font-mono-num tracking-tight">
                    Rp {{ number_format($totalBalance, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-white/80 font-medium">Akumulasi seluruh akun aktif</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center shrink-0">
                <i data-lucide="wallet-cards" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Cash -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between transition-all hover:border-slate-300">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-500">Saldo Kas Fisik</span>
                <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                    Rp {{ number_format($totalCash, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-emerald-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Kas laci kasir & kas kecil</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100/80">
                <i data-lucide="banknote" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Bank Accounts -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between transition-all hover:border-slate-300">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-500">Saldo Rekening Bank</span>
                <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                    Rp {{ number_format($totalBank, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-blue-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>Rekening operasional bisnis</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/80">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Daftar Akun Kas & Bank</h2>
                    <p class="text-xs text-slate-400">Kelola akun kasir, kas operasional, dan rekening bank bisnis</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateAccountModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Akun Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Filter Form -->
        <form action="{{ route('accounts.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search (Col 7) -->
            <div class="lg:col-span-7 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nama Akun / No. Rekening
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: BCA, Kasir Utama, atau nomor rek..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Type (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Tipe Akun
                </label>
                <select name="type" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Tipe Akun</option>
                    <option value="cash" {{ request('type') === 'cash' ? 'selected' : '' }}>Kas Fisik (Cash)</option>
                    <option value="bank" {{ request('type') === 'bank' ? 'selected' : '' }}>Rekening Bank (Bank)</option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Lainnya / E-Wallet</option>
                </select>
            </div>

            <!-- Reset (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-center">
                @if(request()->hasAny(['search', 'type']))
                    <a href="{{ route('accounts.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- ACCOUNTS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="wallet" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Akun Kas & Rekening Bank</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $accounts->total() }} Akun
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Kode & Nama Akun</th>
                        <th class="py-3 px-4 border-b border-white/10">Tipe Akun</th>
                        <th class="py-3 px-4 border-b border-white/10">No. Rekening / Bank</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Saldo Saat Ini</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Default Kasir</th>
                        <th class="py-3 px-5 text-right w-36 border-b border-white/10">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Kode & Nama Akun -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-900 flex items-center gap-2">
                                    <span>{{ $acc->name }}</span>
                                    @if($acc->is_default)
                                        <span class="px-2 py-0.5 rounded-md bg-brand-50 text-brand-600 border border-brand-200 text-[10px] font-bold">Utama</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $acc->account_code }}</div>
                            </td>

                            <!-- Tipe Akun -->
                            <td class="py-3.5 px-4">
                                @if($acc->type === 'cash')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="banknote" class="w-3 h-3"></i> Kas Fisik
                                    </span>
                                @elseif($acc->type === 'bank')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i data-lucide="building-2" class="w-3 h-3"></i> Bank
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Lainnya
                                    </span>
                                @endif
                            </td>

                            <!-- No Rekening / Bank -->
                            <td class="py-3.5 px-4">
                                @if($acc->type === 'bank')
                                    <div class="font-bold text-slate-800">{{ $acc->bank_name ?? 'Bank' }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $acc->account_number ?? '-' }}</div>
                                @else
                                    <span class="text-slate-400 italic">Kas Tunai</span>
                                @endif
                            </td>

                            <!-- Saldo Saat Ini -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-black text-sm font-mono-num {{ $acc->current_balance < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                    Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    Awal: Rp {{ number_format($acc->opening_balance, 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- Default Kasir -->
                            <td class="py-3.5 px-4 text-center">
                                @if($acc->is_default)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-bold text-xs">
                                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Default
                                    </span>
                                @else
                                    <form action="{{ route('accounts.default', $acc->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-slate-400 hover:text-brand-600 transition cursor-pointer">
                                            Set Default
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Edit Button -->
                                    <button type="button" onclick="openEditAccountModal({{ $acc->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition cursor-pointer" title="Edit Akun">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    @if(!$acc->is_default)
                                        <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST" class="inline" id="delete_acc_{{ $acc->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete_acc_{{ $acc->id }}', 'Hapus Akun {{ $acc->name }}?', 'Data akun ini akan dihapus dari sistem.')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Akun">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="wallet" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Akun Kas / Bank</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Tambah Akun Baru" di atas untuk menambahkan akun keuangan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>

</div>

@push('modals')
    @include('finance.accounts._create_modal')
@endpush

@endsection
