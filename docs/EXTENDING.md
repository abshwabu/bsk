# Extending Bina Site Kit

This guide explains how to extend **Bina Site Kit** by adding custom **Page Blocks** and building new **Toggleable Modules**.

---

## Part 1: Adding a New Page Block

All page blocks in Bina Site Kit are self-contained classes located in `app/Blocks/`. The `BlockRegistry` automatically discovers any class implementing `App\Blocks\PageBlock`, registering its Filament schema and rendering its Blade template.

### The `PageBlock` Contract

```php
namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;

interface PageBlock
{
    /**
     * Unique internal identifier for the block (e.g. 'pricing_table').
     */
    public static function name(): string;

    /**
     * Filament Builder Block definition and form schema.
     */
    public static function schema(): Block;

    /**
     * Path to the Blade view template (e.g. 'blocks.pricing-table').
     */
    public static function view(): string;
}
```

---

### Step-by-Step Example: Creating a `PricingTableBlock`

#### Step 1: Create the Block Class
Create `app/Blocks/PricingTableBlock.php`:

```php
<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class PricingTableBlock implements PageBlock
{
    public static function name(): string
    {
        return 'pricing_table';
    }

    public static function view(): string
    {
        return 'blocks.pricing-table';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Pricing Table')
            ->icon('heroicon-o-currency-dollar')
            ->schema([
                TextInput::make('heading')
                    ->label('Section Heading')
                    ->default('Transparent Investment Plans')
                    ->required(),
                TextInput::make('subheading')
                    ->label('Section Subheading')
                    ->default('Choose the tier that matches your organization’s stage.'),
                Repeater::make('tiers')
                    ->label('Pricing Tiers')
                    ->schema([
                        TextInput::make('name')
                            ->label('Plan Name')
                            ->placeholder('e.g. Starter, Growth, Enterprise')
                            ->required(),
                        TextInput::make('price')
                            ->label('Price Tag')
                            ->placeholder('e.g. $49/mo, $199/mo, Custom')
                            ->required(),
                        TextInput::make('description')
                            ->label('Short Description')
                            ->placeholder('Ideal for early-stage teams'),
                        Repeater::make('features')
                            ->label('Features Included')
                            ->simple(TextInput::make('feature')->required()),
                        TextInput::make('cta_label')
                            ->label('Button Label')
                            ->default('Get Started')
                            ->required(),
                        TextInput::make('cta_url')
                            ->label('Button Link')
                            ->default('/contact')
                            ->required(),
                        Toggle::make('is_highlighted')
                            ->label('Highlight as Most Popular')
                            ->default(false),
                    ])
                    ->defaultItems(3)
                    ->collapsible()
                    ->columns(2),
            ]);
    }
}
```

#### Step 2: Create the Blade View
Create `resources/views/blocks/pricing-table.blade.php`:

```blade
@php
    $heading = $data['heading'] ?? '';
    $subheading = $data['subheading'] ?? null;
    $tiers = $data['tiers'] ?? [];
@endphp

<section class="py-16 md:py-24 bg-slate-50">
    <div class="mx-auto max-w-7xl px-6">
        @if ($heading)
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    {{ $heading }}
                </h2>
                @if ($subheading)
                    <p class="mt-4 text-base text-slate-600 sm:text-lg">
                        {{ $subheading }}
                    </p>
                @endif
            </div>
        @endif

        @if (! empty($tiers))
            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 items-stretch">
                @foreach ($tiers as $tier)
                    @php
                        $isPopular = ! empty($tier['is_highlighted']);
                    @endphp
                    <div class="relative flex flex-col justify-between rounded-2xl bg-white p-8 shadow-sm transition hover:shadow-md ring-1 {{ $isPopular ? 'ring-primary shadow-lg scale-105' : 'ring-slate-200' }}">
                        @if ($isPopular)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white uppercase tracking-wider">
                                Most Popular
                            </span>
                        @endif

                        <div>
                            <h3 class="text-xl font-bold text-slate-900">{{ $tier['name'] ?? '' }}</h3>
                            @if (! empty($tier['description']))
                                <p class="mt-2 text-sm text-slate-500">{{ $tier['description'] }}</p>
                            @endif
                            <div class="mt-6 flex items-baseline">
                                <span class="font-heading text-4xl font-extrabold tracking-tight text-slate-900">
                                    {{ $tier['price'] ?? '' }}
                                </span>
                            </div>

                            @if (! empty($tier['features']))
                                <ul class="mt-6 space-y-3 text-sm text-slate-600 border-t border-slate-100 pt-6">
                                    @foreach ($tier['features'] as $feat)
                                        <li class="flex items-center space-x-2">
                                            <svg class="h-4 w-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            <span>{{ is_array($feat) ? ($feat['feature'] ?? '') : $feat }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="mt-8">
                            <a
                                href="{{ $tier['cta_url'] ?? '/contact' }}"
                                class="inline-flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-semibold transition shadow-sm {{ $isPopular ? 'bg-primary text-white hover:opacity-90' : 'bg-slate-100 text-slate-800 hover:bg-slate-200' }}"
                            >
                                {{ $tier['cta_label'] ?? 'Get Started' }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
```

