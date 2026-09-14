<!-- CREATE ROLE MODAL -->
<div id="createRoleModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100">
                    <i data-lucide="shield-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Tambah Peran & Atur Hak Akses Baru</h3>
                    <p class="text-[11px] text-slate-400">Tentukan nama peran dan centang menu yang dapat diakses</p>
                </div>
            </div>
            <button onclick="closeModal('createRoleModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form Body (Scrollable) -->
        <form method="POST" action="{{ route('roles.store') }}" class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="p-6 overflow-y-auto space-y-6 flex-1">
                
                <!-- Role Name Input -->
                <div class="max-w-md">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Peran / Jabatan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 py-2">
                        <input type="text" name="name" required placeholder="Contoh: Staf Gudang, Supervisor Kasir" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Nama peran akan otomatis diubah menjadi format identifier sistem (misal: staf_gudang).</p>
                </div>

                <!-- Global Toggle Buttons -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Matriks Hak Akses Menu</h4>
                        <p class="text-[10px] text-slate-400">Centang izin menu dan aksi yang diizinkan untuk peran ini</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAllPermissions('create', true)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-600 transition cursor-pointer">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllPermissions('create', false)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 transition cursor-pointer">
                            Batal Semua
                        </button>
                    </div>
                </div>

                <!-- Modules Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($modules as $moduleName => $modData)
                        @php
                            $modSlug = 'create_mod_' . Str::slug($moduleName);
                        @endphp
                        <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                            <!-- Module Header -->
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-600 shadow-2xs">
                                        <i data-lucide="{{ $modData['icon'] }}" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="font-bold text-xs text-slate-800">{{ $moduleName }}</span>
                                </div>
                                <button type="button" onclick="toggleModuleGroup('{{ $modSlug }}')" class="text-[10px] font-bold text-brand-600 hover:underline cursor-pointer">
                                    Pilih/Batal
                                </button>
                            </div>

                            <!-- Permissions List -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                @foreach($modData['permissions'] as $permKey => $permLabel)
                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200/60 hover:border-brand-300 hover:bg-brand-50/30 transition cursor-pointer select-none">
                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" class="create-perm-checkbox {{ $modSlug }} rounded text-brand-500 focus:ring-brand-500/20 border-slate-300">
                                        <span class="text-[11px] font-medium text-slate-700 leading-tight">{{ $permLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" onclick="closeModal('createRoleModal')" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Peran</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openCreateRoleModal() {
        openModal('createRoleModal');
    }

    function toggleAllPermissions(prefix, check) {
        document.querySelectorAll(`.${prefix}-perm-checkbox`).forEach(cb => {
            cb.checked = check;
        });
    }

    function toggleModuleGroup(moduleClass) {
        const checkboxes = document.querySelectorAll(`.${moduleClass}`);
        if (checkboxes.length === 0) return;
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }
</script>
