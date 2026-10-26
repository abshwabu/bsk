<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Services\Navigation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class BinaOptimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bina:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize Bina Site Kit for production: caches config, routes, views, and navigation.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Optimizing Bina Site Kit...');

        // 1. Config Cache
        $this->components->task('Caching framework configuration', function () {
            Artisan::call('config:cache');

            return true;
        });

        // 2. Route Cache
        $this->components->task('Caching application routes', function () {
            Artisan::call('route:cache');

            return true;
        });

        // 3. View Cache
        $this->components->task('Pre-compiling Blade views', function () {
            Artisan::call('view:cache');

            return true;
        });

        // 4. Flush and pre-warm site caches
        $this->components->task('Re-indexing navigation and sitemap', function () {
            SiteSetting::flushCache();
            Navigation::flushCache();
            Cache::forget('sitemap.xml');

            return true;
        });

        $this->newLine();
        $this->info('🚀 Bina Site Kit optimized successfully for high-performance hosting!');

        return Command::SUCCESS;
    }
}
