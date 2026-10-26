<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class BinaDoctor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bina:doctor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform diagnostic checks on PHP environment, database, storage permissions, and configuration for shared hosting.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->info('🩺 Running Bina Site Kit Doctor Diagnostics...');
        $this->newLine();

        $checks = [];
        $hasFailure = false;

        // 1. PHP Version
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.2.0', '>=');
        $checks[] = [
            'name' => 'PHP Version (>= 8.2)',
            'status' => $phpOk ? 'PASS' : 'FAIL',
            'details' => "v{$phpVersion}".($phpOk ? ' (meets requirement)' : ' (Requires PHP >= 8.2.0)'),
        ];
        if (! $phpOk) {
            $hasFailure = true;
        }

        // 2. Extensions: gd / imagick
        $hasGd = extension_loaded('gd');
        $hasImagick = extension_loaded('imagick');
        $imgExtOk = $hasGd || $hasImagick;
        $checks[] = [
            'name' => 'Extension: gd / imagick',
            'status' => $imgExtOk ? 'PASS' : 'FAIL',
            'details' => $imgExtOk ? ('Loaded: '.implode(', ', array_filter([$hasGd ? 'gd' : null, $hasImagick ? 'imagick' : null]))) : 'Missing image processing extension (install gd or imagick)',
        ];
        if (! $imgExtOk) {
            $hasFailure = true;
        }

        // Required Extensions
        $requiredExtensions = [
            'mbstring' => 'Multibyte string support',
            'intl' => 'Internationalization support',
            'fileinfo' => 'File MIME-type detection',
            'pdo' => 'Database abstraction layer',
            'openssl' => 'Encryption and HTTPS client',
            'curl' => 'HTTP client / Telegram notifications',
        ];

        foreach ($requiredExtensions as $ext => $desc) {
            $loaded = extension_loaded($ext);
            $checks[] = [
                'name' => "Extension: {$ext}",
                'status' => $loaded ? 'PASS' : 'FAIL',
                'details' => $loaded ? 'Loaded' : "Missing extension ({$desc})",
            ];
            if (! $loaded) {
                $hasFailure = true;
            }
        }

        // 3. Storage Writable
        $storagePath = storage_path();
        $storageWritable = is_writable($storagePath);
        $logsWritable = is_writable(storage_path('logs'));
        $frameworkWritable = is_writable(storage_path('framework'));
        $allStorageWritable = $storageWritable && $logsWritable && $frameworkWritable;

        $checks[] = [
            'name' => 'Writable: storage',
            'status' => $allStorageWritable ? 'PASS' : 'FAIL',
            'details' => $allStorageWritable ? 'Writable (root, logs, framework)' : 'Permissions error: Ensure web server user can write to storage/',
        ];
        if (! $allStorageWritable) {
            $hasFailure = true;
        }

        // 4. Bootstrap Cache Writable
        $bootstrapCache = base_path('bootstrap/cache');
        $bootstrapWritable = is_writable($bootstrapCache);
        $checks[] = [
            'name' => 'Writable: bootstrap/cache',
            'status' => $bootstrapWritable ? 'PASS' : 'FAIL',
            'details' => $bootstrapWritable ? 'Writable' : 'Permissions error: Ensure web server user can write to bootstrap/cache/',
        ];
        if (! $bootstrapWritable) {
            $hasFailure = true;
        }

        // 5. APP_KEY
        $appKey = config('app.key');
        $keyOk = ! empty($appKey);
        $checks[] = [
            'name' => 'Application Key (APP_KEY)',
            'status' => $keyOk ? 'PASS' : 'FAIL',
            'details' => $keyOk ? 'Configured' : 'Missing! Run "php artisan key:generate"',
        ];
        if (! $keyOk) {
            $hasFailure = true;
        }

        // 6. APP_DEBUG in Production
        $env = config('app.env') ?: app()->environment();
        $isProduction = ($env === 'production') || app()->environment('production');
        $debugEnabled = (bool) config('app.debug');
        if ($isProduction && $debugEnabled) {
            $checks[] = [
                'name' => 'Production Debug Mode',
                'status' => 'FAIL',
                'details' => 'APP_DEBUG is true in production! Set APP_DEBUG=false in .env',
            ];
            $hasFailure = true;
        } elseif ($isProduction) {
            $checks[] = [
                'name' => 'Production Debug Mode',
                'status' => 'PASS',
                'details' => 'Disabled (APP_DEBUG=false)',
            ];
        } else {
            $checks[] = [
                'name' => 'Environment & Debug',
                'status' => 'PASS',
                'details' => 'Environment: '.$env.' (Debug: '.($debugEnabled ? 'true' : 'false').')',
            ];
        }

        // 7. Storage link or published assets
        $publicStorage = public_path('storage');
        $hasSymlink = is_link($publicStorage);
        $hasDir = is_dir($publicStorage) && ! $hasSymlink;

        if ($hasSymlink) {
            $checks[] = [
                'name' => 'Public Storage',
                'status' => 'PASS',
                'details' => 'Symlinked to storage/app/public',
            ];
        } elseif ($hasDir) {
            $checks[] = [
                'name' => 'Public Storage',
                'status' => 'PASS',
                'details' => 'Published directory (symlink-free layout)',
            ];
        } else {
            $checks[] = [
                'name' => 'Public Storage',
                'status' => 'WARN',
                'details' => 'public/storage missing. Run "php artisan storage:link" or "bina:publish-storage"',
            ];
        }

        // 8. Database Connection
        try {
            DB::connection()->getPdo();
            $dbDriver = DB::connection()->getDriverName();
            $dbName = DB::connection()->getDatabaseName();
            $checks[] = [
                'name' => 'Database Connection',
                'status' => 'PASS',
                'details' => "Connected ({$dbDriver}: {$dbName})",
            ];
        } catch (Throwable $e) {
            $checks[] = [
                'name' => 'Database Connection',
                'status' => 'FAIL',
                'details' => 'Failed: '.$e->getMessage(),
            ];
            $hasFailure = true;
        }

        // 9. Telegram Credentials
        try {
            $botToken = site('telegram_bot_token');
            $chatId = site('telegram_chat_id');
            $telegramOk = ! empty($botToken) && ! empty($chatId);
            $checks[] = [
                'name' => 'Telegram Notifications',
                'status' => $telegramOk ? 'PASS' : 'WARN',
                'details' => $telegramOk ? 'Bot token and chat ID configured' : 'Not configured (telegram_bot_token or telegram_chat_id empty)',
            ];
        } catch (Throwable $e) {
            $checks[] = [
                'name' => 'Telegram Notifications',
                'status' => 'WARN',
                'details' => 'Unable to read site settings: '.$e->getMessage(),
            ];
        }

        // Format and render table
        $rows = array_map(function ($check) {
            $statusFormatted = match ($check['status']) {
                'PASS' => '<fg=green;options=bold>PASS</>',
                'FAIL' => '<fg=red;options=bold>FAIL</>',
                'WARN' => '<fg=yellow;options=bold>WARN</>',
                default => $check['status'],
            };

            return [
                $check['name'],
                $statusFormatted,
                $check['details'],
            ];
        }, $checks);

        $this->table(['Check', 'Status', 'Details'], $rows);

        $this->newLine();
        if ($hasFailure) {
            $this->components->error('Doctor found issues that need attention before deploying to production.');

            return Command::FAILURE;
        }

        $this->components->info('All critical checks passed! Your environment is ready for deployment.');

        return Command::SUCCESS;
    }
}
