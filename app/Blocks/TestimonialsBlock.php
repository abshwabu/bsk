<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TestimonialsBlock implements PageBlock
{
    public static function name(): string
    {
        return 'testimonials';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Testimonials')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('What People Say')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('Feedback from clients who trusted our platform.')
                    ->rows(2)
                    ->maxLength(500),
                Repeater::make('testimonials')
                    ->label('Client Testimonials')
                    ->schema([
                        Textarea::make('quote')
                            ->label('Quote')
                            ->default('The speed, clean architecture, and visual polish exceeded our expectations.')
                            ->required()
                            ->rows(3)
                            ->maxLength(600),
                        TextInput::make('name')
                            ->label('Client Name')
                            ->default('Sarah Jenkins')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('role')
                            ->label('Role / Company')
                            ->default('Founder & Product Lead')
                            ->maxLength(100),
                        FileUpload::make('photo')
                            ->label('Photo Avatar')
                            ->disk('public')
                            ->directory('blocks/testimonials')
                            ->image()
                            ->avatar()
                            ->maxSize(2048),
                    ])
                    ->defaultItems(2)
                    ->collapsible()
                    ->reorderable(),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.testimonials';
    }
}
