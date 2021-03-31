<?php

namespace Depiedra\LaravelGettext\Translators;

use Depiedra\LaravelGettext\FileSystem;
use Depiedra\LaravelGettext\Adapters\AdapterInterface;
use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\Storages\Storage;

class Gettext extends BaseTranslator
{
    /**
     * Translator
     *
     * @var \Gettext\GettextTranslator
     */
    protected $translator;

    /**
     * @var array
     */
    protected $loadedResources = [];

    /**
     * Gettext constructor.
     *
     * @param \Depiedra\LaravelGettext\Config\Models\Config $config
     * @param \Depiedra\LaravelGettext\Adapters\AdapterInterface $adapter
     * @param \Depiedra\LaravelGettext\FileSystem $fileSystem
     * @param \Depiedra\LaravelGettext\Storages\Storage $storage
     */
    public function __construct(Config $config, AdapterInterface $adapter, FileSystem $fileSystem, Storage $storage)
    {
        parent::__construct($config, $adapter, $fileSystem, $storage);

        $this->setLocale($this->storage->getLocale());
        $this->setDomain($this->storage->getDomain());
    }

    /**
     * Creates a new translator instance
     *
     * @return \Gettext\GettextTranslator
     */
    protected function createTranslator()
    {
        $translator = new \Gettext\GettextTranslator();

        return $translator;
    }

    /**
     * Returns the translator instance
     *
     * @return \Gettext\GettextTranslator
     */
    protected function getTranslator()
    {
        if (isset($this->translator)) {
            return $this->translator;
        }

        return $this->translator = $this->createTranslator();
    }

    /**
     * Sets the current locale code
     *
     * @param $locale
     *
     * @return \Depiedra\LaravelGettext\Translators\Gettext
     */
    public function setLocale($locale)
    {
        $locale = ! $this->isLocaleSupported($locale) ? $this->configuration->getFallbackLocale() : $locale;

        try {
            $customLocale = $this->configuration->getCustomLocale() ? "C." : $locale . ".";
            $gettextLocale = $customLocale . $this->getEncoding();

            putenv('LANGUAGE=' . $gettextLocale);

            foreach ($this->configuration->getCategories() as $category) {
                putenv("$category=$gettextLocale");
                setlocale(constant($category), $gettextLocale);
            }

            parent::setLocale($locale);

            if ($this->configuration->isSyncLaravel()) {
                $this->adapter->setLocale($locale);
            }
        } catch (\Exception $e) {
            parent::setLocale($this->configuration->getFallbackLocale());
        }

        return $this;
    }

    /**
     * Sets the current domain and updates gettext domain application
     *
     * @param   String $domain
     *
     * @param bool $default
     *
     * @return \Depiedra\LaravelGettext\Translators\Gettext
     */
    public function setDomain($domain, $default = true)
    {
        $domain = ! $this->isDomainSupported($domain) ? $this->configuration->getDomain() : $domain;

        parent::setDomain($domain, $default);

        $customLocale = $this->configuration->getCustomLocale() ? "/" . $this->getLocale() : "";
        $localePath = $this->fileSystem->getDomainPath() . $customLocale;

        $this->getTranslator()->loadDomain($domain, $localePath, $default);

        return $this;
    }

    /**
     * Gets a translation using the original string.
     *
     * @param string $original
     *
     * @return string
     */
    public function gettext($original)
    {
        return $this->getTranslator()->gettext($original);
    }

    /**
     * Gets a translation checking the plural form.
     *
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function ngettext($original, $plural, $value)
    {
        return $this->getTranslator()->ngettext($original, $plural, $value);
    }

    /**
     * Gets a translation checking the domain and the plural form.
     *
     * @param string $domain
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function dngettext($domain, $original, $plural, $value)
    {
        return $this->setDomain($domain, false)->getTranslator()->dngettext($domain, $original, $plural, $value);
    }

    /**
     * Gets a translation checking the context and the plural form.
     *
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function npgettext($context, $original, $plural, $value)
    {
        return $this->getTranslator()->npgettext($context, $original, $plural, $value);
    }

    /**
     * Gets a translation checking the context.
     *
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    public function pgettext($context, $original)
    {
        return $this->getTranslator()->pgettext($context, $original);
    }

    /**
     * Gets a translation checking the domain.
     *
     * @param string $domain
     * @param string $original
     *
     * @return string
     */
    public function dgettext($domain, $original)
    {
        return $this->setDomain($domain, false)->getTranslator()->dgettext($domain, $original);
    }

    /**
     * Gets a translation checking the domain and context.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    public function dpgettext($domain, $context, $original)
    {
        return $this->setDomain($domain, false)->getTranslator()->dpgettext($domain, $context, $original);
    }

    /**
     * Gets a translation checking the domain, the context and the plural form.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function dnpgettext($domain, $context, $original, $plural, $value)
    {
        return $this->setDomain($domain, false)->getTranslator()->dnpgettext($domain, $context, $original, $plural, $value);
    }
}
