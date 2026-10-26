<?php

namespace App\StarterTemplates;

use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\SiteSetting;

class PortfolioTemplate implements StarterTemplateInterface
{
    public function id(): string
    {
        return 'portfolio';
    }

    public function name(): string
    {
        return 'Creative Studio & Portfolio';
    }

    public function description(): string
    {
        return 'Curated showcase for agencies, designers, and studios with project galleries and category filters.';
    }

    public function defaultSettings(): array
    {
        return [
            'site_name' => 'Studio Forma',
            'tagline' => 'Architecture, Branding & Digital Product Studio',
            'primary_color' => '#18181b',
            'accent_color' => '#f59e0b',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
            'enabled_modules' => [
                'portfolio' => true,
                'catalog' => false,
            ],
        ];
    }

    public function seed(SiteSetting $setting): void
    {
        // 1. Enable portfolio module
        $modules = (array) $setting->enabled_modules;
        $modules['portfolio'] = true;
        $setting->update(['enabled_modules' => $modules]);

        // 2. Project Categories
        $brandCategory = ProjectCategory::query()->updateOrCreate(
            ['slug' => 'brand-identity'],
            ['name' => 'Brand Identity', 'sort' => 1]
        );

        $digitalCategory = ProjectCategory::query()->updateOrCreate(
            ['slug' => 'digital-products'],
            ['name' => 'Digital Products', 'sort' => 2]
        );

        $spatialCategory = ProjectCategory::query()->updateOrCreate(
            ['slug' => 'spatial-design'],
            ['name' => 'Spatial Design', 'sort' => 3]
        );

        // 3. Projects
        Project::query()->updateOrCreate(
            ['slug' => 'kroma-living-experience'],
            [
                'title' => 'Kroma Living Experience',
                'project_category_id' => $brandCategory->id,
                'client_name' => 'Kroma Development Group',
                'summary' => 'Holistic brand identity, spatial typography, and digital experience for an eco-conscious urban development.',
                'body' => '<p>Kroma approached Studio Forma to define their global visual identity ahead of groundbreaking. We designed a comprehensive identity system honoring sustainable raw materials and architectural geometry.</p><p>The deliverable included identity guidelines, custom bespoke typography, signage specifications, and a digital storytelling platform.</p>',
                'cover_image' => null,
                'gallery' => [],
                'completed_on' => now()->subMonths(3),
                'is_featured' => true,
                'is_published' => true,
                'sort' => 1,
                'seo_title' => 'Kroma Living Experience - Case Study',
                'seo_description' => 'Comprehensive brand identity and digital storytelling for Kroma Living.',
            ]
        );

        Project::query()->updateOrCreate(
            ['slug' => 'aura-telehealth-platform'],
            [
                'title' => 'Aura Telehealth Platform',
                'project_category_id' => $digitalCategory->id,
                'client_name' => 'Aura Health Technologies',
                'summary' => 'Next-generation remote patient telemetry web application and accessible medical design system.',
                'body' => '<p>Designing medical telemetry requires balancing clinical precision with calm, reassuring user experience. We collaborated with physicians and researchers to map patient workflows into an accessible interface.</p><p>The platform reduced critical data lookup times by 42% in clinical trials.</p>',
                'cover_image' => null,
                'gallery' => [],
                'completed_on' => now()->subMonths(6),
                'is_featured' => true,
                'is_published' => true,
                'sort' => 2,
                'seo_title' => 'Aura Telehealth Platform - Case Study',
                'seo_description' => 'Accessible healthcare telemetry interface and design system for Aura Health.',
            ]
        );

        Project::query()->updateOrCreate(
            ['slug' => 'veloce-urban-mobility'],
            [
                'title' => 'Veloce Urban Mobility',
                'project_category_id' => $digitalCategory->id,
                'client_name' => 'Veloce Mobility Ltd',
                'summary' => 'Smart micromobility interface designed for seamless multimodal city commuting.',
                'body' => '<p>Studio Forma created a high-contrast mobile interface with sub-second unlock flows and offline route tracking for thousands of daily riders across Europe.</p>',
                'cover_image' => null,
                'gallery' => [],
                'completed_on' => now()->subMonths(9),
                'is_featured' => true,
                'is_published' => true,
                'sort' => 3,
                'seo_title' => 'Veloce Urban Mobility - Case Study',
                'seo_description' => 'High-velocity micromobility design by Studio Forma.',
            ]
        );

        Project::query()->updateOrCreate(
            ['slug' => 'sylvan-pavilion'],
            [
                'title' => 'Sylvan Pavilion Concept',
                'project_category_id' => $spatialCategory->id,
                'client_name' => 'City Cultural Trust',
                'summary' => 'Minimalist timber pavilion designed for natural light, passive airflow, and community gatherings.',
                'body' => '<p>An architectural exploration of locally harvested timber and carbon-neutral public infrastructure.</p>',
                'cover_image' => null,
                'gallery' => [],
                'completed_on' => now()->subYear(),
                'is_featured' => false,
                'is_published' => true,
                'sort' => 4,
                'seo_title' => 'Sylvan Pavilion Concept - Case Study',
                'seo_description' => 'Architectural spatial design study by Studio Forma.',
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
                'seo_title' => 'Studio Forma - Architecture, Branding & Digital Products',
                'seo_description' => 'We craft distinctive visual identities and resilient digital experiences for ambitious teams.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Crafting Distinctive Identities & Modern Experiences',
                            'subheading' => 'We are an independent creative practice working at the intersection of branding, architecture, and digital craftsmanship.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '40',
                            'alignment' => 'left',
                            'primary_cta_label' => 'View Selected Work',
                            'primary_cta_url' => '/projects',
                            'secondary_cta_label' => 'Get in Touch',
                            'secondary_cta_url' => '/contact',
                        ],
                    ],
                    [
                        'type' => 'featured_projects',
                        'data' => [
                            'heading' => 'Selected Works',
                            'subheading' => 'Recent collaborations with pioneering founders and cultural institutions.',
                            'count' => 3,
                            'view_all_label' => 'View All Projects',
                            'view_all_url' => '/projects',
                        ],
                    ],
                    [
                        'type' => 'testimonials',
                        'data' => [
                            'heading' => 'Collaborator Notes',
                            'subheading' => 'What founders and clients say about working with Studio Forma.',
                            'testimonials' => [
                                [
                                    'quote' => 'Studio Forma brought unmatched aesthetic rigor and technical precision to our brand launch.',
                                    'name' => 'Clara Lindqvist',
                                    'role' => 'Founding Principal, Kroma Living',
                                    'photo' => null,
                                ],
                                [
                                    'quote' => 'Their ability to balance minimalist beauty with rigorous UX helped Aura secure our Series A.',
                                    'name' => 'Dr. Julian Thorne',
                                    'role' => 'Co-Founder & CEO, Aura Health',
                                    'photo' => null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta_banner',
                        'data' => [
                            'heading' => 'Have an Ambitious Project in Mind?',
                            'text' => 'We are currently reviewing select commissions for the upcoming season.',
                            'button_label' => "Let's Collaborate",
                            'button_url' => '/contact',
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
                'seo_title' => 'About Studio Forma - Design Philosophy',
                'seo_description' => 'Disciplined craft, rigorous thinking, and timeless aesthetics.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'About Studio Forma',
                            'subheading' => 'Disciplined craft, rigorous thinking, and timeless aesthetics.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '30',
                            'alignment' => 'center',
                            'primary_cta_label' => 'View Projects',
                            'primary_cta_url' => '/projects',
                            'secondary_cta_label' => null,
                            'secondary_cta_url' => null,
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'heading' => 'Our Design Manifesto',
                            'content' => '<p>We believe that thoughtful design moves businesses and culture forward. In a digital world crowded with ephemeral noise, we build digital and spatial artifacts that remain compelling over years, not weeks.</p><p>From high-volume mobile platforms to bespoke editorial identities, our studio works as an integrated partner alongside your core team.</p>',
                            'width' => 'narrow',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'data' => [
                            'heading' => 'Studio Milestones',
                            'subheading' => 'A decade of creative exploration and international impact.',
                            'stats' => [
                                ['number' => '48+', 'label' => 'Commissions Delivered'],
                                ['number' => '14', 'label' => 'Design Accolades'],
                                ['number' => '10+', 'label' => 'Years in Practice'],
                                ['number' => '9', 'label' => 'Countries Reached'],
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
                'seo_title' => 'Contact Studio Forma',
                'seo_description' => 'Inquire about new commissions, press inquiries, or studio visits.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Start a Conversation',
                            'subheading' => 'We welcome new project briefs, studio visits, and creative collaborations.',
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
                            'heading' => 'Send Us a Project Brief',
                            'subheading' => 'Tell us about your timeline, scope, and objectives.',
                        ],
                    ],
                ],
            ]
        );
    }
}
