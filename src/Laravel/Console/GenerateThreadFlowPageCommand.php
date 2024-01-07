<?php

namespace SequentSoft\ThreadFlow\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class GenerateThreadFlowPageCommand extends GeneratorCommand
{
    protected $name = 'thread-flow:page';

    protected $description = 'Generate a new ThreadFlow page';

    protected $type = 'ThreadFlow page';

    protected function getStub(): string
    {
        return $this->option('empty')
                        ? $this->resolveStubPath('/stubs/thread-flow-page-empty.stub')
                        : $this->resolveStubPath('/stubs/thread-flow-page.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\ThreadFlow\Pages';
    }

    protected function getOptions(): array
    {
        return [
            ['empty', 'e', InputOption::VALUE_NONE, 'Create an empty page'],
        ];
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }
}
