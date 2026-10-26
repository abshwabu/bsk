<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

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
                TextInput::make('heading')
                    ->label('Heading (Optional)')
                    ->maxLength(255),
                RichEditor::make('content')
                    ->label('Body Content')
                    ->default('<p>We build elegant, reliable digital products that empower businesses to scale smoothly.</p>')
                    ->required(),
                Select::make('width')
                    ->label('Container Width')
                    ->options([
                        'narrow' => 'Narrow (Max 3xl)',
                        'wide' => 'Wide (Max 5xl)',
                        'full' => 'Full (Max 7xl)',
                    ])
                    ->default('narrow'),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.rich-text';
    }
}
