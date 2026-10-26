<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Services\ImageOptimizer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
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

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) site()->isModuleEnabled('portfolio');
    }

    public static function canViewAny(): bool
    {
        return (bool) site()->isModuleEnabled('portfolio');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Group::make([
                            Section::make('Project Overview')
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
                                        ->unique(Project::class, 'slug', ignoreRecord: true)
                                        ->maxLength(255),
                                    Select::make('project_category_id')
                                        ->label('Category')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->required()
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')
                                                ->required()
                                                ->unique(ProjectCategory::class, 'slug'),
                                        ]),
                                    Textarea::make('summary')
                                        ->label('Short Summary')
                                        ->rows(3)
                                        ->maxLength(500),
                                    RichEditor::make('body')
                                        ->label('Project Description & Case Study'),
                                ]),

                            Section::make('Media & Gallery')
                                ->schema([
                                    FileUpload::make('cover_image')
                                        ->label('Cover Image (Optimized to max 1600px WebP)')
                                        ->disk('public')
                                        ->directory('projects')
                                        ->image()
                                        ->maxSize(10240)
                                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): ?string {
                                            return ImageOptimizer::optimizeAndStore($file, 'projects');
                                        }),
                                    FileUpload::make('gallery')
                                        ->label('Project Gallery (Optimized WebP)')
                                        ->disk('public')
                                        ->directory('projects/gallery')
                                        ->multiple()
                                        ->reorderable()
                                        ->image()
                                        ->maxSize(10240)
                                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): ?string {
                                            return ImageOptimizer::optimizeAndStore($file, 'projects/gallery');
                                        }),
                                ]),

                            Section::make('Search Engine Optimization (SEO)')
                                ->collapsed()
                                ->schema([
                                    TextInput::make('seo_title')
                                        ->label('SEO Title')
                                        ->placeholder('Leave blank to use project title')
                                        ->maxLength(255),
                                    Textarea::make('seo_description')
                                        ->label('Meta Description')
                                        ->rows(3)
                                        ->maxLength(500),
                                    FileUpload::make('og_image')
                                        ->label('Social Share Image (OG Image)')
                                        ->disk('public')
                                        ->directory('seo')
                                        ->image()
                                        ->maxSize(2048),
                                ]),
                        ])
                            ->columnSpan(['lg' => 2]),

                        Group::make([
                            Section::make('Project Metadata')
                                ->schema([
                                    TextInput::make('client_name')
                                        ->label('Client Name')
                                        ->maxLength(255),
                                    DatePicker::make('completed_on')
                                        ->label('Completion Date'),
                                    TextInput::make('sort')
                                        ->label('Display Order')
                                        ->numeric()
                                        ->default(0),
                                    Toggle::make('is_published')
                                        ->label('Published')
                                        ->default(false),
                                    Toggle::make('is_featured')
                                        ->label('Featured in Highlights')
                                        ->default(false),
                                ]),
                        ])
                            ->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('cover_image')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('client_name')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_on')
                    ->date()
                    ->sortable(),
                TextColumn::make('sort')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('project_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
