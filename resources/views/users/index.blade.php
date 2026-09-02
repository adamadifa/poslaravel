@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <!-- Search -->
            <div class="relative flex-1 min-w-[220px]">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
            </div>

            <!-- Role Filter -->
            <select name="role" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none">
                <option value="">Semua Peran (Role)</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </option>
                @endforeach
            </select>

            @if(request('search') || request('role'))
                <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Add User Modal Trigger -->
        <button onclick="openCreateUserModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Pengguna</span>
        </button>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-semibold text-rose-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
    @endif

    <!-- Users Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-400 uppercase font-bold tracking-wider text-[10px]">
                        <th class="py-3.5 px-5">Pengguna</th>
                        <th class="py-3.5 px-5">Peran / Role</th>
                        <th class="py-3.5 px-5">Kontak</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5">Terdaftar</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-500 to-amber-500 text-white font-bold text-xs flex items-center justify-center shadow-2xs shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                @forelse($user->roles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold 
                                        {{ $role->name == 'super_admin' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                        {{ $role->name == 'cashier' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                        {{ $role->name == 'manager' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                        {{ !in_array($role->name, ['super_admin', 'cashier', 'manager']) ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 text-[11px]">Tanpa Peran</span>
                                @endforelse
                            </td>
                            <td class="py-3.5 px-5 text-slate-500 text-[11px]">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="py-3.5 px-5">
                                @if($user->is_active ?? true)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-slate-400 text-[11px]">
                                {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="openEditUserModal({{ json_encode($user) }}, '{{ $user->roles->first()->name ?? '' }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 transition" title="Edit Pengguna">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Pengguna">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL CREATE USER -->
<!-- ========================================================================= -->
<div id="createUserModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-xl space-y-5 transition-colors">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-base text-slate-900">Tambah Pengguna Baru</h3>
            <button onclick="closeCreateUserModal()" class="text-slate-400 hover:text-slate-600 p-1"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Budi Santoso" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Email</label>
                <input type="email" name="email" required placeholder="budi@pospro.com" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Peran (Role)</label>
                    <select name="role" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Nomor Telepon</label>
                    <input type="text" name="phone" placeholder="08123456789" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Password</label>
                <input type="password" name="password" required placeholder="Minimal 8 karakter" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 text-white text-xs font-bold shadow-md shadow-brand-500/25 hover:from-brand-600 hover:to-amber-600 transition">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL EDIT USER -->
<!-- ========================================================================= -->
<div id="editUserModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-xl space-y-5 transition-colors">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-base text-slate-900">Edit Pengguna</h3>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 p-1"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Email</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Peran (Role)</label>
                    <select id="edit_role" name="role" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Nomor Telepon</label>
                    <input type="text" id="edit_phone" name="phone" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Password Baru (Opsional)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-500 border-slate-300">
                <label for="edit_is_active" class="text-xs font-bold text-slate-700 cursor-pointer">Akun Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 text-white text-xs font-bold shadow-md shadow-brand-500/25 hover:from-brand-600 hover:to-amber-600 transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateUserModal() {
        const modal = document.getElementById('createUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreateUserModal() {
        const modal = document.getElementById('createUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditUserModal(user, role) {
        document.getElementById('editUserForm').action = `/users/${user.id}`;
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_role').value = role || '';
        document.getElementById('edit_is_active').checked = user.is_active ? true : false;

        const modal = document.getElementById('editUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditUserModal() {
        const modal = document.getElementById('editUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
