<?php namespace Depiedra\LaravelGettext\Testing;

use \Illuminate\Foundation\Testing\TestCase;
use Depiedra\LaravelGettext\ServiceProvider;

/**
 * Created by PhpStorm.
 * User: shaggyz
 * Date: 17/10/16
 * Time: 14:41
 */
class BaseTestCase extends TestCase
{
    /**
     * Base app path
     *
     * @var string
     */
    protected $appPath;

    /**
     * Instantiates the laravel environment.
     *
     * @return mixed
     */
    public function createApplication()
    {
        // relative path in package folder
        if (!$this->appPath) {
            return null;
        }

        $app = require $this->appPath;
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $app->register(ServiceProvider::class);

        return $app;
    }
}