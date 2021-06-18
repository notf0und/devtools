<?php

namespace App\Console\Commands;

use App\Models\Database;
use App\Models\Environment;
use App\Models\Project;
use App\Models\Ssh;
use App\ProcessManager;
use App\Services\Lagoon\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class LagoonAddProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lagoon:add {project?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a lagoon project.';

    protected Client $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new Client;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $ssh = Ssh::isLagoon()->first();
        if (!$ssh) {
            $this->error('No ssh connection found for lagoon.');
            $this->line('You can import configuration from ~/.ssh/config with "php artisan ssh:cim" command.');
            if ($this->confirm('Do you want to run that command now?', true)) {
                $this->call('ssh:import');
                $this->call('lagoon:add');
            }
            return 0;
        }

        $this->info('Getting list of projects.');

        try {
            $response = $this->client->allProjects();

            if (!Arr::has($response, 'data.allProjects')) {
                return 0;
            }
        } catch (\ErrorException $exception) {
            return 0;
        }

        $projects = Arr::get($response, 'data.allProjects');
        $projectsName = Arr::pluck($projects, 'name');
        $projectName = $this->choice('Select a project', $projectsName);
        $attributes = Arr::first(Arr::where($projects, fn($project) => $project['name'] === $projectName));

        $project = Project::updateOrCreate(['id' => $attributes['id']], $attributes);


        $response = $this->client->projectByName($project->name);
        if (!Arr::has($response, 'data.projectByName.environments')) {
            return 0;
        }

        $environments = Arr::get($response, 'data.projectByName.environments');

        $processes = $this->getProcesses($environments);
        $processManager = new ProcessManager();


        if (!$processes->count()) {
            return $this->finish($projectName);
        }

        $this->info("Getting from lagoon data of {$processes->count()} environments.");
        $processManager->runParallel($processes->toArray(), 5, 1000);

        foreach ($processes as $process) {
            $output = $process->getOutput();
            $output = explode(PHP_EOL, $output);
            $environment =  Arr::first(
                Arr::where(
                    $environments,
                    fn($environment) => $environment['name'] === $this->getEnv($output, 'LAGOON_GIT_BRANCH')
                )
            );

            if (!$environment) {
                $this->error("Error executing '{$process->getCommandLine()}': {$process->getOutput()}");
                continue;
            }

            $database = Database::updateOrCreate(['name' => $environment['openshiftProjectName']], [
                'name' => $environment['openshiftProjectName'],
                'username' => $this->getEnv($output, 'MARIADB_USERNAME'),
                'password' => $this->getEnv($output, 'MARIADB_PASSWORD'),
                'host' => $this->getEnv($output, 'MARIADB_HOST'),
                'database' => $this->getEnv($output, 'MARIADB_DATABASE'),
                'port' => $this->getEnv($output, 'MARIADB_PORT'),
                'ssh_id' => $ssh->id
            ]);

            $attributes = array_merge($environment, [
                'project_id' => $project->id,
                'database_id' => $database->id
            ]);

            Environment::updateOrCreate(['id' =>$environment['id']], $attributes);
        }

        return $this->finish($projectName, $processes->count());
    }

    private function getEnv(array $variables, string $variable)
    {
        $result =  Arr::where($variables, fn($env) => strpos($env, $variable) !== false);

        if (empty($result)) {
            return null;
        }

        $line = Arr::first($result);

        return substr($line, strpos($line, '=') + 1);
    }

    private function getProcesses($environments): Collection
    {
        $processes = collect();
        foreach ($environments as $environment) {
            if (Database::where('name', $environment['openshiftProjectName'])->first()) {
                if (!$this->confirm("Download {$environment['name']} configuration again?")) {
                    continue;
                }
            }

            $command = implode(' ', [
                'ssh',
                $environment['openshiftProjectName'] .'@lagoon',
                'env'
            ]);

            $process = Process::fromShellCommandline($command);
            $process->setTimeout(90);
            $processes->push($process);
        }

        return $processes;
    }

    private function finish($project, $count = 0)
    {
        $description = Str::plural('environment', $count);

        $this->notify(
            "Configuration for {$project} downloaded",
            "The configuration of {$count} {$description} have been imported into the database.",
            resource_path('icon.png'),
        );
        return 0;
    }
}
