<?php namespace Depiedra\LaravelGettext;

use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\Exceptions\DirectoryNotFoundException;
use Depiedra\LaravelGettext\Exceptions\LocaleFileNotFoundException;
use Gettext\Extractors\PhpCode;
use Gettext\Merge;
use Gettext\Translations;

class FileSystem
{
    /**
     * Package configuration model
     *
     * @var Config
     */
    protected $configuration;

    /**
     * File system base path
     * All paths will be relative to this
     *
     * @var string
     */
    protected $basePath;

    /**
     * The folder name in which the language files are stored
     *
     * @var string
     */
    protected $folderName;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    private static $regex;

    private static $scanRegex;

    /**
     * @var array
     */
    private static $suffixes = [
        '.blade.php' => 'Blade',
        '.csv' => 'Csv',
        '.jed.json' => 'Jed',
        '.js' => 'JsCode',
        '.json' => 'Json',
        '.mo' => 'Mo',
        '.php' => ['PhpCode', 'PhpArray'],
        '.po' => 'Po',
        '.pot' => 'Po',
        '.twig' => 'Twig',
        '.xliff' => 'Xliff',
        '.yaml' => 'Yaml',
    ];

    /**
     * @var array
     */
    private static $scanSuffixes = [
        '.blade.php' => 'Blade',
        '.php' => ['PhpCode', 'PhpArray'],
        '.js' => 'JsCode',
    ];

    /**
     * @var array
     */
    private static $codeFunctions = [
        'gettext' => 'gettext',
        'i__' => 'gettext',
        'ngettext' => 'ngettext',
        'n__' => 'ngettext',
        'pgettext' => 'pgettext',
        'p__' => 'pgettext',
        'dgettext' => 'dgettext',
        'd__' => 'dgettext',
        'dngettext' => 'dngettext',
        'dn__' => 'dngettext',
        'dpgettext' => 'dpgettext',
        'dp__' => 'dpgettext',
        'npgettext' => 'npgettext',
        'np__' => 'npgettext',
        'dnpgettext' => 'dnpgettext',
        'dnp__' => 'dnpgettext',
        'noop' => 'noop',
        'noop__' => 'noop',
    ];

    /**
     * @param \Illuminate\Filesystem\Filesystem $files The filesystem
     * @param Config $config
     */
    public function __construct($files, Config $config)
    {
        $this->files = $files;
        $this->configuration = $config;

        $this->basePath = $this->configuration->getBasePath();
        $this->folderName = 'i18n';
    }

