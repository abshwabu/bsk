<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * Generate dynamic sitemap.xml cached for 1 hour.
     */
    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            $urls = [];

            // 1. Home Page
            $homePage = Page::query()->where('is_home', true)->where('is_published', true)->first();
            $urls[] = [
                'loc' => url('/'),
                'lastmod' => $homePage?->updated_at?->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ];

            // 2. Published CMS Pages
            $pages = Page::query()
                ->where('is_published', true)
                ->where('is_home', false)
                ->get();

            foreach ($pages as $p) {
                $urls[] = [
                    'loc' => url('/'.$p->slug),
                    'lastmod' => $p->updated_at?->toAtomString() ?? now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }

            // 3. Portfolio Projects (if enabled)
            if (site()->isModuleEnabled('portfolio')) {
                $urls[] = [
                    'loc' => route('projects.index'),
                    'lastmod' => now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];

                $projects = Project::query()->published()->get();
                foreach ($projects as $project) {
                    $urls[] = [
                        'loc' => route('projects.show', $project->slug),
                        'lastmod' => $project->updated_at?->toAtomString() ?? now()->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.7',
                    ];
                }
            }

            // 4. Catalog Products (if enabled)
            if (site()->isModuleEnabled('catalog')) {
                $urls[] = [
                    'loc' => route('shop.index'),
                    'lastmod' => now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];

                $products = Product::query()->published()->get();
                foreach ($products as $prod) {
                    $urls[] = [
                        'loc' => route('shop.show', $prod->slug),
                        'lastmod' => $prod->updated_at?->toAtomString() ?? now()->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            }

            // Build XML
            $xmlLines = [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            ];

            foreach ($urls as $item) {
                $xmlLines[] = '  <url>';
                $xmlLines[] = '    <loc>'.htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8').'</loc>';
                $xmlLines[] = '    <lastmod>'.$item['lastmod'].'</lastmod>';
                $xmlLines[] = '    <changefreq>'.$item['changefreq'].'</changefreq>';
                $xmlLines[] = '    <priority>'.$item['priority'].'</priority>';
                $xmlLines[] = '  </url>';
            }

            $xmlLines[] = '</urlset>';

            return implode("\n", $xmlLines);
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * Serve dynamic robots.txt referencing the sitemap.
     */
    public function robots(): Response
    {
        $discourageIndexing = (bool) site('discourage_indexing', false);
        $sitemapUrl = url('/sitemap.xml');

        if ($discourageIndexing) {
            $content = "User-agent: *\nDisallow: /\n";
        } else {
            $content = "User-agent: *\n"
                ."Disallow: /admin\n"
                ."Disallow: /cart\n"
                ."Disallow: /checkout\n"
                ."Disallow: /order-confirmed/\n"
                ."Allow: /\n\n"
                ."Sitemap: {$sitemapUrl}\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
