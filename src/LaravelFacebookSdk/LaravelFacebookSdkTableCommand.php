<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LaravelFacebookSdkTableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravel-facebook-sdk:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the LaravelFacebookSdk database columns';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $fullPath = $this->createBaseMigration();

        file_put_contents($fullPath, $this->getMigrationStub());

        $this->info('Migration created successfully!');

        $this->call('dump-autoload');
    }

    /**
     * Create a base migration file.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'add_laravel_facebook_sdk_columns';

        $path = $this->laravel['path'].'/database/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

    /**
     * Get the contents of the migration stub.
     *
     * @return string
     */
    protected function getMigrationStub()
    {
        $stub = file_get_contents(__DIR__.'/stubs/migration.stub');

        return str_replace('facebook_user_table', $this->argument('table'), $stub);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('table', InputArgument::REQUIRED, 'The name of your users table.'),
        );
    }
}
