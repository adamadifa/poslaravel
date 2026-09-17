<script>
    // Helper to format unit display name without duplicate text (e.g. "Dus" instead of "Dus (dus)")
    function formatUnitDisplay(u) {
        if (!u) return '';
        const name = (u.name || '').trim();
        const shortName = (u.short_name || '').trim();
        if (shortName && shortName.toLowerCase() !== name.toLowerCase()) {
            return `${name} (${shortName})`;
        }
        return name;
    }

    // Helper to get only units that are configured for this specific product (Base Unit + Conversions)
    function getProductConfiguredUnits(type, currentUnitId = null) {
        const configuredUnitIds = new Set();
        let baseUnitId = null;

        // 1. Base unit from Tab 1
        const baseUnitSelect = document.getElementById(`${type}_input_base_unit_id`);
        if (baseUnitSelect && baseUnitSelect.value) {
            baseUnitId = parseInt(baseUnitSelect.value);
            configuredUnitIds.add(baseUnitId);
        }

        // 2. Conversion units from Tab 3
        const convContainer = document.getElementById(`${type}_conversions_container`);
        if (convContainer) {
            const fromSelects = convContainer.querySelectorAll('select[name*="[from_unit_id]"]');
            fromSelects.forEach(s => {
                if (s.value) configuredUnitIds.add(parseInt(s.value));
            });
            const toSelects = convContainer.querySelectorAll('select[name*="[to_unit_id]"]');
            toSelects.forEach(s => {
                if (s.value) configuredUnitIds.add(parseInt(s.value));
            });
        }

        // 3. Keep current unit ID if provided (e.g. when editing existing saved row)
        if (currentUnitId) {
            configuredUnitIds.add(parseInt(currentUnitId));
        }

        // If at least one unit is configured, return only the configured units
        if (configuredUnitIds.size > 0 && typeof availableUnits !== 'undefined') {
            const filtered = availableUnits.filter(u => configuredUnitIds.has(u.id));
            if (filtered.length > 0) {
                return filtered.map(u => ({
                    ...u,
                    is_base: u.id === baseUnitId
                }));
            }
        }

        // Fallback to all available units if no unit configured yet
        if (typeof availableUnits !== 'undefined') {
            return availableUnits.map(u => ({
                ...u,
                is_base: false
            }));
        }

        return [];
    }

    // Refresh unit dropdowns in Tab 2 (Barcodes) & Tab 4 (Tiered Prices)
    function updateProductUnitDropdowns(type) {
        // Refresh Tiered Prices
        const tieredContainer = document.getElementById(`${type}_tiered_container`);
        if (tieredContainer) {
            const selects = tieredContainer.querySelectorAll('select[name*="[unit_id]"]');
            selects.forEach(sel => {
                const currentVal = $(sel).val();
                const units = getProductConfiguredUnits(type, currentVal);
                let options = '<option value="">Pilih Satuan</option>';
                units.forEach(u => {
                    const tag = u.is_base ? ' (Satuan Dasar)' : '';
                    options += `<option value="${u.id}" ${u.id == currentVal ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
                });
                $(sel).html(options).val(currentVal).trigger('change.select2');
            });
        }

        // Refresh Barcodes
        const barcodeContainer = document.getElementById(`${type}_barcodes_container`);
        if (barcodeContainer) {
            const selects = barcodeContainer.querySelectorAll('select[name*="[unit_id]"]');
            selects.forEach(sel => {
                const currentVal = $(sel).val();
                const units = getProductConfiguredUnits(type, currentVal);
                let options = '<option value="">Pilih Satuan</option>';
                units.forEach(u => {
                    const tag = u.is_base ? ' (Satuan Dasar)' : '';
                    options += `<option value="${u.id}" ${u.id == currentVal ? 'selected' : ''}>${formatUnitDisplay(u)}${tag}</option>`;
                });
                $(sel).html(options).val(currentVal).trigger('change.select2');
            });
        }
    }

    // Tab switcher helper (Pill segmented style)
    function switchProductTab(type, tab) {
        const tabs = ['basic', 'barcodes', 'conversions', 'tiered'];
        tabs.forEach(t => {
            const content = document.getElementById(`${type}_tab_content_${t}`);
            const btn = document.getElementById(`${type}_tab_btn_${t}`);
            if (content && btn) {
                if (t === tab) {
                    content.classList.remove('hidden');
                    btn.classList.add('bg-white', 'text-slate-800', 'shadow-2xs', 'font-bold');
                    btn.classList.remove('text-slate-500', 'font-semibold');
                    const icon = btn.querySelector('i');
                    if (icon) icon.classList.add('text-brand-500');
                } else {
                    content.classList.add('hidden');
                    btn.classList.remove('bg-white', 'text-slate-800', 'shadow-2xs', 'font-bold');
                    btn.classList.add('text-slate-500', 'font-semibold');
                    const icon = btn.querySelector('i');
                    if (icon) icon.classList.remove('text-brand-500');
                }
            }
        });

        // Automatically update unit options when switching to Barcodes or Tiered Pricing tab
        if (tab === 'tiered' || tab === 'barcodes') {
            updateProductUnitDropdowns(type);
        }
    }

    window.formatUnitDisplay = formatUnitDisplay;
    window.getProductConfiguredUnits = getProductConfiguredUnits;
    window.updateProductUnitDropdowns = updateProductUnitDropdowns;
    window.switchProductTab = switchProductTab;
</script>
