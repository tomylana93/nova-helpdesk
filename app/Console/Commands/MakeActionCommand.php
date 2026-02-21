<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeActionCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:action';

    /**
     * @var string
     */
    protected $signature = 'make:action
                            {name : The action class name}
                            {--force : Create the class even if the action already exists}';

    /**
     * @var string
     */
    protected $description = 'Create a new action class';

    /**
     * @var string
     */
    protected $type = 'Action';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/action.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Actions';
    }

    protected function getNameInput(): string
    {
        $name = parent::getNameInput();
        $name = str_replace('/', '\\', $name);

        $segments = explode('\\', $name);
        $className = array_pop($segments);

        if ($className !== null && ! Str::endsWith($className, 'Action')) {
            $className .= 'Action';
        }

        if ($className !== null) {
            $segments[] = $className;
        }

        return implode('\\', $segments);
    }
}
