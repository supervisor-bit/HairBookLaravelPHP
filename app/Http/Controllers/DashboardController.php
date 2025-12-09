<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Visit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::with(['visits' => fn ($q) => $q->latest('occurred_at')])
            ->orderBy('name')
            ->get();

        $groups = ProductGroup::with('products')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $products = Product::with('group')
            ->orderBy('name')
            ->get();

        $section = $request->get('section', 'clients');
        $lowStockOnly = $section === 'products' ? $request->boolean('low_stock') : false;

        $visits = Visit::with([
            'client',
            'services.products.product.group',
            'retailItems.product.group',
        ])->latest('occurred_at')
            ->limit(20)
            ->get();

        $selectedClient = null;
        $clientStats = ['count' => 0, 'total' => 0];
        if ($clients->isNotEmpty()) {
            $selectedClientId = $request->integer('client') ?: $clients->first()->id;
            $selectedClient = Client::with([
                'visits' => fn ($q) => $q->latest('occurred_at')->with([
                    'services.products.product.group',
                    'retailItems.product.group',
                ]),
                'notes' => fn ($q) => $q->latest(),
            ])->find($selectedClientId);

            if ($selectedClient) {
                $clientStats['count'] = $selectedClient->visits->count();
                $clientStats['total'] = $selectedClient->visits->sum('total_price');
            }
        }

        $groupParam = $request->get('group');
        $selectedGroupId = match (true) {
            $section === 'products' && $groupParam === null => null, // default „Všechny skupiny“
            $groupParam === 'all' => null,
            default => $request->integer('group') ?: optional($groups->first())->id,
        };

        $selectedProduct = null;

        $productMovements = collect();

        if ($section === 'products' && $products->isNotEmpty()) {
            $selectedProductId = $request->integer('product');

            if (!$selectedProductId && $selectedGroupId) {
                $selectedProductId = optional(
                    $products->firstWhere('product_group_id', $selectedGroupId)
                )->id;
            }

            $selectedProductId = $selectedProductId ?: $products->first()->id;
            $selectedProduct = Product::with([
                'group',
                'serviceLines.service.visit.client',
                'retailLines.visit.client',
                'adjustments',
            ])->find($selectedProductId);

            if ($selectedProduct) {
                foreach ($selectedProduct->adjustments as $adj) {
                    $reason = $adj->reason ?? '';

                    // úprava: neukazujeme automatické odpisy z uzavřených návštěv, aby se nezdvojovaly s řádky návštěv
                    if (str_starts_with($reason, 'Uzavření návštěvy') || str_starts_with($reason, 'Prodej domů u návštěvy')) {
                        continue;
                    }

                    $productMovements->push([
                        'date' => $adj->created_at,
                        'delta' => $adj->delta_units,
                        'label' => $reason ?: 'Korekce skladu',
                    ]);
                }

                foreach ($selectedProduct->serviceLines as $line) {
                    $visit = optional($line->service)->visit;
                    if (!$visit) {
                        continue;
                    }
                    $productMovements->push([
                        'date' => $visit->occurred_at ?? $visit->created_at,
                        'delta' => -abs($line->deducted_units),
                        'label' => 'Návštěva ' . optional($visit->client)->name . ' • ' . ($line->service->title ?? 'služba'),
                    ]);
                }

                foreach ($selectedProduct->retailLines as $line) {
                    $visit = $line->visit;
                    if (!$visit) {
                        continue;
                    }
                    $productMovements->push([
                        'date' => $visit->occurred_at ?? $visit->created_at,
                        'delta' => -abs($line->quantity_units),
                        'label' => 'Prodej domů ' . optional($visit->client)->name,
                    ]);
                }

                $productMovements = $productMovements->sortByDesc('date')->take(15)->values();
            }
        }

        $productFormMode = $request->get('product_mode', 'create');

        $productDataset = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'group' => $p->group?->name,
                'usage' => $p->usage_type,
                'stock' => $p->stock_units,
                'min' => $p->min_units,
                'package_size_grams' => $p->package_size_grams,
            ];
        })->values();

        return view('dashboard', [
            'clients' => $clients,
            'groups' => $groups,
            'products' => $products,
            'visits' => $visits,
            'selectedClient' => $selectedClient,
            'selectedGroupId' => $selectedGroupId,
            'section' => $section,
            'selectedProduct' => $selectedProduct,
            'productFormMode' => $productFormMode,
            'productMovements' => $productMovements,
            'lowStockOnly' => $lowStockOnly,
            'productDataset' => $productDataset,
            'clientStats' => $clientStats,
        ]);
    }
}
