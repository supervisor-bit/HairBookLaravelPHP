<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason_type' => ['nullable', 'in:work,retail'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $delta = $data['direction'] === 'in'
            ? $data['quantity']
            : -$data['quantity'];

        $reason = match ($data['direction']) {
            'in' => 'Příjem na sklad',
            'out' => $data['reason_type'] === 'retail'
                ? 'Výdej - prodej domů'
                : 'Výdej - práce v salonu',
        };

        if (!empty($data['note'])) {
            $reason .= ' • ' . $data['note'];
        }

        $projected = $product->stock_units + $delta;
        if ($projected < 0) {
            return redirect()->back()->with('status', 'Výdej by snížil stav pod nulu, uprav množství.');
        }

        DB::transaction(function () use ($product, $delta, $reason) {
            $product->increment('stock_units', $delta);

            StockAdjustment::create([
                'product_id' => $product->id,
                'delta_units' => $delta,
                'reason' => $reason,
            ]);
        });

        return redirect()->back()->with('status', 'Pohyb zapsán.');
    }

    public function storeBatch(Request $request)
    {
        $data = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.product_id' => ['required', 'exists:products,id'],
            'rows.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['rows'] as $row) {
                $product = Product::find($row['product_id']);
                if (!$product) {
                    continue;
                }

                $product->increment('stock_units', $row['quantity']);

                StockAdjustment::create([
                    'product_id' => $product->id,
                    'delta_units' => $row['quantity'],
                    'reason' => 'Hromadný příjem',
                ]);
            }
        });

        return redirect()->back()->with('status', 'Hromadný příjem zapsán.');
    }

    public function bulkReceiptForm()
    {
        $products = Product::with('productGroup')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $productDataset = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'stock' => $p->stock_units,
            'group_name' => $p->productGroup?->name,
        ]);

        $groups = \App\Models\ProductGroup::orderBy('name')->get();

        return view('products.bulk-receipt', [
            'products' => $products,
            'productDataset' => $productDataset,
            'groups' => $groups,
        ]);
    }
}
