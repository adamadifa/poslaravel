<!-- MODAL CREATE UNIT -->
<div id="createUnitModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs {{ $errors->hasBag('default') && !old('_method') ? 'flex' : 'hidden' }} items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/60 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center border border-brand-100/60 dark:border-brand-800/40">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white tracking-tight">Tambah Satuan Baru</h3>
                    <p class="text-[11px] text-slate-400">Nama takaran dan simbol singkatan unit barang</p>
                </div>
            </div>
            <button onclick="closeCreateUnitModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createUnitForm" action="{{ route('units.store') }}" method="POST" novalidate class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            @csrf

            <!-- Nama Satuan -->
            <div id="create_group_name">
                <div id="create_box_name" class="relative rounded-xl border {{ $errors->has('name') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2">
                    <label id="create_label_name" class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
                        Nama Satuan <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i id="create_icon_name" data-lucide="scale" class="w-4 h-4 {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <input type="text" name="name" id="create_input_name" value="{{ old('_method') ? '' : old('name') }}" required placeholder="Contoh: Karton, Lusin, Botol" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <p id="create_error_name" class="mt-1 text-[11px] font-medium {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('name') && !old('_method') ? $errors->first('name') : 'Nama lengkap satuan' }}
                </p>
            </div>

            <!-- Simbol / Singkatan Satuan -->
            <div id="create_group_short_name">
                <div id="create_box_short_name" class="relative rounded-xl border {{ $errors->has('short_name') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2">
                    <label id="create_label_short_name" class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold {{ $errors->has('short_name') && !old('_method') ? 'text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
                        Simbol / Singkatan <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i id="create_icon_short_name" data-lucide="hash" class="w-4 h-4 {{ $errors->has('short_name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <input type="text" name="short_name" id="create_input_short_name" value="{{ old('_method') ? '' : old('short_name') }}" required placeholder="Contoh: krt, lsn, btl" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <p id="create_error_short_name" class="mt-1 text-[11px] font-medium {{ $errors->has('short_name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('short_name') && !old('_method') ? $errors->first('short_name') : 'Simbol singkatan pada nota kasir' }}
                </p>
            </div>

            <!-- Modal Footer / Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateUnitModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Satuan</span>
                </button>
            </div>
        </form>
    </div>
</div>
