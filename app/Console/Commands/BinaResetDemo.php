<?php

namespace App\Console\Commands;

use App\Models\Inquiry;
use App\Models\OrderRequest;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\SiteSetting;
use App\StarterTemplates\TemplateManager;
use Illuminate\Console\Command;

class BinaResetDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bina:reset-demo
        {--template=business : The starter template to reseed (business, portfolio, charity, store)}
        {--force : Force reset without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely wipe all site pages, demo products, projects, and inquiries, then reseed a clean starter template.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();

        // 1. Production Guard: STRICTLY REFUSE TO RUN IN PRODUCTION
        $env = config('app.env') ?: app()->environment();
        if ($env === 'production' || app()->environment('production')) {
            $this->components->error('DANGER: Cannot reset demo content in production environment! Aborting.');

            return Command::FAILURE;
        }

        $templateId = strtolower((string) ($this->option('template') ?: 'business'));
        $template = TemplateManager::find($templateId);

        if (! $template) {
            $this->components->error("Unknown starter template [{$templateId}]. Choose from: ".implode(', ', TemplateManager::ids()));

            return Command::FAILURE;
        }

        // 2. Interactive Confirmation Prompt
        if (! $this->option('force')) {
            $confirmed = $this->confirm(
                '⚠️ This will PERMANENTLY WIPE all existing pages, projects, catalog products, inquiries, and orders. Do you wish to proceed?',
                false
            );

            if (! $confirmed) {
                $this->components->info('Demo reset cancelled.');

                return Command::SUCCESS;
            }
        }

        // 3. Wipe Content Safely
        $this->components->task('Clearing existing CMS pages and blocks', function () {
            Page::query()->delete();

            return true;
        });

        $this->components->task('Clearing portfolio projects and categories', function () {
            Project::query()->delete();
            ProjectCategory::query()->delete();

            return true;
        });

        $this->components->task('Clearing catalog products and categories', function () {
            Product::query()->delete();
            ProductCategory::query()->delete();

            return true;
        });

        $this->components->task('Clearing inquiries and customer order requests', function () {
            Inquiry::query()->delete();
            OrderRequest::query()->delete();

            return true;
        });

        // 4. Reseed Chosen Template
        $this->components->task("Reseeding template [{$template->name()}]", function () use ($templateId) {
            $setting = SiteSetting::singleton();
            TemplateManager::apply($templateId, $setting);

            return true;
        });

        $this->newLine();
        $this->components->info("✨ Demo content successfully reset to starter template [{$template->name()}].");

        return Command::SUCCESS;
    }
}
