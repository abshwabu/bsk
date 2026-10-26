<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ImageGalleryBlock implements PageBlock
{
    public static function name(): string
    {
        return 'image_gallery';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Image Gallery')
            ->icon('heroicon-o-photo')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Photo Gallery')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('A visual showcase of our work and community.')
                    ->rows(2)
                    ->maxLength(500),
                Grid::make(2)
                    ->schema([
                        Select::make('layout')
                            ->label('Gallery Layout')
                            ->options([
                                'grid' => 'Standard Grid',
                                'masonry' => 'Masonry Columns',
                            ])
                            ->default('grid'),
                        Select::make('columns')
                            ->label('Columns')
                            ->options([
                                '2' => '2 Columns',
                                '3' => '3 Columns',
                                '4' => '4 Columns',
                            ])
                            ->default('3'),
                    ]),
                FileUpload::make('images')
                    ->label('Upload Gallery Images')
                    ->disk('public')
                    ->directory('blocks/gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->maxSize(3072),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.image-gallery';
    }
}
