<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderRequest extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Order request statuses.
     *
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Scope for new order requests.
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope by status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Generate a unique reference like BSK-YYYYMMDD-XXXX.
     */
    public static function generateReference(): string
    {
        $prefix = 'BSK-'.date('Ymd').'-';

        do {
            $code = $prefix.strtoupper(Str::random(4));
        } while (static::where('reference', $code)->exists());

        return $code;
    }

    public function confirm(): self
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->save();

        return $this;
    }

    public function deliver(): self
    {
        $this->status = self::STATUS_DELIVERED;
        $this->save();

        return $this;
    }

    public function cancel(): self
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();

        return $this;
    }

    public function formattedTotal(): string
    {
        $currency = $this->currency ?: 'USD';
        $formatted = number_format((float) $this->total, 2);

        return "{$currency} {$formatted}";
    }
}
