<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * Get recipe ingredients and available raw materials for a product.
     */
    public function getProductRecipes(Product $product): JsonResponse
    {
        $product->load([
            'recipes.ingredient.baseUnit',
            'recipes.ingredient.conversions.toUnit',
            'recipes.unit',
        ]);

        $availableIngredients = Product::with(['baseUnit', 'conversions.toUnit'])
            ->where('id', '!=', $product->id)
            ->where('product_type', 'raw_material')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::where('is_active', true)->orderBy('name')->get();

        $totalHpp = $product->calculateRecipeHpp();

        return response()->json([
            'status' => 'success',
            'product' => $product,
            'recipes' => $product->recipes,
            'total_recipe_hpp' => $totalHpp,
            'available_ingredients' => $availableIngredients,
            'units' => $units,
        ]);
    }

    /**
     * Sync / save recipes for a product.
     */
    public function syncProductRecipes(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'recipes' => ['nullable', 'array'],
            'recipes.*.ingredient_product_id' => ['required', 'exists:products,id', 'different:product_id'],
            'recipes.*.quantity' => ['required', 'numeric', 'gt:0'],
            'recipes.*.unit_id' => ['required', 'exists:units,id'],
            'recipes.*.waste_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'recipes.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated, $product) {
            $recipesData = $validated['recipes'] ?? [];
            $incomingIngredientIds = [];

            // Delete existing recipes not in incoming list
            foreach ($recipesData as $item) {
                $incomingIngredientIds[] = $item['ingredient_product_id'];
            }

            $product->recipes()->whereNotIn('ingredient_product_id', $incomingIngredientIds)->delete();

            // Insert or update each recipe item
            foreach ($recipesData as $item) {
                $recipe = Recipe::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'ingredient_product_id' => $item['ingredient_product_id'],
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'unit_id' => $item['unit_id'],
                        'waste_percent' => $item['waste_percent'] ?? 0,
                        'notes' => $item['notes'] ?? null,
                    ]
                );

                // Calculate and store cost estimate
                $recipe->cost_estimate = $recipe->calculateCost();
                $recipe->save();
            }

            // Reload recipes
            $product->load([
                'recipes.ingredient.baseUnit',
                'recipes.ingredient.conversions.toUnit',
                'recipes.unit',
            ]);

            $totalHpp = $product->calculateRecipeHpp();

            return response()->json([
                'status' => 'success',
                'message' => "Resep untuk {$product->name} berhasil diperbarui.",
                'recipes' => $product->recipes,
                'total_recipe_hpp' => $totalHpp,
            ]);
        });
    }

    /**
     * Delete an individual recipe ingredient.
     */
    public function destroy(Recipe $recipe): JsonResponse
    {
        $productId = $recipe->product_id;
        $recipe->delete();

        $product = Product::with(['recipes.ingredient.baseUnit', 'recipes.unit'])->findOrFail($productId);
        $totalHpp = $product->calculateRecipeHpp();

        return response()->json([
            'status' => 'success',
            'message' => 'Bahan resep berhasil dihapus.',
            'recipes' => $product->recipes,
            'total_recipe_hpp' => $totalHpp,
        ]);
    }
}
