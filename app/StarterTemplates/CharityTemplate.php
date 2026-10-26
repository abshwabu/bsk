<?php

namespace App\StarterTemplates;

use App\Models\Page;
use App\Models\SiteSetting;

class CharityTemplate implements StarterTemplateInterface
{
    public function id(): string
    {
        return 'charity';
    }

    public function name(): string
    {
        return 'Charity & Non-Profit';
    }

    public function description(): string
    {
        return 'Purpose-driven starter for NGOs, foundations, and community initiatives with donation guides and impact metrics.';
    }

    public function defaultSettings(): array
    {
        return [
            'site_name' => 'HopeForward Global',
            'tagline' => 'Restoring Dignity & Empowering Underserved Communities',
            'primary_color' => '#064e3b',
            'accent_color' => '#10b981',
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
                'seo_title' => 'HopeForward Global - Empowering Communities Worldwide',
                'seo_description' => 'Providing sustainable clean water, healthcare access, and education grants to underserved families.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Together, We Can Change Lives Forever',
                            'subheading' => 'Empowering vulnerable families with sustainable clean water, community healthcare, and educational opportunity across 18 regional partner programs.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '40',
                            'alignment' => 'center',
                            'primary_cta_label' => 'Support Our Mission',
                            'primary_cta_url' => '/donate',
                            'secondary_cta_label' => 'Learn More',
                            'secondary_cta_url' => '/about',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'data' => [
                            'heading' => 'Our Collective Impact',
                            'subheading' => 'Every contribution directly supports transparent, verifiable grassroots programs.',
                            'stats' => [
                                ['number' => '250k+', 'label' => 'Lives Impacted'],
                                ['number' => '48', 'label' => 'Clean Water Wells Built'],
                                ['number' => '100%', 'label' => 'Financial Transparency'],
                                ['number' => '18', 'label' => 'Regional Communities'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'features_grid',
                        'data' => [
                            'heading' => 'Our Core Initiatives',
                            'subheading' => 'Long-term solutions designed in partnership with local community leadership.',
                            'columns' => '3',
                            'features' => [
                                [
                                    'icon' => 'sparkles',
                                    'title' => 'Sustainable Clean Water',
                                    'description' => 'Solar-powered deep water wells and water filtration systems maintained by local technicians.',
                                ],
                                [
                                    'icon' => 'bolt',
                                    'title' => 'Girls Education Grants',
                                    'description' => 'Tuition, school supplies, and digital literacy training for young women across rural communities.',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'title' => 'Mobile Health Clinics',
                                    'description' => 'Direct preventative healthcare, prenatal care, and essential medicines delivered to remote villages.',
                                ],
                                [
                                    'icon' => 'paint-brush',
                                    'title' => 'Micro-Enterprise Loans',
                                    'description' => 'Revolving micro-grants enabling women and local artisans to launch self-sustaining businesses.',
                                ],
                                [
                                    'icon' => 'device-phone-mobile',
                                    'title' => 'Disaster Relief Response',
                                    'description' => 'Rapid humanitarian assistance, food packs, and emergency shelter following climate disasters.',
                                ],
                                [
                                    'icon' => 'chart-bar',
                                    'title' => 'Sustainable Agriculture',
                                    'description' => 'Drought-resistant seed programs and climate-smart irrigation training for smallholder farmers.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'testimonials',
                        'data' => [
                            'heading' => 'Voices from the Field',
                            'subheading' => 'Direct perspectives from our community coordinators and program leaders.',
                            'testimonials' => [
                                [
                                    'quote' => 'The solar water well in our village changed everything. Children are healthy and girls no longer walk miles each morning.',
                                    'name' => 'Amina Diallo',
                                    'role' => 'Community Committee Lead, Senegal',
                                    'photo' => null,
                                ],
                                [
                                    'quote' => 'HopeForward ensures every donation is accounted for with photographic proof and local audit reporting.',
                                    'name' => 'David Ochieng',
                                    'role' => 'Regional Health Director, Kenya',
                                    'photo' => null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta_banner',
                        'data' => [
                            'heading' => 'Every Gift Changes a Family’s Tomorrow',
                            'text' => 'Join our Circle of Hope monthly donor community to provide sustained assistance all year long.',
                            'button_label' => 'Give Today',
                            'button_url' => '/donate',
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
                'seo_title' => 'About HopeForward Global - Our Mission & Vision',
                'seo_description' => 'Learn how our grassroots approach empowers communities with lasting self-reliance.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'About HopeForward Global',
                            'subheading' => 'Founded in 2018 with a belief in transparency, dignity, and local leadership.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '30',
                            'alignment' => 'center',
                            'primary_cta_label' => 'How to Donate',
                            'primary_cta_url' => '/donate',
                            'secondary_cta_label' => null,
                            'secondary_cta_url' => null,
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'heading' => 'Our Core Values & Pledge',
                            'content' => '<p>At HopeForward, we believe that true humanitarian work is not about charity—it is about restoring agency and opportunity. Rather than imposing outside solutions, 100% of our programs are conceptualized and run by local community members.</p><p>We maintain an audited 88% direct-program spending ratio, ensuring your contributions directly impact families on the ground.</p>',
                            'width' => 'narrow',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'data' => [
                            'heading' => 'Financial Accountability',
                            'subheading' => 'Where your donations go: transparent financial allocation.',
                            'stats' => [
                                ['number' => '88%', 'label' => 'Direct Program Support'],
                                ['number' => '7%', 'label' => 'Monitoring & Field Audits'],
                                ['number' => '5%', 'label' => 'Operations & Governance'],
                                ['number' => 'Grade A', 'label' => 'Charity Transparency Rating'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // 3. Donate Page
        Page::query()->updateOrCreate(
            ['slug' => 'donate'],
            [
                'title' => 'Donate',
                'is_published' => true,
                'is_home' => false,
                'show_in_nav' => true,
                'nav_order' => 3,
                'seo_title' => 'Donate to HopeForward Global - Direct Community Support',
                'seo_description' => 'Ways to give: online, wire transfer, corporate matching, and tax-deductible pledges.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Support Our Mission',
                            'subheading' => 'Your tax-deductible contribution provides clean water, healthcare, and education.',
                            'background_image' => null,
                            'background_image_alt' => null,
                            'overlay_opacity' => '30',
                            'alignment' => 'center',
                            'primary_cta_label' => 'Contact Donor Relations',
                            'primary_cta_url' => '/contact',
                            'secondary_cta_label' => null,
                            'secondary_cta_url' => null,
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'heading' => 'How to Contribute',
                            'content' => '<h3>Direct Bank Wire Transfer</h3><p>For wire transfers and larger philanthropic donations, please reference <code>HOPE-GLOBAL-2026</code> on your wire transmission. Reach out via our Contact page for our verified banking coordinates.</p><h3>Tax-Exempt Status</h3><p>HopeForward Global is a registered non-profit organization. Donors in the United States, United Kingdom, and the European Union receive formal receipt documentation for tax-deductible giving.</p>',
                            'width' => 'narrow',
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'data' => [
                            'heading' => 'Donation & Giving FAQs',
                            'subheading' => 'Everything you need to know about contributing to HopeForward Global.',
                            'faqs' => [
                                [
                                    'question' => 'How can I verify that my donation reached the field?',
                                    'answer' => 'All donors receive quarterly field updates detailing project completion photos, GPS coordinates, and community impact reports.',
                                ],
                                [
                                    'question' => 'Does HopeForward accept corporate matching gifts?',
                                    'answer' => 'Yes. Many corporate matching programs match employee gifts 1:1. Please contact us to register your company.',
                                ],
                                [
                                    'question' => 'Can I designate my gift to a specific country or initiative?',
                                    'answer' => 'Yes. You may specify Clean Water, Education, or Medical Care during donation submission.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta_banner',
                        'data' => [
                            'heading' => 'Interested in Corporate or Philanthropic Partnerships?',
                            'text' => 'Our executive development team works directly with foundations and family offices on custom grants.',
                            'button_label' => 'Speak With Donor Relations',
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
                'seo_title' => 'Contact HopeForward Global',
                'seo_description' => 'Reach out to our global coordination team for volunteer inquiries or donations.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Connect With Our Team',
                            'subheading' => 'Our regional coordination teams in Geneva, London, and Nairobi are here to help.',
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
                            'heading' => 'Send Us an Inquiry',
                            'subheading' => 'Fill out the form below and a program officer will follow up with you promptly.',
                        ],
                    ],
                ],
            ]
        );
    }
}
