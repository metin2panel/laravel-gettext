<?php namespace Depiedra\LaravelGettext\Translators;

use Gettext\Translations;
use Depiedra\LaravelGettext\Adapters\AdapterInterface;
use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\FileSystem;
use Depiedra\LaravelGettext\Storages\Storage;

class Translator extends BaseTranslator
{
    /**
     * Translator
     *
     * @var \Gettext\Translator
     */
    protected $translator;

    /**
     * @var array
     */
    protected $loadedResources = [];

    /**
     * Translator constructor.
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
     * @return \Gettext\Translator
     */
    protected function createTranslator()
    {
        $translator = new \Gettext\Translator();

        return $translator;
    }

    /**
     * Returns the translator instance
     *
     * @return \Gettext\Translator
     */
    protected function getTranslator()
    {
        if (isset($this->translator)) {
            return $this->translator;
        }

        return $this->translator = $this->createTranslator();
    }

    /**
     * Set locale overload.
     * Needed to re-build the catalogue when locale changes.
     *
     * @param $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $locale = ! $this->isLocaleSupported($locale) ? $this->configuration->getFallbackLocale() : $locale;

        parent::setLocale($locale);

        $this->loadLocaleFile();

        if ($this->configuration->isSyncLaravel()) {
            $this->adapter->setLocale($locale);
        }

        return $this;
    }

    /**
     * Set domain overload.
     * Needed to re-build the catalogue when domain changes.
     *
     * @param String $domain
     * @param bool $default
     *
     * @return $this
     */
    public function setDomain($domain, $default = true)
    {
        $domain = ! $this->isDomainSupported($domain) ? $this->configuration->getDomain() : $domain;

        parent::setDomain($domain, $default);

        $this->loadLocaleFile($domain);

        if ($default) {
            $this->getTranslator()->defaultDomain($domain);
        }

        return $this;
    }

    /**
     * @internal param $translator
     *
     * @param null|string $domain
     */
    protected function loadLocaleFile($domain = null)
    {
        $domain = $domain ?: $this->getDomain();

        if (isset($this->loadedResources[$domain])
            && isset($this->loadedResources[$domain][$this->getLocale()])
        ) {
            return;
        }

        $translator = $this->getTranslator();

        if (file_exists($file = $this->fileSystem->makeFilePath($this->getLocale(), $domain, 'mo'))) {
            $translations = Translations::fromMoFile($file);
        } elseif (file_exists($file = $this->fileSystem->makeFilePath($this->getLocale(), $domain))) {
            $translations = Translations::fromPoFile($file);
        }

        $translator->loadTranslations($translations ?? []);

        $this->loadedResources[$domain][$this->getLocale()] = true;
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
     * @throws \Depiedra\LaravelGettext\Exceptions\UndefinedDomainException
     */
    public function dgettext($domain, $original)
    {
        return $this->setDomain($domain, null)->getTranslator()->dgettext($domain, $original);
    }

    /**
     * Gets a translation checking the domain and context.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     *
     * @return string
     * @throws \Depiedra\LaravelGettext\Exceptions\UndefinedDomainException
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
     * @throws \Depiedra\LaravelGettext\Exceptions\UndefinedDomainException
     */
    public function dnpgettext($domain, $context, $original, $plural, $value)
    {
        return $this->setDomain($domain, false)->getTranslator()->dnpgettext($domain, $context, $original, $plural, $value);
    }
}