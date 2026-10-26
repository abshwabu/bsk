<?php

namespace App\Http\Controllers;

use App\Models\OrderRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Cart;
use App\Services\TelegramNotifier;
use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ShopController extends Controller
{
    protected function checkModule(): void
    {
        if (! site()->isModuleEnabled('catalog')) {
            abort(404);
        }
    }

    protected function canPreviewUnpublished(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        if (method_exists($user, 'canAccessPanel')) {
            try {
                return (bool) $user->canAccessPanel(Filament::getPanel('admin'));
            } catch (Throwable) {
                return true;
            }
        }

        return true;
    }

    /**
     * Display product catalog grid with category filter and search.
     */
    public function index(Request $request): View
    {
        $this->checkModule();

        $categories = ProductCategory::query()->orderBy('sort')->get();
        $selectedCategory = $request->input('category');
        $search = trim((string) $request->input('q'));

        $query = Product::query()
            ->published()
            ->with('category')
            ->orderBy('sort')
            ->orderByDesc('created_at');

        if ($selectedCategory) {
            $query->whereHas('category', function ($q) use ($selectedCategory) {
                $q->where('slug', $selectedCategory);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(12)->withQueryString();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'cartCount' => Cart::count(),
        ]);
    }

    /**
     * Display a single product detail page.
     */
    public function show(string $slug): View
    {
        $this->checkModule();

        $canPreview = $this->canPreviewUnpublished();

        $product = Product::query()
            ->where('slug', $slug)
            ->with('category')
            ->first();

        if (! $product) {
            abort(404);
        }

        if (! $product->is_published && ! $canPreview) {
            abort(404);
        }

        // Related products from the same category
        $related = Product::query()
            ->published()
            ->where('id', '!=', $product->id)
            ->when($product->product_category_id, fn ($q) => $q->where('product_category_id', $product->product_category_id))
            ->take(3)
            ->get();

        return view('shop.show', [
            'product' => $product,
            'related' => $related,
            'cartCount' => Cart::count(),
        ]);
    }

    /**
     * Display checkout page for order submission.
     */
    public function checkout(): View|RedirectResponse
    {
        $this->checkModule();

        if (Cart::isEmpty()) {
            return redirect()->route('shop.index')->with('notice', 'Your cart is empty. Add products to request an order.');
        }

        return view('shop.checkout', [
            'items' => Cart::items(),
            'total' => Cart::total(),
            'currency' => Cart::currency(),
        ]);
    }

    /**
     * Submit an order request.
     */
    public function submitCheckout(Request $request): JsonResponse|RedirectResponse
    {
        $this->checkModule();

        // 1. Honeypot check
        if ($request->filled('hp_website')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Spam detected.',
                    'errors' => ['hp_website' => ['Spam detected.']],
                ], 422);
            }

            return back()->withErrors(['hp_website' => 'Spam detected.'])->withInput();
        }

        // 2. Time-based spam check
        $timeToken = $request->input('_token_time');
        $isTimeSpam = false;

        if (empty($timeToken)) {
            $isTimeSpam = true;
        } else {
            try {
                $submittedAt = (int) decrypt($timeToken);
                $elapsed = time() - $submittedAt;

                if ($elapsed < 2 || $elapsed > 86400) {
                    $isTimeSpam = true;
                }
            } catch (Throwable) {
                $isTimeSpam = true;
            }
        }

        if ($isTimeSpam) {
            $errorMsg = 'Form submitted too quickly or session expired. Please try again.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $errorMsg,
                    'errors' => ['_token_time' => [$errorMsg]],
                ], 422);
            }

            return back()->withErrors(['_token_time' => $errorMsg])->withInput();
        }

        // 3. Cart check
        if (Cart::isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your cart is empty.',
                ], 422);
            }

            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        // 4. Validation
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // 5. Snapshot items and prices
        $items = Cart::items();
        $total = Cart::total();
        $currency = Cart::currency();
        $reference = OrderRequest::generateReference();

        $order = OrderRequest::create([
            'reference' => $reference,
            'customer_name' => $validated['customer_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'items' => $items,
            'total' => $total,
            'currency' => $currency,
            'status' => OrderRequest::STATUS_NEW,
        ]);

        // 6. Clear session cart
        Cart::clear();

        // 7. Dispatch Telegram notification
        try {
            $notifier = app(TelegramNotifier::class);
            $notifier->sendOrderRequest($order);
        } catch (Throwable $e) {
            Log::warning('Failed to send Telegram order notification: '.$e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'reference' => $order->reference,
                'redirect_url' => route('shop.order-confirmed', ['reference' => $order->reference]),
            ], 201);
        }

        return redirect()->route('shop.order-confirmed', ['reference' => $order->reference]);
    }

    /**
     * Display order confirmation page.
     */
    public function orderConfirmed(string $reference): View
    {
        $this->checkModule();

        $order = OrderRequest::query()
            ->where('reference', $reference)
            ->firstOrFail();

        return view('shop.confirmed', [
            'order' => $order,
        ]);
    }
}
