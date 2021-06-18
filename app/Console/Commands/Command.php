<?php

namespace App\Console\Commands;

use App\Models\Database;
use App\Models\Environment;
use App\Models\Project;
use App\Traits\CommandTrait;
use Carbon\Carbon;
use Illuminate\Console\Command as ConsoleCommand;
use Illuminate\Support\Facades\Log;

abstract class Command extends ConsoleCommand
{
    use CommandTrait;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }

    public function chooseProject()
    {
        $projects = Project::has('environments.database')->get();
        $projectName = $this->choice('Select a project', $projects->pluck('name')->toArray());
        return $projects->where('name', $projectName)->first();
    }

    public function chooseEnvironment($project = null)
    {
        $query = Environment::has('database');

        if ($project instanceof Project) {
            $query->where('project_id', $project->id);
        }

        $environments = $query->get();
        $environmentName = $this->choice('Select an environment', $environments->pluck('name')->toArray());

        return $environments->where('name', $environmentName)->first();
    }

    public function chooseFile()
    {
    }

    public function chooseDatabase()
    {
        $databases = Database::isNotLagoon()->get();

        if (Database::isLagoon()->count()) {
            $databases->push(new Database(['name'=> 'lagoon']));
        }

        if ($databases->isEmpty()) {
            return null;
        }

        $choices = $databases->sortBy('name')->pluck('name')->toArray();

        $default = array_search('homestead', $choices) ?: 0;
        $databaseName = $this->choice('Choose a server to connect:', $choices, $default);

        if (Database::isLagoon()->count() && $databaseName === 'lagoon') {
            $project = $this->chooseProject();
            $environment = $this->chooseEnvironment($project);
            return $environment->database;
        }

        $database = Database::where('name', $databaseName)->firstOrFail();

        if (!$database->database) {
            $databases = $this->showDatabases($database);
            $database->database = $this->choice('Select a database', $databases);
        }

        return $database;
    }

    public function showDatabases($database)
    {
        $hideDatabases = [
            'mysql',
            'sys',
            'tmp',
            'innodb',
            'information_schema',
            'performance_schema',
            '#mysql50#lost+found',
        ];

        $command = "{$database->mysql} -e 'show databases;'";
        $process = $this->runProcess($command);
        $databases = explode(PHP_EOL, $process->getOutput());
        array_shift($databases);
        array_pop($databases);

        foreach ($hideDatabases as $database) {
            $index = array_search($database, $databases);
            if ($index !== false) {
                unset($databases[$index]);
            }
        }

        return $databases;
    }

    public function runShellCommand($command)
    {
        Log::info($command);
        $start = microtime(true);
        $this->runProcess($command);
        $end = microtime(true);
        $diff = $end - $start;
        $time = Carbon::now()->subSeconds($diff)->diffForHumans(['syntax' => true, 'parts' => 2]);

        $message = "Database task completed in {$time}.";
        $this->info($message);
        $this->notify('Database task finished!', $message, resource_path('icon.png'));
    }
}
