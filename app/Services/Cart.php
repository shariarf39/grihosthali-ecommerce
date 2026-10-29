<?php

namespace App\Services;

use App\Models\Product;

/**
 * Session-backed shopping cart. Stores only product id => quantity;
 * prices are always read fresh from the database.
 */
class Cart
{
    private const KEY = 'cart';

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function quantity(int $productId): int
    {
        return $this->raw()[$productId] ?? 0;
    }

    public function set(int $productId, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        session([self::KEY => $cart]);
    }

    public function add(int $productId, int $qty = 1): void
    {
        $this->set($productId, $this->quantity($productId) + $qty);
    }

    public function clear(): void
    {
        session()->forget([self::KEY, 'coupon']);
    }

    /** @return array<int, array{product: Product, qty: int, total: float}> */
    public function items(): array
    {
        $raw = $this->raw();
        if (! $raw) {
            return [];
        }
        $products = Product::active()->whereIn('id', array_keys($raw))->get()->keyBy('id');

        $items = [];
        foreach ($raw as $id => $qty) {
            if ($product = $products->get($id)) {
                $items[] = ['product' => $product, 'qty' => $qty, 'total' => $product->sell_price * $qty];
            }
        }

        return $items;
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function subtotal(): float
    {
        return array_sum(array_column($this->items(), 'total'));
    }

    /** Payload consumed by the cart sidebar and product cards on the frontend. */
    public function toArray(): array
    {
        $items = $this->items();

        return [
            'count' => array_sum(array_column($items, 'qty')),
            'subtotal' => array_sum(array_column($items, 'total')),
            'subtotal_text' => taka(array_sum(array_column($items, 'total'))),
            'items' => array_map(fn ($i) => [
                'id' => $i['product']->id,
                'name' => $i['product']->name,
                'image' => $i['product']->image_url,
                'url' => route('product.show', $i['product']->slug),
                'price_text' => taka($i['product']->sell_price),
                'qty' => $i['qty'],
                'total_text' => taka($i['total']),
            ], $items),
        ];
    }
}
