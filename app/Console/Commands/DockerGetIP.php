<?php

namespace App\Console\Commands;

use App\Models\Container;
use Illuminate\Console\Command;

class DockerGetIP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:get-ip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $process;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->process = new CommandHelpers;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $process = $this->process->runProcess('docker ps');

        $output = explode(PHP_EOL, $process->getOutput());
        array_shift($output);
        array_pop($output);

        if (!$output) {
            $this->error('No containers available');
            return 0;
        }

        $containers = collect();

        foreach ($output as $container) {
            $container = preg_split('/ {2,}/', $container);

            $container = new Container([
                'container_id'  => $container[0],
                'image'         => $container[1],
                'command'       => $container[2],
                'created'       => $container[3],
                'status'        => $container[4],
                'ports'         => isset($container[6]) ? $container[5] : '',
                'names'         => isset($container[6]) ? $container[6] : $container[5],
            ]);

            $containers->push($container);
        }

        $this->process->clear();
        $user_choice = $this->choice('Choose a container to connect with:', $containers->pluck('names')->all(), 0);

        $selected_container = $containers->where('names', $user_choice)->first();
        $command = "docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $selected_container->names";
        $output = explode(PHP_EOL, $this->process->runProcess($command)->getOutput());
        $this->process->clear();
        $this->line("Container: $selected_container->names");
        $this->line("IP: $output[0]");

        return 0;
    }
}
