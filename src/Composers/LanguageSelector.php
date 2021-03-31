<?php

namespace Depiedra\LaravelGettext\Composers;

use Depiedra\LaravelGettext\TranslatorManager;

class LanguageSelector
{
    /**
     * Labels
     *
     * @var array
     */
    protected $labels = [];

    /**
     * @var TranslatorManager|\Depiedra\LaravelGettext\Translators\TranslatorInterface
     */
    protected $gettext;

    /**
     * @param TranslatorManager $gettext
     * @param array $labels
     */
    public function __construct(TranslatorManager $gettext, array $labels = [])
    {
        $this->labels = $labels;
        $this->gettext = $gettext;
    }

    /**
     * @param TranslatorManager $gettext
     * @param array $labels
     *
     * @return LanguageSelector
     */
    public static function create(TranslatorManager $gettext, $labels = [])
    {
        return new LanguageSelector($gettext, $labels);
    }

    /**
     * Renders the language selector
     *
     * @return string
     */
    public function render()
    {
        /** @var string $currentLocale */
        $currentLocale = $this->gettext->getLocale();

        $html = '<ul class="language-selector">';

        foreach ($this->gettext->getSupportedLocales() as $locale) {
            $localeLabel = $locale;

            // Check if label exists
            if (array_key_exists($locale, $this->labels)) {
                $localeLabel = $this->labels[$locale];
            }

            $link = '<a href="/lang/' . $locale . '" class="' . $locale . '">' . $localeLabel . '</a>';

            if ($locale == $currentLocale) {
                $link = '<strong class="active ' . $locale . '">' . $localeLabel . '</strong>';
            }

            $html .= '<li>' . $link . '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
