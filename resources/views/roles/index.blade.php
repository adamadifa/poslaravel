@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100 shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Peran (Role)</p>
                <h4 class="text-xl font-black text-slate-900">{{ $roles->count() }} Role</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                <i data-lucide="key" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Hak Akses Sistem</p>
                <h4 class="text-xl font-black text-slate-900">{{ $allPermissionsCount }} Izin Menu</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengguna Terdaftar</p>
                <h4 class="text-xl font-black text-slate-900">{{ $roles->sum('users_count') }} Staf</h4>
            </div>
        </div>
    </div>

    <!-- Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        
        <!-- Search Form -->
        <form method="GET" action="{{ route('roles.index') }}" class="flex items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[220px] max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peran (role)..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
            </div>

            @if(request('search'))
                <a href="{{ route('roles.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Add Role Button -->
        <button onclick="openCreateRoleModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Peran Baru</span>
        </button>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-semibold text-rose-800 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
    @endif

    <!-- Roles Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-400 uppercase font-bold tracking-wider text-[10px]">
                        <th class="py-3.5 px-5">Peran / Role</th>
                        <th class="py-3.5 px-5">Tipe Akses</th>
                        <th class="py-3.5 px-5">Jumlah Izin</th>
                        <th class="py-3.5 px-5">Staf Aktif</th>
                        <th class="py-3.5 px-5 text-right">Kelola Hak Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($roles as $role)
                        @php
                            $isSuperAdmin = $role->name === 'super_admin';
                            $permCount = $isSuperAdmin ? $allPermissionsCount : $role->permissions->count();
                            $permPercent = $allPermissionsCount > 0 ? round(($permCount / $allPermissionsCount) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs
                                        {{ $isSuperAdmin ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-700' }}">
                                        @if($isSuperAdmin)
                                            <i data-lucide="crown" class="w-4 h-4 text-white"></i>
                                        @else
                                            <i data-lucide="shield" class="w-4 h-4 text-slate-500"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            <span>{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                                            @if($isSuperAdmin)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200">Kunci Sistem</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">slug: {{ $role->name }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-5">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-amber-500"></i>
                                        <span>Akses Penuh (Full Access)</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-50 text-slate-700 border border-slate-200">
                                        <i data-lucide="sliders" class="w-3 h-3 text-slate-400"></i>
                                        <span>Akses Khusus (Custom)</span>
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-5">
                                <div class="space-y-1 max-w-[140px]">
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-slate-700">{{ $permCount }} / {{ $allPermissionsCount }}</span>
                                        <span class="text-brand-600">{{ $permPercent }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-brand-500 to-amber-500 rounded-full" style="width: {{ $permPercent }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $role->users_count > 0 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                    <span class="font-bold text-slate-800">{{ $role->users_count }} Pengguna</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Edit Role & Permission Button -->
                                    <button 
                                        type="button" 
                                        onclick="openEditRoleModal({{ json_encode($role) }}, {{ json_encode($role->permissions->pluck('name')) }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition cursor-pointer" 
                                        title="Atur Hak Akses Menu"
                                    >
                                        <i data-lucide="settings-2" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Delete Role Button (Disabled for super_admin) -->
                                    @if(!$isSuperAdmin)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran {{ ucwords(str_replace('_', ' ', $role->name)) }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" 
                                                title="Hapus Peran"
                                            >
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="p-1.5 text-slate-300 cursor-not-allowed" title="Peran Super Admin tidak dapat dihapus">
                                            <i data-lucide="lock" class="w-4 h-4"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="shield-alert" class="w-8 h-8 text-slate-300"></i>
                                    <p class="font-semibold text-slate-600">Tidak ada peran ditemukan</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODALS -->
@include('roles._create_modal')
@include('roles._edit_modal')

@endsection
