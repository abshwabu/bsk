<?php

namespace App\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class ContactBlock implements PageBlock
{
    public static function name(): string
    {
        return 'contact';
    }

    public static function schema(): Block
    {
        return Block::make(static::name())
            ->label('Contact Section')
            ->icon('heroicon-o-envelope')
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->default('Get In Touch')
                    ->maxLength(255),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->default('Have questions or want to discuss a project? Reach out anytime.')
                    ->rows(2)
                    ->maxLength(500),
                Toggle::make('show_details')
                    ->label('Display Site Contact Details')
                    ->helperText('Pull and display phone, email, address, and social links from Site Settings.')
                    ->default(true),
            ]);
    }

    public static function view(): string
    {
        return 'blocks.contact';
    }
}
