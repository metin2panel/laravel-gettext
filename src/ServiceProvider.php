<?php

namespace Depiedra\LaravelGettext;

use Depiedra\LaravelGettext\Adapters\AdapterInterface;
use Depiedra\LaravelGettext\Config\ConfigManager;
use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\Translators\BaseTranslator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('laravel-gettext.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     * @throws \Depiedra\LaravelGettext\Exceptions\RequiredConfigurationKeyException
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', ConfigManager::DEFAULT_PACKAGE_CONFIG
        );

        $configuration = ConfigManager::create();

        $this->app->bind(AdapterInterface::class, $configuration->get()->getAdapter());

        $this->app->singleton(Config::class, function () use ($configuration) {
            return $configuration->get();
        });

        $this->app->singleton(TranslatorManager::class, function ($app) use ($configuration) {
            return new TranslatorManager($app, $configuration->get());
        });

        $this->app->bind(BaseTranslator::class, function ($app) {
            /** @var \Illuminate\Contracts\Foundation\Application $app */
            return $app->make(TranslatorManager::class)->driver();
        });

        $this->app->alias(BaseTranslator::class, 'laravel-gettext');

        $this->registerCommands();
    }

    /**
     * Register commands
     */
    protected function registerCommands()
    {
        $this->app->bind('depiedra::gettext.create', function ($app) {
            return new Commands\GettextCreate();
        });

        $this->app->bind('depiedra::gettext.update', function ($app) {
            return new Commands\GettextUpdate();
        });

        $this->commands([
            'depiedra::gettext.create',
            'depiedra::gettext.update',
        ]);
    }

    /**
     * Get the services
     *
     * @return array
     */
    public function provides()
    {
        return [
            TranslatorManager::class, BaseTranslator::class, Config::class, 'laravel-gettext',
        ];
    }
}
