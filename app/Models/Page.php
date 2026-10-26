<?php

namespace App\Models;

use App\Services\Navigation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
            'is_home' => 'boolean',
            'show_in_nav' => 'boolean',
            'nav_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if ($page->is_home) {
                static::query()
                    ->when($page->exists, fn ($query) => $query->where('id', '!=', $page->id))
                    ->where('is_home', true)
                    ->update(['is_home' => false]);
            }
        });

        static::saved(function () {
            Navigation::flushCache();
        });

        static::deleted(function () {
            Navigation::flushCache();
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeInNav(Builder $query): Builder
    {
        return $query->where('show_in_nav', true)->orderBy('nav_order');
    }

    public function ogImageUrl(): ?string
    {
        return $this->og_image ? Storage::disk('public')->url($this->og_image) : null;
    }
}
