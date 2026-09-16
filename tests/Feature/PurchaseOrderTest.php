<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Supplier $supplier;

    protected Warehouse $warehouse;

    protected Unit $unit;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->adminUser = User::where('email', 'admin@pospro.com')->first();

        $this->unit = Unit::create(['name' => 'Pcs', 'short_name' => 'pcs', 'is_active' => true]);
        $this->supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Sumber Makmur Jaya',
            'contact_person' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'supplier@makmur.com',
            'address' => 'Kawasan Industri Pulogadung No. 12',
            'city' => 'Jakarta Timur',
            'payment_term_days' => 30,
            'is_active' => true,
        ]);
        $this->warehouse = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Gudang Pusat',
            'address' => 'Jl. Pergudangan Utama No. 8',
            'phone' => '021-888999',
            'is_default' => true,
            'is_active' => true,
        ]);
        $category = Category::create(['name' => 'Sembako', 'slug' => 'sembako', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'base_unit_id' => $this->unit->id,
            'code' => 'PRD-001',
            'sku' => 'SKU-001',
            'name' => 'Minyak Goreng 2L',
            'purchase_price' => 28000,
            'selling_price' => 35000,
            'is_active' => true,
        ]);
    }

    public function test_can_view_purchase_orders_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchase-orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Purchase Order');
    }

    public function test_can_view_and_print_purchase_order_document(): void
    {
        Setting::set('company_name', 'WarungPro Retail Test');
        Setting::set('company_address', 'Jl. Bisnis Merdeka No. 1');

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026090001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'user_id' => $this->adminUser->id,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(3)->toDateString(),
            'status' => 'sent',
            'subtotal' => 280000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_cost' => 15000,
            'grand_total' => 295000,
            'notes' => 'Harap kirim sebelum jam 12 siang.',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'unit_price' => 28000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'subtotal' => 280000,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('purchase-orders.print', $po->id));

        $response->assertStatus(200);
        $response->assertSee('PURCHASE ORDER');
        $response->assertSee('PO-2026090001');
        $response->assertSee('PT Sumber Makmur Jaya');
        $response->assertSee('Gudang Pusat');
        $response->assertSee('Minyak Goreng 2L');
        $response->assertSee('WarungPro Retail Test');
        $response->assertSee('Harap kirim sebelum jam 12 siang.');
    }
}
