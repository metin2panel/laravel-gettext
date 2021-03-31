<?php

namespace Depiedra\LaravelGettext\Storages;

interface Storage
{
    /**
     * Getter for domain
     *
     * @return String
     */
    public function getDomain();

    /**
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain($domain);

    /**
     * Getter for locale
     *
     * @return String
     */
    public function getLocale();

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale);

    /**
     * Getter for locale
     *
     * @return String
     */
    public function getEncoding();

    /**
     * @param string $encoding
     *
     * @return $this
     */
    public function setEncoding($encoding);

    /**
     * Getter for configuration
     *
     * @return \Depiedra\LaravelGettext\Config\Models\Config
     */
    public function getConfiguration();
}