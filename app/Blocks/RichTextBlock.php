<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;

class RichTextBlock implements PageBlock
{
    public static function name(): string
    {
        return 'rich_text';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Rich Text')
            ->icon('heroicon-o-document-text')
            ->schema([
                RichEditor::make('content')
                    ->label('Content')
                    ->required(),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.rich-text';
    }
}
