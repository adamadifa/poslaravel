@extends('layouts.admin')

@section('title', 'Manajemen Meja & Denah F&B')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
                    <i data-lucide="layout-grid" class="w-6 h-6"></i>
                </div>
                Manajemen Meja & Denah
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Kelola daftar nomor meja, kapasitas, tata letak area, dan status ketersediaan resto.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('tables.reservations') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-bold transition flex items-center gap-2 shadow-xs">
                <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
                <span>Jadwal Reservasi</span>
            </a>
            <button type="button" onclick="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition flex items-center gap-2 shadow-md shadow-amber-500/20 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Meja Baru</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 flex items-center gap-3 shadow-xs">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
            <span class="text-sm font-bold">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Total Meja</span>
                <i data-lucide="grid" class="w-4 h-4 text-slate-500"></i>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalTables }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-emerald-600 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Tersedia</span>
                <i data-lucide="check" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $availableCount }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-rose-600 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Terisi (Occupied)</span>
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-black text-rose-600">{{ $occupiedCount }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-amber-600 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Dipesan (Reserved)</span>
                <i data-lucide="bookmark" class="w-4 h-4"></i>
            </div>
            <div class="text-2xl font-black text-amber-600">{{ $reservedCount }}</div>
        </div>
    </div>

    <!-- Filter Bar & Area Tabs -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Area Selector Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('tables.index', ['warehouse_id' => $warehouseId]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ !$selectedArea ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                Semua Area
            </a>
            @foreach($areas as $area)
                <a href="{{ route('tables.index', ['warehouse_id' => $warehouseId, 'area' => $area]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $selectedArea == $area ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    {{ $area }}
                </a>
            @endforeach
        </div>

        <!-- Outlet / Gudang Filter -->
        <form method="GET" action="{{ route('tables.index') }}" class="flex items-center gap-2">
            @if($selectedArea)
                <input type="hidden" name="area" value="{{ $selectedArea }}">
            @endif
            <select name="warehouse_id" onchange="this.form.submit()" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-xs font-bold text-slate-800 dark:text-white focus:ring-1 focus:ring-amber-500 cursor-pointer">
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Visual Table Grid (Floor Plan View) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        @forelse($tables as $table)
            <div class="relative bg-white dark:bg-slate-900 border rounded-2xl p-4 shadow-xs transition-all hover:shadow-md flex flex-col justify-between {{ $table->status === 'available' ? 'border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/10' : ($table->status === 'occupied' ? 'border-rose-300 dark:border-rose-900/40 bg-rose-50/20' : ($table->status === 'reserved' ? 'border-amber-300 dark:border-amber-900/40 bg-amber-50/20' : 'border-slate-300 opacity-60')) }}">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $table->status === 'available' ? 'bg-emerald-100 text-emerald-800' : ($table->status === 'occupied' ? 'bg-rose-100 text-rose-800' : ($table->status === 'reserved' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                            {{ $table->status }}
                        </span>
                        <span class="text-[11px] font-bold text-slate-400 flex items-center gap-1">
                            <i data-lucide="user" class="w-3 h-3"></i> {{ $table->capacity }}
                        </span>
                    </div>

                    <div class="font-black text-xl text-slate-900 dark:text-white my-1 tracking-tight">
                        {{ $table->table_number }}
                    </div>
                    <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                        {{ $table->area }}
                    </div>

                    @if($table->status === 'occupied' && $table->currentSale)
                        <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px]">
                            <div class="font-bold text-slate-800 dark:text-slate-200 truncate">
                                {{ $table->currentSale->customer?->name ?? 'Tamu Walk-in' }}
                            </div>
                            <div class="font-mono text-rose-600 font-bold">
                                Rp {{ number_format($table->currentSale->grand_total, 0, ',', '.') }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Action Toolbar -->
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <button type="button" onclick="openEditModal({{ json_encode($table) }})" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition" title="Edit Meja">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    </button>

                    <form action="{{ route('tables.status', $table->id) }}" method="POST" class="inline">
                        @csrf
                        @if($table->status === 'occupied')
                            <button type="submit" name="status" value="available" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition" title="Kosongkan Meja">
                                Free
                            </button>
                        @elseif($table->status === 'available')
                            <button type="submit" name="status" value="maintenance" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 hover:bg-slate-300 transition" title="Set Maintenance">
                                Set Maint.
                            </button>
                        @elseif($table->status === 'maintenance')
                            <button type="submit" name="status" value="available" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition" title="Aktifkan Meja">
                                Buka
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
                <i data-lucide="layout-grid" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">Belum ada data meja</h3>
                <p class="text-xs text-slate-400 mt-1">Tambahkan meja pertama Anda untuk memulai manajemen meja F&B.</p>
                <button type="button" onclick="openCreateModal()" class="mt-4 px-4 py-2 bg-amber-500 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-amber-600">
                    + Tambah Meja
                </button>
            </div>
        @endforelse
    </div>

</div>

<!-- Modal Tambah Meja -->
<div id="createTableModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Tambah Meja Resto</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('tables.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Outlet / Cabang</label>
                <select name="warehouse_id" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor / Kode Meja</label>
                    <input type="text" name="table_number" required placeholder="Contoh: M-01" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kapasitas (Kursi)</label>
                    <input type="number" name="capacity" value="4" min="1" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Area / Ruangan</label>
                <input type="text" name="area" value="Indoor" required placeholder="Indoor / Outdoor / VIP" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 shadow-md shadow-amber-500/20">Simpan Meja</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Meja -->
<div id="editTableModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Edit Meja Resto</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="editTableForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Outlet / Cabang</label>
                <select id="edit_warehouse_id" name="warehouse_id" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor / Kode Meja</label>
                    <input type="text" id="edit_table_number" name="table_number" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kapasitas (Kursi)</label>
                    <input type="number" id="edit_capacity" name="capacity" min="1" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Area / Ruangan</label>
                    <input type="text" id="edit_area" name="area" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status Meja</label>
                    <select id="edit_status" name="status" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                        <option value="available">Available (Tersedia)</option>
                        <option value="occupied">Occupied (Terisi)</option>
                        <option value="reserved">Reserved (Dipesan)</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" id="deleteTableBtn" onclick="deleteTable()" class="text-rose-600 hover:text-rose-700 text-xs font-bold flex items-center gap-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Meja
                </button>
                <div class="flex gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 shadow-md shadow-amber-500/20">Perbarui Meja</button>
                </div>
            </div>
        </form>
        <form id="deleteTableForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createTableModal').classList.remove('hidden');
        document.getElementById('createTableModal').classList.add('flex');
    }
    function closeCreateModal() {
        document.getElementById('createTableModal').classList.add('hidden');
        document.getElementById('createTableModal').classList.remove('flex');
    }

    let currentEditingTableId = null;

    function openEditModal(table) {
        currentEditingTableId = table.id;
        document.getElementById('editTableForm').action = `/tables/${table.id}`;
        document.getElementById('edit_warehouse_id').value = table.warehouse_id;
        document.getElementById('edit_table_number').value = table.table_number;
        document.getElementById('edit_capacity').value = table.capacity;
        document.getElementById('edit_area').value = table.area;
        document.getElementById('edit_status').value = table.status;

        document.getElementById('editTableModal').classList.remove('hidden');
        document.getElementById('editTableModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editTableModal').classList.add('hidden');
        document.getElementById('editTableModal').classList.remove('flex');
    }

    function deleteTable() {
        if (confirm('Apakah Anda yakin ingin menghapus meja ini?')) {
            const form = document.getElementById('deleteTableForm');
            form.action = `/tables/${currentEditingTableId}`;
            form.submit();
        }
    }
</script>
@endsection
