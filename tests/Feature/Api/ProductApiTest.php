<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehouse;

    protected Unit $unit;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Pusat',
            'code' => 'WH-MAIN',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Pcs',
            'short_name' => 'pcs',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Snack & Makanan',
            'slug' => 'snack-makanan',
            'is_active' => true,
        ]);
    }

    public function test_can_create_product_via_api(): void
    {
        $payload = [
            'name' => 'Keripik Singkong Balado',
            'code' => 'SNK-001',
            'barcode' => '8999999901',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 8000,
            'selling_price' => 12000,
            'min_stock' => 5,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Keripik Singkong Balado',
                    'code' => 'SNK-001',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Keripik Singkong Balado',
            'code' => 'SNK-001',
        ]);

        $product = Product::where('code', 'SNK-001')->first();
        $this->assertNotNull($product);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
    }

    public function test_can_update_product_via_api(): void
    {
        $product = Product::create([
            'name' => 'Keripik Tempe Renyah',
            'code' => 'SNK-002',
            'barcode' => '8999999902',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 7000,
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Keripik Tempe Premium Super',
            'code' => 'SNK-002',
            'barcode' => '8999999902',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 8500,
            'selling_price' => 14000,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/products/'.$product->id, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Keripik Tempe Premium Super',
                    'selling_price' => '14000.00',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Keripik Tempe Premium Super',
        ]);
    }

    public function test_can_create_product_with_multi_units_and_tiered_prices_via_api(): void
    {
        $dusUnit = Unit::create([
            'name' => 'Dus',
            'short_name' => 'dus',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Kopi Kaleng Espresso',
            'code' => 'KOP-001',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'is_active' => true,
            'barcodes' => [
                [
                    'unit_id' => $dusUnit->id,
                    'barcode' => '899888777123',
                ],
            ],
            'conversions' => [
                [
                    'from_unit_id' => $dusUnit->id,
                    'to_unit_id' => $this->unit->id,
                    'conversion_value' => 24,
                ],
            ],
            'tiered_prices' => [
                [
                    'unit_id' => $this->unit->id,
                    'min_qty' => 10,
                    'max_qty' => 50,
                    'price' => 7500,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/products', $payload);

        $response->assertStatus(201);

        $product = Product::where('code', 'KOP-001')->first();
        $this->assertNotNull($product);

        $this->assertDatabaseHas('product_barcodes', [
            'product_id' => $product->id,
            'barcode' => '899888777123',
            'unit_id' => $dusUnit->id,
        ]);

        $this->assertDatabaseHas('unit_conversions', [
            'product_id' => $product->id,
            'from_unit_id' => $dusUnit->id,
            'to_unit_id' => $this->unit->id,
            'conversion_value' => 24,
        ]);

        $this->assertDatabaseHas('tiered_prices', [
            'product_id' => $product->id,
            'unit_id' => $this->unit->id,
            'min_qty' => 10,
            'price' => 7500,
        ]);
    }

    public function test_can_delete_product_via_api(): void
    {
        $product = Product::create([
            'name' => 'Produk Akan Dihapus',
            'code' => 'DEL-001',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/products/'.$product->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}
