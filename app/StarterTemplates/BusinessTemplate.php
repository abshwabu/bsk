<?php

namespace App\StarterTemplates;

use App\Models\Page;
use App\Models\SiteSetting;

class BusinessTemplate implements StarterTemplateInterface
{
    public function id(): string
    {
        return 'business';
    }

    public function name(): string
    {
        return 'Modern Business';
    }

    public function description(): string
    {
        return 'Clean corporate starter with capabilities grid, company stats, executive testimonials, FAQ, and contact.';
    }

    public function defaultSettings(): array
    {
        return [
            'site_name' => 'Vanguard Strategy',
            'tagline' => 'Strategic Advisory & Enterprise Technology Solutions',
            'primary_color' => '#0f172a',
            'accent_color' => '#3b82f6',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
            'enabled_modules' => [
                'portfolio' => false,
                'catalog' => false,
            ],
        ];
    }

    public function seed(SiteSetting $setting): void
    {
        // 1. Home Page
        Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'is_published' => true,
                'is_home' => true,
                'show_in_nav' => true,
                'nav_order' => 1,
                'seo_title' => 'Vanguard Strategy - Strategic Advisory & Enterprise Solutions',
                'seo_description' => 'We partner with enterprise leaders to build resilient businesses, scale technology, and unlock growth.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Strategic Excellence for Modern Enterprises',
                            'subheading' => 'We partner with visionary leaders to build resilient businesses, scale modern technology, and unlock measurable market growth.',
                            'background_image' => null,
                            'background_image_alt' => 'Executive team collaboration',
                            'overlay_opacity' => '40',
                            'alignment' => 'center',
                            'primary_cta_label' => 'Our Services',
                            'primary_cta_url' => '/services',
                            'secondary_cta_label' => 'Contact Us',
                            'secondary_cta_url' => '/contact',
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'data' => [
                            'heading' => 'Our Core Capabilities',
                            'subheading' => 'Comprehensive advisory and technical solutions tailored for high-growth organizations.',
                            'columns' => '3',
                            'features' => [
                                [
                                    'icon' => 'sparkles',
                                    'title' => 'Corporate Strategy',
                                    'description' => 'Market entry analysis, M&A strategy, and long-term organizational transformation frameworks.',
                                ],
                                [
                                    'icon' => 'bolt',
                                    'title' => 'Digital Transformation',
                                    'description' => 'Modernizing legacy architectures and adopting high-velocity cloud engineering practices.',
                                ],
                                [
                                    'icon' => 'chart-bar',
                                    'title' => 'Data & Intelligence',
                                    'description' => 'Unlocking revenue opportunities through executive dashboards and predictive modeling.',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'title' => 'Risk & Governance',
                                    'description' => 'Robust risk posture, compliance roadmaps, and cybersecurity frameworks.',
                                ],
                                [
                                    'icon' => 'device-phone-mobile',
                                    'title' => 'Product Engineering',
                                    'description' => 'Rapid prototyping, user journey validation, and resilient full-stack deployment.',
                                ],
                                [
                                    'icon' => 'paint-brush',
                                    'title' => 'Operational Efficiency',
                                    'description' => 'Streamlining internal workflows and reducing operational overhead across business units.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'data' => [
                            'heading' => 'Proven Impact at Scale',
                            'subheading' => 'Measurable milestones achieved alongside our client partners.',
                            'stats' => [
                                ['number' => '120+', 'label' => 'Enterprises Served'],
                                ['number' => '99.4%', 'label' => 'Client Retention'],
                                ['number' => '3.5x', 'label' => 'Average ROI Multiplier'],
                                ['number' => '15+', 'label' => 'Global Industry Awards'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'testimonials',
                        'data' => [
                            'heading' => 'Trusted by Industry Leaders',
                            'subheading' => 'What executive partners say about working with Vanguard Strategy.',
                            'testimonials' => [
                                [
                                    'quote' => 'Vanguard provided the strategic clarity we needed to navigate a high-stakes cloud migration. Invaluable partnership.',
                                    'name' => 'Elena Rostova',
                                    'role' => 'Chief Technology Officer, Solis Group',
                                    'photo' => null,
                                ],
                                [
                                    'quote' => 'Their ability to bridge boardroom business strategy with pragmatic software execution saved us months of work.',
                                    'name' => 'Marcus Vance',
                                    'role' => 'Managing Director, Apex Capital',
                                    'photo' => null,
                                ],
                                [
                                    'quote' => 'Within 90 days of engagement, our team unlocked a 28% operational efficiency improvement across all lines.',
                                    'name' => 'Sarah Chen',
                                    'role' => 'VP Operations, Horizon Logistics',
                                    'photo' => null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta_banner',
                        'data' => [
                            'heading' => 'Ready to Accelerate Your Growth?',
                            'text' => 'Schedule an executive discovery session with our senior partners to explore collaborative opportunities.',
                            'button_label' => 'Schedule a Consultation',
                            'button_url' => '/contact',
                        ],
                    ],
                ],
            ]
        );

        // 2. About Page
        Page::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'is_published' => true,
                'is_home' => false,
                'show_in_nav' => true,
                'nav_order' => 2,
                'seo_title' => 'About Vanguard Strategy - Leadership & Vision',
                'seo_description' => 'Decades of combined advisory experience driving sustainable transformation.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Decades of Purposeful Leadership',
                            'subheading' => 'We were founded on the belief that strategic advice should be practical, ethical, and relentlessly focused on execution.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '30',
                            'alignment' => 'center',
                            'primary_cta_label' => 'Our Core Services',
                            'primary_cta_url' => '/services',
                            'secondary_cta_label' => null,
                            'secondary_cta_url' => null,
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'heading' => 'Built on Transparency, Driven by Results',
                            'content' => '<p>At Vanguard Strategy, we reject boilerplate slide decks and empty buzzwords. Every business challenge is unique, demanding rigorous analysis, close collaboration with internal teams, and actionable roadmaps that produce tangible return on investment.</p><p>Our multidisciplinary team brings deep experience spanning cloud infrastructure, enterprise software, supply chain optimization, and private equity turnaround.</p>',
                            'width' => 'narrow',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'data' => [
                            'heading' => 'Our Global Reach',
                            'subheading' => 'Delivering excellence across North America, Europe, and Asia-Pacific.',
                            'stats' => [
                                ['number' => '18', 'label' => 'Years in Business'],
                                ['number' => '45+', 'label' => 'Senior Advisors'],
                                ['number' => '8', 'label' => 'Regional Offices'],
                                ['number' => '$1.2B+', 'label' => 'Client Value Unlocked'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // 3. Services Page
        Page::query()->updateOrCreate(
            ['slug' => 'services'],
            [
                'title' => 'Services',
                'is_published' => true,
                'is_home' => false,
                'show_in_nav' => true,
                'nav_order' => 3,
                'seo_title' => 'Our Services - Vanguard Strategy Solutions',
                'seo_description' => 'Explore our complete suite of corporate advisory, cloud architecture, and operations consulting.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'End-to-End Enterprise Solutions',
                            'subheading' => 'From initial discovery to full operational execution, we provide the expertise your leadership team needs.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '30',
                            'alignment' => 'center',
                            'primary_cta_label' => 'Speak With an Advisor',
                            'primary_cta_url' => '/contact',
                            'secondary_cta_label' => null,
                            'secondary_cta_url' => null,
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'data' => [
                            'heading' => 'Specialized Practice Areas',
                            'subheading' => 'Deep industry specialization delivering measurable business outcomes.',
                            'columns' => '3',
                            'features' => [
                                [
                                    'icon' => 'sparkles',
                                    'title' => 'Executive Advisory',
                                    'description' => 'Board-level counsel on market positioning, competitive moats, and capital allocation.',
                                ],
                                [
                                    'icon' => 'bolt',
                                    'title' => 'Cloud Migration',
                                    'description' => 'Zero-downtime database and application transitions to modern containerized platforms.',
                                ],
                                [
                                    'icon' => 'paint-brush',
                                    'title' => 'Product Systems',
                                    'description' => 'Comprehensive design systems and accessible frontend design for SaaS products.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'data' => [
                            'heading' => 'Frequently Asked Questions',
                            'subheading' => 'Common questions about our consulting model and engagement timelines.',
                            'faqs' => [
                                [
                                    'question' => 'How does Vanguard structure client engagements?',
                                    'answer' => 'We offer flexible engagement models including milestone-based project sprints, fractional executive leadership, and embedded team advisory.',
                                ],
                                [
                                    'question' => 'What is the typical duration of a strategy sprint?',
                                    'answer' => 'Most strategic discovery sprints conclude in 4 to 6 weeks, resulting in an executive decision memo and technical roadmap.',
                                ],
                                [
                                    'question' => 'Can your team assist with hands-on technical implementation?',
                                    'answer' => 'Yes. In addition to advisory, our engineering leads assist internal teams with architecture reviews and direct implementation.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta_banner',
                        'data' => [
                            'heading' => 'Have a Specific Challenge in Mind?',
                            'text' => 'Reach out today for a confidential discussion regarding your organizational goals.',
                            'button_label' => 'Contact Us',
                            'button_url' => '/contact',
                        ],
                    ],
                ],
            ]
        );

        // 4. Contact Page
        Page::query()->updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => 'Contact',
                'is_published' => true,
                'is_home' => false,
                'show_in_nav' => true,
                'nav_order' => 4,
                'seo_title' => 'Contact Vanguard Strategy',
                'seo_description' => 'Get in touch with our team for strategic advisory, partnerships, or project consultations.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Connect With Our Leadership',
                            'subheading' => 'We respond to all inquiries within one business day.',
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
                            'heading' => 'Send Us a Message',
                            'subheading' => 'Fill out the form below or reach us directly via email or phone.',
                        ],
                    ],
                ],
            ]
        );
    }
}
