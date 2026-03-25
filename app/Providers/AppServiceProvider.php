<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Import\Archives\RarImportArchiveExtractor;
use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use App\Services\Import\Export\Locators\Archive\TelegramExportArchiveLocator;
use App\Services\Import\Export\Locators\Archive\WhatsAppExportArchiveLocator;
use App\Services\Import\Factories\ImportArchiveExtractorFactory;
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

        $this->app->singleton(ExportArchiveLocatorFactory::class, function ($app) {
            return (new ExportArchiveLocatorFactory())
                ->register('telegram', $app->make(TelegramExportArchiveLocator::class))
                ->register('whatsapp', $app->make(WhatsAppExportArchiveLocator::class));
        });

        $this->app->singleton(ImportArchiveExtractorFactory::class, function ($app) {
            return (new ImportArchiveExtractorFactory())
                ->register($app->make(ZipImportArchiveExtractor::class))
                ->register($app->make(RarImportArchiveExtractor::class));
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
