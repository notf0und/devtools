<?php

namespace App\Console\Commands;

use App\Models\Database;

class DatabaseImportConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import database login configurations from mysql';

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
     * @return mixed
     */
    public function handle()
    {
        $process = $this->runProcess('mysql_config_editor print --all');
        $output = $process->getOutput();
        preg_match_all("/\[[^\]]*\]/", $output, $match);

        $choices = array_map(
            function ($item) {
                return substr($item, 1, -1);
            },
            $match[0]
        );

        foreach ($choices as $choice) {
            $this->import($choice);
        }

        $this->line('Databases configuration imported!');
    }

    public function import($login)
    {

        $process = $this->runProcess("my_print_defaults -s $login");
        $output = $process->getOutput();
        if (strlen($output) === 0) {
            return null;
        }

        $output = explode("\n", $output);

        $database = [
            'name' => $login,
            'username' => substr($output[0], 7),
            'password' => substr($output[1], 11),
            'host' => substr($output[2], 7),
        ];

        return Database::updateOrCreate(['name' => $database['name']], $database);
    }
}
