<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        // Support both form and API requests
        $data = $request->validate([
            'product_group_id' => ['nullable', 'exists:product_groups,id'],
            'group_name' => ['nullable', 'string', 'max:255'], // For API/CSV import
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'usage_type' => ['nullable', 'in:service,retail,both'],
            'package_size_grams' => ['nullable', 'numeric', 'min:0'],
            'stock_units' => ['nullable', 'numeric', 'min:0'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'], // For API/CSV import
            'unit' => ['nullable', 'in:ks,g'], // For API/CSV import
            'min_units' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        // If group_name is provided, find or create the group
        if (!empty($data['group_name'])) {
            $group = ProductGroup::firstOrCreate(
                ['name' => $data['group_name']],
                ['accent_color' => '#7dd3fc']
            );
            $data['product_group_id'] = $group->id;
            unset($data['group_name']);
        }

        // Handle initial_stock (for API/CSV import)
        if (isset($data['initial_stock'])) {
            $data['stock_units'] = $data['initial_stock'];
            unset($data['initial_stock']);
        }

        // Handle unit conversion
        if (isset($data['unit'])) {
            if ($data['unit'] === 'g') {
                $data['package_size_grams'] = 1; // 1g packages
            }
            unset($data['unit']);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['usage_type'] = $data['usage_type'] ?? 'both';
        $data['package_size_grams'] = $data['package_size_grams'] ?? 0;
        $data['stock_units'] = $data['stock_units'] ?? 0;
        $data['min_units'] = $data['min_units'] ?? 0;

        $product = Product::create($data);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            // Load relation for group name
            $product->load('group');
            
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock_units,
                'group' => $product->group ? $product->group->name : null,
                'usage' => $product->usage_type,
                'min' => $product->min_units,
                'package_size_grams' => $product->package_size_grams,
            ], 201);
        }

        // Redirect back to bulk receipt if requested
        if ($request->input('redirect_bulk')) {
            return redirect()->route('products.bulk-receipt')->with('status', 'Produkt vytvořen. Můžete pokračovat v zadávání příjmu.');
        }

        return redirect()->back()->with('status', 'Produkt uložen.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'product_group_id' => ['nullable', 'exists:product_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'usage_type' => ['required', 'in:service,retail,both'],
            'package_size_grams' => ['nullable', 'numeric', 'min:0'],
            'stock_units' => ['nullable', 'numeric', 'min:0'],
            'min_units' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['package_size_grams'] = $data['package_size_grams'] ?? 0;
        $data['min_units'] = $data['min_units'] ?? 0;

        $product->update($data);

        return redirect()->back()->with('status', 'Produkt upraven.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('dashboard', ['section' => 'products'])->with('status', 'Produkt smazán.');
    }
}
