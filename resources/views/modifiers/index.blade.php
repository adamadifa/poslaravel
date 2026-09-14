@extends('layouts.admin')

@section('title', 'Menu Modifiers & Topping F&B')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
                    <i data-lucide="sliders" class="w-6 h-6"></i>
                </div>
                Menu Modifiers & Add-Ons
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Kelola pilihan kustomisasi menu seperti level pedas, opsi topping, ukuran porsi, dan tingkat gula.</p>
        </div>

        <button type="button" onclick="openCreateGroupModal()" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition flex items-center gap-2 shadow-md shadow-amber-500/20 cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Buat Grup Modifier Baru</span>
        </button>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Modifier Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($groups as $group)
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Group Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-sm text-slate-900 dark:text-white">{{ $group->name }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $group->selection_type === 'single' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $group->selection_type === 'single' ? 'Pilih 1 (Radio)' : 'Multi-Pilih' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">
                                {{ $group->is_required ? 'Wajib dipilih pelanggan' : 'Opsional (tidak wajib)' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-1">
                            <button type="button" onclick="openAttachModal({{ json_encode($group) }}, {{ json_encode($group->products->pluck('id')) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Tautkan ke Produk">
                                <i data-lucide="link" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openEditGroupModal({{ json_encode($group) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Grup">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('modifiers.groups.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Hapus grup modifier ini beserta seluruh opsinya?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Grup">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Options List -->
                    <div class="p-5 space-y-2.5">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                            <span>Pilihan Opsi</span>
                            <button type="button" onclick="openCreateModifierModal({{ $group->id }}, '{{ $group->name }}')" class="text-amber-600 hover:text-amber-700 text-xs font-bold flex items-center gap-1">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Opsi
                            </button>
                        </div>

                        @forelse($group->modifiers as $mod)
                            <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30 text-xs">
                                <div class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <span>{{ $mod->name }}</span>
                                    @if($mod->is_default)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Default</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold {{ $mod->price_adjustment > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $mod->price_adjustment > 0 ? '+Rp ' . number_format($mod->price_adjustment, 0, ',', '.') : 'Gratis' }}
                                    </span>
                                    <form action="{{ route('modifiers.destroy', $mod->id) }}" method="POST" onsubmit="return confirm('Hapus opsi ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-300 hover:text-rose-600 transition">
                                            <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada pilihan opsi di grup ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Products count footer -->
                <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20 text-[11px] text-slate-500 flex items-center justify-between">
                    <span>Tertaut ke <strong>{{ $group->products->count() }}</strong> Menu Produk</span>
                    <button type="button" onclick="openAttachModal({{ json_encode($group) }}, {{ json_encode($group->products->pluck('id')) }})" class="text-amber-600 font-bold hover:underline">
                        Kelola Produk
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
                <i data-lucide="sliders" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">Belum ada grup modifier</h3>
                <p class="text-xs text-slate-400 mt-1">Buat modifier pertama Anda untuk menambahkan opsi level pedas atau topping pada menu F&B.</p>
                <button type="button" onclick="openCreateGroupModal()" class="mt-4 px-4 py-2 bg-amber-500 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-amber-600">
                    + Buat Grup Modifier
                </button>
            </div>
        @endforelse
    </div>

</div>

<!-- Modal Buat Grup -->
<div id="createGroupModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Buat Grup Modifier Baru</h3>
            <button type="button" onclick="closeCreateGroupModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('modifiers.groups.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Grup Modifier</label>
                <input type="text" name="name" required placeholder="Contoh: Level Pedas / Topping Ekstra" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tipe Pemilihan</label>
                <select name="selection_type" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold">
                    <option value="single">Single Select (Hanya 1 Opsi, misal Level Pedas)</option>
                    <option value="multiple">Multiple Select (Bisa Banyak, misal Topping)</option>
                </select>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-bold text-slate-800 dark:text-white">Wajib Dipilih Kasir?</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_required" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateGroupModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 shadow-md shadow-amber-500/20">Simpan Grup</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Opsi Modifier -->
<div id="createModifierModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Tambah Opsi Pilihan</h3>
            <button type="button" onclick="closeCreateModifierModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('modifiers.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" id="modal_group_id" name="modifier_group_id">

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Opsi</label>
                <input type="text" name="name" required placeholder="Contoh: Level 3 (Ekstra Pedas)" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Penyesuaian Harga Tambahan (Rp)</label>
                <input type="number" name="price_adjustment" value="0" min="0" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                <span class="text-[10px] text-slate-400 mt-1 block">Isi 0 jika tidak ada biaya tambahan (gratis).</span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-bold text-slate-800 dark:text-white">Jadikan Pilihan Default?</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateModifierModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 shadow-md shadow-amber-500/20">Tambah Opsi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tautkan Produk -->
<div id="attachProductsModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4 shrink-0">
            <div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white" id="attachGroupTitle">Tautkan Produk Menu</h3>
                <p class="text-xs text-slate-400">Pilih menu mana saja yang memiliki pilihan modifier ini.</p>
            </div>
            <button type="button" onclick="closeAttachModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="attachProductsForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <div class="overflow-y-auto space-y-2 flex-1 pr-1">
                @foreach($products as $prod)
                    <label class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}" class="rounded text-amber-500 focus:ring-amber-500 product-attach-checkbox">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-white block">{{ $prod->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-slate-100 text-slate-600">{{ $prod->product_type }}</span>
                    </label>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800 mt-4 shrink-0">
                <button type="button" onclick="closeAttachModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 shadow-md shadow-amber-500/20">Simpan Tautan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateGroupModal() {
        document.getElementById('createGroupModal').classList.remove('hidden');
        document.getElementById('createGroupModal').classList.add('flex');
    }
    function closeCreateGroupModal() {
        document.getElementById('createGroupModal').classList.add('hidden');
        document.getElementById('createGroupModal').classList.remove('flex');
    }

    function openCreateModifierModal(groupId, groupName) {
        document.getElementById('modal_group_id').value = groupId;
        document.getElementById('createModifierModal').classList.remove('hidden');
        document.getElementById('createModifierModal').classList.add('flex');
    }
    function closeCreateModifierModal() {
        document.getElementById('createModifierModal').classList.add('hidden');
        document.getElementById('createModifierModal').classList.remove('flex');
    }

    function openAttachModal(group, attachedProductIds) {
        document.getElementById('attachGroupTitle').textContent = `Tautkan Produk: ${group.name}`;
        document.getElementById('attachProductsForm').action = `/modifiers/groups/${group.id}/sync-products`;

        document.querySelectorAll('.product-attach-checkbox').forEach(cb => {
            cb.checked = attachedProductIds.includes(parseInt(cb.value));
        });

        document.getElementById('attachProductsModal').classList.remove('hidden');
        document.getElementById('attachProductsModal').classList.add('flex');
    }
    function closeAttachModal() {
        document.getElementById('attachProductsModal').classList.add('hidden');
        document.getElementById('attachProductsModal').classList.remove('flex');
    }
</script>
@endsection
