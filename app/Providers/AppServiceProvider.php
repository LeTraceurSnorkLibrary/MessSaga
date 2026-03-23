<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Import\Archive\Factories\ArchiveExportLocatorFactory;
use App\Services\Import\Archive\Locators\FallbackArchiveExportLocator;
use App\Services\Import\Archive\Locators\TelegramArchiveExportLocator;
use App\Services\Import\Archive\Locators\WhatsAppArchiveExportLocator;
use App\Services\Parsers\ParserRegistry;
use App\Services\Parsers\TelegramParser;
use App\Services\Parsers\WhatsAppParser;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Propaganistas\LaravelPhone\PhoneNumber;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ParserRegistry::class, function ($app) {
            $registry = new ParserRegistry();
            $registry->register('telegram', $app->make(TelegramParser::class));
            $registry->register('whatsapp', $app->make(WhatsAppParser::class));

            return $registry;
        });

        $this->app->singleton(ArchiveExportLocatorFactory::class, function ($app) {
            return (new ArchiveExportLocatorFactory())
                ->register('telegram', $app->make(TelegramArchiveExportLocator::class))
                ->register('whatsapp', $app->make(WhatsAppArchiveExportLocator::class))
                ->setFallback($app->make(FallbackArchiveExportLocator::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            try {
                return new PhoneNumber(
                    (string)$value,
                    $parameters
                        ?: ['RU']
                )->isValid();
            } catch (Exception $e) {
                return false;
            }
        });
    }
}
