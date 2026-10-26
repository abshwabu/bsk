<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) site()->isModuleEnabled('catalog');
    }

    public static function canViewAny(): bool
    {
        return (bool) site()->isModuleEnabled('catalog');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Group::make([
                            Section::make('Product Details')
                                ->schema([
                                    TextInput::make('name')
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
                                        ->unique(Product::class, 'slug', ignoreRecord: true)
                                        ->maxLength(255),

                                    Grid::make(2)
                                        ->schema([
                                            Select::make('product_category_id')
                                                ->label('Category')
                                                ->relationship('category', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->createOptionForm([
                                                    TextInput::make('name')->required(),
                                                    TextInput::make('slug')->required(),
                                                ]),

                                            Select::make('stock_status')
                                                ->label('Stock Status')
                                                ->options(Product::stockStatuses())
                                                ->default(Product::STOCK_IN_STOCK)
                                                ->required(),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('price')
                                                ->label('Price')
                                                ->numeric()
                                                ->prefix(site('currency') ?: 'USD')
                                                ->step(0.01)
                                                ->helperText('Leave empty for price upon request.'),

                                            TextInput::make('currency')
                                                ->label('Currency Code')
                                                ->placeholder(site('currency') ?: 'USD')
                                                ->maxLength(10)
                                                ->helperText('Overrides store currency if specified.'),
                                        ]),

                                    RichEditor::make('description')
                                        ->label('Description & Specifications')
                                        ->toolbarButtons([
                                            'bold', 'italic', 'underline', 'strike',
                                            'bulletList', 'orderedList', 'h2', 'h3',
                                            'link', 'undo', 'redo',
                                        ]),
                                ]),

                            Section::make('Product Images')
                                ->description('Upload gallery images. Optimized to max 1600px WebP automatically.')
                                ->schema([
                                    FileUpload::make('images')
                                        ->label('Images Gallery')
                                        ->disk('public')
                                        ->directory('products')
                                        ->multiple()
                                        ->reorderable()
                                        ->image()
                                        ->maxSize(10240)
                                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                                            return ImageOptimizer::optimizeAndStore($file, 'products', 1600);
                                        }),
                                ]),
                        ])->columnSpan(['lg' => 2]),

                        Group::make([
                            Section::make('Publishing & Visibility')
                                ->schema([
                                    Toggle::make('is_published')
                                        ->label('Published')
                                        ->default(true),

                                    Toggle::make('is_featured')
                                        ->label('Featured in Catalog')
                                        ->default(false),

                                    TextInput::make('sort')
                                        ->label('Sort Order')
                                        ->numeric()
                                        ->default(0),
                                ]),

                            Section::make('Search Engine Optimization')
                                ->schema([
                                    TextInput::make('seo_title')
                                        ->label('SEO Title')
                                        ->maxLength(255),

                                    TextInput::make('seo_description')
                                        ->label('SEO Description')
                                        ->maxLength(500),

                                    FileUpload::make('og_image')
                                        ->label('Social Share Image')
                                        ->disk('public')
                                        ->directory('products/seo')
                                        ->image()
                                        ->maxSize(2048),
                                ]),
                        ])->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Image')
                    ->disk('public')
                    ->getStateUsing(fn (Product $record): ?string => $record->primaryImage())
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->placeholder('Uncategorized'),

                TextColumn::make('price')
                    ->label('Price')
                    ->sortable()
                    ->formatStateUsing(fn (Product $record): string => $record->formattedPrice()),

                TextColumn::make('stock_status')
                    ->label('Stock')
                    ->badge()
                    ->colors([
                        'success' => Product::STOCK_IN_STOCK,
                        'danger' => Product::STOCK_OUT_OF_STOCK,
                        'warning' => Product::STOCK_MADE_TO_ORDER,
                    ])
                    ->formatStateUsing(fn (string $state): string => Product::stockStatuses()[$state] ?? $state),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort')
                    ->sortable(),
            ])
            ->reorderable('sort')
            ->defaultSort('sort')
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                SelectFilter::make('stock_status')
                    ->options(Product::stockStatuses()),

                TernaryFilter::make('is_published')
                    ->label('Published'),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_published' => true]);
                            Notification::make()
                                ->title('Selected products published.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_published' => false]);
                            Notification::make()
                                ->title('Selected products unpublished.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