    /**
     * Constructs and returns the full path to the translation files
     *
     * @param null $append
     *
     * @return string
     */
    public function getDomainPath($append = null)
    {
        $path = [
            $this->basePath,
            $this->configuration->getTranslationsPath(),
            $this->folderName,
        ];

        if (! is_null($append)) {
            array_push($path, $append);
        }

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Creates a configured .po file on $path
     * If PHP are not able to create the file the content will be returned instead
     *
     * @param string $path
     * @param string $locale
     * @param string $domain
     * @param bool|true $write
     *
     * @return int|string
     * @throws \Exception
     */
    public function scanFile($path, $locale, $domain, $write = true)
    {
        PhpCode::$options['functions'] = static::$codeFunctions;

        $translations = new Translations();

        $project = $this->configuration->getProject();
        $translator = $this->configuration->getTranslator();
        $relativePath = $this->configuration->getRelativePath();
        $encoding = $this->configuration->getEncoding();
        $keywords = implode(';', $this->configuration->getKeywordsList());

        $translations
            ->setLanguage($locale)
            ->setHeader('Project-Id-Version', $project)
            ->setHeader('Last-Translator', $translator)
            ->setHeader('Language-Team', $translator)
            ->setHeader('X-Poedit-KeywordsList', $keywords)
            ->setHeader('X-Poedit-Basepath', $relativePath)
            ->setHeader('X-Poedit-SourceCharset', $encoding);

        if ($domain != $this->configuration->getDomain() && $domain != 'js_' . $this->configuration->getDomain()) {
            $translations->setDomain($domain);
        }

        $sourcePaths = $this->configuration->getSourcesFromDomain($domain);

        if (! count($sourcePaths)) {
            return false;
        }

        foreach ($sourcePaths as $sourcePath) {
            $realPath = realpath($this->basePath . DIRECTORY_SEPARATOR . $sourcePath);

            if (! $realPath) {
                throw new \Exception(sprintf('Folder %s not exists. Gettext scan aborted.', $realPath));
            }

            $files = $this->files->allFiles($realPath);

            foreach ($files as $file) {
                if ($file === null || ! $file->isFile()) {
                    continue;
                }

                $target = $file->getPathname();

                if (($fn = $this->getScanFunctionName('addFrom', $target, 'File'))) {
                    $translations->$fn($target);
                }
            }
        }

        $this->formatReferences($translations);

        $fn = $this->getFunctionName('to', $path, 'String', 1);

        if ($write) {
            if ($this->files->isFile($path)) {
                return true;
            }

            return $this->files->put($path, $translations->$fn());
        }

        return $translations->$fn();
    }

    /**
     * @param \Gettext\Translations $translations
     *
     * @return void
     */
    protected function formatReferences(Translations $translations)
    {
        foreach ($translations as $translation) {
            /** @var \Gettext\Translation $translation */
            $references = $translation->getReferences();
            $translation->deleteReferences();

            foreach ($references as $reference) {
                list($reference, $line) = $reference;

                $translation->addReference($this->getRelativePathForReference($this->basePath, $reference), $line);
            }
        }
    }

    /**
     * Validate if the directory can be created
     *
     * @param $path
     *
     * @throws \Exception
     */
    protected function createDirectory($path)
    {
        if (! $this->files->makeDirectory($path, 0777)) {
            throw new \Exception(
                sprintf('Can\'t create the directory: %s', $path)
            );
        }
    }

    /**
     * Validate if the directory can be created
     *
     * @param string $path
     * @param bool $preserve
     *
     * @throws \Exception
     */
    protected function deleteDirectory($path, $preserve = false)
    {
        if (! $this->files->deleteDirectory($path, $preserve)) {
            throw new \Exception(
                sprintf('Can\'t delete the directory: %s', $path)
            );
        }
    }

    /**
     * Adds a new locale directory + .po file
     *
     * @param  string $localePath
     * @param  string $locale
     *
     * @throws \Exception
     */
    public function addLocale($localePath, $locale)
    {
        $data = array(
            $localePath,
            "LC_MESSAGES"
        );

        if (! file_exists($localePath)) {
            $this->createDirectory($localePath);
        }

        if ($this->configuration->getCustomLocale()) {
            $data[1] = 'C';

            $gettextPath = implode(DIRECTORY_SEPARATOR, $data);
            if (! file_exists($gettextPath)) {
                $this->createDirectory($gettextPath);
            }

            $data[2] = 'LC_MESSAGES';
        }

        $gettextPath = implode(DIRECTORY_SEPARATOR, $data);
        if (! file_exists($gettextPath)) {
            $this->createDirectory($gettextPath);
        }


        // File generation for each domain
        foreach ($this->configuration->getAllDomains() as $domain) {
            $data[3] = $domain . ".po";

            $localePOPath = implode(DIRECTORY_SEPARATOR, $data);

            if (! $this->scanFile($localePOPath, $locale, $domain)) {
                throw new \Exception(
                    sprintf('Can\'t create the file: %s', $localePOPath)
                );
            }

        }

    }

    /**
     * Update the .po file headers by domain
     * (mainly source-file paths)
     *
     * @param $localePath
     * @param $locale
     * @param $domain
     * @param array $options
     *
     * @return bool
     * @throws \Depiedra\LaravelGettext\Exceptions\LocaleFileNotFoundException
     * @throws \Exception
     */
    public function updateLocale($localePath, $locale, $domain, array $options = [])
    {
        $from = $options['from'] ?? 'po';
        $to = $options['to'] ?? 'po';
        $merge = $options['merge'] ?? 'ours';

        $data = [
            $localePath,
            "LC_MESSAGES",
            $domain . '.' . $from,
        ];

        $data2 = [
            $localePath,
            "LC_MESSAGES",
            $domain . '.' . $to,
        ];

        if ($this->configuration->getCustomLocale()) {
            $customLocale = array('C');
            array_splice($data, 1, 0, $customLocale);
            array_splice($data2, 1, 0, $customLocale);
        }

        $fromPOPath = implode(DIRECTORY_SEPARATOR, $data);
        $toPOPath = implode(DIRECTORY_SEPARATOR, $data2);

        if (! file_exists($fromPOPath)) {
            throw new LocaleFileNotFoundException(
                sprintf('Can\'t read %s verify your locale structure', $fromPOPath)
            );
        }

        $translations = new Translations();

        if (($fn = $this->getFunctionName('addFrom', $fromPOPath, 'File'))) {
            $translations->$fn($fromPOPath);
        }

        $newTranslations = new Translations();

        if (($fn = $this->getFunctionName('addFrom', $fromPOPath, 'String'))) {
            $newTranslations->$fn($this->scanFile($fromPOPath, $locale, $domain, false));
        }

        switch ($merge) {
            case 'ours':
                $merge = Merge::REFERENCES_OURS;
                break;
            case 'theirs':
                $merge = Merge::REFERENCES_THEIRS;
                break;
            default:
                $merge = Merge::DEFAULTS;
                break;
        }

        $translationContents = $newTranslations->mergeWith($translations, $merge);

        $fn = $this->getFunctionName('to', $toPOPath, 'String', 1);

        if (! $this->files->put($toPOPath, $translationContents->$fn())) {
            throw new LocaleFileNotFoundException(
                sprintf('Can\'t write on %s', $toPOPath)
            );
        }

        return true;
    }

    /**
     * Return the relative path from a file or directory to another
     *
     * @param string $from
     * @param string $to
     *
     * @return string
     * @author Laurent Goussard
     */
    public function getRelativePath($from, $to)
    {
        // Compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);

        $from = explode('/', $from);
        $to = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            if ($dir !== $to[$depth]) {
                // Number of remaining directories
                $remaining = count($from) - $depth;

                if ($remaining > 1) {
                    // Add traversals up to first matching directory
                    $padLength = (count($relPath) + $remaining - 1) * -1;

                    $relPath = array_pad(
                        $relPath,
                        $padLength,
                        '..'
                    );

                    break;
                }

                $relPath[0] = './' . $relPath[0];
            }

            array_shift($relPath);
        }

        return implode('/', $relPath);

    }

