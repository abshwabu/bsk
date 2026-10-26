<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\ImageOptimizer;
use App\Services\TelegramNotifier;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Actions as FormActions;
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
                                            ->maxSize(2048)
                                            ->saveUploadedFileUsing(function ($file) {
                                                return ImageOptimizer::optimizeAndStore($file, 'branding', 800);
                                            }),
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
                                Repeater::make('extra_nav_links')
                                    ->label('Additional Navigation Links')
                                    ->helperText('Custom external or specific internal links to include in the site navigation.')
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Link Label')
                                            ->required(),
                                        TextInput::make('url')
                                            ->label('Target URL')
                                            ->required(),
                                        Toggle::make('open_in_new_tab')
                                            ->label('Open in new tab')
                                            ->default(false),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->reorderable(),
                                Textarea::make('footer_text')
                                    ->label('Footer Text')
                                    ->rows(2)
                                    ->maxLength(500),
                                Toggle::make('show_powered_by')
                                    ->label('Display "Powered by" branding line in footer')
                                    ->default(true),
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
                                Toggle::make('discourage_indexing')
                                    ->label('Discourage search engines from indexing this site (noindex)')
                                    ->helperText('Sets robots meta to noindex and blocks crawlers in robots.txt. Useful for staging environments.')
                                    ->default(false),
                                TextInput::make('telegram_bot_token')
                                    ->label('Telegram Bot Token')
                                    ->password()
                                    ->revealable()
                                    ->maxLength(255),
                                TextInput::make('telegram_chat_id')
                                    ->label('Telegram Chat ID')
                                    ->maxLength(255),
                                FormActions::make([
                                    FormAction::make('send_test_telegram')
                                        ->label('Send test message')
                                        ->icon('heroicon-o-paper-airplane')
                                        ->color('primary')
                                        ->action('sendTestTelegram'),
                                ]),
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
                                        TextInput::make('currency')
                                            ->label('Store Currency Code')
                                            ->placeholder('USD')
                                            ->default('USD')
                                            ->maxLength(10)
                                            ->helperText('Default currency code for products and order checkout (e.g. USD, EUR, GBP, KES).'),
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

    public function sendTestTelegram(): void
    {
        $setting = SiteSetting::singleton();
        $token = $this->data['telegram_bot_token'] ?? $setting->telegram_bot_token;
        $chatId = $this->data['telegram_chat_id'] ?? $setting->telegram_chat_id;

        if (empty($token) || empty($chatId)) {
            Notification::make()
                ->title('Missing Telegram Credentials')
                ->body('Please provide both a Telegram Bot Token and Chat ID.')
                ->warning()
                ->send();

            return;
        }

        $notifier = new TelegramNotifier($token, $chatId);
        $sent = $notifier->sendTestMessage();

        if ($sent) {
            Notification::make()
                ->title('Test message sent successfully!')
                ->body('Check your Telegram chat to confirm delivery.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failed to send test message')
                ->body('Telegram API rejected the request or could not connect. Check token, chat ID, and logs.')
                ->danger()
                ->send();
        }
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
