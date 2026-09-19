<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\DiningTable;
use App\Models\Discount;
use App\Models\ModifierGroup;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterDataApiController extends BaseApiController
{
    /**
     * Get list of active categories.
     */
    public function getCategories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->sendResponse($categories, 'Data kategori berhasil dimuat.');
    }

    /**
     * Get list of active warehouses.
     */
    public function getWarehouses(): JsonResponse
    {
        $warehouses = Warehouse::where('is_active', true)->get();

        return $this->sendResponse($warehouses, 'Data cabang/gudang berhasil dimuat.');
    }

    /**
     * Get list of customers.
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $search = $request->query('q');

        $customers = Customer::with('group')
            ->where('is_active', true)
            ->when($search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
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
            'code' => ['nullable', 'string', 'max:50', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        if (empty($validated['code'])) {
            $count = Customer::count() + 1;
            $validated['code'] = 'CUST-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        $customer = Customer::create(array_merge($validated, [
            'is_active' => true,
        ]));

        return $this->sendResponse($customer->load('group'), 'Pelanggan baru berhasil ditambahkan.', 201);
    }

    /**
     * Get customer groups.
     */
    public function getCustomerGroups(): JsonResponse
    {
        $groups = CustomerGroup::orderBy('name')->get();

        return $this->sendResponse($groups, 'Data grup pelanggan berhasil dimuat.');
    }

    /**
     * Get units.
     */
    public function getUnits(): JsonResponse
    {
        $units = Unit::where('is_active', true)->get();

        return $this->sendResponse($units, 'Data satuan unit berhasil dimuat.');
    }

    /**
     * Get dining tables (for F&B/Resto mode).
     */
    public function getTables(): JsonResponse
    {
        $tables = DiningTable::with('currentSale')
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        return $this->sendResponse($tables, 'Data meja makan berhasil dimuat.');
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
        $search = $request->query('q');

        $suppliers = Supplier::where('is_active', true)
            ->when($search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        return $this->sendResponse($suppliers, 'Data pemasok/supplier berhasil dimuat.');
    }

    /**
     * Get list of active discounts & promo vouchers.
     */
    public function getDiscounts(): JsonResponse
    {
        $discounts = Discount::with(['customerGroup', 'rewardProduct'])
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();

        return $this->sendResponse($discounts, 'Data diskon & promosi berhasil dimuat.');
    }

    /**
     * Get list of cash & bank accounts.
     */
    public function getAccounts(): JsonResponse
    {
        $accounts = Account::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return $this->sendResponse($accounts, 'Data akun kas & bank berhasil dimuat.');
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
        ], 'Ringkasan master data berhasil dimuat.');
    }
}
