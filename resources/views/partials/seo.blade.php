@php
    $seoModel = $model ?? ($page ?? ($project ?? ($product ?? null)));
    $siteName = site('site_name') ?? config('app.name', 'Bina Site Kit');

    if ($seoModel && method_exists($seoModel, 'seoTitle')) {
        $resolvedTitle = $title ?? $seoModel->seoTitle();
        $resolvedDesc = $description ?? $seoModel->seoDescription();
        $resolvedImage = $image ?? $seoModel->seoImage();
        $isPublished = $seoModel->is_published ?? true;
    } else {
        $resolvedTitle = $title ?? $siteName;
        $resolvedDesc = $description ?? (site('tagline') ?: 'A clean and modern starter for client websites.');
        $resolvedImage = $image ?? site()->logoUrl();
        $isPublished = true;
    }

    $canonicalUrl = $canonical ?? url()->current();

    // Check robots indexing rules
    $discourageIndexing = (bool) site('discourage_indexing', false);
    $forceNoindex = (isset($noindex) && $noindex) || ! $isPublished || request()->has('preview') || $discourageIndexing;
@endphp

<title>{{ $resolvedTitle }}</title>
<meta name="description" content="{{ $resolvedDesc }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

@if ($forceNoindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:title" content="{{ $resolvedTitle }}">
<meta property="og:description" content="{{ $resolvedDesc }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if ($resolvedImage)
    <meta property="og:image" content="{{ $resolvedImage }}">
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $resolvedTitle }}">
<meta name="twitter:description" content="{{ $resolvedDesc }}">
@if ($resolvedImage)
    <meta name="twitter:image" content="{{ $resolvedImage }}">
@endif

{{-- JSON-LD: Organization or LocalBusiness (on all pages) --}}
@php
    $hasAddress = ! empty(site('address'));
    $orgType = $hasAddress ? 'LocalBusiness' : 'Organization';

    $socials = array_values(array_filter(array_column(site('social_links') ?? [], 'url')));

    $orgSchema = [
        '@context' => 'https://schema.org',
        '@type' => $orgType,
        'name' => $siteName,
        'url' => url('/'),
    ];

    if ($logo = site()->logoUrl()) {
        $orgSchema['logo'] = $logo;
        $orgSchema['image'] = $logo;
    }

    if ($phone = site('phone')) {
        $orgSchema['telephone'] = $phone;
    }

    if ($email = site('email')) {
        $orgSchema['email'] = $email;
    }

    if ($hasAddress) {
        $orgSchema['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => site('address'),
        ];
    }

    if (! empty($socials)) {
        $orgSchema['sameAs'] = $socials;
    }
@endphp
<script type="application/ld+json">
{!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- JSON-LD: BreadcrumbList (on detail pages) --}}
@php
    $breadcrumbsList = [];

    if (isset($breadcrumbs) && is_array($breadcrumbs)) {
        $breadcrumbsList = $breadcrumbs;
    } elseif ($seoModel instanceof \App\Models\Project) {
        $breadcrumbsList = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Projects', 'url' => route('projects.index')],
            ['name' => $seoModel->title, 'url' => route('projects.show', $seoModel->slug)],
        ];
    } elseif ($seoModel instanceof \App\Models\Product) {
        $breadcrumbsList = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Shop', 'url' => route('shop.index')],
        ];
        if ($seoModel->category) {
            $breadcrumbsList[] = ['name' => $seoModel->category->name, 'url' => route('shop.index', ['category' => $seoModel->category->slug])];
        }
        $breadcrumbsList[] = ['name' => $seoModel->name, 'url' => route('shop.show', $seoModel->slug)];
    } elseif ($seoModel instanceof \App\Models\Page && ! $seoModel->is_home) {
        $breadcrumbsList = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => $seoModel->title, 'url' => route('pages.show', $seoModel->slug)],
        ];
    }
@endphp

@if (! empty($breadcrumbsList))
    @php
        $itemsElements = [];
        foreach ($breadcrumbsList as $idx => $bc) {
            $itemsElements[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $bc['name'],
                'item' => $bc['url'],
            ];
        }

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemsElements,
        ];
    @endphp
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- JSON-LD: Product (on shop items) --}}
@if ($seoModel instanceof \App\Models\Product)
    @php
        $productImages = $seoModel->imageUrls();
        if (empty($productImages) && $resolvedImage) {
            $productImages = [$resolvedImage];
        }

        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $seoModel->name,
            'description' => strip_tags((string) ($seoModel->description ?? $resolvedDesc)),
            'image' => $productImages,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => $seoModel->effectiveCurrency(),
                'price' => number_format((float) ($seoModel->price ?? 0), 2, '.', ''),
                'availability' => $seoModel->stock_status === \App\Models\Product::STOCK_IN_STOCK
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ];
    @endphp
<script type="application/ld+json">
{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
