<?php namespace Depiedra\LaravelGettext\Translators;

use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Depiedra\LaravelGettext\Adapters\AdapterInterface;
use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\FileLoader\Cache\ApcuFileCacheLoader;
use Depiedra\LaravelGettext\FileLoader\MoFileLoader;
use Depiedra\LaravelGettext\FileSystem;
use Depiedra\LaravelGettext\Storages\Storage;

class Symfony extends BaseTranslator
{
    /**
     * Symfony translator
     *
     * @var SymfonyTranslator
     */
    protected $translator;

    /**
     * @var array[]
     */
    protected $loadedResources = [];

    public function __construct(Config $config, AdapterInterface $adapter, FileSystem $fileSystem, Storage $storage)
    {
        parent::__construct($config, $adapter, $fileSystem, $storage);

        $this->setLocale($this->storage->getLocale());
        $this->setDomain($this->storage->getDomain());
    }

    /**
     * Creates a new translator instance
     *
     * @return SymfonyTranslator
     */
    protected function createTranslator()
    {
        $translator = new SymfonyTranslator($this->configuration->getLocale());
        $translator->setFallbackLocales([$this->configuration->getFallbackLocale()]);
        $translator->addLoader('mo', new ApcuFileCacheLoader(new MoFileLoader()));
        $translator->addLoader('po', new ApcuFileCacheLoader(new PoFileLoader()));

        return $translator;
    }

    /**
     * Returns the translator instance
     *
     * @return SymfonyTranslator
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

        $this->getTranslator()->setLocale($locale);
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
     *
     * @param String $domain
     *
     * @param bool $default
     *
     * @return $this
     */
    public function setDomain($domain, $default = true)
    {
        $domain = ! $this->isDomainSupported($domain) ? $this->configuration->getDomain() : $domain;

        parent::setDomain($domain, $default);

        $this->loadLocaleFile($domain);

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
            $translator->addResource('mo', $file, $this->getLocale(), $domain);
        } elseif (file_exists($file = $this->fileSystem->makeFilePath($this->getLocale(), $domain))) {
            $translator->addResource('po', $file, $this->getLocale(), $domain);
        }

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
        return $this->dpgettext($this->getDomain(), null, $original);
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
        return $this->dnpgettext($this->getDomain(), null, $original, $plural, $value);
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
        return $this->dnpgettext($domain, null, $original, $plural, $value);
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
        return $this->dnpgettext($this->getDomain(), $context, $original, $plural, $value);
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
        return $this->dpgettext($this->getDomain(), $context, $original);
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
        return $this->dpgettext($domain, null, $original);
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
        return $this->setDomain($domain, false)->getTranslator()->trans(
            $original, [], $domain, $this->getLocale()
        );
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
        return $this->setDomain($domain, false)->getTranslator()->transChoice(
            ($value > 1 ? $plural : $original), $value, [], $domain, $this->getLocale()
        );
    }
}