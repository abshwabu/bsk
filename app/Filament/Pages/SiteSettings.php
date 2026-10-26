<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = SiteSetting::singleton();

        $this->form->fill($setting->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Brand')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                TextInput::make('site_name')
                                    ->label('Site Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('tagline')
                                    ->label('Tagline')
                                    ->maxLength(255),
                                Grid::make(2)
                                    ->schema([
                                        FileUpload::make('logo')
                                            ->label('Logo')
                                            ->disk('public')
                                            ->directory('branding')
                                            ->image()
                                            ->maxSize(2048),
                                        FileUpload::make('favicon')
                                            ->label('Favicon')
                                            ->disk('public')
                                            ->directory('branding')
                                            ->image()
                                            ->maxSize(1024),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        ColorPicker::make('primary_color')
                                            ->label('Primary Color')
                                            ->required(),
                                        ColorPicker::make('accent_color')
                                            ->label('Accent Color')
                                            ->required(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Select::make('font_heading')
                                            ->label('Heading Font')
                                            ->options([
                                                'Inter' => 'Inter',
                                                'Plus Jakarta Sans' => 'Plus Jakarta Sans',
                                                'Poppins' => 'Poppins',
                                                'Roboto' => 'Roboto',
                                                'Outfit' => 'Outfit',
                                                'Montserrat' => 'Montserrat',
                                                'Playfair Display' => 'Playfair Display',
                                                'Merriweather' => 'Merriweather',
                                                'sans-serif' => 'System Sans-Serif',
                                            ])
                                            ->searchable(),
                                        Select::make('font_body')
                                            ->label('Body Font')
                                            ->options([
                                                'Inter' => 'Inter',
                                                'Plus Jakarta Sans' => 'Plus Jakarta Sans',
                                                'Poppins' => 'Poppins',
                                                'Roboto' => 'Roboto',
                                                'Open Sans' => 'Open Sans',
                                                'Lato' => 'Lato',
                                                'Merriweather' => 'Merriweather',
                                                'sans-serif' => 'System Sans-Serif',
                                            ])
                                            ->searchable(),
                                    ]),
                                Textarea::make('footer_text')
                                    ->label('Footer Text')
                                    ->rows(2)
                                    ->maxLength(500),
                            ]),

                        Tab::make('Contact')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->telRegex('/^[+0-9\s\(\)\-\.]*$/')
                                    ->maxLength(255),
                                TextInput::make('whatsapp')
                                    ->label('WhatsApp Number')
                                    ->tel()
                                    ->telRegex('/^[+0-9\s\(\)\-\.]*$/')
                                    ->maxLength(255),
                                Textarea::make('address')
                                    ->label('Physical Address')
                                    ->rows(3)
                                    ->maxLength(500),
                            ]),

                        Tab::make('Social')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Repeater::make('social_links')
                                    ->label('Social Media Profiles')
                                    ->schema([
                                        Select::make('platform')
                                            ->options([
                                                'github' => 'GitHub',
                                                'twitter' => 'Twitter / X',
                                                'facebook' => 'Facebook',
                                                'instagram' => 'Instagram',
                                                'linkedin' => 'LinkedIn',
                                                'youtube' => 'YouTube',
                                                'tiktok' => 'TikTok',
                                                'other' => 'Other',
                                            ])
                                            ->required(),
                                        TextInput::make('url')
                                            ->label('Profile URL')
                                            ->url()
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->reorderable()
                                    ->collapsible(),
                            ]),

                        Tab::make('Integrations')
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                TextInput::make('analytics_id')
                                    ->label('Google Analytics Measurement ID')
                                    ->placeholder('G-XXXXXXXXXX')
                                    ->maxLength(255),
                                TextInput::make('telegram_bot_token')
                                    ->label('Telegram Bot Token')
                                    ->password()
                                    ->revealable()
                                    ->maxLength(255),
                                TextInput::make('telegram_chat_id')
                                    ->label('Telegram Chat ID')
                                    ->maxLength(255),
                            ]),

                        Tab::make('Modules')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Section::make('Feature Modules')
                                    ->description('Toggle available site features and modules.')
                                    ->schema([
                                        Toggle::make('enabled_modules.portfolio')
                                            ->label('Portfolio Module')
                                            ->helperText('Enable portfolio showcase and case studies.'),
                                        Toggle::make('enabled_modules.catalog')
                                            ->label('Catalog Module')
                                            ->helperText('Enable product catalog or services listings.'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = SiteSetting::singleton();
        $setting->update($data);

        Notification::make()
            ->title('Site settings saved successfully.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->submit('save'),
        ];
    }
}
