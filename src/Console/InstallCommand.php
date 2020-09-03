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
        $userModel = config('smartwell.models.common_user.model');

        if ($userModel::count() == 0) {
            $this->call('db:seed', ['--class' => \Smartwell\Models\Seeders\JieerTablesSeeder::class]);
        }
    }

}
