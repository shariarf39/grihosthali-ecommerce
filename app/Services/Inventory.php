<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Single entry point for every stock change. Each change locks the product row,
 * updates its stock and writes a StockMovement so the history always adds up.
 */
class Inventory
{
    /**
     * @param  int  $quantity  signed: positive adds stock, negative removes it
     */
    public static function move(Product|int $product, int $quantity, string $type, ?string $reference = null, ?string $note = null, ?float $unitCost = null): ?StockMovement
    {
        if ($quantity === 0) {
            return null;
        }

        return DB::transaction(function () use ($product, $quantity, $type, $reference, $note, $unitCost) {
            $product = Product::whereKey($product instanceof Product ? $product->id : $product)->lockForUpdate()->first();
            if (! $product) {
                return null;
            }

            $before = (int) $product->stock;
            $after = max(0, $before + $quantity);
            $product->forceFill(['stock' => $after])->save();

            // Alert once, at the moment stock crosses below the product's alert level.
            if ($before > $product->low_stock_alert && $after <= $product->low_stock_alert) {
                app(Notifier::class)->lowStock($product);
            }

            return StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $after - $before,
                'stock_before' => $before,
                'stock_after' => $after,
                'unit_cost' => $unitCost,
                'reference' => $reference,
                'note' => $note,
            ]);
        });
    }

    /** Set stock to an exact counted value (physical stock audit). */
    public static function setCount(Product $product, int $count, ?string $note = null): ?StockMovement
    {
        return self::move($product, $count - (int) $product->fresh()->stock, 'audit', null, $note);
    }
}
