<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class FeaturesGridBlock implements PageBlock
{
    public static function name(): string
    {
        return 'features_grid';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Services & Features Grid')
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Our Services')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('High-impact solutions designed to accelerate your growth.')
                    ->rows(2)
                    ->maxLength(500),
                Select::make('columns')
                    ->label('Grid Columns')
                    ->options([
                        '2' => '2 Columns',
                        '3' => '3 Columns',
                        '4' => '4 Columns',
                    ])
                    ->default('3'),
                Repeater::make('features')
                    ->label('Feature Cards')
                    ->schema([
                        TextInput::make('icon')
                            ->label('Heroicon Name')
                            ->placeholder('e.g. sparkles, bolt, shield-check, rocket-launch')
                            ->default('sparkles')
                            ->required(),
                        TextInput::make('title')
                            ->label('Title')
                            ->default('Service or Feature')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->default('High quality implementation with attention to detail and performance.')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->defaultItems(3)
                    ->collapsible()
                    ->reorderable(),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.features-grid';
    }
}
