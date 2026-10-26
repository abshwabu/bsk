<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class CtaBannerBlock implements PageBlock
{
    public static function name(): string
    {
        return 'cta_banner';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Call To Action Banner')
            ->icon('heroicon-o-megaphone')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Ready to Start Your Project?')
                    ->required()
                    ->maxLength(255),
                Textarea::make('text')
                    ->label('Supporting Text')
                    ->default('Contact our team today for a free consultation and quick delivery timeline.')
                    ->rows(3)
                    ->maxLength(500),
                Grid::make(2)
                    ->schema([
                        TextInput::make('button_label')
                            ->label('Button Label')
                            ->default('Contact Us Now')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('button_url')
                            ->label('Button URL')
                            ->default('#contact')
                            ->required()
                            ->maxLength(255),
                    ]),
                Select::make('theme')
                    ->label('Color Theme')
                    ->options([
                        'primary' => 'Primary Brand Color',
                        'accent' => 'Accent Brand Color',
                        'surface' => 'Subtle Light Card',
                    ])
                    ->default('primary'),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.cta-banner';
    }
}
