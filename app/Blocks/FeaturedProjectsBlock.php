<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class FeaturedProjectsBlock implements PageBlock
{
    public static function name(): string
    {
        return 'featured_projects';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Featured Projects')
            ->icon('heroicon-o-briefcase')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Featured Work')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('A selection of our proudest projects and digital transformations.')
                    ->rows(2)
                    ->maxLength(500),
                Grid::make(3)
                    ->schema([
                        TextInput::make('count')
                            ->label('Projects Count')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('view_all_label')
                            ->label('View All Label')
                            ->default('View All Projects')
                            ->maxLength(100),
                        TextInput::make('view_all_url')
                            ->label('View All URL')
                            ->default('/projects')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.featured-projects';
    }
}
