<?php

namespace App\Console\Commands;

use App\Container;
use Illuminate\Console\Command;

class DockerSSH extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:ssh';

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
        $this->process->clear();
        $process = $this->process->runProcess('docker ps');

        $output = explode(PHP_EOL, $process->getOutput());
        array_shift($output);
        array_pop($output);

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

        $user_choice = $this->choice('Choose a container to connect with:', $containers->pluck('names')->all(), 0);

        $selected_container = $containers->where('names', $user_choice)->first();
        $command = 'docker exec -it ' . $selected_container->container_id . ' sh';
        $this->process->streamProcess($command);
        $this->process->clear();

        return $containers;
    }
}
