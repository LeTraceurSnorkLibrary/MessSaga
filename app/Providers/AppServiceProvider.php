<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Parsers\ParserRegistry;
use App\Services\Parsers\TelegramParser;
use App\Services\Parsers\WhatsAppParser;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
