<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class VisitClosingService
{
    public function close(Visit $visit): Visit
    {
        return DB::transaction(function () use ($visit) {
            $visit->loadMissing([
                'services.products.product',
                'retailItems.product',
            ]);

            if ($visit->isClosed()) {
                return $visit;
            }

            foreach ($visit->services as $service) {
                foreach ($service->products as $line) {
                    if (!$line->product) {
                        continue;
                    }

                    $deducted = $line->deducted_units;
                    if (!$deducted && $line->used_grams > 0 && $line->product->package_size_grams > 0) {
                        $deducted = $line->used_grams / $line->product->package_size_grams;
                        $line->update(['deducted_units' => $deducted]);
                    }

                    if ($deducted === 0.0) {
                        continue;
                    }

                    $this->applyAdjustment($line->product, -$deducted, 'Uzavření návštěvy #' . $visit->id);
                }
            }

            foreach ($visit->retailItems as $item) {
                if (!$item->product || $item->quantity_units == 0) {
                    continue;
                }

                $this->applyAdjustment($item->product, -$item->quantity_units, 'Prodej domů u návštěvy #' . $visit->id);
            }

            $visit->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            return $visit;
        });
    }

    protected function applyAdjustment(Product $product, float $delta, string $reason): void
    {
        $product->increment('stock_units', $delta);

        StockAdjustment::create([
            'product_id' => $product->id,
            'delta_units' => $delta,
            'reason' => $reason,
        ]);
    }
}
