@extends('layouts.admin')

@section('title', 'Laporan Rekap Shift Kasir')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Rekap Shift Kasir (POS)',
        'subtitle' => 'Audit pembukaan/penutupan shift kasir, saldo awal kas kecil, akumulasi penjualan, dan verifikasi selisih fisik uang kas.',
        'exportExcelUrl' => route('reports.cashier-shifts.export-excel', request()->all()),
        'exportPdfUrl' => route('reports.cashier-shifts.export-pdf', request()->all()),
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Penjualan Shift</p>
            <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalShiftSales, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Dari seluruh sesi kasir</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Laba Jasa Agen & PPOB</p>
            <h3 class="text-xl font-black text-emerald-600 mt-1.5">+Rp {{ number_format($totalAgentProfit, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">In: Rp {{ number_format($totalAgentCashIn, 0, ',', '.') }} | Out: Rp {{ number_format($totalAgentCashOut, 0, ',', '.') }}</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Biaya / Kas Keluar</p>
            <h3 class="text-xl font-black text-rose-600 mt-1.5">Rp {{ number_format($totalShiftExpenses, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Pengeluaran operasional kasir</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Sesi Shift Kasir</p>
            <h3 class="text-xl font-black text-blue-600 mt-1.5">{{ number_format($totalShiftCount, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Sesi</span></h3>
            <p class="text-[11px] text-slate-500 mt-2">Sesi buka & tutup kasir</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Selisih Fisik Kas</p>
            <h3 class="text-xl font-black {{ $totalCashDifference < 0 ? 'text-rose-600' : ($totalCashDifference > 0 ? 'text-emerald-600' : 'text-slate-900') }} mt-1.5">
                {{ $totalCashDifference < 0 ? '-Rp ' : ($totalCashDifference > 0 ? '+Rp ' : 'Rp ') }}{{ number_format(abs($totalCashDifference), 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-slate-500 mt-2">{{ $totalCashDifference != 0 ? 'Terdapat selisih kas fisik' : 'Fisik kas 100% seimbang' }}</p>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.cashier-shifts') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Dari Tanggal (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Dari Tanggal
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- Sampai Tanggal (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Sampai Tanggal
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- Kasir (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Kasir / User
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="user_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $c)
                            <option value="{{ $c->id }}" {{ $userId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Gudang + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Gudang / Cabang
                    </label>
                    <div class="flex items-center gap-2">
                        <i data-lucide="warehouse" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <select name="warehouse_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Semua Cabang</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if(request()->hasAny(['start_date', 'end_date', 'user_id', 'warehouse_id']))
                    <a href="{{ route('reports.cashier-shifts') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- CASHIER SHIFTS TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="user-check" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Rekap Sesi Shift Kasir</h3>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-white/20 text-white font-bold text-xs">
                {{ $shifts->total() }} Sesi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Kasir & Cabang</th>
                        <th class="py-3 px-4 border-b border-white/10">Waktu Buka / Tutup</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Modal Awal</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Penjualan Kas</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Agen & PPOB</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Biaya / Kas Keluar</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Fisik Kas Tutup</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Selisih Fisik</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shifts as $sh)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5">
                            <div class="font-bold text-slate-900">{{ $sh->user->name ?? 'Kasir' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $sh->warehouse->name ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            <div>Buka: {{ $sh->opened_at ? $sh->opened_at->format('d/m/Y H:i') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">Tutup: {{ $sh->closed_at ? $sh->closed_at->format('d/m/Y H:i') : 'Masih Terbuka' }}</div>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($sh->starting_cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($sh->total_sales, 0, ',', '.') }}
                            <div class="text-[10px] font-normal text-slate-400">{{ $sh->total_transactions }} Struk</div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="font-bold text-emerald-600">
                                +Rp {{ number_format($sh->total_agent_profit ?? 0, 0, ',', '.') }}
                            </div>
                            @if($sh->agentTransactions->isNotEmpty())
                                <button type="button" onclick="showShiftAgentModal({{ json_encode($sh->agentTransactions) }}, '{{ $sh->user->name ?? 'Kasir' }}', '{{ $sh->opened_at ? $sh->opened_at->format('d/m/Y H:i') : '-' }}', {{ (float) ($sh->total_agent_cash_in ?? 0) }}, {{ (float) ($sh->total_agent_cash_out ?? 0) }}, {{ (float) ($sh->total_agent_profit ?? 0) }})" class="mt-0.5 inline-flex items-center gap-1 text-[10px] font-bold text-purple-600 hover:text-purple-700 hover:underline cursor-pointer">
                                    <span>{{ $sh->agentTransactions->count() }} Transaksi</span>
                                    <i data-lucide="info" class="w-3 h-3"></i>
                                </button>
                            @else
                                <div class="text-[10px] text-slate-400">0 Transaksi</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="font-bold {{ ($sh->total_expenses ?? 0) > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                Rp {{ number_format($sh->total_expenses ?? 0, 0, ',', '.') }}
                            </div>
                            @if($sh->expenses->isNotEmpty())
                                <button type="button" onclick="showShiftExpensesModal({{ json_encode($sh->expenses) }}, '{{ $sh->user->name ?? 'Kasir' }}', '{{ $sh->opened_at ? $sh->opened_at->format('d/m/Y H:i') : '-' }}')" class="mt-0.5 inline-flex items-center gap-1 text-[10px] font-bold text-brand-600 hover:text-brand-700 hover:underline cursor-pointer">
                                    <span>{{ $sh->expenses->count() }} Pengeluaran</span>
                                    <i data-lucide="info" class="w-3 h-3"></i>
                                </button>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-slate-800 font-medium">
                            {{ $sh->closing_cash !== null ? 'Rp ' . number_format($sh->closing_cash, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-black font-mono-num {{ $sh->cash_difference < 0 ? 'text-rose-600' : ($sh->cash_difference > 0 ? 'text-emerald-600' : 'text-slate-400') }}">
                            @if($sh->cash_difference !== null)
                                {{ $sh->cash_difference < 0 ? '-Rp ' : ($sh->cash_difference > 0 ? '+Rp ' : 'Rp ') }}{{ number_format(abs($sh->cash_difference), 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-3 px-5 text-center">
                            @if($sh->status === 'closed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700">Ditutup</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700">Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <i data-lucide="user-check" class="w-10 h-10 text-slate-300 mb-2 mx-auto"></i>
                            <p class="font-bold text-sm text-slate-600">Tidak ada sesi shift kasir pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>
</div>

<!-- MODAL RINCIAN BIAYA / PENGELUARAN SHIFT KASIR -->
<div id="shiftExpensesDetailModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight" id="detailModalTitle">Rincian Biaya Kasir</h3>
                    <p class="text-[11px] text-slate-500" id="detailModalSubtitle">Daftar pengeluaran selama shift</p>
                </div>
            </div>
            <button onclick="closeShiftExpensesModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Body Table -->
        <div class="p-6 overflow-y-auto">
            <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">No</th>
                            <th class="py-2.5 px-3">Kategori & Keterangan</th>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="detailModalTableBody" class="divide-y divide-slate-100">
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="3" class="py-2.5 px-3 text-right text-slate-700">Total Pengeluaran:</td>
                            <td class="py-2.5 px-3 text-right font-black text-rose-600" id="detailModalTotalAmount">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end shrink-0">
            <button type="button" onclick="closeShiftExpensesModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL RINCIAN TRANSAKSI AGEN & PPOB SHIFT KASIR -->
<div id="shiftAgentDetailModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[88vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-purple-50/70 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-xs">
                    <i data-lucide="repeat" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight" id="agentModalTitle">Rincian Transaksi Agen & PPOB</h3>
                    <p class="text-[11px] text-slate-500" id="agentModalSubtitle">Daftar transaksi transfer, tarik tunai, dan produk digital</p>
                </div>
            </div>
            <button onclick="closeShiftAgentModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Summary Bar -->
        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 grid grid-cols-3 gap-2 text-xs shrink-0">
            <div class="p-2.5 rounded-xl bg-white border border-slate-200">
                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Kas Masuk (In)</span>
                <span class="font-bold text-slate-900 font-mono-num" id="agentModalCashIn">Rp 0</span>
            </div>
            <div class="p-2.5 rounded-xl bg-white border border-slate-200">
                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Kas Keluar (Out)</span>
                <span class="font-bold text-rose-600 font-mono-num" id="agentModalCashOut">Rp 0</span>
            </div>
            <div class="p-2.5 rounded-xl bg-white border border-slate-200">
                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Laba Bersih Toko</span>
                <span class="font-bold text-emerald-600 font-mono-num" id="agentModalNetProfit">Rp 0</span>
            </div>
        </div>

        <!-- Body Table -->
        <div class="p-6 overflow-y-auto">
            <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Tipe Layanan</th>
                            <th class="py-2.5 px-3">Tujuan / Produk</th>
                            <th class="py-2.5 px-3">Akun Sumber</th>
                            <th class="py-2.5 px-3 text-right">Nominal</th>
                            <th class="py-2.5 px-3 text-right">Laba Toko</th>
                        </tr>
                    </thead>
                    <tbody id="agentModalTableBody" class="divide-y divide-slate-100">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end shrink-0">
            <button type="button" onclick="closeShiftAgentModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showShiftExpensesModal(expenses, cashierName, openedAt) {
        const modal = document.getElementById('shiftExpensesDetailModal');
        const title = document.getElementById('detailModalTitle');
        const subtitle = document.getElementById('detailModalSubtitle');
        const tbody = document.getElementById('detailModalTableBody');
        const totalElem = document.getElementById('detailModalTotalAmount');

        title.innerText = `Rincian Biaya Kasir: ${cashierName}`;
        subtitle.innerText = `Sesi Shift: ${openedAt}`;

        let total = 0;
        let html = '';

        if (!expenses || expenses.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-slate-400">Tidak ada rincian pengeluaran.</td></tr>';
            totalElem.innerText = 'Rp 0';
        } else {
            expenses.forEach((exp, idx) => {
                const amount = parseFloat(exp.amount) || 0;
                total += amount;
                const timeStr = exp.expense_date ? new Date(exp.expense_date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-';

                html += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3 text-slate-500">${idx + 1}</td>
                        <td class="py-2.5 px-3">
                            <div class="font-bold text-slate-900">${exp.category || 'Operasional'}</div>
                            <div class="text-[11px] text-slate-500">${exp.notes || '-'}</div>
                        </td>
                        <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">${timeStr}</td>
                        <td class="py-2.5 px-3 text-right font-black text-rose-600 font-mono-num whitespace-nowrap">
                            Rp ${parseInt(amount).toLocaleString('id-ID')}
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            totalElem.innerText = `Rp ${parseInt(total).toLocaleString('id-ID')}`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeShiftExpensesModal() {
        const modal = document.getElementById('shiftExpensesDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function showShiftAgentModal(transactions, cashierName, openedAt, cashIn, cashOut, netProfit) {
        const modal = document.getElementById('shiftAgentDetailModal');
        document.getElementById('agentModalTitle').innerText = `Transaksi Agen & PPOB: ${cashierName}`;
        document.getElementById('agentModalSubtitle').innerText = `Sesi Shift: ${openedAt}`;
        document.getElementById('agentModalCashIn').innerText = `Rp ${parseInt(cashIn || 0).toLocaleString('id-ID')}`;
        document.getElementById('agentModalCashOut').innerText = `Rp ${parseInt(cashOut || 0).toLocaleString('id-ID')}`;
        document.getElementById('agentModalNetProfit').innerText = `+Rp ${parseInt(netProfit || 0).toLocaleString('id-ID')}`;

        const tbody = document.getElementById('agentModalTableBody');
        let html = '';

        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-400">Tidak ada data transaksi agen.</td></tr>';
        } else {
            transactions.forEach((tx) => {
                let badge = '';
                if (tx.service_type === 'BANK_TRANSFER') {
                    badge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">Transfer</span>';
                } else if (tx.service_type === 'CASH_WITHDRAWAL') {
                    badge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Tarik Tunai</span>';
                } else {
                    badge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700">PPOB / Pulsa</span>';
                }

                const accName = tx.account ? tx.account.name : '-';
                const target = tx.destination_target || tx.product_code || '-';
                const holder = tx.destination_holder ? ` (${tx.destination_holder})` : '';

                html += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3">${badge}</td>
                        <td class="py-2.5 px-3">
                            <div class="font-bold text-slate-900">${target}${holder}</div>
                            <div class="text-[10px] text-slate-400 font-mono">${tx.transaction_number}</div>
                        </td>
                        <td class="py-2.5 px-3 text-slate-600">${accName}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900 font-mono-num whitespace-nowrap">
                            Rp ${parseInt(tx.principal_amount || 0).toLocaleString('id-ID')}
                        </td>
                        <td class="py-2.5 px-3 text-right font-black text-emerald-600 font-mono-num whitespace-nowrap">
                            +Rp ${parseInt(tx.net_profit || 0).toLocaleString('id-ID')}
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeShiftAgentModal() {
        const modal = document.getElementById('shiftAgentDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
@endsection
