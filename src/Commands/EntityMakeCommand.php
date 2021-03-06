<?php

namespace emirsator\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class EntityMakeCommand extends Command
{
    /**
     * The console signature.
     *
     * @var string
     */
    protected $signature = 'make:entity {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a entity with all required files';
    
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->composer = app()['composer'];
    }

    /**
     * Alias for the fire method.
     *
     * In Laravel 5.5 the fire() method has been renamed to handle().
     * This alias provides support for both Laravel 5.4 and 5.5.
     */
    public function handle()
    {
        $this->fire();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $name = $this->argument('name');

        $this->makeEntity($name);
    }

    /**
     * Generate an entity.
     */
    protected function makeEntity($name)
    {
        $this->info($name);

        // if ($this->files->exists($path = $this->getPath($name))) {
        //     return $this->error($this->type . ' already exists!');
        // }

        // $this->makeDirectory($path);
        // $this->files->put($path, $this->compileMigrationStub());

        $entityName = str_replace('_', '', $name);
        $entityPathName = strtolower(str_replace('_', '-', $name));
        $entitySmallName = strtolower(substr($entityName, 0, 1)) . substr($entityName, 1);
        
        $this->createDirectory('app\Http\Requests');
        $this->createDirectory('app\Repositories');
        $this->createDirectory('app\Repositories\Interfaces');
        $this->createDirectory('app\Services');
        $this->createDirectory('app\Services\Interfaces');
        $this->createDirectory('routes\web-routes');
        $this->createDirectory('resources\lang\en');

        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'app\Http\Controllers\\'.$entityName.'Controller.php', "Http\Controllers\Controller");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'app\Http\Requests\\'.$entityName.'StoreRequest.php', "Http\Requests\StoreRequest");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'app\Repositories\Interfaces\\'.$entityName.'RepositoryInterface.php', "Repositories\Interfaces\RepositoryInterface");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'app\Repositories\\'.$entityName.'Repository.php', "Repositories\Repository");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'app\Services\Interfaces\\'.$entityName.'ServiceInterface.php', "Services\Interfaces\ServiceInterface");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'app\Services\\'.$entityName.'Service.php', "Services\Service");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'routes\web-routes\\'.$entityPathName.'.php', "Routes\Route");

        // Create views folder
        $this->createDirectory('resources\views\\'.$entityPathName);

        // Create views
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'resources\views\\'.$entityPathName.'\\details.blade.php', "Views\Details");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'resources\views\\'.$entityPathName.'\\index.blade.php', "Views\Index");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'resources\views\\'.$entityPathName.'\\edit.blade.php', "Views\Edit");
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'resources\views\\'.$entityPathName.'\\create.blade.php', "Views\Create");
        
        // Create en Lang
        $this->generateFile($entityName, $entityPathName, $entitySmallName, 'resources\lang\en\\'.$entityPathName.'.php', "Langs\Lang");

        $this->composer->dumpAutoloads();
    }

    protected function compileStub($stubName, $entityName, $entityPathName = "", $entitySmallName = "")
    {
        $stub = $this->files->get(__DIR__ . '\..\stubs\\' . $stubName .'.stub');
        $stub = str_replace('{{entity}}', $entityName, $stub);
        $stub = str_replace('{{entity-path}}', $entityPathName, $stub);
        $stub = str_replace('{{entity-small}}', $entitySmallName, $stub);

        return $stub;
    }

    protected function generateFile($entityName, $entityPathName, $entitySmallName, $outputFile, $stubName)
    {
        $stub = $this->compileStub($stubName, $entityName, $entityPathName, $entitySmallName);

        $this->storeFile($outputFile, $stub);
    }

    protected function storeFile($path, $content)
    {
        $this->files->put($path, $content);
    }

    protected function createDirectory($path)
    {
        if (!$this->files->exists($path))
        {
            $this->files->makeDirectory($path);
        }
    }
}