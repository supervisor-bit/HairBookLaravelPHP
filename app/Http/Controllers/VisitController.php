<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\ServiceTemplate;
use App\Models\Visit;
use App\Services\VisitClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function create(Client $client)
    {
        $serviceTemplates = ServiceTemplate::orderBy('position')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $productGroups = \App\Models\ProductGroup::orderBy('name')->get();
        $previousVisits = $client->visits()
            ->withCount(['services', 'retailItems'])
            ->orderBy('occurred_at', 'desc')
            ->take(10)
            ->get();
        
        // Load duplicated visit if present in session
        $duplicatedVisitId = session('duplicatedVisit');
        $duplicatedVisit = null;
        if ($duplicatedVisitId) {
            $duplicatedVisit = Visit::with(['services.products.product'])
                ->select(['id', 'client_id', 'total_price', 'note', 'occurred_at'])
                ->find($duplicatedVisitId);
        }
        
        return view('visits.create', compact('client', 'serviceTemplates', 'products', 'productGroups', 'previousVisits', 'duplicatedVisit'));
    }
    
    public function show(Visit $visit)
    {
        $visit->load(['client', 'services.products.product', 'retailItems.product']);
        
        return view('visits.show', compact('visit'));
    }

    public function store(Request $request, VisitClosingService $closer)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'occurred_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'retail_price' => ['nullable', 'numeric', 'min:0'],
            'services' => ['array'],
            'services.*.title' => ['nullable', 'string', 'max:255'],
            'services.*.note' => ['nullable', 'string'],
            'services.*.products' => ['array'],
            'services.*.products.*.product_id' => ['required_with:services.*.products.*.used_grams', 'exists:products,id'],
            'services.*.products.*.used_grams' => ['nullable', 'numeric', 'min:0'],
            'retail' => ['array'],
            'retail.*.product_id' => ['required_with:retail.*.quantity_units', 'exists:products,id'],
            'retail.*.quantity_units' => ['nullable', 'numeric', 'min:0'],
            'retail.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'close_now' => ['sometimes', 'boolean'],
        ]);

        $closeNow = $request->boolean('close_now', false);

        $visit = null;

        DB::transaction(function () use (&$visit, $validated, $closeNow, $closer) {
            $visit = Visit::create([
                'client_id' => $validated['client_id'],
                'occurred_at' => $validated['occurred_at'] ?? now(),
                'note' => $validated['note'] ?? null,
                'total_price' => $validated['total_price'] ?? 0,
                'retail_price' => $validated['retail_price'] ?? null,
            ]);

            $position = 1;
            foreach ($validated['services'] ?? [] as $serviceData) {
                $title = trim($serviceData['title'] ?? '');
                if ($title === '') {
                    continue;
                }

                $service = $visit->services()->create([
                    'title' => $title,
                    'note' => $serviceData['note'] ?? null,
                    'position' => $position++,
                ]);

                foreach ($serviceData['products'] ?? [] as $productLine) {
                    $productId = $productLine['product_id'] ?? null;
                    $usedGrams = (float) ($productLine['used_grams'] ?? 0);
                    if (!$productId || $usedGrams <= 0) {
                        continue;
                    }

                    $product = Product::find($productId);
                    $deductedUnits = $product && $product->package_size_grams > 0
                        ? $usedGrams / $product->package_size_grams
                        : 0;

                    $service->products()->create([
                        'product_id' => $productId,
                        'used_grams' => $usedGrams,
                        'deducted_units' => $deductedUnits,
                    ]);
                }
            }

            foreach ($validated['retail'] ?? [] as $retailLine) {
                $productId = $retailLine['product_id'] ?? null;
                $quantity = (float) ($retailLine['quantity_units'] ?? 0);
                if (!$productId || $quantity <= 0) {
                    continue;
                }

                $visit->retailItems()->create([
                    'product_id' => $productId,
                    'quantity_units' => $quantity,
                    'unit_price' => $retailLine['unit_price'] ?? 0,
                ]);
            }

            if ($closeNow) {
                $closer->close($visit);
            }
        });

        $clientId = $visit?->client_id ?? $validated['client_id'];

        return redirect()->route('dashboard', ['section' => 'clients', 'client' => $clientId])
            ->with('status', $closeNow ? 'Návštěva uzavřena a odepsána ze skladu.' : 'Návštěva uložená jako koncept.')
            ->with('newVisitId', $visit->id);
    }

    public function close(Visit $visit, VisitClosingService $closer)
    {
        if ($visit->isClosed()) {
            return redirect()->back()->with('status', 'Návštěva už byla uzavřená.');
        }

        $closer->close($visit);

        return redirect()->back()
            ->with('status', 'Návštěva uzavřena a odepsána ze skladu.')
            ->with('newVisitId', $visit->id);
    }

    public function duplicate(Request $request, Visit $visit, VisitClosingService $closer)
    {
        // Duplikace vždy vytvoří otevřenou návštěvu (bez uzavření)
        $closeNow = false;
        $occurredAt = now();

        $visit->loadMissing('services.products.product');

        // Žádná kontrola skladu, protože návštěva zůstává otevřená
        $newVisit = null;

        DB::transaction(function () use (&$newVisit, $visit, $occurredAt, $closeNow, $closer) {
            // Duplikace bez retail_price - produkty pro domácí použití se neduplikují
            $newVisit = Visit::create([
                'client_id' => $visit->client_id,
                'occurred_at' => $occurredAt,
                'note' => $visit->note,
                'total_price' => $visit->total_price,
            ]);

            $position = 1;
            foreach ($visit->services as $service) {
                $newService = $newVisit->services()->create([
                    'title' => $service->title,
                    'note' => $service->note,
                    'position' => $position++,
                ]);

                foreach ($service->products as $line) {
                    if (!$line->product) {
                        continue;
                    }

                    $deducted = $line->deducted_units;
                    if (!$deducted && $line->used_grams > 0 && $line->product->package_size_grams > 0) {
                        $deducted = $line->used_grams / $line->product->package_size_grams;
                    }

                    $newService->products()->create([
                        'product_id' => $line->product_id,
                        'used_grams' => $line->used_grams,
                        'deducted_units' => $deducted ?? 0,
                    ]);
                }
            }

            if ($closeNow) {
                $closer->close($newVisit);
            }
        });

        return redirect()->route('visits.create', ['client' => $visit->client_id])
            ->with('duplicatedVisit', $newVisit->id);
    }
}
