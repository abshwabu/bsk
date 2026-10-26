<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'is_published' => true,
                'is_home' => true,
                'show_in_nav' => true,
                'nav_order' => 1,
                'seo_title' => 'Bina Site Kit - High Performance Starter for Client Sites',
                'seo_description' => 'A clean, modern Laravel 11 and Filament 3 starter kit with modular page blocks and dynamic branding.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Elevate Your Digital Presence with Bina Site Kit',
                            'subheading' => 'A refined Laravel 11 starter designed for agencies, founders, and small businesses looking for speed, modular blocks, and complete brand control.',
                            'background_image' => null,
                            'background_image_alt' => 'Hero background banner',
                            'overlay_opacity' => '40',
                            'alignment' => 'center',
                            'primary_cta_label' => 'Explore Our Work',
                            'primary_cta_url' => '#services',
                            'secondary_cta_label' => 'Get in Touch',
                            'secondary_cta_url' => '#contact',
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'data' => [
                            'heading' => 'Engineered for Performance and Growth',
                            'subheading' => 'Everything you need to deliver world-class client websites quickly and reliably.',
                            'columns' => '3',
                            'features' => [
                                [
                                    'icon' => 'sparkles',
                                    'title' => 'Modular Block System',
                                    'description' => 'Compose custom pages with auto-discovered blocks that render responsive, semantic HTML.',
                                ],
                                [
                                    'icon' => 'paint-brush',
                                    'title' => 'Dynamic Branding & Fonts',
                                    'description' => 'Live-swappable primary and accent colors with full opacity support and Google Fonts integration.',
                                ],
                                [
                                    'icon' => 'bolt',
                                    'title' => 'Shared-Hosting Friendly',
                                    'description' => 'Zero Redis or queue worker requirements. Built for maximum reliability on standard hosting.',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'title' => 'Encrypted Credentials',
                                    'description' => 'Secure encrypted casting for sensitive integration secrets like Telegram bot tokens.',
                                ],
                                [
                                    'icon' => 'device-phone-mobile',
                                    'title' => 'Mobile-First Experience',
                                    'description' => 'Polished Alpine.js accordions, touch-friendly image lightboxes, and accessible layouts.',
                                ],
                                [
                                    'icon' => 'chart-bar',
                                    'title' => 'SEO & Social Ready',
                                    'description' => 'Per-page metadata, Open Graph images, and automatic sitemaps for maximum search visibility.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'heading' => 'Designed with Intent, Crafted for Speed',
                            'content' => '<p>Every website built on <strong>Bina Site Kit</strong> starts with solid architectural principles. Instead of cumbersome page builders, clients enjoy a clean Filament admin panel where content can be updated in seconds.</p><p>Brand colors and Google fonts reflect instantly on public pages with full Tailwind opacity support. You get all the speed of hand-coded Blade templates combined with modern CMS flexibility.</p>',
                            'width' => 'narrow',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'data' => [
                            'heading' => 'Proven Client Results',
                            'subheading' => 'Measurable milestones achieved by teams using our site framework.',
                            'stats' => [
                                ['number' => '150+', 'label' => 'Websites Delivered'],
                                ['number' => '99.9%', 'label' => 'Uptime Reliability'],
                                ['number' => '2.4x', 'label' => 'Faster Load Times'],
                                ['number' => '100%', 'label' => 'Client Satisfaction'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'image_gallery',
                        'data' => [
                            'heading' => 'Showcase Gallery',
                            'subheading' => 'Click on any image to preview in full resolution via the Alpine lightbox.',
                            'layout' => 'grid',
                            'columns' => '3',
                            'images' => [
                                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
                                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                                'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
                            ],
                        ],
                    ],
                    [
                        'type' => 'testimonials',
                        'data' => [
                            'heading' => 'Endorsed by Founders & Teams',
                            'subheading' => 'Read what creators and business owners say about their experience with us.',
                            'testimonials' => [
                                [
                                    'quote' => 'Bina Site Kit cut our delivery timeline in half. The clients love how intuitive the Filament administration is.',
                                    'name' => 'Elena Rostova',
                                    'role' => 'Principal Architect, Studio Nine',
                                    'photo' => null,
                                ],
                                [
                                    'quote' => 'Flawless performance and beautiful typography out of the box. The block architecture made adding custom sections effortless.',
                                    'name' => 'Marcus Vance',
                                    'role' => 'Founder, Velocity Labs',
                                    'photo' => null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'data' => [
                            'heading' => 'Frequently Asked Questions',
                            'subheading' => 'Common questions about using our site kit for your next client project.',
                            'faqs' => [
                                [
                                    'question' => 'How does the block builder work?',
                                    'answer' => 'Blocks are self-contained classes in App\Blocks implementing PageBlock. The BlockRegistry automatically discovers them and populates Filament Builder schemas.',
                                ],
                                [
                                    'question' => 'Can I customize brand colors and fonts?',
                                    'answer' => 'Yes, through the Site Settings page in /admin. The public layout maps primary and accent colors to CSS variables with full Tailwind opacity support.',
                                ],
                                [
                                    'question' => 'Is there any heavy JavaScript dependency?',
                                    'answer' => 'None. The public site only uses Alpine.js for lightweight interactions like accordions and image lightboxes.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta_banner',
                        'data' => [
                            'heading' => 'Ready to Launch Your New Website?',
                            'text' => 'Get in touch today and discover how quickly your next project can go live.',
                            'button_label' => 'Start Your Project',
                            'button_url' => '#contact',
                            'theme' => 'primary',
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'data' => [
                            'heading' => 'Connect With Our Team',
                            'subheading' => 'Reach out by phone, email, or send us a message below.',
                            'show_details' => true,
                        ],
                    ],
                ],
            ]
        );
    }
}
