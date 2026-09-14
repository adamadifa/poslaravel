<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecipeAndIngredientStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Warehouse $warehouse;

    protected Unit $pcsUnit;

    protected Unit $gramUnit;

    protected Unit $mlUnit;

    protected Category $fnbCategory;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin']);
        $this->adminUser = User::factory()->create(['email' => 'admin@pos.test']);
        $this->adminUser->assignRole('super_admin');

        $this->warehouse = Warehouse::create([
            'code' => 'WH-TEST',
            'name' => 'Outlet Utama',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->pcsUnit = Unit::create(['name' => 'Pcs', 'short_name' => 'pcs', 'is_active' => true]);
        $this->gramUnit = Unit::create(['name' => 'Gram', 'short_name' => 'gr', 'is_active' => true]);
        $this->mlUnit = Unit::create(['name' => 'Mililiter', 'short_name' => 'ml', 'is_active' => true]);

        $this->fnbCategory = Category::create(['name' => 'Coffee & Beverages', 'is_active' => true]);
    }

    public function test_can_sync_product_recipes(): void
    {
        // 1. Create Raw Materials
        $coffeeBean = Product::create([
            'name' => 'Espresso Coffee Beans',
            'code' => 'RAW-BEAN',
            'product_type' => 'raw_material',
            'base_unit_id' => $this->gramUnit->id,
            'purchase_price' => 300, // Rp 300 per gram
            'selling_price' => 0,
            'is_active' => true,
        ]);

        $freshMilk = Product::create([
            'name' => 'Fresh Milk',
            'code' => 'RAW-MILK',
            'product_type' => 'raw_material',
            'base_unit_id' => $this->mlUnit->id,
            'purchase_price' => 25, // Rp 25 per ml
            'selling_price' => 0,
            'is_active' => true,
        ]);

        // 2. Create Menu Item
        $latte = Product::create([
            'name' => 'Ice Caffe Latte',
            'code' => 'MENU-LATTE',
            'product_type' => 'beverage',
            'category_id' => $this->fnbCategory->id,
            'base_unit_id' => $this->pcsUnit->id,
            'purchase_price' => 0,
            'selling_price' => 28000,
            'is_active' => true,
        ]);

        // 3. Post Recipe Sync
        $response = $this->actingAs($this->adminUser)->postJson(route('products.recipes.sync', $latte), [
            'recipes' => [
                [
                    'ingredient_product_id' => $coffeeBean->id,
                    'quantity' => 18,
                    'unit_id' => $this->gramUnit->id,
                    'waste_percent' => 0,
                ],
                [
                    'ingredient_product_id' => $freshMilk->id,
                    'quantity' => 150,
                    'unit_id' => $this->mlUnit->id,
                    'waste_percent' => 0,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('recipes', [
            'product_id' => $latte->id,
            'ingredient_product_id' => $coffeeBean->id,
            'quantity' => 18,
        ]);

        $this->assertDatabaseHas('recipes', [
            'product_id' => $latte->id,
            'ingredient_product_id' => $freshMilk->id,
            'quantity' => 150,
        ]);

        // Total HPP: (18 * 300) + (150 * 25) = 5400 + 3750 = 9150
        $this->assertEquals(9150, $latte->calculateRecipeHpp());
    }

    public function test_sale_process_auto_deducts_raw_materials_from_recipe(): void
    {
        // 1. Raw Materials & Initial Stocks
        $coffeeBean = Product::create([
            'name' => 'Espresso Beans',
            'code' => 'RAW-BEAN-01',
            'product_type' => 'raw_material',
            'base_unit_id' => $this->gramUnit->id,
            'purchase_price' => 300,
            'selling_price' => 0,
            'is_active' => true,
        ]);

        $freshMilk = Product::create([
            'name' => 'Fresh Milk',
            'code' => 'RAW-MILK-01',
            'product_type' => 'raw_material',
            'base_unit_id' => $this->mlUnit->id,
            'purchase_price' => 25,
            'selling_price' => 0,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $coffeeBean->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1000, // 1000g
        ]);

        ProductStock::create([
            'product_id' => $freshMilk->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 2000, // 2000ml
        ]);

        // 2. Menu Item with Recipe
        $latte = Product::create([
            'name' => 'Ice Caffe Latte',
            'code' => 'MENU-LATTE-01',
            'product_type' => 'beverage',
            'category_id' => $this->fnbCategory->id,
            'base_unit_id' => $this->pcsUnit->id,
            'purchase_price' => 0,
            'selling_price' => 28000,
            'is_active' => true,
        ]);

        Recipe::create([
            'product_id' => $latte->id,
            'ingredient_product_id' => $coffeeBean->id,
            'quantity' => 18,
            'unit_id' => $this->gramUnit->id,
            'waste_percent' => 0,
            'cost_estimate' => 5400,
        ]);

        Recipe::create([
            'product_id' => $latte->id,
            'ingredient_product_id' => $freshMilk->id,
            'quantity' => 150,
            'unit_id' => $this->mlUnit->id,
            'waste_percent' => 0,
            'cost_estimate' => 3750,
        ]);

        // 3. Process Sale for 2 Cups of Latte
        $saleService = app(SaleService::class);
        $this->actingAs($this->adminUser);

        $sale = $saleService->processSale([
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'product_id' => $latte->id,
                    'unit_id' => $this->pcsUnit->id,
                    'quantity' => 2,
                    'price' => 28000,
                ],
            ],
            'paid_amount' => 56000,
            'payment_method' => 'cash',
        ]);

        $this->assertInstanceOf(Sale::class, $sale);

        // 4. Verify Raw Material Stock Deductions:
        // Coffee Beans: 1000 - (18 * 2) = 964
        $coffeeStock = ProductStock::where('product_id', $coffeeBean->id)->where('warehouse_id', $this->warehouse->id)->value('quantity');
        $this->assertEquals(964, (float) $coffeeStock);

        // Fresh Milk: 2000 - (150 * 2) = 1700
        $milkStock = ProductStock::where('product_id', $freshMilk->id)->where('warehouse_id', $this->warehouse->id)->value('quantity');
        $this->assertEquals(1700, (float) $milkStock);

        // Stock movement entries
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $coffeeBean->id,
            'reference_type' => 'SaleRecipe',
            'quantity' => 36,
            'type' => 'out',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $freshMilk->id,
            'reference_type' => 'SaleRecipe',
            'quantity' => 300,
            'type' => 'out',
        ]);

        // 5. Void Sale and verify revert
        $saleService->voidSale($sale, 'Customer Cancelled', $this->adminUser->id);

        $revertedCoffeeStock = ProductStock::where('product_id', $coffeeBean->id)->where('warehouse_id', $this->warehouse->id)->value('quantity');
        $this->assertEquals(1000, (float) $revertedCoffeeStock);

        $revertedMilkStock = ProductStock::where('product_id', $freshMilk->id)->where('warehouse_id', $this->warehouse->id)->value('quantity');
        $this->assertEquals(2000, (float) $revertedMilkStock);
    }

    public function test_can_manage_raw_materials_and_keep_separated_from_products(): void
    {
        // 1. Create Raw Material via endpoint
        $response = $this->actingAs($this->adminUser)->post(route('raw-materials.store'), [
            'name' => 'Sirup Hazelnut Monin',
            'code' => 'RAW-SYR-01',
            'base_unit_id' => $this->mlUnit->id,
            'purchase_price' => 120,
            'min_stock' => 100,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('raw-materials.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Sirup Hazelnut Monin',
            'product_type' => 'raw_material',
            'purchase_price' => 120,
        ]);

        $rawItem = Product::where('code', 'RAW-SYR-01')->first();

        // 2. Verify raw-materials.index includes this item and consumes flash message
        $rawResponse = $this->actingAs($this->adminUser)->get(route('raw-materials.index'));
        $rawResponse->assertOk();
        $rawResponse->assertSee('Sirup Hazelnut Monin');

        // 3. Verify products.index excludes this raw material by default
        $prodResponse = $this->actingAs($this->adminUser)->get(route('products.index'));
        $prodResponse->assertOk();
        $prodResponse->assertDontSee('Sirup Hazelnut Monin');

        // 4. Update raw material
        $updateResponse = $this->actingAs($this->adminUser)->put(route('raw-materials.update', $rawItem), [
            'name' => 'Sirup Hazelnut Monin 700ml',
            'code' => 'RAW-SYR-01',
            'base_unit_id' => $this->mlUnit->id,
            'purchase_price' => 135,
            'min_stock' => 150,
            'is_active' => '1',
        ]);
        $updateResponse->assertRedirect(route('raw-materials.index'));
        $this->assertDatabaseHas('products', [
            'id' => $rawItem->id,
            'name' => 'Sirup Hazelnut Monin 700ml',
            'purchase_price' => 135,
        ]);

        // 5. Delete raw material
        $deleteResponse = $this->actingAs($this->adminUser)->delete(route('raw-materials.destroy', $rawItem));
        $deleteResponse->assertRedirect(route('raw-materials.index'));
        $this->assertSoftDeleted('products', ['id' => $rawItem->id]);
    }
}
