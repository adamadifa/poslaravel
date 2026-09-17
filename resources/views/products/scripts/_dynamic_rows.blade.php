<script>
    // Image preview helper
    function previewProductImage(input, type) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(`${type}_image_preview_img`);
                const icon = document.getElementById(`${type}_image_preview_icon`);
                if (img && icon) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    icon.classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Dynamic Multi-Barcode Row (Outset Floating Standard)
    function addBarcodeRow(type, barcode = '', unitId = '') {
        const container = document.getElementById(`${type}_barcodes_container`);
        if (!container) return;
        
        const index = container.children.length;
        const units = getProductConfiguredUnits(type, unitId);
        let unitOptions = '<option value="">Pilih Satuan</option>';
        units.forEach(u => {
            const tag = u.is_base ? ' (Satuan Dasar)' : '';
            unitOptions += `<option value="${u.id}" ${u.id == unitId ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
        });

        const row = document.createElement('div');
        row.className = 'p-3 rounded-xl bg-slate-50/70 border border-slate-200/90 shadow-2xs flex flex-wrap sm:flex-nowrap items-center gap-3';
        row.innerHTML = `
            <div class="flex-1 w-full">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Kode Barcode Scanner <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <i data-lucide="scan" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <input type="text" name="barcodes[${index}][barcode]" value="${barcode}" placeholder="Scan / ketik barcode..." required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="w-full sm:w-52">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Satuan Jual <span class="text-rose-500">*</span>
                    </label>
                    <select name="barcodes[${index}][unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${unitOptions}
                    </select>
                </div>
            </div>

            <button type="button" onclick="this.closest('.p-3').remove()" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition shrink-0" title="Hapus Barcode">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(row);

        // Initialize Select2 on barcode select
        $(row).find('select').select2({
            dropdownParent: $(`#${type}ProductModal`),
            placeholder: 'Pilih Satuan',
            width: '100%'
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Dynamic Unit Conversion Row (Outset Floating Standard)
    function addConversionRow(type, fromUnitId = '', toUnitId = '', value = '') {
        const container = document.getElementById(`${type}_conversions_container`);
        if (!container) return;

        const index = container.children.length;
        let fromOptions = '<option value="">Satuan Besar (Dari)</option>';
        let toOptions = '<option value="">Satuan Kecil (Ke)</option>';
        
        let formattedValue = '';
        if (value !== '' && value !== null && value !== undefined) {
            const num = parseFloat(value);
            if (!isNaN(num)) {
                formattedValue = Number(Math.round(num * 100) / 100);
            } else {
                formattedValue = value;
            }
        }

        if (typeof availableUnits !== 'undefined') {
            availableUnits.forEach(u => {
                fromOptions += `<option value="${u.id}" ${u.id == fromUnitId ? 'selected' : ''}>${formatUnitDisplay(u)}</option>`;
                toOptions += `<option value="${u.id}" ${u.id == toUnitId ? 'selected' : ''}>${formatUnitDisplay(u)}</option>`;
            });
        }

        const row = document.createElement('div');
        row.className = 'p-3 rounded-xl bg-slate-50/70 border border-slate-200/90 shadow-2xs flex flex-wrap sm:flex-nowrap items-center gap-3';
        row.innerHTML = `
            <div class="flex-1 w-full">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        1 Satuan Besar (Dari) <span class="text-rose-500">*</span>
                    </label>
                    <select name="conversions[${index}][from_unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${fromOptions}
                    </select>
                </div>
            </div>

            <div class="w-full sm:w-36">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Isi / Rasio <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="any" name="conversions[${index}][conversion_value]" value="${formattedValue}" placeholder="Nilai (40)" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <div class="flex-1 w-full">
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Satuan Dasar (Ke) <span class="text-rose-500">*</span>
                    </label>
                    <select name="conversions[${index}][to_unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${toOptions}
                    </select>
                </div>
            </div>

            <button type="button" onclick="this.closest('.p-3').remove(); updateProductUnitDropdowns('${type}');" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition shrink-0" title="Hapus Konversi">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(row);

        // Initialize Select2 on conversion selects and update dependent dropdowns on change
        $(row).find('select').each(function() {
            $(this).select2({
                dropdownParent: $(`#${type}ProductModal`),
                width: '100%'
            }).on('change', function() {
                updateProductUnitDropdowns(type);
            });
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Dynamic Tiered Price Row Helper (Outset Floating Standard)
    function addTieredRow(type, unitId = '', customerGroupId = '', minQty = '1', maxQty = '', price = '') {
        const container = document.getElementById(`${type}_tiered_container`);
        if (!container) return;
        const index = container.children.length;

        const units = getProductConfiguredUnits(type, unitId);
        let unitOptions = '<option value="">Pilih Satuan</option>';
        units.forEach(u => {
            const tag = u.is_base ? ' (Satuan Dasar)' : '';
            unitOptions += `<option value="${u.id}" ${u.id == unitId ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
        });

        let groupOptions = '<option value="">Semua Pelanggan (Umum)</option>';
        if (typeof customerGroups !== 'undefined') {
            customerGroups.forEach(g => {
                groupOptions += `<option value="${g.id}" ${g.id == customerGroupId ? 'selected' : ''}>${g.name}</option>`;
            });
        }

        const row = document.createElement('div');
        row.className = 'p-3.5 rounded-xl bg-slate-50/70 border border-slate-200/90 shadow-2xs space-y-3';
        row.innerHTML = `
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 text-[11px] font-black flex items-center justify-center">
                        #${index + 1}
                    </span>
                    <span class="text-xs font-bold text-slate-800">Konfigurasi Tier Grosir</span>
                </div>
                <button type="button" onclick="this.closest('.p-3.5').remove()" class="px-2 py-1 rounded-lg text-rose-500 hover:bg-rose-50 text-[11px] font-bold transition flex items-center gap-1">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Hapus Baris</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <!-- Satuan -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Satuan Jual <span class="text-rose-500">*</span>
                    </label>
                    <select name="tiered_prices[${index}][unit_id]" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${unitOptions}
                    </select>
                </div>

                <!-- Grup Member -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Target Member
                    </label>
                    <select name="tiered_prices[${index}][customer_group_id]" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        ${groupOptions}
                    </select>
                </div>

                <!-- Rentang Qty -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Rentang Qty (Min - Max) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                        <input type="number" step="any" name="tiered_prices[${index}][min_qty]" value="${minQty}" placeholder="Min (1)" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                        <span class="text-slate-400 font-bold text-xs">-</span>
                        <input type="number" step="any" name="tiered_prices[${index}][max_qty]" value="${maxQty}" placeholder="Max (∞)" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <!-- Harga Jual Khusus -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Harga Jual Satuan <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-brand-500">Rp</span>
                        <input type="number" step="any" name="tiered_prices[${index}][price]" value="${price}" placeholder="0" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>
        `;
        container.appendChild(row);
        
        // Initialize Select2 on dynamic tiered selects
        $(row).find('select').each(function() {
            $(this).select2({
                dropdownParent: $(`#${type}ProductModal`),
                width: '100%'
            });
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    window.previewProductImage = previewProductImage;
    window.addBarcodeRow = addBarcodeRow;
    window.addConversionRow = addConversionRow;
    window.addTieredRow = addTieredRow;
</script>
