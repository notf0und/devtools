<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:restore {--server=} {--database=} {--file=} {--latest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a database previously stored.';

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
        $file = $this->chooseFile();

        if (!$file) {
            $this->line('No database backups found.');
            return;
        }

        $this->line("Restoring $file");

        $connection = $this->helpers->generateConnection($this);
        $this->line('Restoring database.');
        $this->restore($connection, $file);
        $this->line("Database restore completed!");
        ;
    }

    private function restore($connection, $file)
    {
        $command = "mysql --login-path=$connection->name -e 'DROP SCHEMA IF EXISTS `$connection->database`; CREATE DATABASE `$connection->database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'";
        $this->helpers->runProcess($command);

        $command = "bunzip2 < $file | mysql --login-path=$connection->name $connection->database";
        $this->helpers->runProcess($command);
    }

    private function chooseFile()
    {
        $path = "storage/app/database/backups";

        if (!file_exists($path)) {
            return null;
        }


        if (!$server = $this->option('server')) {
            if ($this->option('latest')) {
                $command = "find $path -printf '%T+ %p\\n' | sort -r | head -n1";
                $process = $this->helpers->runProcess($command);
                $fullPath = preg_split('/[\s]+/', $process->getOutput());
                $server = explode('/', $fullPath[1])[4];
            } else {
                $process = $this->helpers->runProcess("ls $path");
                $choices = explode(PHP_EOL, $process->getOutput());
                array_pop($choices);
                $server = $this->choice('Choose a server directory:', $choices, 0);
            }
        }

        $path .= "/$server";

        if (!$database = $this->option('database')) {
            if ($this->option('latest')) {
                $command = "find $path -printf '%T+ %p\\n' | sort -r | head -n1";
                $process = $this->helpers->runProcess($command);
                $fullPath = preg_split('/[\s]+/', $process->getOutput());
                $database = explode('/', $fullPath[1])[5];
            } else {
                $process = $this->helpers->runProcess("ls $path");
                $choices = explode(PHP_EOL, $process->getOutput());
                array_pop($choices);
                $database = $this->choice('Choose a database directory:', $choices, 0);
            }
        }

        $path .= "/$database";

        if (!$file = $this->option('file')) {
            if ($this->option('latest')) {
                $command = "find $path -printf '%T+ %p\\n' | sort -r | head -n1";
                $process = $this->helpers->runProcess($command);
                $fullPath = preg_split('/[\s]+/', $process->getOutput());
                $file = explode('/', $fullPath[1])[6];
            } else {
                $process = $this->helpers->runProcess("ls $path");
                $choices = explode(PHP_EOL, $process->getOutput());
                array_pop($choices);
                $file = $this->choice('Choose a file to restore:', $choices, 0);
            }
        }

        $path .= "/$file";

        return $path;
    }
}
