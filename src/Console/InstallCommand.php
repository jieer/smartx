<?php

namespace SmartX\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{

    protected $signature = 'smartx:install';


    protected $description = 'Install the smartx package';



    protected $directory = '';


    public function handle()
    {
        $this->initDatabase();
        $this->initSmartXDirectory();
    }

    public function initDatabase()
    {
        $this->call('migrate');
        $userModel = config('smartx.models.common_user.model');

        if ($userModel::count() == 0) {
            $this->call('db:seed', ['--class' => \SmartX\Models\Seeders\SmartXTablesSeeder::class]);
        }
    }

    public function initSmartXDirectory()
    {
        $this->directory = config('smartx.directory.controller');

        if (is_dir($this->directory)) {
            $this->line("<error>{$this->directory} directory already exists !</error> ");
        } else {
            $this->makeDir('/');
            $this->line('<info>SmartX directory was created:</info> '.str_replace(base_path(), '', $this->directory));
        }
        $this->createAuthController();

        $this->createRoutesFile();

    }

    public function createAuthController()
    {
        $authController = $this->directory.'/AuthController.php';
        $contents = $this->getStub('AuthController');

        $this->laravel['files']->put(
            $authController,
            str_replace('DummyNamespace', config('smartx.route.namespace'), $contents)
        );
        $this->line('<info>AuthController file was created:</info> '.str_replace(base_path(), '', $authController));
    }


    protected function createRoutesFile()
    {
        $file = base_path('routes/'.config('smartx.auth_guard').'.php');

        $contents = $this->getStub('routes');
        $this->laravel['files']->put($file, str_replace('DummyNamespace', config('smartx.route.namespace'), $contents));
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
