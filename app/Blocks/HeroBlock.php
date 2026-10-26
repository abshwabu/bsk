<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class HeroBlock implements PageBlock
{
    public static function name(): string
    {
        return 'hero';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Hero Section')
            ->icon('heroicon-o-sparkles')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Build Your Next Great Project')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('A modern, modular starter kit designed for high-performing client websites.')
                    ->rows(3)
                    ->maxLength(500),
                Grid::make(2)
                    ->schema([
                        FileUpload::make('background_image')
                            ->label('Background Image')
                            ->disk('public')
                            ->directory('blocks/hero')
                            ->image()
                            ->maxSize(3072),
                        TextInput::make('background_image_alt')
                            ->label('Image Alt Text')
                            ->default('Hero visual background')
                            ->maxLength(255),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('overlay_opacity')
                            ->label('Overlay Opacity')
                            ->options([
                                '0' => '0% (No overlay)',
                                '20' => '20%',
                                '40' => '40%',
                                '60' => '60%',
                                '80' => '80%',
                            ])
                            ->default('40'),
                        Select::make('alignment')
                            ->label('Text Alignment')
                            ->options([
                                'left' => 'Left',
                                'center' => 'Center',
                                'right' => 'Right',
                            ])
                            ->default('center'),
                    ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('primary_cta_label')
                            ->label('Primary CTA Label')
                            ->default('Get Started')
                            ->maxLength(100),
                        TextInput::make('primary_cta_url')
                            ->label('Primary CTA URL')
                            ->default('#')
                            ->maxLength(255),
                    ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('secondary_cta_label')
                            ->label('Secondary CTA Label')
                            ->default('Learn More')
                            ->maxLength(100),
                        TextInput::make('secondary_cta_url')
                            ->label('Secondary CTA URL')
                            ->default('#')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.hero';
    }
}
