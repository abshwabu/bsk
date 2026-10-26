<?php

namespace App\Services;

use App\Models\Product;

class Cart
{
    public const SESSION_KEY = 'cart';

    /**
     * Get all cart items from session.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    /**
     * Get items formatted as a list with calculated line subtotals.
     *
     * @return list<array<string, mixed>>
     */
    public static function items(): array
    {
        $cart = static::all();
        $items = [];

        foreach ($cart as $id => $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = (float) ($item['price'] ?? 0);

            $items[] = array_merge($item, [
                'id' => (int) $id,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => round($price * $qty, 2),
            ]);
        }

        return $items;
    }

    /**
     * Add a product to the cart.
     */
    public static function add(Product|int $product, int $qty = 1): array
    {
        if (is_int($product)) {
            $product = Product::query()->published()->findOrFail($product);
        }

        $cart = static::all();
        $id = $product->id;
        $qty = max(1, $qty);

        if (isset($cart[$id])) {
            $cart[$id]['qty'] += $qty;
        } else {
            $cart[$id] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) ($product->price ?? 0),
                'currency' => $product->effectiveCurrency(),
                'qty' => $qty,
                'image' => $product->primaryImageUrl(),
                'stock_status' => $product->stock_status,
            ];
        }

        session()->put(self::SESSION_KEY, $cart);

        return $cart;
    }

    /**
     * Update quantity for a specific product in the cart.
     */
    public static function update(int $productId, int $qty): array
    {
        $cart = static::all();

        if ($qty <= 0) {
            unset($cart[$productId]);
        } elseif (isset($cart[$productId])) {
            $cart[$productId]['qty'] = $qty;
        }

        session()->put(self::SESSION_KEY, $cart);

        return $cart;
    }

    /**
     * Remove a product from the cart.
     */
    public static function remove(int $productId): array
    {
        $cart = static::all();
        unset($cart[$productId]);

        session()->put(self::SESSION_KEY, $cart);

        return $cart;
    }

    /**
     * Clear all items from the cart.
     */
    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Total number of items in the cart.
     */
    public static function count(): int
    {
        $cart = static::all();

        return array_sum(array_column($cart, 'qty'));
    }

    /**
     * Subtotal amount of all cart items.
     */
    public static function total(): float
    {
        $total = 0.0;
        foreach (static::all() as $item) {
            $total += ((float) ($item['price'] ?? 0)) * max(1, (int) ($item['qty'] ?? 1));
        }

        return round($total, 2);
    }

    /**
     * Currency code used in the cart.
     */
    public static function currency(): string
    {
        $cart = static::all();
        if (! empty($cart)) {
            $first = reset($cart);
            if (! empty($first['currency'])) {
                return $first['currency'];
            }
        }

        return site('currency') ?: 'USD';
    }

    /**
     * Check if cart is empty.
     */
    public static function isEmpty(): bool
    {
        return empty(static::all());
    }
}
