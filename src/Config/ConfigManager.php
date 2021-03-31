<?php

namespace Depiedra\LaravelGettext\Config;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Depiedra\LaravelGettext\Config\Models\Config as ConfigModel;
use Depiedra\LaravelGettext\Storages\SessionStorage;
use Depiedra\LaravelGettext\Adapters\LaravelAdapter;
use Depiedra\LaravelGettext\Exceptions\RequiredConfigurationKeyException;

class ConfigManager
{
    /**
     * Config model
     *
     * @var ConfigModel
     */
    protected $config;

    /**
     * Package configuration route (published)
     */
    const DEFAULT_PACKAGE_CONFIG = 'laravel-gettext';

    /**
     * @param array $config
     *
     * @throws RequiredConfigurationKeyException
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->config = $this->generateFromArray($config);
        } else {
            $this->config = new ConfigModel;
        }
    }

    /**
     * Get new instance of the ConfigManager
     *
     * @param null $config
     *
     * @return static
     * @throws \Depiedra\LaravelGettext\Exceptions\RequiredConfigurationKeyException
     */
    public static function create($config = null)
    {
        if (is_null($config)) {
            $config = Config::get(static::DEFAULT_PACKAGE_CONFIG);
        }

        return new static($config);
    }

    /**
     * Get the config model
     *
     * @return ConfigModel
     */
    public function get()
    {
        return $this->config;
    }

    /**
     * Creates a configuration container and checks the required fields
     *
     * @param array $config
     *
     * @return ConfigModel
     * @throws \Depiedra\LaravelGettext\Exceptions\RequiredConfigurationKeyException
     * @throws \Exception
     */
    protected function generateFromArray(array $config)
    {
        $requiredKeys = [
            'locale', 'fallback-locale', 'encoding', 'handler'
        ];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $config)) {
                throw new RequiredConfigurationKeyException(
                    sprintf('Unconfigured required value: %s', $key)
                );
            }
        }

        $container = new ConfigModel();

        $container->setHandler($config['handler'])
            ->setLocale($config['locale'])
            ->setSessionIdentifier($config['session-identifier'] ?? 'laravel-gettext')
            ->setEncoding($config['encoding'])
            ->setCategories(Arr::get($config, 'categories', ['LC_ALL']))
            ->setFallbackLocale($config['fallback-locale'])
            ->setSupportedLocales($config['supported-locales'])
            ->setLocaleLabels($config['locale-labels'])
            ->setLocaleEnglishLabels($config['locale-english-labels'])
            ->setDomain($config['domain'])
            ->setTranslationsPath($config['translations-path'])
            ->setProject($config['project'])
            ->setTranslator($config['translator'])
            ->setSourcePaths($config['source-paths'])
            ->setSyncLaravel($config['sync-laravel'])
            ->setAdapter($config['adapter'] ?? LaravelAdapter::class)
            ->setStorage($config['storage'] ?? SessionStorage::class);

        if (array_key_exists('relative-path', $config)) {
            $container->setRelativePath($config['relative-path']);
        }

        if (array_key_exists('base-path', $config)) {
            $container->setBasePath($config['base-path']);
        }

        if (array_key_exists("custom-locale", $config)) {
            $container->setCustomLocale($config['custom-locale']);
        }

        if (array_key_exists("keywords-list", $config)) {
            $container->setKeywordsList($config['keywords-list']);
        }

        return $container;
    }
}
