<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    protected function checkModule(): void
    {
        if (! site()->isModuleEnabled('catalog')) {
            abort(404);
        }
    }

    /**
     * Display the shopping cart.
     */
    public function index(): View
    {
        $this->checkModule();

        return view('shop.cart', [
            'items' => Cart::items(),
            'total' => Cart::total(),
            'currency' => Cart::currency(),
            'count' => Cart::count(),
        ]);
    }

    /**
     * Add an item to the cart.
     */
    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $this->checkModule();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $product = Product::query()->published()->findOrFail($validated['product_id']);
        $qty = (int) ($validated['qty'] ?? 1);

        Cart::add($product, $qty);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$product->name} added to cart.",
                'count' => Cart::count(),
                'total' => Cart::total(),
                'currency' => Cart::currency(),
            ]);
        }

        return back()->with('cart_success', "{$product->name} added to your cart.");
    }

    /**
     * Update quantity for an item.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $this->checkModule();

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        Cart::update((int) $validated['product_id'], (int) $validated['qty']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count' => Cart::count(),
                'total' => Cart::total(),
                'currency' => Cart::currency(),
                'items' => Cart::items(),
            ]);
        }

        return back();
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $this->checkModule();

        Cart::remove($productId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count' => Cart::count(),
                'total' => Cart::total(),
                'currency' => Cart::currency(),
                'items' => Cart::items(),
            ]);
        }

        return back()->with('cart_success', 'Item removed from cart.');
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $this->checkModule();

        Cart::clear();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count' => 0,
                'total' => 0,
                'currency' => Cart::currency(),
                'items' => [],
            ]);
        }

        return back()->with('cart_success', 'Cart cleared.');
    }
}
