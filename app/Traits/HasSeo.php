<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasSeo
{
    /**
     * Get the SEO title with fallback to Model Title + Site Name.
     */
    public function seoTitle(): string
    {
        if (! empty($this->seo_title)) {
            return $this->seo_title;
        }

        $baseTitle = $this->title ?? $this->name ?? null;
        $siteName = site('site_name') ?? config('app.name', 'Bina Site Kit');

        if (! empty($baseTitle)) {
            return "{$baseTitle} - {$siteName}";
        }

        return $siteName;
    }

    /**
     * Get the SEO description with fallback to first 160 chars of body / summary / description / tagline.
     */
    public function seoDescription(): string
    {
        if (! empty($this->seo_description)) {
            return $this->seo_description;
        }

        $raw = null;
        if (! empty($this->summary)) {
            $raw = $this->summary;
        } elseif (! empty($this->description)) {
            $raw = $this->description;
        } elseif (! empty($this->body)) {
            $raw = $this->body;
        } elseif (! empty($this->blocks) && is_array($this->blocks)) {
            foreach ($this->blocks as $block) {
                $data = $block['data'] ?? [];
                if (! empty($data['body'])) {
                    $raw = $data['body'];
                    break;
                }
                if (! empty($data['subheading'])) {
                    $raw = $data['subheading'];
                    break;
                }
            }
        }

        if (! empty($raw)) {
            $clean = trim(preg_replace('/\s+/', ' ', strip_tags($raw)));
            if ($clean !== '') {
                return Str::limit($clean, 160);
            }
        }

        return site('tagline') ?? 'A clean and modern starter for client websites.';
    }

    /**
     * Get the SEO / OpenGraph image URL with sensible fallback.
     */
    public function seoImage(): ?string
    {
        if (! empty($this->og_image)) {
            return Storage::disk('public')->url($this->og_image);
        }

        if (! empty($this->cover_image)) {
            return Storage::disk('public')->url($this->cover_image);
        }

        if (method_exists($this, 'primaryImageUrl') && $url = $this->primaryImageUrl()) {
            return $url;
        }

        return site()->logoUrl();
    }

    /**
     * Check if the model is missing explicit SEO fields.
     */
    public function isMissingSeo(): bool
    {
        return empty($this->seo_description) || empty($this->og_image);
    }

    /**
     * Get list of missing SEO fields for health reports and dashboard warnings.
     *
     * @return list<string>
     */
    public function missingSeoFields(): array
    {
        $missing = [];

        if (empty($this->seo_description)) {
            $missing[] = 'Description';
        }

        if (empty($this->og_image)) {
            $missing[] = 'Social Image';
        }

        return $missing;
    }
}
