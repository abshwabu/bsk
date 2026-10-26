<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BinaPublishStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bina:publish-storage {--clean : Remove existing files or symlink in public/storage before copying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy storage/app/public assets directly into public/storage for symlink-free shared hosting.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = storage_path('app/public');
        $destination = public_path('storage');

        File::ensureDirectoryExists($source);

        if ($this->option('clean')) {
            if (is_link($destination)) {
                $this->components->info('Removing existing storage symlink...');
                File::delete($destination);
            } elseif (File::isDirectory($destination)) {
                $this->components->info('Cleaning existing files in public/storage...');
                File::cleanDirectory($destination);
            }
        } elseif (is_link($destination)) {
            $this->components->warn(
                'public/storage is currently a symlink. Run with --clean to replace the symlink with a direct directory copy.'
            );

            return Command::FAILURE;
        }

        File::ensureDirectoryExists($destination);

        $this->components->task('Publishing storage assets to public/storage', function () use ($source, $destination) {
            return File::copyDirectory($source, $destination);
        });

        $fileCount = count(File::allFiles($destination));
        $this->components->info("Storage assets successfully published ({$fileCount} files in public/storage).");

        return Command::SUCCESS;
    }
}
