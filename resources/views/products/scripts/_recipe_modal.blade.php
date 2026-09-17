<script>
    // ==========================================
    // RECIPE & BOM MODAL CONTROLLER
    // ==========================================
    let currentRecipeProductId = null;
    let currentRecipeProductSellingPrice = 0;
    let currentRecipeList = [];
    let availableRecipeIngredients = [];
    let availableRecipeUnits = [];

    function openRecipeModal(productId, productName, sellingPrice, productType) {
        currentRecipeProductId = productId;
        currentRecipeProductSellingPrice = parseFloat(sellingPrice) || 0;
        currentRecipeList = [];

        document.getElementById('recipe_modal_product_name').innerText = productName;
        document.getElementById('recipe_modal_product_badge').innerText = productType ? productType.toUpperCase() : 'PRODUK';
        document.getElementById('recipe_modal_selling_price').innerText = 'Rp ' + Math.round(currentRecipeProductSellingPrice).toLocaleString('id-ID');

        const modal = document.getElementById('recipeModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        const tbody = document.getElementById('recipe_table_body');
        tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-slate-400 text-xs">Memuat resep bahan baku...</td></tr>';

        fetch(`/products/${productId}/recipes`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    availableRecipeIngredients = data.available_ingredients || [];
                    availableRecipeUnits = data.units || [];

                    // Populate ingredient dropdown
                    const ingSelect = document.getElementById('recipe_new_ingredient_id');
                    ingSelect.innerHTML = '<option value="">Pilih Bahan Baku...</option>';
                    availableRecipeIngredients.forEach(ing => {
                        const baseUnitName = ing.base_unit ? (ing.base_unit.short_name || ing.base_unit.name) : '';
                        const typeTag = ing.product_type === 'raw_material' ? '[Bahan]' : '';
                        ingSelect.innerHTML += `<option value="${ing.id}" data-price="${ing.purchase_price}" data-unit="${ing.base_unit_id}">${typeTag} ${ing.name} (HPP: Rp ${Math.round(ing.purchase_price).toLocaleString('id-ID')}/${baseUnitName})</option>`;
                    });

                    // Populate unit dropdown
                    const unitSelect = document.getElementById('recipe_new_unit_id');
                    unitSelect.innerHTML = '<option value="">Pilih Satuan...</option>';
                    availableRecipeUnits.forEach(u => {
                        unitSelect.innerHTML += `<option value="${u.id}">${formatUnitDisplay(u)}</option>`;
                    });

                    // Initialize Select2 for recipe modal
                    $('#recipe_new_ingredient_id').select2({
                        dropdownParent: $('#recipeModal'),
                        placeholder: 'Pilih Bahan Baku...',
                        allowClear: true,
                        width: '100%'
                    });

                    $('#recipe_new_unit_id').select2({
                        dropdownParent: $('#recipeModal'),
                        placeholder: 'Pilih Satuan...',
                        allowClear: true,
                        width: '100%'
                    });

                    // Auto-select unit when ingredient is chosen
                    $('#recipe_new_ingredient_id').off('select2:select change').on('select2:select change', function() {
                        const selectedOption = $(this).find('option:selected');
                        const defaultUnitId = selectedOption.data('unit');
                        if (defaultUnitId) {
                            $('#recipe_new_unit_id').val(defaultUnitId).trigger('change');
                        }
                    });

                    // Map existing recipes
                    currentRecipeList = (data.recipes || []).map(r => ({
                        ingredient_product_id: r.ingredient_product_id,
                        ingredient_name: r.ingredient ? r.ingredient.name : 'Bahan',
                        quantity: parseFloat(r.quantity) || 0,
                        unit_id: r.unit_id,
                        unit_name: r.unit ? (r.unit.short_name || r.unit.name) : '',
                        waste_percent: parseFloat(r.waste_percent) || 0,
                        cost_estimate: parseFloat(r.cost_estimate) || 0,
                        notes: r.notes || ''
                    }));

                    renderRecipeTable();
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-rose-500 text-xs">Gagal memuat resep produk.</td></tr>';
            });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeRecipeModal() {
        const modal = document.getElementById('recipeModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function renderRecipeTable() {
        const tbody = document.getElementById('recipe_table_body');
        document.getElementById('recipe_items_count').innerText = currentRecipeList.length;

        if (currentRecipeList.length === 0) {
            tbody.innerHTML = `<tr>
                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                    Belum ada bahan baku pada resep ini. Tambahkan bahan racikan di atas.
                </td>
            </tr>`;
            calculateRecipeTotals();
            return;
        }

        let html = '';
        currentRecipeList.forEach((item, idx) => {
            html += `
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="py-2.5 px-4 font-semibold text-slate-900">
                        ${item.ingredient_name}
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <input type="number" step="any" min="0.001" value="${item.quantity}" onchange="updateRecipeQty(${idx}, this.value)" class="w-20 text-center text-xs font-semibold bg-white border border-slate-200 rounded-md py-1 px-1 focus:border-brand-500 focus:outline-none">
                    </td>
                    <td class="py-2.5 px-3">
                        <select onchange="updateRecipeUnit(${idx}, this.value)" class="text-xs bg-white border border-slate-200 rounded-md py-1 px-1.5 focus:border-brand-500 focus:outline-none">
                            ${availableRecipeUnits.map(u => `<option value="${u.id}" ${u.id == item.unit_id ? 'selected' : ''}>${u.short_name || u.name}</option>`).join('')}
                        </select>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <input type="number" step="any" min="0" max="100" value="${item.waste_percent}" onchange="updateRecipeWaste(${idx}, this.value)" class="w-16 text-center text-xs font-semibold bg-white border border-slate-200 rounded-md py-1 px-1 focus:border-brand-500 focus:outline-none">
                    </td>
                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-800">
                        Rp ${Math.round(item.cost_estimate || 0).toLocaleString('id-ID')}
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <button type="button" onclick="removeRecipeIngredientRow(${idx})" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Bahan">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        calculateRecipeTotals();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function addRecipeIngredientRow() {
        const ingSelect = document.getElementById('recipe_new_ingredient_id');
        const qtyInput = document.getElementById('recipe_new_qty');
        const unitSelect = document.getElementById('recipe_new_unit_id');

        const ingId = parseInt(ingSelect.value);
        const qty = parseFloat(qtyInput.value);
        const unitId = parseInt(unitSelect.value);

        if (!ingId) {
            alert('Silakan pilih bahan baku terlebih dahulu.');
            return;
        }
        if (!qty || qty <= 0) {
            alert('Silakan masukkan takaran yang valid.');
            return;
        }
        if (!unitId) {
            alert('Silakan pilih satuan takaran.');
            return;
        }

        const existing = currentRecipeList.find(r => r.ingredient_product_id === ingId);
        if (existing) {
            alert('Bahan baku ini sudah ada di resep.');
            return;
        }

        const ingData = availableRecipeIngredients.find(i => i.id === ingId);
        const unitData = availableRecipeUnits.find(u => u.id === unitId);

        let costEst = (parseFloat(ingData?.purchase_price) || 0) * qty;

        currentRecipeList.push({
            ingredient_product_id: ingId,
            ingredient_name: ingData ? ingData.name : 'Bahan',
            quantity: qty,
            unit_id: unitId,
            unit_name: unitData ? (unitData.short_name || unitData.name) : '',
            waste_percent: 0,
            cost_estimate: costEst,
            notes: ''
        });

        $('#recipe_new_ingredient_id').val('').trigger('change');
        qtyInput.value = '';
        $('#recipe_new_unit_id').val('').trigger('change');
        renderRecipeTable();
    }

    function updateRecipeQty(idx, val) {
        if (currentRecipeList[idx]) {
            currentRecipeList[idx].quantity = parseFloat(val) || 0;
            const ingData = availableRecipeIngredients.find(i => i.id === currentRecipeList[idx].ingredient_product_id);
            currentRecipeList[idx].cost_estimate = (parseFloat(ingData?.purchase_price) || 0) * currentRecipeList[idx].quantity;
            renderRecipeTable();
        }
    }

    function updateRecipeUnit(idx, val) {
        if (currentRecipeList[idx]) {
            currentRecipeList[idx].unit_id = parseInt(val);
            const unitData = availableRecipeUnits.find(u => u.id === parseInt(val));
            currentRecipeList[idx].unit_name = unitData ? (unitData.short_name || unitData.name) : '';
        }
    }

    function updateRecipeWaste(idx, val) {
        if (currentRecipeList[idx]) {
            currentRecipeList[idx].waste_percent = parseFloat(val) || 0;
            calculateRecipeTotals();
        }
    }

    function removeRecipeIngredientRow(idx) {
        currentRecipeList.splice(idx, 1);
        renderRecipeTable();
    }

    function calculateRecipeTotals() {
        let totalHpp = 0;
        currentRecipeList.forEach(item => {
            totalHpp += (parseFloat(item.cost_estimate) || 0) * (1 + ((parseFloat(item.waste_percent) || 0) / 100));
        });

        document.getElementById('recipe_modal_total_cost').innerText = 'Rp ' + Math.round(totalHpp).toLocaleString('id-ID');

        let margin = 0;
        if (currentRecipeProductSellingPrice > 0) {
            margin = ((currentRecipeProductSellingPrice - totalHpp) / currentRecipeProductSellingPrice) * 100;
        }

        const marginEl = document.getElementById('recipe_modal_margin');
        if (marginEl) {
            marginEl.innerText = margin.toFixed(1) + '%';
            if (margin >= 40) {
                marginEl.className = 'text-sm font-extrabold text-emerald-600 font-mono mt-0.5';
            } else if (margin >= 20) {
                marginEl.className = 'text-sm font-extrabold text-amber-600 font-mono mt-0.5';
            } else {
                marginEl.className = 'text-sm font-extrabold text-rose-600 font-mono mt-0.5';
            }
        }
    }

    function saveProductRecipes() {
        if (!currentRecipeProductId) return;

        const btn = document.getElementById('saveRecipeBtn');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i><span>Menyimpan...</span>';

        const payload = {
            recipes: currentRecipeList.map(r => ({
                ingredient_product_id: r.ingredient_product_id,
                quantity: r.quantity,
                unit_id: r.unit_id,
                waste_percent: r.waste_percent,
                notes: r.notes || null
            }))
        };

        fetch(`/products/${currentRecipeProductId}/recipes`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Simpan Resep</span>';
            if (data.status === 'success') {
                alert(data.message);
                closeRecipeModal();
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan resep.');
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Simpan Resep</span>';
            alert('Terjadi kesalahan koneksi saat menyimpan resep.');
        });
    }

    window.openRecipeModal = openRecipeModal;
    window.closeRecipeModal = closeRecipeModal;
    window.renderRecipeTable = renderRecipeTable;
    window.addRecipeIngredientRow = addRecipeIngredientRow;
    window.updateRecipeQty = updateRecipeQty;
    window.updateRecipeUnit = updateRecipeUnit;
    window.updateRecipeWaste = updateRecipeWaste;
    window.removeRecipeIngredientRow = removeRecipeIngredientRow;
    window.calculateRecipeTotals = calculateRecipeTotals;
    window.saveProductRecipes = saveProductRecipes;
</script>
