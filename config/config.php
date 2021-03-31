<?php


return [

    /**
     * Translation handlers, options are:
     *
     * - symfony: (recommended) uses the symfony translations component. Incompatible with php-gettext
     * you must uninstall the php-gettext module before use this handler.
     *
     * - gettext: requires the php-gettext module installed. This handler has well-known cache issues
     *
     * - default: translator
     */
    'handler' => 'default',

    /**
     * Session identifier: Key under which the current locale will be stored.
     */
    'session-identifier' => 'gettext',

    /**
     * Default locale: this will be the default for your application.
     * Is to be supposed that all strings are written in this language.
     */
    'locale' => 'en_US',

    /**
     * Supported locales: An array containing all allowed languages
     */
    'supported-locales' => [
        'en_US',
    ],

    /**
     * Supported locales: An array containing all allowed languages
     */
    'locale-labels' => [
        'en_US' => 'English',
    ],

    /**
     * Supported locales: An array containing all allowed languages
     */
    'locale-english-labels' => [
        'en_US' => 'English',
    ],

    /**
     * Default charset encoding.
     */
    'encoding' => 'UTF-8',

    /**
     * -----------------------------------------------------------------------
     * All standard configuration ends here. The following values
     * are only for special cases.
     * -----------------------------------------------------------------------
     **/

    /**
     * Locale categories to set
     */
    'categories' => [
        'LC_MESSAGES',
    ],

    'base-path' => base_path('app'),

    /**
     * Base translation directory path (don't use trailing slash)
     */
    'translations-path' => '../resources/lang',

    /**
     * Relative path to the app folder: is used on .po header files
     */
    'relative-path' => '../../../../../app',

    /**
     * Fallback locale: When default locale is not available
     */
    'fallback-locale' => 'en_US',

    /**
     * Default domain used for translations: It is the file name for .po and .mo files
     */
    'domain' => 'messages',

    /**
     * Project name: is used on .po header files
     */
    'project' => 'MultilanguageLaravelApplication',

    /**
     * Translator contact data (used on .po headers too)
     */
    'translator' => 'James Translator <james@translations.colm>',

    /**
     * Paths where Poedit will search recursively for strings to translate.
     * All paths are relative to app/ (don't use trailing slash).
     *
     * Remember to call artisan gettext:update after change this.
     */
    'source-paths' => [
        'Http',
        '../resources/views',
        'Console',
    ],

    /**
     * Multi-domain directory paths. If you want the translations in
     * different files, just wrap your paths into a domain name.
     * for example:
     */
    /*
    'source-paths' => [

        // 'frontend' domain
        'frontend' => [
			'controllers',
			'views/frontend',
		],

        // 'backend' domain
		'backend' => [
			'views/backend',
		],

        // 'messages' domain (matches default domain)
		'storage/views',
	],
    */

    /**
     * Sync laravel: A flag that determines if the laravel built-in locale must
     * be changed when you call LaravelGettext::setLocale.
     */
    'sync-laravel' => true,

    /**
     * The adapter used to sync the laravel built-in locale
     */
    'adapter' => \Depiedra\LaravelGettext\Adapters\LaravelAdapter::class,

    /**
     * Where to store the current locale/domain
     *
     * By default, in the session.
     * Can be changed for only memory or your own storage mechanism
     *
     * @see \Depiedra\LaravelGettext\Storages\Storage
     */
    'storage' => \Depiedra\LaravelGettext\Storages\SessionStorage::class,

    /**
     * Use custom locale that is not supported by the system
     */
    'custom-locale' => false,

    /**
     * The keywords list used by poedit to search the strings to be translated
     *
     * The "_", "__" and "gettext" are singular translation functions
     * The "_n" and "ngettext" are plural translation functions
     * The "dgettext" function allows a translation domain to be explicitly specified
     *
     * "__" and "_n" and "_i" and "_s" are helpers functions @see \Depiedra\LaravelGettext\helpers.php
     */
    'keywords-list' => [
        'gettext', 'i__', 'ngettext:1,2', 'n__:1,2', 'pgettext:1c,2', 'p__:1c,2', 'dgettext:2', 'd__:2',
        'dngettext:2,3', 'dn__:2,3', 'dpgettext:2c,3', 'dp__:2c,3', 'npgettext:1c,2,3', 'np__:1c,2,3',
        'dnpgettext:2c,3,4', 'dnp__:2c,3,4', 'noop', 'noop__',
    ],
];
