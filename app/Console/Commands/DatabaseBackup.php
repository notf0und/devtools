<?php
namespace App\Console\Commands;

use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database into a file';

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
        $this->info('Select origin:');
        $origin = $this->chooseDatabase();

        $timestamp = Carbon::now()->format('Y_m_d_His');
        $path = $origin->getAttribute('download_path');
        $destination = "{$path}/{$timestamp}.sql.bz2";

//        $command = "{$origin->getAttribute('mysqldump')} | bzip2 -9 > {$destination}";
        $command = "{$origin->mysqldumpCommand(null, '| bzip2 -9')} > {$destination}";

        $this->line('Creating database backup.');
        $this->runShellCommand($command);
        $this->info($destination);

        return 0;
    }
}
