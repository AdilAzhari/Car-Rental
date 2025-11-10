<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format amount with currency symbol
     */
    public static function format(float|int $amount, ?string $currency = null): string
    {
        $currency ??= config('app.currency');
        $currencyConfig = config("app.currencies.{$currency}");

        if (! $currencyConfig) {
            // Fallback to default currency if specified currency is not supported
            $currency = config('app.currency', 'MYR');
            $currencyConfig = config("app.currencies.{$currency}");
        }

        $symbol = $currencyConfig['symbol'] ?? config('app.currency_symbol', 'RM');
        $position = $currencyConfig['position'] ?? config('app.currency_position', 'before');
        $decimalPlaces = $currencyConfig['decimal_places'] ?? 2;

        $formattedAmount = number_format($amount, $decimalPlaces);

        return $position === 'before'
            ? $symbol.$formattedAmount
            : $formattedAmount.$symbol;
    }

    /**
     * Get currency symbol
     */
    public static function getSymbol(?string $currency = null): string
    {
        $currency ??= config('app.currency');
        $currencyConfig = config("app.currencies.{$currency}");

        return $currencyConfig['symbol'] ?? config('app.currency_symbol', 'RM');
    }

    /**
     * Get currency code
     */
    public static function getCurrency(): string
    {
        return config('app.currency', 'MYR');
    }

    /**
     * Get all supported currencies
     */
    public static function getSupportedCurrencies(): array
    {
        return config('app.currencies', []);
    }

    /**
     * Check if currency is supported
     */
    public static function isSupported(string $currency): bool
    {
        return isset(config('app.currencies')[$currency]);
    }

    /**
     * Get currency name
     */
    public static function getCurrencyName(?string $currency = null): string
    {
        $currency ??= config('app.currency');
        $currencyConfig = config("app.currencies.{$currency}");

        return $currencyConfig['name'] ?? $currency;
    }

    /**
     * Convert amount to cents/smallest unit for payment processors
     */
    public static function toCents(float|int $amount, ?string $currency = null): int
    {
        $currency ??= config('app.currency');
        $currencyConfig = config("app.currencies.{$currency}");
        $decimalPlaces = $currencyConfig['decimal_places'] ?? 2;

        return (int) ($amount * 10 ** $decimalPlaces);
    }

    /**
     * Convert from cents/smallest unit to decimal amount
     */
    public static function fromCents(int $cents, ?string $currency = null): float
    {
        $currency ??= config('app.currency');
        $currencyConfig = config("app.currencies.{$currency}");
        $decimalPlaces = $currencyConfig['decimal_places'] ?? 2;

        return $cents / 10 ** $decimalPlaces;
    }
}
