<?php

namespace Jieer\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{

    protected $signature = 'jieer:install';


    protected $description = 'Install the jieer package';



    protected $directory = '';


    public function handle()
    {
        $this->initDatabase();
        $this->initJieerDirectory();
    }

    public function initDatabase()
    {
        $this->call('migrate');
        $userModel = config('jieer.models.common_user.model');

        if ($userModel::count() == 0) {
            $this->call('db:seed', ['--class' => \Jieer\Models\Seeders\JieerTablesSeeder::class]);
        }
    }

    public function initJieerDirectory()
    {
        $this->directory = config('jieer.directory.controller');

        if (is_dir($this->directory)) {
            $this->line("<error>{$this->directory} directory already exists !</error> ");

            return;
        }

        $this->makeDir('/');
        $this->line('<info>Admin directory was created:</info> '.str_replace(base_path(), '', $this->directory));

        $this->createRoutesFile();

    }


    protected function createRoutesFile()
    {
        $file = __DIR__ . '/../routes/'.config('jieer.auth_guard').'.php';

        $contents = $this->getStub('routes');
        $this->laravel['files']->put($file, str_replace('DummyNamespace', config('jieer.route.namespace'), $contents));
        $this->line('<info>Routes file was created:</info> '.str_replace(base_path(), '', $file));
    }

    protected function getStub($name)
    {
        return $this->laravel['files']->get(__DIR__."/stubs/$name.stub");
    }

    protected function makeDir($path = '')
    {
        $this->laravel['files']->makeDirectory("{$this->directory}/$path", 0755, true, true);
    }

}
