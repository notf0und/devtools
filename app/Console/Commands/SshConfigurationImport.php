<?php
namespace App\Console\Commands;

use App\Models\Ssh;
use Illuminate\Support\Str;

class SshConfigurationImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssh:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import config from ~/.ssh/config file';

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
        $process = $this->runProcess('cat ~/.ssh/config');
        $output = $process->getOutput();
        $sshes = $this->getHostsFromOutput($output);

        foreach ($sshes as $ssh) {
            Ssh::updateOrCreate(['host' => $ssh['host']], $ssh);
        }

        $this->info('SSH configuration imported!');

        return 0;
    }

    public function getHostsFromOutput($output)
    {
        $mapped = array_map(function ($item) {
            $variables = explode(PHP_EOL, $item);
            foreach ($variables as $key => $variable) {
                $variables[$key] = isset($variable) ? trim($variable) : false;
                if (!empty($variables[$key]) && $key === 0) {
                    $variables['host'] = $variables[$key];
                }
                if (!empty($variables[$key]) && $key !== 0) {
                    $arguments = explode(' ', $variables[$key]);
                    $newKey = Str::snake($arguments[0]);

                    if ($arguments[0] === 'CheckHostIP') {
                        $newKey = 'check_host_ip';
                    }

                    $variables[$newKey] = $arguments[1];
                }
                unset($variables[$key]);
            }
            return $variables;
        }, explode('host', $output));

        return array_filter($mapped);
    }
}
