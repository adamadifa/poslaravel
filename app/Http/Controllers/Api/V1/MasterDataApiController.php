<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\DiningTable;
use App\Models\Discount;
use App\Models\DiscountItem;
use App\Models\ModifierGroup;
use App\Models\PpobProduct;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MasterDataApiController extends BaseApiController
{
    /**
     * Get list of active categories.
     */
    public function getCategories(): JsonResponse
    {
        $categories = Category::with(['parent'])
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->sendResponse($categories, 'Data kategori berhasil dimuat.');
    }

    /**
     * Store a newly created category.
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'default_notes' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        $defaultNotes = null;
        if ($request->filled('default_notes_raw')) {
            $defaultNotes = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('default_notes_raw')))));
        } elseif ($request->has('default_notes')) {
            $rawNotes = $request->input('default_notes');
            if (is_array($rawNotes)) {
                $defaultNotes = array_values(array_filter(array_map('trim', $rawNotes)));
            } elseif (is_string($rawNotes)) {
                $defaultNotes = array_values(array_filter(array_map('trim', explode(',', $rawNotes))));
            }
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'default_notes' => $defaultNotes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->sendResponse($category->load('parent')->loadCount('products'), 'Kategori berhasil ditambahkan.', 201);
    }

    /**
     * Update the specified category.
     */
    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'default_notes' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        $defaultNotes = null;
        if ($request->filled('default_notes_raw')) {
            $defaultNotes = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('default_notes_raw')))));
        } elseif ($request->has('default_notes')) {
            $rawNotes = $request->input('default_notes');
            if (is_array($rawNotes)) {
                $defaultNotes = array_values(array_filter(array_map('trim', $rawNotes)));
            } elseif (is_string($rawNotes)) {
                $defaultNotes = array_values(array_filter(array_map('trim', explode(',', $rawNotes))));
            }
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'default_notes' => $defaultNotes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->sendResponse($category->load('parent')->loadCount('products'), 'Kategori berhasil diperbarui.');
    }

    /**
     * Delete the specified category.
     */
    public function destroyCategory(Category $category): JsonResponse
    {
        if ($category->products()->count() > 0) {
            return $this->sendError('Kategori tidak dapat dihapus karena masih digunakan oleh produk.', [], 422);
        }

        $category->delete();

        return $this->sendResponse(null, 'Kategori berhasil dihapus.');
    }

    /**
     * Get list of active warehouses.
     */
    public function getWarehouses(): JsonResponse
    {
        $warehouses = Warehouse::orderByDesc('is_default')->orderBy('name')->get();

        return $this->sendResponse($warehouses, 'Data cabang/gudang berhasil dimuat.');
    }

    /**
     * Store a new warehouse.
     */
    public function storeWarehouse(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:warehouses,code'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        if (empty($validated['code'])) {
            $count = Warehouse::count() + 1;
            $validated['code'] = 'GDG-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        }

        $isDefault = $request->boolean('is_default', false);
        if ($isDefault) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $validated['is_default'] = $isDefault;
        $validated['is_active'] = $request->boolean('is_active', true);

        $warehouse = Warehouse::create($validated);

        return $this->sendResponse($warehouse, 'Gudang baru berhasil ditambahkan.', 201);
    }

    /**
     * Update the specified warehouse.
     */
    public function updateWarehouse(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:warehouses,code,'.$warehouse->id],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $isDefault = $request->boolean('is_default', false);
        if ($isDefault) {
            Warehouse::where('id', '!=', $warehouse->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $validated['is_default'] = $isDefault;
        $validated['is_active'] = $request->boolean('is_active', true);

        $warehouse->update($validated);

        return $this->sendResponse($warehouse, 'Data gudang berhasil diperbarui.');
    }

    /**
     * Delete the specified warehouse.
     */
    public function destroyWarehouse(Warehouse $warehouse): JsonResponse
    {
        if ($warehouse->is_default) {
            return $this->sendError('Gudang utama default tidak dapat dihapus.', [], 422);
        }

        $warehouse->delete();

        return $this->sendResponse(null, 'Gudang berhasil dihapus.');
    }

    /**
     * Get list of customers.
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');
        $groupId = $request->query('customer_group_id');

        $customers = Customer::with('group')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($groupId, function ($q, $groupId) {
                $q->where('customer_group_id', $groupId);
            })
            ->orderBy('name')
            ->get();

        return $this->sendResponse($customers, 'Data pelanggan berhasil dimuat.');
    }

    /**
     * Store a new customer.
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:customers,code'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        if (empty($validated['code'])) {
            $count = Customer::count() + 1;
            $validated['code'] = 'CUST-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $customer = Customer::create($validated);

        return $this->sendResponse($customer->load('group'), 'Pelanggan baru berhasil ditambahkan.', 201);
    }

    /**
     * Update the specified customer.
     */
    public function updateCustomer(Request $request, Customer $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:customers,code,'.$customer->id],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $customer->update($validated);

        return $this->sendResponse($customer->load('group'), 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Delete the specified customer.
     */
    public function destroyCustomer(Customer $customer): JsonResponse
    {
        $customer->delete();

        return $this->sendResponse(null, 'Pelanggan berhasil dihapus.');
    }

    /**
     * Get customer groups with member count.
     */
    public function getCustomerGroups(): JsonResponse
    {
        $groups = CustomerGroup::withCount('customers')->orderBy('name')->get();

        return $this->sendResponse($groups, 'Data grup pelanggan berhasil dimuat.');
    }

    /**
     * Get units.
     */
    public function getUnits(): JsonResponse
    {
        $units = Unit::withCount('products')
            ->orderBy('name')
            ->get();

        return $this->sendResponse($units, 'Data satuan unit berhasil dimuat.');
    }

    /**
     * Store a newly created unit.
     */
    public function storeUnit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50', 'unique:units,name'],
            'short_name' => ['required', 'string', 'max:15'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        $unit = Unit::create([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->sendResponse($unit->loadCount('products'), 'Satuan berhasil ditambahkan.', 201);
    }

    /**
     * Update the specified unit.
     */
    public function updateUnit(Request $request, Unit $unit): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50', 'unique:units,name,'.$unit->id],
            'short_name' => ['required', 'string', 'max:15'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        $unit->update([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->sendResponse($unit->loadCount('products'), 'Satuan berhasil diperbarui.');
    }

    /**
     * Delete the specified unit.
     */
    public function destroyUnit(Unit $unit): JsonResponse
    {
        if ($unit->products()->count() > 0) {
            return $this->sendError('Satuan tidak dapat dihapus karena masih digunakan sebagai satuan dasar produk.', [], 422);
        }

        $unit->delete();

        return $this->sendResponse(null, 'Satuan berhasil dihapus.');
    }

    public function getTables(Request $request): JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');
        $area = $request->query('area');

        $tables = DiningTable::with(['warehouse', 'currentSale'])
            ->when($warehouseId, function ($q, $warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->when($area, function ($q, $area) {
                $q->where('area', $area);
            })
            ->orderBy('area')
            ->orderBy('sort_order')
            ->orderBy('table_number')
            ->get();

        return $this->sendResponse($tables, 'Data meja makan berhasil dimuat.');
    }

    /**
     * Store a new dining table.
     */
    public function storeTable(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'table_number' => ['required', 'string', 'max:50'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'area' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:available,occupied,reserved,cleaning'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        if (empty($validated['warehouse_id'])) {
            $defaultWh = Warehouse::where('is_default', true)->first() ?? Warehouse::first();
            $validated['warehouse_id'] = $defaultWh ? $defaultWh->id : null;
        }

        $validated['status'] = $validated['status'] ?? 'available';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $table = DiningTable::create($validated);

        return $this->sendResponse($table->load('warehouse'), 'Meja makan baru berhasil ditambahkan.', 201);
    }

    /**
     * Update the specified dining table.
     */
    public function updateTable(Request $request, DiningTable $table): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'table_number' => ['required', 'string', 'max:50'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'area' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:available,occupied,reserved,cleaning'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $validated['status'] = $validated['status'] ?? $table->status;
        $validated['sort_order'] = $validated['sort_order'] ?? $table->sort_order;
        $validated['is_active'] = $request->boolean('is_active', true);

        $table->update($validated);

        return $this->sendResponse($table->load(['warehouse', 'currentSale']), 'Data meja makan berhasil diperbarui.');
    }

    /**
     * Delete the specified dining table.
     */
    public function destroyTable(DiningTable $table): JsonResponse
    {
        if ($table->current_sale_id || $table->status === 'occupied') {
            return $this->sendError('Meja yang sedang digunakan atau ada transaksi aktif tidak dapat dihapus.', [], 422);
        }

        $table->delete();

        return $this->sendResponse(null, 'Meja makan berhasil dihapus.');
    }

    /**
     * Get modifier groups.
     */
    public function getModifierGroups(): JsonResponse
    {
        $modifiers = ModifierGroup::with('modifiers')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->sendResponse($modifiers, 'Data modifiers berhasil dimuat.');
    }

    /**
     * Get receipt and store profile branding settings.
     */
    public function getReceiptSettings(): JsonResponse
    {
        $companyLogo = Setting::get('company_logo', '');

        return $this->sendResponse([
            'company_name' => Setting::get('company_name', 'WarungPro POS'),
            'company_tagline' => Setting::get('company_tagline', ''),
            'company_address' => Setting::get('company_address', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_logo' => $companyLogo,
            'company_logo_url' => ! empty($companyLogo) ? asset('storage/'.$companyLogo) : null,
            'receipt_header' => Setting::get('receipt_header', ''),
            'receipt_footer' => Setting::get('receipt_footer', ''),
            'receipt_paper_size' => Setting::get('receipt_paper_size', '58mm'),
            'receipt_show_logo' => Setting::get('receipt_show_logo', '0') === '1',
            'business_type' => Setting::get('business_type', 'retail'),
            'fnb_service_charge_percent' => (float) Setting::get('fnb_service_charge_percent', '0'),
            'allow_manual_price_edit' => Setting::get('pos_allow_manual_price_edit', '1') === '1',
        ], 'Pengaturan struk dan profil toko berhasil dimuat.');
    }

    /**
     * Get list of suppliers.
     */
    public function getSuppliers(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');

        $suppliers = Supplier::when($search, function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%");
        })
            ->orderBy('name')
            ->get();

        return $this->sendResponse($suppliers, 'Data pemasok/supplier berhasil dimuat.');
    }

    /**
     * Store a new supplier.
     */
    public function storeSupplier(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:suppliers,code'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        if (empty($validated['code'])) {
            $count = Supplier::count() + 1;
            $validated['code'] = 'SUPP-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        $validated['payment_term_days'] = $validated['payment_term_days'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier = Supplier::create($validated);

        return $this->sendResponse($supplier, 'Pemasok baru berhasil ditambahkan.', 201);
    }

    /**
     * Update the specified supplier.
     */
    public function updateSupplier(Request $request, Supplier $supplier): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:suppliers,code,'.$supplier->id],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $validated['payment_term_days'] = $validated['payment_term_days'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier->update($validated);

        return $this->sendResponse($supplier, 'Data pemasok berhasil diperbarui.');
    }

    /**
     * Delete the specified supplier.
     */
    public function destroySupplier(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return $this->sendResponse(null, 'Pemasok berhasil dihapus.');
    }

    /**
     * Get list of active discounts & promo vouchers.
     */
    public function getDiscounts(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');
        $type = $request->query('type');
        $status = $request->query('status');

        $discounts = Discount::with(['customerGroup', 'rewardProduct', 'items.product'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status == '1' || $status === 'true');
            })
            ->orderByDesc('id')
            ->get();

        return $this->sendResponse($discounts, 'Data diskon & promosi berhasil dimuat.');
    }

    /**
     * Store a new discount.
     */
    public function storeDiscount(StoreDiscountRequest $request): JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['is_combinable'] = $request->boolean('is_combinable', false);
            $validated['value'] = $validated['value'] ?? 0;

            $discount = Discount::create($validated);

            if (! empty($request->input('product_ids'))) {
                foreach ($request->input('product_ids') as $pId) {
                    DiscountItem::create([
                        'discount_id' => $discount->id,
                        'product_id' => $pId,
                    ]);
                }
            }

            DB::commit();

            return $this->sendResponse(
                $discount->load(['customerGroup', 'rewardProduct', 'items.product']),
                'Promo diskon berhasil ditambahkan.',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Gagal menambahkan promo: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified discount.
     */
    public function updateDiscount(UpdateDiscountRequest $request, Discount $discount): JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['is_combinable'] = $request->boolean('is_combinable', false);
            $validated['value'] = $validated['value'] ?? 0;

            $discount->update($validated);

            $discount->items()->delete();
            if (! empty($request->input('product_ids'))) {
                foreach ($request->input('product_ids') as $pId) {
                    DiscountItem::create([
                        'discount_id' => $discount->id,
                        'product_id' => $pId,
                    ]);
                }
            }

            DB::commit();

            return $this->sendResponse(
                $discount->load(['customerGroup', 'rewardProduct', 'items.product']),
                'Data promo diskon berhasil diperbarui.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Gagal memperbarui promo: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Delete the specified discount.
     */
    public function destroyDiscount(Discount $discount): JsonResponse
    {
        $discount->delete();

        return $this->sendResponse(null, 'Promo diskon berhasil dihapus.');
    }

    /**
     * Get list of cash & bank accounts with type filters and financial totals.
     */
    public function getAccounts(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $search = $request->query('q') ?? $request->query('search');

        $query = Account::when($type && $type !== 'all', fn ($q) => $q->where('type', $type))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('account_code', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%");
                });
            });

        $accounts = (clone $query)->orderByDesc('is_default')->orderBy('name')->get();

        $totalCash = Account::where('type', 'cash')->where('is_active', true)->sum('current_balance');
        $totalBank = Account::where('type', 'bank')->where('is_active', true)->sum('current_balance');
        $totalBankAgent = Account::where('type', 'bank_agent')->where('is_active', true)->sum('current_balance');
        $totalPpob = Account::where('type', 'ppob_provider')->where('is_active', true)->sum('current_balance');
        $totalBalance = Account::where('is_active', true)->sum('current_balance');

        return $this->sendResponse([
            'accounts' => $accounts,
            'summary' => [
                'total_balance' => (float) $totalBalance,
                'total_cash' => (float) $totalCash,
                'total_bank' => (float) $totalBank,
                'total_bank_agent' => (float) $totalBankAgent,
                'total_ppob' => (float) $totalPpob,
                'accounts_count' => $accounts->count(),
            ],
        ], 'Data akun kas & bank berhasil dimuat.');
    }

    /**
     * Store new Account.
     */
    public function storeAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_code' => 'required|string|max:50|unique:accounts,account_code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,bank_agent,ppob_provider,other',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:150',
            'bank_name' => 'nullable|string|max:100',
            'opening_balance' => 'required|numeric|min:0',
            'alert_minimum_balance' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['alert_minimum_balance'] = $validated['alert_minimum_balance'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['is_active'] = true;

        if ($validated['is_default']) {
            Account::where('is_default', true)->update(['is_default' => false]);
        }

        $account = Account::create($validated);

        return $this->sendResponse($account, "Akun {$account->name} berhasil ditambahkan.", 201);
    }

    /**
     * Update specified Account.
     */
    public function updateAccount(Request $request, Account $account): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_code' => "required|string|max:50|unique:accounts,account_code,{$account->id}",
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,bank_agent,ppob_provider,other',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:150',
            'bank_name' => 'nullable|string|max:100',
            'alert_minimum_balance' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $validated['alert_minimum_balance'] = $validated['alert_minimum_balance'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['is_default']) {
            Account::where('id', '!=', $account->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $account->update($validated);

        return $this->sendResponse($account, "Akun {$account->name} berhasil diperbarui.");
    }

    /**
     * Delete specified Account.
     */
    public function destroyAccount(Account $account): JsonResponse
    {
        if ($account->is_default) {
            return $this->sendError('Akun default / utama tidak dapat dihapus.', [], 422);
        }

        $account->delete();

        return $this->sendResponse(null, "Akun {$account->name} berhasil dihapus.");
    }

    /**
     * Get Mutations of an Account (Buku Mutasi / Rekening Koran) with Date Filter.
     */
    public function getAccountMutations(Request $request, Account $account): JsonResponse
    {
        $type = $request->query('type'); // credit, debit
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $limit = (int) ($request->query('limit', 50));

        $mutations = $account->mutations()
            ->with(['user'])
            ->when($type && $type !== 'all', fn ($q) => $q->where('mutation_type', $type))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->latest('id')
            ->limit($limit)
            ->get();

        $totalDebit = (clone $account->mutations())
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('mutation_type', 'debit')
            ->sum('amount');

        $totalCredit = (clone $account->mutations())
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('mutation_type', 'credit')
            ->sum('amount');

        return $this->sendResponse([
            'account' => $account,
            'mutations' => $mutations,
            'summary' => [
                'total_debit' => (float) $totalDebit,
                'total_credit' => (float) $totalCredit,
                'current_balance' => (float) $account->current_balance,
                'mutations_count' => $mutations->count(),
            ],
        ], 'Buku mutasi rekening berhasil dimuat.');
    }

    /**
     * Get list of PPOB products with filters & summary statistics.
     */
    public function getPpobProducts(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $provider = $request->query('provider');
        $search = $request->query('q') ?? $request->query('search');

        $query = PpobProduct::with('defaultAccount')
            ->when($category && $category !== 'all', fn ($q) => $q->where('category', $category))
            ->when($provider && $provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            });

        $products = (clone $query)->orderBy('category')->orderBy('provider')->orderBy('selling_price')->get();

        $totalActive = PpobProduct::where('is_active', true)->count();
        $totalPulsa = PpobProduct::whereIn('category', ['pulsa', 'paket_data'])->count();
        $totalTokenPln = PpobProduct::where('category', 'token_pln')->count();
        $totalEwallet = PpobProduct::where('category', 'ewallet')->count();
        $totalTagihan = PpobProduct::where('category', 'tagihan')->count();
        $providers = PpobProduct::select('provider')->whereNotNull('provider')->where('provider', '!=', '')->distinct()->pluck('provider');

        return $this->sendResponse([
            'products' => $products,
            'providers' => $providers,
            'summary' => [
                'total_active' => $totalActive,
                'total_pulsa' => $totalPulsa,
                'total_token_pln' => $totalTokenPln,
                'total_ewallet' => $totalEwallet,
                'total_tagihan' => $totalTagihan,
                'total_count' => $products->count(),
            ],
        ], 'Data katalog produk PPOB berhasil dimuat.');
    }

    /**
     * Store newly created PPOB product.
     */
    public function storePpobProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'category' => 'required|string|in:pulsa,paket_data,token_pln,ewallet,tagihan,other',
            'provider' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50|unique:ppob_products,code',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'default_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        if (empty($validated['code'])) {
            $validated['code'] = 'PPOB-'.strtoupper(substr($validated['category'], 0, 3)).'-'.rand(1000, 9999);
        }

        $product = PpobProduct::create($validated);

        return $this->sendResponse($product->load('defaultAccount'), "Produk PPOB '{$product->name}' berhasil ditambahkan.", 201);
    }

    /**
     * Show PPOB product.
     */
    public function showPpobProduct(PpobProduct $ppobProduct): JsonResponse
    {
        return $this->sendResponse($ppobProduct->load('defaultAccount'), 'Detail produk PPOB berhasil dimuat.');
    }

    /**
     * Update existing PPOB product.
     */
    public function updatePpobProduct(Request $request, PpobProduct $ppobProduct): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'category' => 'required|string|in:pulsa,paket_data,token_pln,ewallet,tagihan,other',
            'provider' => 'nullable|string|max:100',
            'code' => "nullable|string|max:50|unique:ppob_products,code,{$ppobProduct->id}",
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'default_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $ppobProduct->update($validated);

        return $this->sendResponse($ppobProduct->load('defaultAccount'), "Produk PPOB '{$ppobProduct->name}' berhasil diperbarui.");
    }

    /**
     * Delete PPOB product.
     */
    public function destroyPpobProduct(PpobProduct $ppobProduct): JsonResponse
    {
        $name = $ppobProduct->name;
        $ppobProduct->delete();

        return $this->sendResponse(null, "Produk PPOB '{$name}' berhasil dihapus.");
    }

    /**
     * Get master data summary counts for dashboard/group badges.
     */
    public function getMasterSummary(): JsonResponse
    {
        return $this->sendResponse([
            'products_count' => Product::where('is_active', true)->where('product_type', '!=', 'raw_material')->count(),
            'raw_materials_count' => Product::where('is_active', true)->where('product_type', 'raw_material')->count(),
            'categories_count' => Category::where('is_active', true)->count(),
            'units_count' => Unit::where('is_active', true)->count(),
            'customers_count' => Customer::where('is_active', true)->count(),
            'suppliers_count' => Supplier::where('is_active', true)->count(),
            'warehouses_count' => Warehouse::where('is_active', true)->count(),
            'tables_count' => DiningTable::where('is_active', true)->count(),
            'modifiers_count' => ModifierGroup::where('is_active', true)->count(),
            'discounts_count' => Discount::where('is_active', true)->count(),
            'accounts_count' => Account::where('is_active', true)->count(),
            'ppob_products_count' => PpobProduct::where('is_active', true)->count(),
        ], 'Ringkasan master data berhasil dimuat.');
    }
}
