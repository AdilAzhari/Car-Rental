<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Widget
{
    protected string $view = 'filament.widgets.language-switcher';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    public function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    public function getAvailableLocales(): array
    {
        return [
            'en' => [
                'name' => 'English',
                'flag' => '🇺🇸',
                'native' => 'English',
            ],
            'ar' => [
                'name' => 'Arabic',
                'flag' => '🇸🇦',
                'native' => 'العربية',
            ],
        ];
    }

    public function switchLanguage(string $locale): void
    {
        if (in_array($locale, ['en', 'ar'])) {
            Session::put('locale', $locale);
            $this->redirect(request()->header('Referer'));
        }
    }
}
