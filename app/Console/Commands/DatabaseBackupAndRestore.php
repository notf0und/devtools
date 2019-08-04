<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackupAndRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:br';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $helpers;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->helpers = new CommandHelpers;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->helpers->streamProcess('php artisan database:backup');
        $this->helpers->streamProcess('php artisan database:restore --latest');
    }
}
