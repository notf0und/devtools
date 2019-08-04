<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup {--server=} {--database=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup a database';

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
        $connection = $this->helpers->generateConnection($this);
        $this->line('Creating database backup.');
        $backup = $this->backup($connection);

        $this->line($backup);
        return $backup;
    }

    private function backup($connection)
    {
        $timestamp = Carbon::now()->format('Y_m_d_His');
        $path = $this->generatePath($connection);
        $backup="$path/$timestamp.sql.bz2";
        $command = "mysqldump --login-path=$connection->name $connection->database --opt --single-transaction --quick --compress | bzip2 -9 > $backup";
        $this->helpers->runProcess($command);

        return $backup;
    }

    private function generatePath($connection)
    {
        $path = "storage/app/database/backups/$connection->name/$connection->database";
        $directories = explode('/', $path);

        $currentPath = '';
        foreach ($directories as $directory) {
            $currentPath .= "$directory/";
            if (!file_exists($currentPath)) {
                mkdir($currentPath);
            }
        }

        return $path;
    }
}
