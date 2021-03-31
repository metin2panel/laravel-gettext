<?php

namespace Depiedra\LaravelGettext;

use Depiedra\LaravelGettext\Adapters\AdapterInterface;
use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\Translators\Gettext;
use Depiedra\LaravelGettext\Translators\Symfony;
use Depiedra\LaravelGettext\Translators\Translator;
use Illuminate\Support\Manager;
use Illuminate\Foundation\Application;

class TranslatorManager extends Manager
{
    /**
     * Config container
     *
     * @type \Depiedra\LaravelGettext\Config\Models\Config
     */
    protected $config;

    /**
     * TranslatorManager constructor.
     *
     * @param \Illuminate\Foundation\Application $app
     * @param \Depiedra\LaravelGettext\Config\Models\Config $config
     */
    public function __construct(Application $app, Config $config)
    {
        parent::__construct($app);

        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->getHandler();
    }

    /**
     * @return \Depiedra\LaravelGettext\Translators\Gettext
     */
    public function createGettextDriver()
    {
        $adapter = $this->container->make(AdapterInterface::class);
        $fileSystem = new FileSystem($this->container['files'], $this->config);
        $storage = $this->container->make($this->config->getStorage());

        return new Gettext($this->config, $adapter, $fileSystem, $storage);
    }

    /**
     * @return \Depiedra\LaravelGettext\Translators\Symfony
     */
    public function createSymfonyDriver()
    {
        $adapter = $this->container->make(AdapterInterface::class);
        $fileSystem = new FileSystem($this->container['files'], $this->config);
        $storage = $this->container->make($this->config->getStorage());

        return new Symfony($this->config, $adapter, $fileSystem, $storage);
    }

    /**
     * @return \Depiedra\LaravelGettext\Translators\Translator
     */
    public function createDefaultDriver()
    {
        $adapter = $this->container->make(AdapterInterface::class);
        $fileSystem = new FileSystem($this->container['files'], $this->config);
        $storage = $this->container->make($this->config->getStorage());

        return new Translator($this->config, $adapter, $fileSystem, $storage);
    }
}
