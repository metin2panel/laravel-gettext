<?php

namespace Depiedra\LaravelGettext\Commands;

use Depiedra\LaravelGettext\Config\Models\Config;
use Depiedra\LaravelGettext\FileSystem;
use Illuminate\Console\Command;

class BaseCommand extends Command
{
    /**
     * Filesystem helper
     *
     * @var \Depiedra\LaravelGettext\FileSystem
     */
    protected $fileSystem;

    /**
     * Package configuration data
     *
     * @var \Depiedra\LaravelGettext\Config\Models\Config
     */
    protected $configuration;

    /**
     * Prepares the package environment for gettext commands
     *
     * @return void
     */
    protected function prepare()
    {
        $this->configuration = app(Config::class);
        $this->fileSystem = new FileSystem(app('files'), $this->configuration);
    }
}
