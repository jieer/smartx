<?php

namespace Smartwell\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{

    protected $signature = 'jieer:install';


    protected $description = 'Install the jieer package';


    protected $directory = '';


    public function handle()
    {
        $this->initDatabase();
    }

    public function initDatabase()
    {
        $this->call('migrate');

    }

}
