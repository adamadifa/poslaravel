@extends('layouts.admin')

@section('title', 'Audit Trail & Rekam Jejak Aktivitas')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/20">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                Audit Trail & Aktivitas
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Rekam jejak setiap manipulasi data, perubahan inventori, transaksi, dan sesi login staf.</p>
        </div>
    </div>

    <!-- Filter Card with Outset Floating Labels -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <form method="GET" action="{{ route('audit-trails.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <!-- Search Keyword -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Cari Deskripsi / User</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
            </div>

            <!-- Filter User -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Pengguna (Staf)</label>
                <select name="user_id" class="w-full border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
                    <option value="">Semua Staf</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Action -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Aksi / Event</label>
                <select name="action" class="w-full border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none capitalize">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal Mulai -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white bg-transparent focus:ring-0 focus:outline-none">
            </div>

            <!-- Actions Button -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('audit-trails.index') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white font-bold text-xs uppercase tracking-wider">
                        <th class="px-5 py-3.5">Waktu & Tanggal</th>
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-5 py-3.5">Aksi</th>
                        <th class="px-5 py-3.5">Aktivitas & Deskripsi</th>
                        <th class="px-5 py-3.5">IP Address</th>
                        <th class="px-5 py-3.5 text-center">Detail Diff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($auditTrails as $trail)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">
                                <div class="font-bold text-slate-700 dark:text-slate-200">{{ $trail->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trail->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap font-bold text-slate-800 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 font-extrabold flex items-center justify-center text-[10px]">
                                        {{ strtoupper(substr($trail->user_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span>{{ $trail->user_name ?? ($trail->user->name ?? 'System') }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($trail->action === 'created')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Created</span>
                                @elseif($trail->action === 'updated')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Updated</span>
                                @elseif($trail->action === 'deleted')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Deleted</span>
                                @elseif($trail->action === 'login')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Login</span>
                                @elseif($trail->action === 'logout')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Logout</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800">{{ $trail->action }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
                                <div>{{ $trail->description }}</div>
                                @if($trail->auditable_type)
                                    <span class="text-[10px] text-slate-400 font-mono">Model: {{ class_basename($trail->auditable_type) }} #{{ $trail->auditable_id }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                {{ $trail->ip_address ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                @if($trail->old_values || $trail->new_values)
                                    <button type="button" onclick="showAuditModal({{ $trail->id }})" class="p-2 rounded-xl bg-brand-50 dark:bg-brand-950/40 text-brand-600 dark:text-brand-400 hover:bg-brand-100 transition font-bold" title="Lihat Perubahan">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 stroke-1"></i>
                                <div class="font-bold text-slate-600 dark:text-slate-300">Belum ada rekam jejak aktivitas</div>
                                <div class="text-[11px] text-slate-400">Aktivitas pengguna akan dicatat secara otomatis di sini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditTrails->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $auditTrails->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Audit Diff Details -->
<div id="auditModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-brand-500 text-white">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-black text-base text-slate-800 dark:text-white" id="modalTitle">Detail Perubahan Data</h3>
                    <p class="text-xs text-slate-400" id="modalSubtitle">Perbandingan nilai sebelum & sesudah</p>
                </div>
            </div>
            <button type="button" onclick="closeAuditModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-4 flex-1">
            <div id="diffContent" class="space-y-4">
                <!-- Injected by JavaScript -->
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="button" onclick="closeAuditModal()" class="px-5 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-300 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function showAuditModal(id) {
        fetch(`/audit-trails/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalTitle').textContent = `${data.action.toUpperCase()} — ${data.description}`;
                document.getElementById('modalSubtitle').textContent = `Oleh ${data.user_name} pada ${data.created_at} (IP: ${data.ip_address || 'N/A'})`;

                let html = '';

                if (data.action === 'updated') {
                    html += `
                        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden text-xs">
                            <div class="grid grid-cols-3 bg-slate-100 dark:bg-slate-800 font-bold p-3 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
                                <div>Kolom / Atribut</div>
                                <div class="text-rose-600">Nilai Sebelum (Old)</div>
                                <div class="text-emerald-600">Nilai Sesudah (New)</div>
                            </div>
                            <div class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                    `;

                    for (let key in data.new_values) {
                        let oldVal = data.old_values && data.old_values[key] !== undefined ? JSON.stringify(data.old_values[key]) : '<span class="text-slate-400">null</span>';
                        let newVal = JSON.stringify(data.new_values[key]);

                        html += `
                            <div class="grid grid-cols-3 p-3 items-center">
                                <div class="font-bold text-slate-800 dark:text-slate-200 font-sans">${key}</div>
                                <div class="text-rose-600 break-words bg-rose-50/50 dark:bg-rose-950/30 p-1.5 rounded-lg">${oldVal}</div>
                                <div class="text-emerald-600 break-words bg-emerald-50/50 dark:bg-emerald-950/30 p-1.5 rounded-lg">${newVal}</div>
                            </div>
                        `;
                    }

                    html += `</div></div>`;
                } else if (data.action === 'created') {
                    html += `
                        <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-xs">
                            <div class="font-bold text-emerald-800 dark:text-emerald-300 mb-2">Data Baru Ditambahkan:</div>
                            <pre class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-emerald-100 font-mono overflow-x-auto text-slate-700 dark:text-slate-300">${JSON.stringify(data.new_values, null, 2)}</pre>
                        </div>
                    `;
                } else if (data.action === 'deleted') {
                    html += `
                        <div class="p-4 rounded-2xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-xs">
                            <div class="font-bold text-rose-800 dark:text-rose-300 mb-2">Data Yang Dihapus (Snapshot):</div>
                            <pre class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-rose-100 font-mono overflow-x-auto text-slate-700 dark:text-slate-300">${JSON.stringify(data.old_values, null, 2)}</pre>
                        </div>
                    `;
                }

                document.getElementById('diffContent').innerHTML = html;
                document.getElementById('auditModal').classList.remove('hidden');
            });
    }

    function closeAuditModal() {
        document.getElementById('auditModal').classList.add('hidden');
    }
</script>
@endsection
