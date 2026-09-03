<!-- MODAL CREATE WAREHOUSE -->
<div id="createWarehouseModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs {{ $errors->hasBag('default') && !old('_method') ? 'flex' : 'hidden' }} items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="building" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Tambah Gudang / Cabang</h3>
                    <p class="text-[11px] text-slate-400">Atur lokasi penyimpanan inventori dan cabang outlet</p>
                </div>
            </div>
            <button onclick="closeCreateWarehouseModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createWarehouseForm" action="{{ route('warehouses.store') }}" method="POST" novalidate class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            @csrf

            <!-- Nama Gudang -->
            <div id="create_group_name">
                <div id="create_box_name" class="relative rounded-xl border {{ $errors->has('name') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                    <label id="create_label_name" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                        Nama Gudang / Cabang <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i id="create_icon_name" data-lucide="warehouse" class="w-4 h-4 {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <input type="text" name="name" id="create_input_name" value="{{ old('_method') ? '' : old('name') }}" required placeholder="Contoh: Gudang Utama / Cabang Kemang" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <p id="create_error_name" class="mt-1 text-[11px] font-medium {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('name') && !old('_method') ? $errors->first('name') : 'Nama identitas lokasi fisik gudang atau toko' }}
                </p>
            </div>

            <!-- Kode Gudang & Telepon (2 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Kode Gudang (Opsional)
                        </label>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="hash" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <input type="text" name="code" value="{{ old('_method') ? '' : old('code') }}" placeholder="Auto: WH-001" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                    <p class="mt-1 text-[10px] text-slate-400 px-1">Kosongkan untuk otomatis dari sistem</p>
                </div>

                <div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            No. Telepon Gudang
                        </label>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <input type="text" name="phone" value="{{ old('_method') ? '' : old('phone') }}" placeholder="021-xxxxxxx" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                    <p class="mt-1 text-[10px] text-slate-400 px-1">Kontak operasional cabang</p>
                </div>
            </div>

            <!-- Alamat Lengkap Textarea -->
            <div>
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Alamat Lokasi Gudang (Opsional)
                    </label>
                    <div class="flex items-start gap-2.5 pt-0.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                        <textarea name="address" rows="2" placeholder="Jalan, Kawasan Industri / Kompleks Ruko..." class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none resize-none">{{ old('_method') ? '' : old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Set as Default Warehouse -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50/70 border border-amber-200/80">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-500"></i>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Jadikan Gudang Utama (Default)</span>
                        <span class="block text-[10px] text-slate-500">Gudang bawaan untuk transaksi kasir POS & penerimaan barang</span>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" {{ (old('_method') ? '' : old('is_default')) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            <!-- Status Aktif Switch -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/50 border border-slate-200/90">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="toggle-right" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Status Gudang Aktif</span>
                        <span class="block text-[10px] text-slate-400">Dapat menerima mutasi dan digunakan dalam transaksi</span>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ (old('_method') ? '' : old('is_active', '1')) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Modal Footer / Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateWarehouseModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Gudang</span>
                </button>
            </div>
        </form>
    </div>
</div>
