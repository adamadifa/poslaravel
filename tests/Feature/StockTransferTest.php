<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Warehouse $warehouseFrom;

    protected Warehouse $warehouseTo;

    protected Unit $unit;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->adminUser = User::where('email', 'admin@pospro.com')->first();

        $this->warehouseFrom = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Gudang Pusat',
            'address' => 'Tasikmalaya',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->warehouseTo = Warehouse::create([
            'code' => 'WH-02',
            'name' => 'Gudang Cabang Bandung',
            'address' => 'Bandung',
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->unit = Unit::create(['name' => 'Pcs', 'short_name' => 'pcs', 'is_active' => true]);

        $category = Category::create(['name' => 'Snack', 'slug' => 'snack', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'base_unit_id' => $this->unit->id,
            'code' => 'PRD-00016',
            'name' => 'Bengbeng',
            'purchase_price' => 2000,
            'selling_price' => 2500,
            'is_active' => true,
        ]);
    }

    public function test_can_create_draft_and_dispatch_stock_transfer(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('stock-transfers.store'), [
            'from_warehouse_id' => $this->warehouseFrom->id,
            'to_warehouse_id' => $this->warehouseTo->id,
            'transfer_date' => now()->toDateString(),
            'notes' => 'Transfer Bengbeng ke Bandung',
            'action' => 'dispatch',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity_sent' => '5,00',
                ],
            ],
        ]);

        $response->assertRedirect(route('stock-transfers.index'));
        $this->assertDatabaseHas('stock_transfers', [
            'from_warehouse_id' => $this->warehouseFrom->id,
            'to_warehouse_id' => $this->warehouseTo->id,
            'status' => 'in_transit',
        ]);

        $transfer = StockTransfer::first();
        $this->assertEquals(5.0, (float) $transfer->items->first()->quantity_sent);
    }

    public function test_can_receive_stock_transfer(): void
    {
        // First create in_transit transfer
        $this->actingAs($this->adminUser)->post(route('stock-transfers.store'), [
            'from_warehouse_id' => $this->warehouseFrom->id,
            'to_warehouse_id' => $this->warehouseTo->id,
            'transfer_date' => now()->toDateString(),
            'action' => 'dispatch',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity_sent' => 5,
                ],
            ],
        ]);

        $transfer = StockTransfer::first();
        $item = $transfer->items->first();

        // Receive transfer
        $response = $this->actingAs($this->adminUser)->post(route('stock-transfers.receive', $transfer->id), [
            'items' => [
                $item->id => [
                    'quantity_received' => '5,00',
                ],
            ],
        ]);

        $response->assertRedirect(route('stock-transfers.index'));
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transfer->id,
            'status' => 'completed',
        ]);

        $transfer->refresh();
        $this->assertEquals(5.0, (float) $transfer->items->first()->quantity_received);
    }
}
