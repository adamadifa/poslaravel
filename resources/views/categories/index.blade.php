@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        <form method="GET" action="{{ route('categories.index') }}" class="flex items-center gap-3 flex-1 w-full">
            <!-- Outset Floating-label Filter Input (Full Width) -->
            <div class="relative w-full rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Cari Kategori
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik nama kategori untuk mencari..." 
                           class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-0 focus:outline-none">
                    @if(request('search'))
                        <a href="{{ route('categories.index') }}" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 shrink-0">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <button onclick="openCreateCategoryModal()" class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Kategori</span>
        </button>
    </div>

    <!-- Categories Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Table Card Header (Solid Orange Theme) -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center shadow-xs">
                    <i data-lucide="tag" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white tracking-tight flex items-center gap-2">
                        <span>Daftar Kategori</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white">
                            {{ $categories->total() }} Total
                        </span>
                    </h3>
                    <p class="text-[11px] text-white/80">Daftar kategori produk yang terdaftar dalam sistem kasir POS</p>
                </div>
            </div>

            @if(request('search'))
                <div class="text-[11px] text-white flex items-center gap-1.5 bg-black/15 px-3 py-1.5 rounded-xl self-start sm:self-auto">
                    <i data-lucide="filter" class="w-3.5 h-3.5 text-white/90"></i>
                    <span>Hasil pencarian: <strong class="text-white underline decoration-white/40">"{{ request('search') }}"</strong></span>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold tracking-wider text-[10px]">
                        <th class="pt-1.5 pb-2.5 px-6">Nama Kategori</th>
                        <th class="pt-1.5 pb-2.5 px-5">Induk Kategori</th>
                        <th class="pt-1.5 pb-2.5 px-5 text-center">Jumlah Produk</th>
                        <th class="pt-1.5 pb-2.5 px-5 text-center">Status</th>
                        <th class="pt-1.5 pb-2.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium text-slate-700 dark:text-slate-200">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-6">
                                <div class="font-bold text-slate-900 dark:text-white leading-snug">{{ $category->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $category->slug }}</div>
                            </td>
                            <td class="py-2.5 px-5">
                                @if($category->parent)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $category->parent->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Kategori Utama</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-5 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $category->products_count }}
                            </td>
                            <td class="py-2.5 px-5 text-center">
                                @if($category->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditCategoryModal({{ json_encode($category) }})" class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Kategori">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @if($category->products_count === 0)
                                        <form id="delete-cat-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete-cat-{{ $category->id }}', 'Hapus Kategori {{ addslashes($category->name) }}?', 'Kategori ini akan dihapus permanen dari sistem.')" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Hapus Kategori">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                                Belum ada data kategori.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

</div>

@endsection

@push('modals')
<!-- CREATE & EDIT MODALS (Separated partials loaded into body root) -->
@include('categories._create_modal')
@include('categories._edit_modal')
@endpush

@push('scripts')
<script>
    function openCreateCategoryModal() {
        const modal = document.getElementById('createCategoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    }
    function closeCreateCategoryModal() {
        const modal = document.getElementById('createCategoryModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    function openEditCategoryModal(cat) {
        const form = document.getElementById('editCategoryForm');
        form.action = `/categories/${cat.id}`;
        
        const actionInput = document.getElementById('edit_form_action');
        if (actionInput) actionInput.value = `/categories/${cat.id}`;

        const nameInput = document.getElementById('edit_cat_name');
        nameInput.value = cat.name || '';
        document.getElementById('edit_cat_parent_id').value = cat.parent_id || '';
        document.getElementById('edit_cat_description').value = cat.description || '';
        document.getElementById('edit_cat_is_active').checked = cat.is_active ? true : false;

        // Reset edit validation state
        setFieldStatus('edit', 'name', null);

        const modal = document.getElementById('editCategoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    }
    function closeEditCategoryModal() {
        const modal = document.getElementById('editCategoryModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Helper to toggle realtime validation appearance
    function setFieldStatus(prefix, field, errorMsg) {
        const box = document.getElementById(`${prefix}_box_${field}`);
        const label = document.getElementById(`${prefix}_label_${field}`);
        const icon = document.getElementById(`${prefix}_icon_${field}`);
        const err = document.getElementById(`${prefix}_error_${field}`);

        if (!box || !label) return;

        if (errorMsg) {
            // Error state (Red border, label, and icon)
            box.className = 'relative rounded-xl border border-rose-500 ring-2 ring-rose-500/10 bg-white transition px-4 pt-3 pb-2.5';
            label.className = 'absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-rose-500';
            if (icon) icon.className = 'w-4 h-4 text-rose-500 shrink-0';
            if (err) {
                err.textContent = errorMsg;
                err.className = 'mt-1 text-[11px] font-medium text-rose-500 px-1';
            }
        } else {
            // Normal state
            box.className = 'relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5';
            label.className = 'absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700';
            if (icon) icon.className = 'w-4 h-4 text-slate-400 shrink-0';
            if (err) {
                err.textContent = field === 'name' ? (prefix === 'create' ? 'Nama kategori wajib diisi dan unik per tingkat' : 'Nama kategori wajib diisi') : '';
                err.className = 'mt-1 text-[11px] text-slate-400 px-1';
            }
        }
    }

    // Setup realtime listeners for form validation
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Create Form Realtime
        const createNameInput = document.getElementById('create_input_name');
        const createForm = document.getElementById('createCategoryForm');

        if (createNameInput) {
            const validateCreateName = () => {
                const val = createNameInput.value.trim();
                if (!val) {
                    setFieldStatus('create', 'name', 'Nama lengkap wajib diisi');
                    return false;
                } else if (val.length > 100) {
                    setFieldStatus('create', 'name', 'Nama kategori maksimal 100 karakter');
                    return false;
                } else {
                    setFieldStatus('create', 'name', null);
                    return true;
                }
            };

            createNameInput.addEventListener('input', validateCreateName);
            createNameInput.addEventListener('blur', validateCreateName);

            if (createForm) {
                createForm.addEventListener('submit', function (e) {
                    if (!validateCreateName()) {
                        e.preventDefault();
                        createNameInput.focus();
                    }
                });
            }
        }

        // 2. Edit Form Realtime
        const editNameInput = document.getElementById('edit_cat_name');
        const editForm = document.getElementById('editCategoryForm');

        if (editNameInput) {
            const validateEditName = () => {
                const val = editNameInput.value.trim();
                if (!val) {
                    setFieldStatus('edit', 'name', 'Nama lengkap wajib diisi');
                    return false;
                } else if (val.length > 100) {
                    setFieldStatus('edit', 'name', 'Nama kategori maksimal 100 karakter');
                    return false;
                } else {
                    setFieldStatus('edit', 'name', null);
                    return true;
                }
            };

            editNameInput.addEventListener('input', validateEditName);
            editNameInput.addEventListener('blur', validateEditName);

            if (editForm) {
                editForm.addEventListener('submit', function (e) {
                    if (!validateEditName()) {
                        e.preventDefault();
                        editNameInput.focus();
                    }
                });
            }
        }
    });
</script>
@endpush
