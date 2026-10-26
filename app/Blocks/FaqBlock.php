<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class FaqBlock implements PageBlock
{
    public static function name(): string
    {
        return 'faq';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('FAQ Accordion')
            ->icon('heroicon-o-question-mark-circle')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Frequently Asked Questions')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('Everything you need to know about our platform and delivery process.')
                    ->rows(2)
                    ->maxLength(500),
                Repeater::make('faqs')
                    ->label('Questions & Answers')
                    ->schema([
                        TextInput::make('question')
                            ->label('Question')
                            ->default('How quickly can we launch a project?')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('answer')
                            ->label('Answer')
                            ->default('Most projects are ready within 1 to 2 weeks thanks to modular blocks and preconfigured panels.')
                            ->required()
                            ->rows(3)
                            ->maxLength(800),
                    ])
                    ->defaultItems(3)
                    ->collapsible()
                    ->reorderable(),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.faq';
    }
}
