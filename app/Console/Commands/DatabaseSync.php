<?php

namespace App\Console\Commands;

class DatabaseSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize databases piping between them';

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
    public function handle(): int
    {
        $this->info('Select origin:');
        $origin = $this->chooseDatabase();

        $this->info('Select destination:');
        $destination = $this->chooseDatabase();

        $command = "{$origin->mysqldump} | {$destination->mysql}";

        $this->line("Start database backup & restore process.");
        $this->runShellCommand($command);

        return 0;
    }
}
