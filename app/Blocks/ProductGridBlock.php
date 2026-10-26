<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ProductGridBlock implements PageBlock
{
    public static function name(): string
    {
        return 'product_grid';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Product Grid')
            ->icon('heroicon-o-shopping-bag')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Featured Products')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('Explore our curated selection of products available for direct order.')
                    ->rows(2)
                    ->maxLength(500),
                Grid::make(3)
                    ->schema([
                        TextInput::make('count')
                            ->label('Products Count')
                            ->numeric()
                            ->default(4)
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('view_all_label')
                            ->label('View All Label')
                            ->default('View All Products')
                            ->maxLength(100),
                        TextInput::make('view_all_url')
                            ->label('View All URL')
                            ->default('/shop')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.product-grid';
    }
}
