<?php

namespace App\StarterTemplates;

use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SiteSetting;

class StoreTemplate implements StarterTemplateInterface
{
    public function id(): string
    {
        return 'store';
    }

    public function name(): string
    {
        return 'Artisan Store & Catalog';
    }

    public function description(): string
    {
        return 'E-commerce boutique and product catalog with shopping cart and Telegram order requests.';
    }

    public function defaultSettings(): array
    {
        return [
            'site_name' => 'Artisan & Oak',
            'tagline' => 'Curated Goods for Intentional Living',
            'primary_color' => '#1c1917',
            'accent_color' => '#d97706',
            'currency' => 'USD',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
            'enabled_modules' => [
                'portfolio' => false,
                'catalog' => true,
            ],
        ];
    }

    public function seed(SiteSetting $setting): void
    {
        // 1. Enable catalog module
        $modules = (array) $setting->enabled_modules;
        $modules['catalog'] = true;
        $setting->update(['enabled_modules' => $modules]);

        // 2. Product Categories
        $leatherCategory = ProductCategory::query()->updateOrCreate(
            ['slug' => 'leather-goods'],
            ['name' => 'Leather Goods', 'sort' => 1]
        );

        $ceramicsCategory = ProductCategory::query()->updateOrCreate(
            ['slug' => 'ceramics-home'],
            ['name' => 'Ceramics & Home', 'sort' => 2]
        );

        $stationeryCategory = ProductCategory::query()->updateOrCreate(
            ['slug' => 'stationery'],
            ['name' => 'Stationery', 'sort' => 3]
        );

        // 3. Products
        Product::query()->updateOrCreate(
            ['slug' => 'heritage-leather-weekender'],
            [
                'name' => 'Heritage Leather Weekender',
                'product_category_id' => $leatherCategory->id,
                'description' => '<p>Crafted from full-grain vegetable-tanned leather, the Heritage Weekender is designed to age beautifully through years of travel. Features solid brass hardware, YKK zippers, and an interior laptop sleeve.</p>',
                'price' => 185.00,
                'images' => [],
                'stock_status' => Product::STOCK_IN_STOCK,
                'is_featured' => true,
                'is_published' => true,
                'sort' => 1,
                'seo_title' => 'Heritage Leather Weekender - Artisan & Oak',
                'seo_description' => 'Full-grain vegetable-tanned leather travel bag handcrafted for longevity.',
            ]
        );

        Product::query()->updateOrCreate(
            ['slug' => 'hand-thrown-ceramic-mug'],
            [
                'name' => 'Hand-Thrown Ceramic Mug',
                'product_category_id' => $ceramicsCategory->id,
                'description' => '<p>Wheel-thrown stoneware featuring a matte speckled glaze and comfortable ergonomic handle. Each piece is unique and dishwasher-safe.</p>',
                'price' => 34.00,
                'images' => [],
                'stock_status' => Product::STOCK_IN_STOCK,
                'is_featured' => true,
                'is_published' => true,
                'sort' => 2,
                'seo_title' => 'Hand-Thrown Ceramic Mug - Artisan & Oak',
                'seo_description' => 'Wheel-thrown artisanal stoneware mug with matte speckled glaze.',
            ]
        );

        Product::query()->updateOrCreate(
            ['slug' => 'minimalist-brass-rollerball'],
            [
                'name' => 'Minimalist Brass Rollerball Pen',
                'product_category_id' => $stationeryCategory->id,
                'description' => '<p>Machined from solid brass with a satisfying balanced weight in hand. Refillable with standard Schmidt rollerball cartridges.</p>',
                'price' => 42.00,
                'images' => [],
                'stock_status' => Product::STOCK_IN_STOCK,
                'is_featured' => true,
                'is_published' => true,
                'sort' => 3,
                'seo_title' => 'Minimalist Brass Rollerball Pen - Artisan & Oak',
                'seo_description' => 'Solid brass machined rollerball pen engineered for fluid daily writing.',
            ]
        );

        Product::query()->updateOrCreate(
            ['slug' => 'linen-bound-journal'],
            [
                'name' => 'Linen Bound Journal',
                'product_category_id' => $stationeryCategory->id,
                'description' => '<p>Hardcover journal wrapped in natural Belgian linen with 192 pages of fountain pen-friendly 100gsm acid-free cream paper.</p>',
                'price' => 28.00,
                'images' => [],
                'stock_status' => Product::STOCK_IN_STOCK,
                'is_featured' => true,
                'is_published' => true,
                'sort' => 4,
                'seo_title' => 'Linen Bound Journal - Artisan & Oak',
                'seo_description' => 'Belgian linen hardcover journal with acid-free fountain pen paper.',
            ]
        );

        Product::query()->updateOrCreate(
            ['slug' => 'cedar-and-sage-candle'],
            [
                'name' => 'Cedar & Wild Sage Candle',
                'product_category_id' => $ceramicsCategory->id,
                'description' => '<p>100% natural soy wax hand-poured into a reusable ceramic vessel with a crackling wooden wick. 50 hour burn time.</p>',
                'price' => 26.00,
                'images' => [],
                'stock_status' => Product::STOCK_IN_STOCK,
                'is_featured' => false,
                'is_published' => true,
                'sort' => 5,
                'seo_title' => 'Cedar & Wild Sage Candle - Artisan & Oak',
                'seo_description' => 'Hand-poured soy candle with wild sage, cedarwood, and a wood wick.',
            ]
        );

        // 4. Pages
        // Home
        Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'is_published' => true,
                'is_home' => true,
                'show_in_nav' => true,
                'nav_order' => 1,
                'seo_title' => 'Artisan & Oak - Thoughtful Everyday Goods',
                'seo_description' => 'Handcrafted leather goods, wheel-thrown ceramics, and heirloom stationery.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Simple, Beautiful Objects Made to Last',
                            'subheading' => 'Crafted in small batches with sustainable raw materials and timeless utility. Designed to elevate your daily rituals.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '40',
                            'alignment' => 'center',
                            'primary_cta_label' => 'Shop Collection',
                            'primary_cta_url' => '/shop',
                            'secondary_cta_label' => 'Our Story',
                            'secondary_cta_url' => '/about',
                        ],
                    ],
                    [
                        'type' => 'product_grid',
                        'data' => [
                            'heading' => 'Featured Collection',
                            'subheading' => 'Our most cherished handcrafted goods, restocked in limited quantities.',
                            'count' => 4,
                            'view_all_label' => 'Explore Full Catalog',
                            'view_all_url' => '/shop',
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'data' => [
                            'heading' => 'The Artisan & Oak Guarantee',
                            'subheading' => 'Honoring slow craftsmanship, responsible materials, and direct maker partnerships.',
                            'columns' => '3',
                            'features' => [
                                [
                                    'icon' => 'sparkles',
                                    'title' => 'Small Batch Production',
                                    'description' => 'Each batch is produced in limited runs to minimize waste and ensure unmatched attention to detail.',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'title' => 'Ethically Sourced Materials',
                                    'description' => 'Full-grain vegetable-tanned leathers, certified organic cotton, and FSC-certified woods.',
                                ],
                                [
                                    'icon' => 'bolt',
                                    'title' => 'Lifetime Repair Pledge',
                                    'description' => 'We stand behind our goods. If any stitching or hardware fails, we will repair or replace it.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'testimonials',
                        'data' => [
                            'heading' => 'Customer Reflections',
                            'subheading' => 'From patrons who appreciate intentional design and durability.',
                            'testimonials' => [
                                [
                                    'quote' => 'The Heritage Weekender has travelled with me across 12 countries. The patina only gets better with every trip.',
                                    'name' => 'Thomas Sterling',
                                    'role' => 'Architect & Photographer',
                                    'photo' => null,
                                ],
                                [
                                    'quote' => 'Ordering was effortless and receiving a personal order follow-up gave it that authentic neighborhood shop feel.',
                                    'name' => 'Sarah Beaumont',
                                    'role' => 'Interior Stylist',
                                    'photo' => null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta_banner',
                        'data' => [
                            'heading' => 'Ready to Refresh Your Daily Essentials?',
                            'text' => 'Browse our catalog and submit an order request directly to our team.',
                            'button_label' => 'Shop the Catalog',
                            'button_url' => '/shop',
                        ],
                    ],
                ],
            ]
        );

        // About
        Page::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'is_published' => true,
                'is_home' => false,
                'show_in_nav' => true,
                'nav_order' => 2,
                'seo_title' => 'Our Workshop & Philosophy - Artisan & Oak',
                'seo_description' => 'Discover our commitment to slow production, ethical raw materials, and independent makers.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Our Workshop & Philosophy',
                            'subheading' => 'Honoring slow production, raw materials, and independent makers.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '30',
                            'alignment' => 'center',
                            'primary_cta_label' => 'View Catalog',
                            'primary_cta_url' => '/shop',
                            'secondary_cta_label' => null,
                            'secondary_cta_url' => null,
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'heading' => 'Built to Outlast Trends',
                            'content' => '<p>Artisan & Oak was founded in 2021 as a counterpoint to disposable consumer goods. We believe objects in your everyday life should carry tactile warmth, tell a story, and withstand daily use for decades.</p><p>We collaborate directly with independent leatherworkers, ceramicists, and woodturners who have dedicated their lives to their craft.</p>',
                            'width' => 'narrow',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'data' => [
                            'heading' => 'By the Numbers',
                            'subheading' => 'Our commitment to intentional craftsmanship.',
                            'stats' => [
                                ['number' => '100%', 'label' => 'Plastic-Free Packaging'],
                                ['number' => '32', 'label' => 'Independent Artisans'],
                                ['number' => '4.9/5', 'label' => 'Verified Customer Rating'],
                                ['number' => 'Lifetime', 'label' => 'Hardware Guarantee'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Contact
        Page::query()->updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => 'Contact',
                'is_published' => true,
                'is_home' => false,
                'show_in_nav' => true,
                'nav_order' => 3,
                'seo_title' => 'Contact Artisan & Oak',
                'seo_description' => 'Inquire about custom orders, wholesale partnerships, or shipping questions.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Customer Care & Wholesale',
                            'subheading' => 'Have questions about materials or need corporate gifting options? Reach out to our team.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '30',
                            'alignment' => 'center',
                            'primary_cta_label' => null,
                            'primary_cta_url' => null,
                            'secondary_cta_label' => null,
                            'secondary_cta_url' => null,
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'data' => [
                            'heading' => 'Send Us a Note',
                            'subheading' => 'Fill out the form below or email us directly at hello@example.com.',
                        ],
                    ],
                ],
            ]
        );
    }
}
