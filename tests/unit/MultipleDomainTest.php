<?php

use \Mockery as m;

use Depiedra\LaravelGettext\Testing\BaseTestCase;
use Depiedra\LaravelGettext\Config\ConfigManager;
use Depiedra\LaravelGettext\Adapters\LaravelAdapter;
use Depiedra\LaravelGettext\FileSystem;
use Depiedra\LaravelGettext\Translators\Translator;

/**
 * Created by PhpStorm.
 * User: shaggyz
 * Date: 17/10/16
 * Time: 15:08
 */
class MultipleDomainTest extends BaseTestCase
{
    /**
     * Base app path
     *
     * @var string
     */
    protected $appPath = __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';

    /**
     * FileSystem helper
     *
     * @var FileSystem
     */
    protected $fileSystem;

    /**
     * Configuration manager
     *
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * Testing base path
     *
     * @var String
     */
    protected $basePath;

    public function __construct()
    {
        parent::__construct();
    }

    public function setUp(): void
    {
        parent::setUp();

        // $testConfig array
        $testConfig = include __DIR__ . '/../config/config.php';
        $this->configManager = ConfigManager::create($testConfig);

        $this->basePath = realpath(__DIR__ . '/..');

        $config = $this->configManager->get();
        $config->setBasePath($this->basePath);

        $this->fileSystem = new FileSystem(app('files'), $config);
    }

    /**
     * Test domain configuration
     */
    public function testDomainConfiguration()
    {
        $expected = [
            'messages',
            'frontend',
            'backend',
        ];

        $result = $this->configManager->get()->getAllDomains();
        $this->assertTrue($result === $expected);
    }

    public function testFrontendDomainPaths()
    {
        $expectedPaths = [
            'controllers',
            'views/frontend'
        ];

        $actualPaths = $this->configManager->get()->getSourcesFromDomain('frontend');
        $this->assertEquals($expectedPaths, $actualPaths);
    }

    public function testBackendDomainPaths()
    {
        $expectedPaths = [
            'views/backend'
        ];

        $actualPaths = $this->configManager->get()->getSourcesFromDomain('backend');
        $this->assertEquals($expectedPaths, $actualPaths);
    }

    public function testDefaultDomainPaths()
    {
        $expectedPaths = [
            'views/messages',
            'views/misc'
        ];

        $actualPaths = $this->configManager->get()->getSourcesFromDomain('messages');
        $this->assertEquals($expectedPaths, $actualPaths);
    }

    public function testNoMissingDomainPaths()
    {
        // config/config.php doesn't contain a domain named `missing`, and should return no records
        $this->assertCount(0, $this->configManager->get()->getSourcesFromDomain('missing'));
    }

    /**
     * Test the update
     *
     * @throws \Exception
     */
    public function testFileSystem()
    {
        // Domain path test
        $domainPath = $this->fileSystem->getDomainPath();

        // Locale path test
        $locale = 'es_AR';
        $localePath = $this->fileSystem->getDomainPath($locale);

        // Create locale test
        $localesGenerated = $this->fileSystem->generateLocales();
        $this->assertTrue($this->fileSystem->checkDirectoryStructure(true));

        $this->assertCount(3, $localesGenerated);
        $this->assertTrue(is_dir($domainPath));
        $this->assertTrue(strpos($domainPath, 'i18n') !== false);

        foreach ($localesGenerated as $localeGenerated) {
            $this->assertTrue(file_exists($localeGenerated));
        }

        $this->assertTrue(is_dir($localePath));

        // Update locale test
        $this->assertTrue($this->fileSystem->updateLocale($localePath, $locale, "backend"));
    }

    public function testGetRelativePath()
    {
        // dir/
        $from = __DIR__;
        $to = dirname(dirname(__DIR__));

        $result = $this->fileSystem->getRelativePath($to, $from);

        // Relative path from base path: unit/
        $this->assertSame("unit/", $result);
    }

    /**
     * Mocker tear-down
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Clear all files generated for testing purposes
     */
    protected function clearFiles()
    {
        $dir = __DIR__ . '/../lang/i18n';

        $this->fileSystem->clearDirectory($dir);
    }
}
