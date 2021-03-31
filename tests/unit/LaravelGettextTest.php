<?php

use \Mockery as m;

use Depiedra\LaravelGettext\Adapters\AdapterInterface;
use Depiedra\LaravelGettext\Storages\MemoryStorage;
use Depiedra\LaravelGettext\Testing\Adapter\TestAdapter;
use Depiedra\LaravelGettext\Testing\BaseTestCase;
use Depiedra\LaravelGettext\Config\ConfigManager;
use Depiedra\LaravelGettext\Adapters\LaravelAdapter;
use Depiedra\LaravelGettext\FileSystem;
use Depiedra\LaravelGettext\Translators\Translator;

class LaravelGettextTest extends BaseTestCase
{
    /**
     * Base app path
     *
     * @var string
     */
    protected $appPath = __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @throws \Depiedra\LaravelGettext\Exceptions\RequiredConfigurationKeyException
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $testConfig = include __DIR__ . '/../config/config.php';

        $config = ConfigManager::create($testConfig);
        $adapter = app($config->get()->getAdapter());
        $fileSystem = new FileSystem(app('files'), $config->get());

        $fileSystem->generateLocales();

        $domains = $config->get()->getAllDomains();

        foreach ($config->get()->getSupportedLocales() as $locale) {
            $localePath = $fileSystem->getDomainPath($locale);

            if (! file_exists($localePath)) {
                $fileSystem->addLocale($localePath, $locale);

                continue;
            }

            foreach ($domains as $domain) {
                $fileSystem->updateLocale(
                    $localePath,
                    $locale,
                    $domain
                );
            }
        }

        $translator = new Translator(
            $config->get(),
            $adapter,
            $fileSystem,
            new MemoryStorage($config->get())
        );

        $this->translator = $translator;
    }

    public function testAdapter()
    {
        $testConfig = include __DIR__ . '/../config/config.php';
        $config = ConfigManager::create($testConfig);
        $adapter = app($config->get()->getAdapter());
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertInstanceOf(TestAdapter::class, $adapter);
    }

    /**
     * Test setting locale.
     */
    public function testSetLocale()
    {
        $response = $this->translator->setLocale('en_US');

        $this->assertEquals('en_US', $response);
    }

    /**
     * Test getting locale.
     * It should receive locale from mocked config.
     */
    public function testGetLocale()
    {
        $response = $this->translator->getLocale();

        $this->assertEquals('en_US', $response);
    }

    public function testIsLocaleSupported()
    {
        $this->assertTrue($this->translator->isLocaleSupported('en_US'));
    }

    /**
     * Test dumping locale to string
     */
    public function testToString()
    {
        $response = $this->translator->__toString();

        $this->assertEquals('en_US', $response);
    }

    public function testGetEncoding()
    {
        $response = $this->translator->getEncoding();
        $this->assertNotEmpty($response);
        $this->assertEquals('UTF-8', $response);
    }

    public function testSetEncoding()
    {
        $response = $this->translator->setEncoding('UTF-8');
        $this->assertNotEmpty($response);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
