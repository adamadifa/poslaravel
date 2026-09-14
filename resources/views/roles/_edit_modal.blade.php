<!-- EDIT ROLE MODAL -->
<div id="editRoleModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight" id="edit_modal_title">Edit Hak Akses Peran</h3>
                    <p class="text-[11px] text-slate-400">Ubah hak akses menu dan aksi untuk peran ini</p>
                </div>
            </div>
            <button onclick="closeModal('editRoleModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form Body (Scrollable) -->
        <form id="editRoleForm" method="POST" action="" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @method('PUT')

            <div class="p-6 overflow-y-auto space-y-6 flex-1">
                
                <!-- Role Name Input -->
                <div class="max-w-md">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Peran / Jabatan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 py-2">
                        <input type="text" name="name" id="edit_role_name" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                    <p id="edit_role_notice" class="text-[10px] text-slate-400 mt-1"></p>
                </div>

                <!-- Global Toggle Buttons -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Matriks Hak Akses Menu</h4>
                        <p class="text-[10px] text-slate-400">Centang izin menu yang aktif untuk peran ini</p>
                    </div>
                    <div class="flex items-center gap-2" id="edit_toggle_buttons_container">
                        <button type="button" onclick="toggleAllPermissions('edit', true)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-600 transition cursor-pointer">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllPermissions('edit', false)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 transition cursor-pointer">
                            Batal Semua
                        </button>
                    </div>
                </div>

                <!-- Modules Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($modules as $moduleName => $modData)
                        @php
                            $modSlug = 'edit_mod_' . Str::slug($moduleName);
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
                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" id="edit_perm_{{ str_replace('.', '_', $permKey) }}" class="edit-perm-checkbox {{ $modSlug }} rounded text-brand-500 focus:ring-brand-500/20 border-slate-300">
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
                <button type="button" onclick="closeModal('editRoleModal')" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openEditRoleModal(role, assignedPermissions) {
        const form = document.getElementById('editRoleForm');
        form.action = `/roles/${role.id}`;

        const nameInput = document.getElementById('edit_role_name');
        nameInput.value = role.name;

        const notice = document.getElementById('edit_role_notice');
        const toggleButtons = document.getElementById('edit_toggle_buttons_container');

        if (role.name === 'super_admin') {
            nameInput.readOnly = true;
            notice.innerText = 'Peran Super Administrator memiliki akses penuh ke semua modul dan tidak dapat dibatasi.';
            notice.className = 'text-[10px] font-bold text-amber-600 mt-1';
            toggleAllPermissions('edit', true);
            toggleButtons.classList.add('opacity-50', 'pointer-events-none');
        } else {
            nameInput.readOnly = false;
            notice.innerText = '';
            toggleButtons.classList.remove('opacity-50', 'pointer-events-none');

            // Reset all checkboxes first
            document.querySelectorAll('.edit-perm-checkbox').forEach(cb => {
                cb.checked = false;
            });

            // Check assigned permissions
            if (Array.isArray(assignedPermissions)) {
                assignedPermissions.forEach(pName => {
                    const el = document.getElementById(`edit_perm_${pName.replace(/\./g, '_')}`);
                    if (el) {
                        el.checked = true;
                    }
                });
            }
        }

        openModal('editRoleModal');
    }
</script>
