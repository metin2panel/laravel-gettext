<?php

namespace Depiedra\LaravelGettext;

/**
 * @method static \Depiedra\LaravelGettext\Translators\TranslatorInterface setLocale(string $locale)
 * @method static string getLocale()
 * @method static \Depiedra\LaravelGettext\Translators\TranslatorInterface setDomain(string $domain)
 * @method static string getDomain()
 * @method static bool isLocaleSupported(string $locale)
 * @method static array supportedLocales()
 * @method static bool isDomainSupported(string $domain)
 * @method static \Depiedra\LaravelGettext\Translators\TranslatorInterface driver()
 * @method static array localeLabels()
 * @method static string|null getLocaleLabel()
 * @method static array localeEnglishLabels()
 * @method static string|null getLocaleEnglishLabel()
 *
 * @see \Gettext\BaseTranslator
 * @see \Depiedra\LaravelGettext\Translators\BaseTranslator
 * @see \Depiedra\LaravelGettext\Translators\TranslatorInterface
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-gettext';
    }
}
