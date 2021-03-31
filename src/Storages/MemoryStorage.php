<?php

namespace Depiedra\LaravelGettext\Storages;

use Depiedra\LaravelGettext\Config\Models\Config;

class MemoryStorage implements Storage
{
    /**
     * Config container
     *
     * @type \Depiedra\LaravelGettext\Config\Models\Config
     */
    protected $configuration;

    /**
     * SessionStorage constructor.
     *
     * @param \Depiedra\LaravelGettext\Config\Models\Config $configuration
     */
    public function __construct(Config $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @var String
     */
    protected $domain;

    /**
     * Current locale
     *
     * @type String
     */
    protected $locale;

    /**
     * Current encoding
     *
     * @type String
     */
    protected $encoding;

    /**
     * Getter for domain
     *
     * @return String
     */
    public function getDomain()
    {
        return $this->domain ?: $this->configuration->getDomain();
    }

    /**
     * @param String $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Getter for locale
     *
     * @return String
     */
    public function getLocale()
    {
        return $this->locale ?: $this->configuration->getLocale();
    }

    /**
     * @param String $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Getter for configuration
     *
     * @return \Depiedra\LaravelGettext\Config\Models\Config
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Getter for encoding
     *
     * @return String
     */
    public function getEncoding()
    {
        return $this->encoding ?: $this->configuration->getEncoding();
    }

    /**
     * @param String $encoding
     *
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }
}