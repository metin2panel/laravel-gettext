<?php
/**
 * Created by PhpStorm.
 * User: aaflalo
 * Date: 17-08-01
 * Time: 10:22
 */

namespace unit;

use Depiedra\LaravelGettext\Adapters\LaravelAdapter;
use Depiedra\LaravelGettext\Config\ConfigManager;
use Depiedra\LaravelGettext\FileSystem;
use Depiedra\LaravelGettext\Storages\MemoryStorage;
use Depiedra\LaravelGettext\Testing\BaseTestCase;
use Depiedra\LaravelGettext\Translators\Translator;

class TranslationTest extends BaseTestCase
{

    /**
     * Base app path
     *
     * @var string
     */
    protected $appPath = __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';
    /**
     * @var FileSystem
     */
    protected $fileSystem;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @throws \Depiedra\LaravelGettext\Exceptions\LocaleFileNotFoundException
     * @throws \Depiedra\LaravelGettext\Exceptions\RequiredConfigurationKeyException
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $testConfig = include __DIR__ . '/../config/config_fr.php';

        $config = ConfigManager::create($testConfig);
        $adapter = new LaravelAdapter();
        $this->fileSystem = new FileSystem(app('files'), $config->get());

        $this->fileSystem->generateLocales();

        $domains = $config->get()->getAllDomains();

        foreach ($config->get()->getSupportedLocales() as $locale) {
            $localePath = $this->fileSystem->getDomainPath($locale);

            if (! file_exists($localePath)) {
                $this->fileSystem->addLocale($localePath, $locale);

                continue;
            }

            foreach ($domains as $domain) {
                $this->fileSystem->updateLocale(
                    $localePath,
                    $locale,
                    $domain,
                    'po',
                    'po'
                );
            }
        }

        $translator = new Translator(
            $config->get(),
            $adapter,
            $this->fileSystem,
            new MemoryStorage($config->get())
        );

        $this->translator = $translator;
    }

    public function testFrenchTranslation()
    {
        $string = $this->translator->setLocale('fr_FR')->gettext('Controller string');
        $this->assertEquals('Chaine de caractère du controlleur', $string);
    }

    public function testFrenchTranslationReplacement()
    {
        $string = $this->translator->setLocale('fr_FR')->gettext('Hello %s, how are you ?');
        $this->assertEquals('Salut %s, comment va ?', $string);
    }
}
