<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;

interface PageBlock
{
    public static function name(): string;

    public static function schema(): Block;

    public static function view(): string;
}