    /**
     * Return the relative path from a file or directory to another
     *
     * @param string $from
     * @param string $to
     *
     * @return string
     * @author Laurent Goussard
     */
    public function getRelativePathForReference($from, $to)
    {
        $from = explode('\\', str_replace('/', '\\', $from));
        $to = explode('\\', str_replace('/', '\\', $to));

        $relpath = [];
        $dotted = 0;
        for ($i = 0; $i < count($from); $i++) {
            if ($i >= count($to)) {
                $dotted++;
            } elseif ($to[$i] != $from[$i]) {
                $relpath[] = $to[$i];
                $dotted++;
            }
        }

        return str_repeat('../', $dotted) . implode('/', array_merge($relpath, array_slice($to, count($from))));
    }

    /**
     * Checks the required directory
     * Optionally checks each local directory, if $checkLocales is true
     *
     * @param bool|false $checkLocales
     *
     * @return bool
     * @throws DirectoryNotFoundException
     */
    public function checkDirectoryStructure($checkLocales = false)
    {
        // Application base path
        if (! file_exists($this->basePath)) {
            throw new Exceptions\DirectoryNotFoundException(
                sprintf(
                    'Missing root path directory:  %s, check the \'base-path\' key in your configuration.',
                    $this->basePath
                )
            );
        }

        // Domain path
        $domainPath = $this->getDomainPath();

        // Translation files domain path
        if (! file_exists($domainPath)) {
            throw new Exceptions\DirectoryNotFoundException(
                sprintf(
                    'Missing base required directory: %s, remember to run \'artisan gettext:create\' the first time',
                    $domainPath
                )
            );
        }

        if (! $checkLocales) {
            return true;
        }

        foreach ($this->configuration->getSupportedLocales() as $locale) {
            // Default locale is not needed
            if ($locale == $this->configuration->getLocale()) {
                continue;
            }

            $localePath = $this->getDomainPath($locale);

            if (! file_exists($localePath)) {
                throw new Exceptions\DirectoryNotFoundException(
                    sprintf(
                        'Missing locale required directory: %s, maybe you forgot to run \'artisan gettext:update\'',
                        $locale
                    )
                );
            }
        }

        return true;
    }

