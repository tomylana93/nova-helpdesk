<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

#[Signature('make:table
    {name : The name of the table class}
    {--force : Create the class even if it already exists}')]
#[Description('Create a new table class')]
class MakeTableCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $type = 'Table';

    protected function getStub(): string
    {
        return base_path('stubs/table.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Tables';
    }

    protected function qualifyClass($name): string
    {
        $name = ltrim((string) $name, '\\/');
        $name = str_replace('/', '\\', $name);

        if (Str::startsWith($name, 'Tables\\')) {
            $name = Str::after($name, 'Tables\\');
        }

        return parent::qualifyClass($name);
    }
}
