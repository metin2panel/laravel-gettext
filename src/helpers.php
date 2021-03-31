<?php

use Depiedra\LaravelGettext\TranslatorManager;

if (! function_exists('laravel_gettext')) {
    /**
     * @param string|null $translator
     *
     * @return \Depiedra\LaravelGettext\TranslatorManager|\Depiedra\LaravelGettext\Translators\TranslatorInterface
     */
    function laravel_gettext($translator = null)
    {
        if (is_null($translator)) {
            return app(TranslatorManager::class);
        }

        return app(TranslatorManager::class)->driver($translator);
    }
}

if (! function_exists('i__')) {
    /**
     * Returns the translation of a string.
     *
     * @param string $original
     *
     * @return string
     */
    function i__($original)
    {
        $text = app(TranslatorManager::class)->gettext($original);

        if (func_num_args() === 1) {
            return $text;
        }

        $args = array_slice(func_get_args(), 1);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}

if (! function_exists('noop__')) {
    /**
     * Noop, marks the string for translation but returns it unchanged.
     *
     * @param string $original
     *
     * @return string
     */
    function noop__($original)
    {
        return $original;
    }
}

if (! function_exists('n__')) {
    /**
     * Returns the singular/plural translation of a string.
     *
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    function n__($original, $plural, $value)
    {
        $text = app(TranslatorManager::class)->ngettext($original, $plural, $value);

        if (func_num_args() === 3) {
            return $text;
        }

        $args = array_slice(func_get_args(), 3);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}

if (! function_exists('p__')) {
    /**
     * Returns the translation of a string in a specific context.
     *
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    function p__($context, $original)
    {
        $text = app(TranslatorManager::class)->pgettext($context, $original);

        if (func_num_args() === 2) {
            return $text;
        }

        $args = array_slice(func_get_args(), 2);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}

if (! function_exists('d__')) {
    /**
     * Returns the translation of a string in a specific domain.
     *
     * @param string $domain
     * @param string $original
     *
     * @return string
     */
    function d__($domain, $original)
    {
        $text = app(TranslatorManager::class)->dgettext($domain, $original);

        if (func_num_args() === 2) {
            return $text;
        }

        $args = array_slice(func_get_args(), 2);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}

if (! function_exists('dp__')) {
    /**
     * Returns the translation of a string in a specific domain and context.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    function dp__($domain, $context, $original)
    {
        $text = app(TranslatorManager::class)->dpgettext($domain, $context, $original);

        if (func_num_args() === 3) {
            return $text;
        }

        $args = array_slice(func_get_args(), 3);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}

if (! function_exists('dn__')) {
    /**
     * Returns the singular/plural translation of a string in a specific domain.
     *
     * @param string $domain
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    function dn__($domain, $original, $plural, $value)
    {
        $text = app(TranslatorManager::class)->dngettext($domain, $original, $plural, $value);

        if (func_num_args() === 4) {
            return $text;
        }

        $args = array_slice(func_get_args(), 4);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}

if (! function_exists('np__')) {
    /**
     * Returns the singular/plural translation of a string in a specific context.
     *
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    function np__($context, $original, $plural, $value)
    {
        $text = app(TranslatorManager::class)->npgettext($context, $original, $plural, $value);

        if (func_num_args() === 4) {
            return $text;
        }

        $args = array_slice(func_get_args(), 4);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}

if (! function_exists('dnp__')) {
    /**
     * Returns the singular/plural translation of a string in a specific domain and context.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    function dnp__($domain, $context, $original, $plural, $value)
    {
        $text = app(TranslatorManager::class)->dnpgettext($domain, $context, $original, $plural, $value);

        if (func_num_args() === 5) {
            return $text;
        }

        $args = array_slice(func_get_args(), 5);

        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}
