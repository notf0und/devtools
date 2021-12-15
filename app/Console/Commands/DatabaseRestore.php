<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a database previously stored.';

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
            return 1;
        }

        $destination = $this->chooseDatabase();
        $dbName = $destination->database;
        $refreshCommand = "-e 'DROP SCHEMA IF EXISTS `$dbName`; CREATE SCHEMA `$dbName`'";
        $destination->database = null;
        $command = $destination->mysqlCommand(null, $refreshCommand);
        $destination->database = $dbName;

        if (!$destination->is_lagoon) {
            $this->runProcess($command);
        }

        $origin = Storage::path($file);
        $command = "bunzip2 < {$origin} | {$destination->mysql}";

        $this->line('Restoring database backup.');
        $this->runShellCommand($command);

        return 0;
    }

    public function chooseFile(): ?string
    {
        $path = "database/backups";

        if (!Storage::exists($path)) {
            return null;
        }

        do {
            $path .= '/';
            $this->info($path);
            $directories = Storage::directories($path);
            $files = Storage::files($path);
            $options = array_map(fn($item) => Str::after($item, $path), array_merge($directories, $files));
            $selection = $this->choice('Select a file to restore:', $options, 0);
            $path .= $selection;
        } while (!in_array($path, $files));

        return $path;
    }
}
