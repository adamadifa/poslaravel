<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\ModifierRecipe;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class FnbMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouse = Warehouse::where('is_default', true)->first() ?? Warehouse::first();
        if (! $warehouse) {
            $warehouse = Warehouse::create([
                'code' => 'WH-MAIN',
                'name' => 'Outlet Utama',
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        // 1. Units (Satuan)
        $pcs = Unit::firstOrCreate(['name' => 'Pcs'], ['short_name' => 'pcs', 'is_active' => true]);
        $gram = Unit::firstOrCreate(['name' => 'Gram'], ['short_name' => 'gr', 'is_active' => true]);
        $ml = Unit::firstOrCreate(['name' => 'Mililiter'], ['short_name' => 'ml', 'is_active' => true]);
        $kg = Unit::firstOrCreate(['name' => 'Kilogram'], ['short_name' => 'kg', 'is_active' => true]);
        $liter = Unit::firstOrCreate(['name' => 'Liter'], ['short_name' => 'ltr', 'is_active' => true]);
        $porsi = Unit::firstOrCreate(['name' => 'Porsi'], ['short_name' => 'prs', 'is_active' => true]);

        // 2. Categories
        $catCoffee = Category::firstOrCreate(['name' => 'Coffee & Espresso Bar'], ['slug' => 'coffee-espresso-bar', 'is_active' => true]);
        $catNonCoffee = Category::firstOrCreate(['name' => 'Tea & Non-Coffee'], ['slug' => 'tea-non-coffee', 'is_active' => true]);
        $catFood = Category::firstOrCreate(['name' => 'Food & Snack'], ['slug' => 'food-snack', 'is_active' => true]);
        $catRaw = Category::firstOrCreate(['name' => 'Bahan Baku & Dapur'], ['slug' => 'bahan-baku-dapur', 'is_active' => true]);

        // 3. Raw Materials (Bahan Baku)
        $rawCoffeeBean = Product::firstOrCreate(
            ['code' => 'RAW-COFFEE-01'],
            [
                'name' => 'Biji Kopi Espresso Blend',
                'slug' => 'biji-kopi-espresso-blend',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $gram->id,
                'purchase_price' => 280, // Rp 280 per gram (Rp 280.000 / kg)
                'selling_price' => 0,
                'min_stock' => 500,
                'is_active' => true,
            ]
        );

        $rawFreshMilk = Product::firstOrCreate(
            ['code' => 'RAW-MILK-01'],
            [
                'name' => 'Fresh Milk Full Cream',
                'slug' => 'fresh-milk-full-cream',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $ml->id,
                'purchase_price' => 22, // Rp 22 per ml (Rp 22.000 / liter)
                'selling_price' => 0,
                'min_stock' => 2000,
                'is_active' => true,
            ]
        );

        $rawMatcha = Product::firstOrCreate(
            ['code' => 'RAW-MATCHA-01'],
            [
                'name' => 'Bubuk Matcha Pure Uji',
                'slug' => 'bubuk-matcha-pure-uji',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $gram->id,
                'purchase_price' => 450, // Rp 450 per gram
                'selling_price' => 0,
                'min_stock' => 200,
                'is_active' => true,
            ]
        );

        $rawSyrup = Product::firstOrCreate(
            ['code' => 'RAW-SYRUP-01'],
            [
                'name' => 'Gula Cair (Simple Syrup)',
                'slug' => 'gula-cair-simple-syrup',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $ml->id,
                'purchase_price' => 15, // Rp 15 per ml
                'selling_price' => 0,
                'min_stock' => 1000,
                'is_active' => true,
            ]
        );

        $rawCup = Product::firstOrCreate(
            ['code' => 'RAW-CUP-01'],
            [
                'name' => 'Cup Plastik 16oz + Tutup Dome',
                'slug' => 'cup-plastik-16oz-tutup-dome',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 750, // Rp 750 per pcs
                'selling_price' => 0,
                'min_stock' => 100,
                'is_active' => true,
            ]
        );

        $rawStraw = Product::firstOrCreate(
            ['code' => 'RAW-STRAW-01'],
            [
                'name' => 'Sedotan Paper Straw Steril',
                'slug' => 'sedotan-paper-straw-steril',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 150, // Rp 150 per pcs
                'selling_price' => 0,
                'min_stock' => 200,
                'is_active' => true,
            ]
        );

        $rawBun = Product::firstOrCreate(
            ['code' => 'RAW-BUN-01'],
            [
                'name' => 'Brioche Burger Bun',
                'slug' => 'brioche-burger-bun',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 3500, // Rp 3.500 per pcs
                'selling_price' => 0,
                'min_stock' => 20,
                'is_active' => true,
            ]
        );

        $rawPatty = Product::firstOrCreate(
            ['code' => 'RAW-PATTY-01'],
            [
                'name' => 'Australian Beef Patty 100g',
                'slug' => 'australian-beef-patty-100g',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 12500, // Rp 12.500 per pcs
                'selling_price' => 0,
                'min_stock' => 30,
                'is_active' => true,
            ]
        );

        $rawCheese = Product::firstOrCreate(
            ['code' => 'RAW-CHEESE-01'],
            [
                'name' => 'Keju Slice Red Cheddar',
                'slug' => 'keju-slice-red-cheddar',
                'product_type' => 'raw_material',
                'category_id' => $catRaw->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 2000, // Rp 2.000 per slice
                'selling_price' => 0,
                'min_stock' => 50,
                'is_active' => true,
            ]
        );

        // 4. Initial Stock for Raw Materials
        ProductStock::updateOrCreate(['product_id' => $rawCoffeeBean->id, 'warehouse_id' => $warehouse->id], ['quantity' => 15000]); // 15 kg
        ProductStock::updateOrCreate(['product_id' => $rawFreshMilk->id, 'warehouse_id' => $warehouse->id], ['quantity' => 30000]);  // 30 liter
        ProductStock::updateOrCreate(['product_id' => $rawMatcha->id, 'warehouse_id' => $warehouse->id], ['quantity' => 2500]);     // 2.5 kg
        ProductStock::updateOrCreate(['product_id' => $rawSyrup->id, 'warehouse_id' => $warehouse->id], ['quantity' => 10000]);     // 10 liter
        ProductStock::updateOrCreate(['product_id' => $rawCup->id, 'warehouse_id' => $warehouse->id], ['quantity' => 500]);        // 500 pcs
        ProductStock::updateOrCreate(['product_id' => $rawStraw->id, 'warehouse_id' => $warehouse->id], ['quantity' => 1000]);     // 1000 pcs
        ProductStock::updateOrCreate(['product_id' => $rawBun->id, 'warehouse_id' => $warehouse->id], ['quantity' => 100]);         // 100 pcs
        ProductStock::updateOrCreate(['product_id' => $rawPatty->id, 'warehouse_id' => $warehouse->id], ['quantity' => 150]);       // 150 pcs
        ProductStock::updateOrCreate(['product_id' => $rawCheese->id, 'warehouse_id' => $warehouse->id], ['quantity' => 200]);      // 200 pcs

        // 5. Finished Goods F&B (Menu yang Dijual ke Kasir)
        // Menu 1: Ice Caffe Latte
        $latte = Product::firstOrCreate(
            ['code' => 'MENU-LATTE'],
            [
                'name' => 'Ice Caffe Latte',
                'slug' => 'ice-caffe-latte',
                'product_type' => 'beverage',
                'category_id' => $catCoffee->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 0,
                'selling_price' => 28000,
                'min_stock' => 0,
                'is_active' => true,
            ]
        );

        // Resep Ice Caffe Latte
        Recipe::updateOrCreate(['product_id' => $latte->id, 'ingredient_product_id' => $rawCoffeeBean->id], ['quantity' => 18, 'unit_id' => $gram->id, 'cost_estimate' => 18 * 280]);
        Recipe::updateOrCreate(['product_id' => $latte->id, 'ingredient_product_id' => $rawFreshMilk->id], ['quantity' => 150, 'unit_id' => $ml->id, 'cost_estimate' => 150 * 22]);
        Recipe::updateOrCreate(['product_id' => $latte->id, 'ingredient_product_id' => $rawSyrup->id], ['quantity' => 10, 'unit_id' => $ml->id, 'cost_estimate' => 10 * 15]);
        Recipe::updateOrCreate(['product_id' => $latte->id, 'ingredient_product_id' => $rawCup->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 750]);
        Recipe::updateOrCreate(['product_id' => $latte->id, 'ingredient_product_id' => $rawStraw->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 150]);

        // Menu 2: Ice Americano
        $americano = Product::firstOrCreate(
            ['code' => 'MENU-AMERICANO'],
            [
                'name' => 'Ice Americano',
                'slug' => 'ice-americano',
                'product_type' => 'beverage',
                'category_id' => $catCoffee->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 0,
                'selling_price' => 22000,
                'min_stock' => 0,
                'is_active' => true,
            ]
        );

        // Resep Ice Americano
        Recipe::updateOrCreate(['product_id' => $americano->id, 'ingredient_product_id' => $rawCoffeeBean->id], ['quantity' => 18, 'unit_id' => $gram->id, 'cost_estimate' => 18 * 280]);
        Recipe::updateOrCreate(['product_id' => $americano->id, 'ingredient_product_id' => $rawCup->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 750]);
        Recipe::updateOrCreate(['product_id' => $americano->id, 'ingredient_product_id' => $rawStraw->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 150]);

        // Menu 3: Ice Uji Matcha Latte
        $matchaLatte = Product::firstOrCreate(
            ['code' => 'MENU-MATCHA'],
            [
                'name' => 'Ice Uji Matcha Latte',
                'slug' => 'ice-uji-matcha-latte',
                'product_type' => 'beverage',
                'category_id' => $catNonCoffee->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 0,
                'selling_price' => 32000,
                'min_stock' => 0,
                'is_active' => true,
            ]
        );

        // Resep Ice Matcha Latte
        Recipe::updateOrCreate(['product_id' => $matchaLatte->id, 'ingredient_product_id' => $rawMatcha->id], ['quantity' => 12, 'unit_id' => $gram->id, 'cost_estimate' => 12 * 450]);
        Recipe::updateOrCreate(['product_id' => $matchaLatte->id, 'ingredient_product_id' => $rawFreshMilk->id], ['quantity' => 160, 'unit_id' => $ml->id, 'cost_estimate' => 160 * 22]);
        Recipe::updateOrCreate(['product_id' => $matchaLatte->id, 'ingredient_product_id' => $rawSyrup->id], ['quantity' => 15, 'unit_id' => $ml->id, 'cost_estimate' => 15 * 15]);
        Recipe::updateOrCreate(['product_id' => $matchaLatte->id, 'ingredient_product_id' => $rawCup->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 750]);
        Recipe::updateOrCreate(['product_id' => $matchaLatte->id, 'ingredient_product_id' => $rawStraw->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 150]);

        // Menu 4: Classic Beef Cheeseburger
        $burger = Product::firstOrCreate(
            ['code' => 'MENU-BURGER'],
            [
                'name' => 'Classic Beef Cheeseburger',
                'slug' => 'classic-beef-cheeseburger',
                'product_type' => 'food',
                'category_id' => $catFood->id,
                'base_unit_id' => $pcs->id,
                'purchase_price' => 0,
                'selling_price' => 42000,
                'min_stock' => 0,
                'is_active' => true,
            ]
        );

        // Resep Cheeseburger
        Recipe::updateOrCreate(['product_id' => $burger->id, 'ingredient_product_id' => $rawBun->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 3500]);
        Recipe::updateOrCreate(['product_id' => $burger->id, 'ingredient_product_id' => $rawPatty->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 12500]);
        Recipe::updateOrCreate(['product_id' => $burger->id, 'ingredient_product_id' => $rawCheese->id], ['quantity' => 1, 'unit_id' => $pcs->id, 'cost_estimate' => 2000]);

        // 6. Modifiers (Add-ons) & Modifier Recipes
        $modGroupCoffee = ModifierGroup::firstOrCreate(
            ['name' => 'Extra & Add-on Kopi'],
            [
                'selection_type' => 'multiple',
                'is_required' => false,
                'is_active' => true,
            ]
        );
        $modGroupCoffee->products()->syncWithoutDetaching([$latte->id, $americano->id, $matchaLatte->id]);

        $modExtraShot = Modifier::firstOrCreate(
            ['modifier_group_id' => $modGroupCoffee->id, 'name' => 'Extra Espresso Shot (+18g)'],
            [
                'price_adjustment' => 6000,
                'is_default' => false,
                'is_active' => true,
            ]
        );
        ModifierRecipe::updateOrCreate(
            ['modifier_id' => $modExtraShot->id, 'ingredient_product_id' => $rawCoffeeBean->id],
            ['quantity' => 18, 'unit_id' => $gram->id]
        );

        $modGroupBurger = ModifierGroup::firstOrCreate(
            ['name' => 'Extra Topping Burger'],
            [
                'selection_type' => 'multiple',
                'is_required' => false,
                'is_active' => true,
            ]
        );
        $modGroupBurger->products()->syncWithoutDetaching([$burger->id]);

        $modExtraCheese = Modifier::firstOrCreate(
            ['modifier_group_id' => $modGroupBurger->id, 'name' => 'Double Cheese (+1 Slice)'],
            [
                'price_adjustment' => 4000,
                'is_default' => false,
                'is_active' => true,
            ]
        );
        ModifierRecipe::updateOrCreate(
            ['modifier_id' => $modExtraCheese->id, 'ingredient_product_id' => $rawCheese->id],
            ['quantity' => 1, 'unit_id' => $pcs->id]
        );

        // 7. Dining Tables (Denah Meja Resto)
        DiningTable::firstOrCreate(['table_number' => 'T01'], ['warehouse_id' => $warehouse->id, 'area' => 'Indoor AC', 'capacity' => 2, 'status' => 'available', 'is_active' => true]);
        DiningTable::firstOrCreate(['table_number' => 'T02'], ['warehouse_id' => $warehouse->id, 'area' => 'Indoor AC', 'capacity' => 4, 'status' => 'available', 'is_active' => true]);
        DiningTable::firstOrCreate(['table_number' => 'T03'], ['warehouse_id' => $warehouse->id, 'area' => 'Indoor AC', 'capacity' => 4, 'status' => 'available', 'is_active' => true]);
        DiningTable::firstOrCreate(['table_number' => 'OUT-01'], ['warehouse_id' => $warehouse->id, 'area' => 'Outdoor Garden', 'capacity' => 4, 'status' => 'available', 'is_active' => true]);
        DiningTable::firstOrCreate(['table_number' => 'OUT-02'], ['warehouse_id' => $warehouse->id, 'area' => 'Outdoor Garden', 'capacity' => 6, 'status' => 'available', 'is_active' => true]);
        DiningTable::firstOrCreate(['table_number' => 'VIP-1'], ['warehouse_id' => $warehouse->id, 'area' => 'VIP Room', 'capacity' => 8, 'status' => 'available', 'is_active' => true]);
    }
}
