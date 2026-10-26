<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class MissingSeoWidget extends BaseWidget
{
    protected static ?string $heading = 'SEO Health: Incomplete Pages';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Page::query()
                    ->where(function (Builder $query) {
                        $query->whereNull('seo_description')
                            ->orWhere('seo_description', '')
                            ->orWhereNull('og_image')
                            ->orWhere('og_image', '');
                    })
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Page Title')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('URL Slug')
                    ->formatStateUsing(fn (Page $record): string => $record->is_home ? '/ (Home)' : '/'.$record->slug),

                TextColumn::make('is_published')
                    ->label('Published')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Published' : 'Draft'),

                TextColumn::make('missing_fields')
                    ->label('Missing SEO')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function (Page $record): array {
                        return $record->missingSeoFields();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit SEO')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Page $record): string => PageResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('All pages have complete SEO metadata!')
            ->emptyStateDescription('Every page has an explicit meta description and social share image configured.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
