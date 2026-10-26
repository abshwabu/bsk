<?php

namespace App\Filament\Resources;

use App\Blocks\BlockRegistry;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Group::make([
                            Section::make('General')
                                ->schema([
                                    TextInput::make('title')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                            if ($operation === 'create') {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),
                                    TextInput::make('slug')
                                        ->required()
                                        ->unique(Page::class, 'slug', ignoreRecord: true)
                                        ->notIn(['admin', 'storage', 'sitemap.xml', 'robots.txt', 'inquiry', 'shop', 'projects', 'cart', 'checkout', 'order-confirmed'])
                                        ->validationMessages([
                                            'not_in' => 'The slug ":input" is reserved by the system and cannot be used.',
                                        ])
                                        ->maxLength(255),
                                ])
                                ->columns(2),

                            Section::make('Content')
                                ->description('Compose this page using modular blocks.')
                                ->schema([
                                    Builder::make('blocks')
                                        ->label('Page Blocks')
                                        ->blocks(BlockRegistry::all())
                                        ->collapsible()
                                        ->cloneable(),
                                ]),

                            Section::make('Search Engine Optimization (SEO)')
                                ->collapsed()
                                ->schema([
                                    TextInput::make('seo_title')
                                        ->label('SEO Title')
                                        ->placeholder('Leave blank to fallback to page title')
                                        ->maxLength(255),
                                    Textarea::make('seo_description')
                                        ->label('Meta Description')
                                        ->rows(3)
                                        ->maxLength(500),
                                    FileUpload::make('og_image')
                                        ->label('Open Graph Image')
                                        ->disk('public')
                                        ->directory('seo')
                                        ->image()
                                        ->maxSize(2048),
                                ]),
                        ])
                            ->columnSpan(['lg' => 2]),

                        Group::make([
                            Section::make('Settings')
                                ->schema([
                                    Toggle::make('is_published')
                                        ->label('Published')
                                        ->helperText('Make this page publicly visible.')
                                        ->default(false),
                                    Toggle::make('is_home')
                                        ->label('Home Page')
                                        ->helperText('Make this the site homepage (unsets any previous home page).')
                                        ->default(false),
                                    Toggle::make('show_in_nav')
                                        ->label('Show in Navigation')
                                        ->default(false),
                                    TextInput::make('nav_order')
                                        ->label('Navigation Order')
                                        ->numeric()
                                        ->default(0),
                                ]),
                        ])
                            ->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->badge(),
                IconColumn::make('is_home')
                    ->label('Home')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('show_in_nav')
                    ->label('In Nav')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('nav_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_home')->label('Home Page'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
