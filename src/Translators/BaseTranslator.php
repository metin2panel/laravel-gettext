<?php

namespace Depiedra\LaravelGettext\Translators;

use Depiedra\LaravelGettext\Adapters\AdapterInterface;
use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\Exceptions\UndefinedDomainException;
use Depiedra\LaravelGettext\FileSystem;
use Depiedra\LaravelGettext\Storages\Storage;

abstract class BaseTranslator extends \Gettext\BaseTranslator implements TranslatorInterface
{
    /**
     * Config container
     *
     * @type \Depiedra\LaravelGettext\Config\Models\Config
     */
    protected $configuration;

    /**
     * Framework adapter
     *
     * @type \Depiedra\LaravelGettext\Adapters\LaravelAdapter
     */
    protected $adapter;

    /**
     * File system helper
     *
     * @var \Depiedra\LaravelGettext\FileSystem
     */
    protected $fileSystem;

    /**
     * @var Storage
     */
    protected $storage;


    /**
     * Initializes the module translator
     *
     * @param Config $config
     * @param AdapterInterface $adapter
     * @param FileSystem $fileSystem
     *
     * @param Storage $storage
     */
    public function __construct(
        Config $config, AdapterInterface $adapter, FileSystem $fileSystem, Storage $storage)
    {
        $this->configuration = $config;
        $this->adapter = $adapter;
        $this->fileSystem = $fileSystem;
        $this->storage = $storage;
    }

    /**
     * @return $this|\Gettext\TranslatorInterface|null
     */
    public function register()
    {
        return $this;
    }

    /**
     * Returns the current locale string identifier
     *
     * @return String
     */
    public function getLocale()
    {
        return $this->storage->getLocale();
    }

    /**
     * Sets and stores on session the current locale code
     *
     * @param $locale
     *
     * @return BaseTranslator
     */
    public function setLocale($locale)
    {
        if ($locale != $this->getLocale()) {
            $this->storage->setLocale($locale);
        }

        return $this;
    }

    /**
     * Returns a boolean that indicates if $locale
     * is supported by configuration
     *
     * @param $locale
     *
     * @return bool
     */
    public function isLocaleSupported($locale)
    {
        if ($locale) {
            return in_array($locale, $this->configuration->getSupportedLocales());
        }

        return false;
    }

    /**
     * Return the current locale
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->getLocale();
    }

    /**
     * Gets the Current encoding.
     *
     * @return mixed
     */
    public function getEncoding()
    {
        return $this->storage->getEncoding();
    }

    /**
     * Sets the Current encoding.
     *
     * @param mixed $encoding the encoding
     *
     * @return self
     */
    public function setEncoding($encoding)
    {
        $this->storage->setEncoding($encoding);

        return $this;
    }

    /**
     * Sets the current domain and updates gettext domain application
     *
     * @param   String $domain
     *
     * @param bool $default
     *
     * @return  self
     */
    public function setDomain($domain, $default = true)
    {
        if ($default && $domain != $this->getDomain()) {
            $this->storage->setDomain($domain);
        }

        return $this;
    }

    /**
     * Returns a boolean that indicates if $locale
     * is supported by configuration
     *
     * @param $domain
     *
     * @return bool
     */
    public function isDomainSupported($domain)
    {
        if ($domain) {
            return in_array($domain, $this->configuration->getAllDomains());
        }

        return false;
    }

    /**
     * Returns the current domain
     *
     * @return String
     */
    public function getDomain()
    {
        return $this->storage->getDomain();
    }

    /**
     * Returns supported locales
     *
     * @return array
     */
    public function supportedLocales()
    {
        return $this->configuration->getSupportedLocales();
    }

    /**
     * @return array
     */
    public function localeLabels()
    {
        return $this->configuration->getLocaleLabels();
    }

    /**
     * @return string|null
     */
    public function getLocaleLabel()
    {
        return $this->configuration->getLocaleLabel($this->getLocale());
    }

    /**
     * @return array
     */
    public function localeEnglishLabels()
    {
        return $this->configuration->getLocaleEnglishLabels();
    }

    /**
     * @return string|null
     */
    public function getLocaleEnglishLabel()
    {
        return $this->configuration->getLocaleEnglishLabel($this->getLocale());
    }

    /**
     * Include the gettext functions
     */
    public static function includeFunctions()
    {
        // ignored
    }
}
