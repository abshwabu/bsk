<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use ReflectionClass;

class BlockRegistry
{
    /**
     * Cache of discovered block classes mapped by block name.
     *
     * @var array<string, class-string<PageBlock>>|null
     */
    protected static ?array $blockClasses = null;

    /**
     * Auto-discover all classes in App\Blocks implementing PageBlock.
     *
     * @return array<string, class-string<PageBlock>>
     */
    public static function getBlockClasses(): array
    {
        if (static::$blockClasses !== null) {
            return static::$blockClasses;
        }

        $classes = [];
        $directory = app_path('Blocks');

        if (! is_dir($directory)) {
            return static::$blockClasses = [];
        }

        $files = scandir($directory);

        foreach ($files as $file) {
            if (! str_ends_with($file, '.php')) {
                continue;
            }

            $className = 'App\\Blocks\\'.pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            if ($reflection->isInstantiable() && $reflection->implementsInterface(PageBlock::class)) {
                $classes[$className::name()] = $className;
            }
        }

        return static::$blockClasses = $classes;
    }

    /**
     * Return all block schemas for Filament Builder.
     *
     * @return array<int, Block>
     */
    public static function all(): array
    {
        return array_values(array_map(
            fn (string $blockClass) => $blockClass::schema(),
            static::getBlockClasses()
        ));
    }

    /**
     * Find a block class by name.
     *
     * @return class-string<PageBlock>|null
     */
    public static function find(string $name): ?string
    {
        $classes = static::getBlockClasses();

        return $classes[$name] ?? null;
    }

    /**
     * Get the blade view for a block name.
     */
    public static function getView(string $name): ?string
    {
        $class = static::find($name);

        return $class ? $class::view() : null;
    }

    /**
     * Manually register a block class (primarily for runtime/tests).
     *
     * @param  class-string<PageBlock>  $class
     */
    public static function register(string $class): void
    {
        if (static::$blockClasses === null) {
            static::getBlockClasses();
        }

        if (class_exists($class)) {
            $reflection = new ReflectionClass($class);
            if ($reflection->isInstantiable() && $reflection->implementsInterface(PageBlock::class)) {
                static::$blockClasses[$class::name()] = $class;
            }
        }
    }

    /**
     * Flush the cached block classes.
     */
    public static function flush(): void
    {
        static::$blockClasses = null;
    }
}
