<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class StatsBlock implements PageBlock
{
    public static function name(): string
    {
        return 'stats';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Statistics & Numbers')
            ->icon('heroicon-o-chart-bar')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Proven Track Record')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('Key milestones achieved across client launches.')
                    ->rows(2)
                    ->maxLength(500),
                Repeater::make('stats')
                    ->label('Metrics')
                    ->schema([
                        TextInput::make('number')
                            ->label('Number / Value')
                            ->placeholder('e.g. 99%, 10k+, 24/7')
                            ->default('99%')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('label')
                            ->label('Label')
                            ->placeholder('e.g. Client Satisfaction')
                            ->default('Satisfaction Rate')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->defaultItems(4)
                    ->columns(2)
                    ->collapsible()
                    ->reorderable(),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.stats';
    }
}
