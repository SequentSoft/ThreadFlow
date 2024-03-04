<?php

namespace SequentSoft\ThreadFlow\Laravel\Console;

use Illuminate\Console\MigrationGeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

use function Illuminate\Filesystem\join_paths;

#[AsCommand(name: 'threadflow:session-table')]
class SessionTableThreadFlowCommand extends MigrationGeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'threadflow:session-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the thread flow session database table';

    /**
     * Get the migration table name.
     *
     * @return string
     */
    protected function migrationTableName()
    {
        return 'thread_flow_sessions';
    }

    /**
     * Get the path to the migration stub file.
     *
     * @return string
     */
    protected function migrationStubFile()
    {
        return __DIR__.'/stubs/thread-flow-session-table.stub';
    }

    /**
     * Determine whether a migration for the table already exists.
     *
     * @param string $table
     * @return bool
     */
    protected function migrationExists($table)
    {
        return count($this->files->glob(
            join_paths($this->laravel->databasePath('migrations'), '*_*_*_*_create_' . $table . '_table.php'),
        )) !== 0;
    }
}
