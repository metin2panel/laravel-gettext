<?php

namespace Depiedra\LaravelGettext\Translators;

interface TranslatorInterface extends \Gettext\TranslatorInterface
{
    /**
     * Sets the current locale code
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * Returns the current locale string identifier
     *
     * @return String
     */
    public function getLocale();

    /**
     * Returns a boolean that indicates if $locale
     * is supported by configuration
     *
     * @param string $locale
     */
    public function isLocaleSupported($locale);

    /**
     * Returns supported locales
     *
     * @return array
     */
    public function supportedLocales();

    /**
     * Return the current locale
     *
     * @return mixed
     */
    public function __toString();

    /**
     * Gets the Current encoding.
     *
     * @return mixed
     */
    public function getEncoding();

    /**
     * Sets the Current encoding.
     *
     * @param mixed $encoding the encoding
     *
     * @return self
     */
    public function setEncoding($encoding);

    /**
     * Sets the current domain and updates gettext domain application
     *
     * @param   String $domain
     * @param bool $default
     *
     * @return  self
     */
    public function setDomain($domain, $default = true);

    /**
     * Returns the current domain
     *
     * @return String
     */
    public function getDomain();

    /**
     * Returns a boolean that indicates if $locale
     * is supported by configuration
     *
     * @param $domain
     *
     * @return bool
     */
    public function isDomainSupported($domain);

    /**
     * @return array
     */
    public function localeLabels();

    /**
     * @return string|null
     */
    public function getLocaleLabel();

    /**
     * @return array
     */
    public function localeEnglishLabels();

    /**
     * @return string|null
     */
    public function getLocaleEnglishLabel();
}