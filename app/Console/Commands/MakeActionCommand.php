<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

#[Signature('make:action
    {name : The name of the action class}
    {--force : Create the class even if it already exists}')]
#[Description('Create a new action class')]
class MakeActionCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $type = 'Action';

    protected function getStub(): string
    {
        return base_path('stubs/action.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Actions';
    }

    protected function qualifyClass($name): string
    {
        $name = ltrim((string) $name, '\\/');
        $name = str_replace('/', '\\', $name);

        if (Str::startsWith($name, 'Actions\\')) {
            $name = Str::after($name, 'Actions\\');
        }

        return parent::qualifyClass($name);
    }
}
