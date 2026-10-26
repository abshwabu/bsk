<?php

namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, HasSeo;

    public const STOCK_IN_STOCK = 'in_stock';

    public const STOCK_OUT_OF_STOCK = 'out_of_stock';

    public const STOCK_MADE_TO_ORDER = 'made_to_order';

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('sitemap.xml');
        });

        static::deleted(function () {
            Cache::forget('sitemap.xml');
        });
    }

    /**
     * Category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Scope published products.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope featured products.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Stock status options.
     *
     * @return array<string, string>
     */
    public static function stockStatuses(): array
    {
        return [
            self::STOCK_IN_STOCK => 'In Stock',
            self::STOCK_OUT_OF_STOCK => 'Out of Stock',
            self::STOCK_MADE_TO_ORDER => 'Made to Order',
        ];
    }

    public function stockStatusLabel(): string
    {
        return static::stockStatuses()[$this->stock_status] ?? 'In Stock';
    }

    /**
     * Primary image file path.
     */
    public function primaryImage(): ?string
    {
        $images = $this->images ?? [];

        return ! empty($images) ? $images[0] : null;
    }

    /**
     * Primary image public URL.
     */
    public function primaryImageUrl(): ?string
    {
        $image = $this->primaryImage();

        return $image ? Storage::disk('public')->url($image) : null;
    }

    /**
     * Array of public URLs for all product images.
     *
     * @return list<string>
     */
    public function imageUrls(): array
    {
        $images = $this->images ?? [];

        return array_values(array_map(function ($img) {
            return Storage::disk('public')->url($img);
        }, $images));
    }

    /**
     * Get the resolved currency for this product.
     */
    public function effectiveCurrency(): string
    {
        return $this->currency ?: (site('currency') ?: 'USD');
    }

    /**
     * Human-formatted price.
     */
    public function formattedPrice(): string
    {
        if ($this->price === null) {
            return 'Price upon request';
        }

        $currency = $this->effectiveCurrency();
        $formatted = number_format((float) $this->price, 2);

        return "{$currency} {$formatted}";
    }
}
