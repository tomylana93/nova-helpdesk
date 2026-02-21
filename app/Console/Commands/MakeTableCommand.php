<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeTableCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:table';

    /**
     * @var string
     */
    protected $signature = 'make:table
                            {name : The table class name}
                            {--model= : Model class (FQCN or short name)}
                            {--resource= : JsonResource class (FQCN or short name)}
                            {--force : Create the class even if the table already exists}';

    /**
     * @var string
     */
    protected $description = 'Create a new table class that extends AbstractTable';

    /**
     * @var string
     */
    protected $type = 'Table';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/table.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Tables';
    }

    protected function getNameInput(): string
    {
        $name = parent::getNameInput();
        $name = str_replace('/', '\\', $name);

        $segments = explode('\\', $name);
        $className = array_pop($segments);

        if ($className !== null && ! Str::endsWith($className, 'Table')) {
            $className .= 'Table';
        }

        if ($className !== null) {
            $segments[] = $className;
        }

        return implode('\\', $segments);
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $tableClass = class_basename($name);
        $entity = Str::replaceEnd('Table', '', $tableClass);

        if ($entity === '') {
            $entity = $tableClass;
        }

        $modelNamespace = $this->resolveModelClass($entity);
        $resourceNamespace = $this->resolveResourceClass($entity);

        return str_replace(
            [
                '{{ namespacedModel }}',
                '{{ namespacedResource }}',
                '{{ model }}',
                '{{ resource }}',
            ],
            [
                $modelNamespace,
                $resourceNamespace,
                class_basename($modelNamespace),
                class_basename($resourceNamespace),
            ],
            $stub
        );
    }

    protected function resolveModelClass(string $entity): string
    {
        $model = $this->option('model');

        if (is_string($model) && filled($model)) {
            return $this->qualifyOptionClass($model, 'Models');
        }

        return $this->qualifyOptionClass($entity, 'Models');
    }

    protected function resolveResourceClass(string $entity): string
    {
        $resource = $this->option('resource');

        if (is_string($resource) && filled($resource)) {
            return $this->qualifyOptionClass($resource, 'Http\\Resources');
        }

        return $this->qualifyOptionClass($entity.'Resource', 'Http\\Resources');
    }

    protected function qualifyOptionClass(string $class, string $defaultNamespace): string
    {
        $class = ltrim(str_replace('/', '\\', trim($class)), '\\');

        if (Str::contains($class, '\\')) {
            return $class;
        }

        return trim($this->rootNamespace(), '\\').'\\'.$defaultNamespace.'\\'.$class;
    }
}