    /**
     * Creates the localization directories and files by domain
     *
     * @return array
     * @throws \Exception
     */
    public function generateLocales()
    {
        if (! file_exists($this->getDomainPath())) {
            $this->createDirectory($this->getDomainPath());
        }

        $localePaths = [];

        foreach ($this->configuration->getSupportedLocales() as $locale) {
            $localePath = $this->getDomainPath($locale);

            $this->addLocale($localePath, $locale);
            array_push($localePaths, $localePath);
        }

        return $localePaths;
    }

    /**
     * Get the filesystem base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set the filesystem base path
     *
     * @param $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Removes the directory contents recursively
     *
     * @param string $path
     *
     * @return void
     */
    public function clearDirectory($path)
    {
        $this->files->deleteDirectory($path, true);
    }

    /**
     * Get the folder name
     *
     * @return string
     */
    public function getFolderName()
    {
        return $this->folderName;
    }

    /**
     * Set the folder name
     *
     * @param $folderName
     */
    public function setFolderName($folderName)
    {
        $this->folderName = $folderName;
    }

    /**
     * Returns the full path for a .po/.mo file from its domain and locale
     *
     * @param        $locale
     * @param        $domain
     *
     * @param string $type
     *
     * @return string
     */
    public function makeFilePath($locale, $domain, $type = 'po')
    {
        $filePath = implode(DIRECTORY_SEPARATOR, [
            $locale,
            'LC_MESSAGES',
            $domain . "." . $type
        ]);

        return $this->getDomainPath($filePath);
    }

    /**
     * Get the format based in the extension.
     *
     * @param $prefix
     * @param string $file
     * @param $suffix
     * @param int $key
     *
     * @return string|null
     */
    private function getFunctionName($prefix, $file, $suffix, $key = 0)
    {
        if (preg_match(self::getRegex(), strtolower($file), $matches)) {
            $format = self::$suffixes[$matches[1]];
            if (is_array($format)) {
                $format = $format[$key];
            }

            return sprintf('%s%s%s', $prefix, $format, $suffix);
        }

        return false;
    }

    /**
     * Get the format based in the extension.
     *
     * @param $prefix
     * @param string $file
     * @param $suffix
     * @param int $key
     *
     * @return string|null
     */
    private function getScanFunctionName($prefix, $file, $suffix, $key = 0)
    {
        if (preg_match(self::getScanRegex(), strtolower($file), $matches)) {
            $format = self::$scanSuffixes[$matches[1]];
            if (is_array($format)) {
                $format = $format[$key];
            }

            return sprintf('%s%s%s', $prefix, $format, $suffix);
        }

        return false;
    }

    /**
     * Returns the regular expression to detect the file format.
     *
     * @param string
     *
     * @return string
     */
    private static function getRegex()
    {
        if (self::$regex === null) {
            self::$regex = '/(' . str_replace('.', '\\.', implode('|', array_keys(self::$suffixes))) . ')$/';
        }

        return self::$regex;
    }

    /**
     * Returns the regular expression to detect the file format.
     *
     * @param string
     *
     * @return string
     */
    private static function getScanRegex()
    {
        if (self::$scanRegex === null) {
            self::$scanRegex = '/(' . str_replace('.', '\\.', implode('|', array_keys(self::$scanSuffixes))) . ')$/';
        }

        return self::$scanRegex;
    }
}
