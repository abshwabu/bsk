<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'completed_on' => 'date',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://') || str_starts_with($this->cover_image, '//')) {
            return $this->cover_image;
        }

        return Storage::disk('public')->url($this->cover_image);
    }

    /**
     * @return array<int, string>
     */
    public function galleryUrls(): array
    {
        $gallery = $this->gallery ?? [];
        if (! is_array($gallery)) {
            return [];
        }

        $urls = [];
        foreach ($gallery as $img) {
            if (! $img) {
                continue;
            }
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '//')) {
                $urls[] = $img;
            } else {
                $urls[] = Storage::disk('public')->url($img);
            }
        }

        return $urls;
    }

    public function ogImageUrl(): ?string
    {
        if ($this->og_image) {
            return Storage::disk('public')->url($this->og_image);
        }

        return $this->coverImageUrl();
    }

    public function previousProject(): ?self
    {
        return static::query()
            ->published()
            ->where(function ($query) {
                $query->where('sort', '<', $this->sort)
                    ->orWhere(function ($q) {
                        $q->where('sort', $this->sort)
                            ->where('id', '<', $this->id);
                    });
            })
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->first();
    }

    public function nextProject(): ?self
    {
        return static::query()
            ->published()
            ->where(function ($query) {
                $query->where('sort', '>', $this->sort)
                    ->orWhere(function ($q) {
                        $q->where('sort', $this->sort)
                            ->where('id', '>', $this->id);
                    });
            })
            ->orderBy('sort')
            ->orderBy('id')
            ->first();
    }
}
