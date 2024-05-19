<?php

namespace SequentSoft\ThreadFlow\Laravel\Console;

use Illuminate\Console\GeneratorCommand;

class GenerateThreadFlowFormCommand extends GeneratorCommand
{
    protected $name = 'threadflow:form';

    protected $description = 'Generate a new ThreadFlow form';

    protected $type = 'ThreadFlow form';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/thread-flow-form.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\ThreadFlow\Pages';
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
