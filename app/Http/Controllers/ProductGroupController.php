<?php

namespace App\Http\Controllers;

use App\Models\ProductGroup;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'accent_color' => ['nullable', 'string', 'max:32'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        ProductGroup::create($data);

        return redirect()->back()->with('status', 'Skupina produktu uložená.');
    }

    public function update(Request $request, ProductGroup $productGroup)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'accent_color' => ['nullable', 'string', 'max:32'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $productGroup->update($data);

        return redirect()->back()->with('status', 'Skupina upravena.');
    }

    public function destroy(ProductGroup $productGroup)
    {
        $productGroup->delete();

        return redirect()->back()->with('status', 'Skupina smazána.');
    }
}
