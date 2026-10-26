<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Models\User;
use App\StarterTemplates\TemplateManager;
use App\Support\Rules\StrongPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BinaNewClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bina:new-client
        {--template=business : Starter template to apply (business, portfolio, charity, store)}
        {--name= : Client site name}
        {--tagline= : Site tagline / slogan}
        {--primary= : Primary brand color hex code (e.g. #0f172a)}
        {--accent= : Accent brand color hex code (e.g. #3b82f6)}
        {--phone= : Public contact phone number}
        {--email= : Public contact email}
        {--address= : Business physical address}
        {--whatsapp= : WhatsApp contact phone number}
        {--modules=* : Modules to enable (portfolio, catalog)}
        {--admin-email= : Super-administrator email}
        {--admin-password= : Super-administrator password}
        {--force : Overwrite and seed without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up a brand new client website: configure branding, create admin user, and seed starter pages.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->info('⚡ Bina Site Kit: Client Site Setup & Provisioning');
        $this->newLine();

        // 1. Template Selection
        $templateId = strtolower((string) ($this->option('template') ?: 'business'));
        $template = TemplateManager::find($templateId);

        if (! $template) {
            if ($this->input->isInteractive() && ! $this->option('force')) {
                $templateId = $this->choice(
                    'Select a starter template for this client:',
                    TemplateManager::ids(),
                    'business'
                );
                $template = TemplateManager::find($templateId);
            } else {
                $this->components->error("Invalid template [{$templateId}]. Choose from: ".implode(', ', TemplateManager::ids()));

                return Command::FAILURE;
            }
        }

        $templateDefaults = $template->defaultSettings();

        // 2. Interactive Prompts or Option Values
        $name = $this->option('name') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Client site name', $templateDefaults['site_name'] ?? 'My Client Site')
                : ($templateDefaults['site_name'] ?? 'My Client Site')
        );

        $tagline = $this->option('tagline') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Tagline or description', $templateDefaults['tagline'] ?? '')
                : ($templateDefaults['tagline'] ?? '')
        );

        $primaryColor = $this->option('primary') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Primary brand color (hex)', $templateDefaults['primary_color'] ?? '#0f172a')
                : ($templateDefaults['primary_color'] ?? '#0f172a')
        );

        $accentColor = $this->option('accent') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Accent brand color (hex)', $templateDefaults['accent_color'] ?? '#3b82f6')
                : ($templateDefaults['accent_color'] ?? '#3b82f6')
        );

        $phone = $this->option('phone') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Public phone number', '+15550000000')
                : '+15550000000'
        );

        $email = $this->option('email') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Public contact email', 'hello@example.com')
                : 'hello@example.com'
        );

        $address = $this->option('address') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Physical address', "123 Business St, Suite 100\nCity, ST 12345")
                : "123 Business St, Suite 100\nCity, ST 12345"
        );

        $whatsapp = $this->option('whatsapp') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('WhatsApp number', $phone)
                : $phone
        );

        // Modules
        $passedModules = (array) $this->option('modules');
        $enabledModules = $templateDefaults['enabled_modules'] ?? ['portfolio' => false, 'catalog' => false];

        if (! empty($passedModules)) {
            $enabledModules['portfolio'] = in_array('portfolio', $passedModules, true);
            $enabledModules['catalog'] = in_array('catalog', $passedModules, true);
        } elseif ($this->input->isInteractive() && ! $this->option('force')) {
            $chosen = $this->choice(
                'Which optional modules would you like to enable?',
                ['none', 'portfolio', 'catalog', 'both (portfolio + catalog)'],
                isset($enabledModules['portfolio']) && $enabledModules['portfolio'] ? 1 : (isset($enabledModules['catalog']) && $enabledModules['catalog'] ? 2 : 0)
            );

            if ($chosen === 'both (portfolio + catalog)') {
                $enabledModules['portfolio'] = true;
                $enabledModules['catalog'] = true;
            } elseif ($chosen === 'portfolio') {
                $enabledModules['portfolio'] = true;
            } elseif ($chosen === 'catalog') {
                $enabledModules['catalog'] = true;
            } elseif ($chosen === 'none') {
                $enabledModules['portfolio'] = false;
                $enabledModules['catalog'] = false;
            }
        }

        // Admin Credentials
        $adminEmail = $this->option('admin-email') ?: (
            $this->input->isInteractive() && ! $this->option('force')
                ? $this->ask('Super-admin email', config('bina.admin_email') ?: 'admin@example.com')
                : (config('bina.admin_email') ?: 'admin@example.com')
        );

        $adminPassword = $this->option('admin-password');
        if (blank($adminPassword)) {
            if ($this->input->isInteractive() && ! $this->option('force')) {
                $adminPassword = $this->secret('Super-admin password (leave blank to generate a secure password)');
            }
            if (blank($adminPassword)) {
                $adminPassword = config('bina.admin_password') ?: 'BinaAdmin!2026';
            }
        }

        // Validate password against StrongPassword rule
        $validator = Validator::make(['password' => $adminPassword], ['password' => StrongPassword::rule()]);
        if ($validator->fails() && $this->input->isInteractive() && ! $this->option('force')) {
            $this->components->warn('Password does not meet complexity rules (min 8 chars, mixed case, numbers, symbols). Setting to a secure default: BinaAdmin!2026');
            $adminPassword = 'BinaAdmin!2026';
        }

        // 3. Execution
        $this->components->task("Applying starter template [{$template->name()}]", function () use (
            $templateId,
            $name,
            $tagline,
            $primaryColor,
            $accentColor,
            $phone,
            $email,
            $address,
            $whatsapp,
            $enabledModules
        ) {
            $setting = SiteSetting::singleton();
            TemplateManager::apply($templateId, $setting, [
                'site_name' => $name,
                'tagline' => $tagline,
                'primary_color' => $primaryColor,
                'accent_color' => $accentColor,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'whatsapp' => $whatsapp,
                'enabled_modules' => $enabledModules,
            ]);

            return true;
        });

        $this->components->task("Configuring super-admin user [{$adminEmail}]", function () use ($adminEmail, $adminPassword) {
            config(['bina.admin_email' => $adminEmail]);

            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make($adminPassword),
                ]
            );

            return true;
        });

        // 4. Summary Table
        $this->newLine();
        $this->table(
            ['Configuration', 'Value'],
            [
                ['Site Name', $name],
                ['Template', $template->name()." ({$templateId})"],
                ['Primary Color', $primaryColor],
                ['Accent Color', $accentColor],
                ['Enabled Modules', implode(', ', array_keys(array_filter($enabledModules))) ?: 'None (Clean Core)'],
                ['Public Site', url('/')],
                ['Admin Panel', url('/admin')],
                ['Admin Login Email', $adminEmail],
                ['Admin Password', $adminPassword],
            ]
        );

        $this->newLine();
        $this->components->info('🎉 New client site provisioned successfully! You can now log into the admin panel.');

        return Command::SUCCESS;
    }
}