#### Step 3: Immediate Availability
That's it! Because `BlockRegistry::all()` auto-discovers all classes implementing `PageBlock`, your new `PricingTableBlock` will immediately appear in the Filament `PageResource` block builder dropdown, and will render automatically when added to any page.

---

## Part 2: Adding a New Toggleable Module

Modules in Bina Site Kit are optional, domain-specific features (such as `portfolio` or `catalog`) that can be turned on or off per client. When disabled:
- Filament navigation items are hidden from administrators.
- Public routes return an HTTP 404.

Here is how to create a new **Events & Workshops** module (`events`).

---

### Step 1: Create Database Migration & Model

Generate the migration and model:
```bash
php artisan make:model Event -m
```

In the migration file:
```php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('summary')->nullable();
    $table->longText('description')->nullable();
    $table->dateTime('starts_at');
    $table->string('location')->nullable();
    $table->decimal('ticket_price', 10, 2)->nullable();
    $table->boolean('is_published')->default(true);
    $table->integer('sort')->default(0);
    $table->timestamps();
});
```

In `app/Models/Event.php`:
```php
namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasSeo;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'is_published' => 'boolean',
            'ticket_price' => 'decimal:2',
            'sort' => 'integer',
        ];
    }
}
```

---

### Step 2: Register Module in `SiteSetting`

In `app/Models/SiteSetting.php`, add `'events'` to the default `enabled_modules` array:

```php
'enabled_modules' => [
    'portfolio' => false,
    'catalog' => false,
    'events' => false, // New module key
],
```

Add the toggle checkbox in `app/Filament/Pages/SiteSettings.php` under the **Modules** tab:

```php
Tabs\Tab::make('Modules')
    ->icon('heroicon-o-puzzle-piece')
    ->schema([
        Section::make('Toggleable Feature Modules')
            ->schema([
                Toggle::make('enabled_modules.portfolio')
                    ->label('Portfolio & Case Studies Module'),
                Toggle::make('enabled_modules.catalog')
                    ->label('Product Catalog & Order Requests Module'),
                Toggle::make('enabled_modules.events')
                    ->label('Events & Workshops Module')
                    ->helperText('Enable public event listings and admin management.'),
            ]),
    ]),
```

---

### Step 3: Guard the Filament Admin Resource

When generating the Filament Resource (`php artisan make:filament-resource EventResource`), override `shouldRegisterNavigation()`:

```php
namespace App\Filament\Resources;

use App\Models\Event;
use Filament\Resources\Resource;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Content';

    /**
     * Hide from admin navigation when the module is disabled.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return site()->isModuleEnabled('events');
    }

    // ... schema and table definitions
}
```

---

### Step 4: Guard Public Routes in `routes/web.php`

In `routes/web.php`, wrap your event routes in a check against `site()->isModuleEnabled('events')`:

```php
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', function () {
        if (! site()->isModuleEnabled('events')) {
            abort(404);
        }

        $events = \App\Models\Event::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->paginate(12);

        return view('events.index', compact('events'));
    })->name('index');

    Route::get('/{slug}', function (string $slug) {
        if (! site()->isModuleEnabled('events')) {
            abort(404);
        }

        $event = \App\Models\Event::query()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('events.show', compact('event'));
    })->name('show');
});
```

Remember to add `events` to the reserved slugs array in `PageResource` and the negative lookahead regex in `routes/web.php` so no CMS page collides with `/events`.
