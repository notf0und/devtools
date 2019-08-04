<?php

namespace App\Console\Commands;

use App\Database;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CommandHelpers
{
    public function runProcess($input)
    {
        $process = new Process($input);
        $process->setTimeout(360000);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

    public function streamProcess($input)
    {
        $process = new Process($input);
        $process->setTimeout(360000);
        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        return $process;
    }

    public function clear()
    {
        $this->streamProcess('clear');
    }

    /*********************************
     * Here start database helpers
     * *******************************
     */
    public function generateConnection($command)
    {
        
        if ($connection = $this->chooseConnection($command)) {
            $connection->database = $this->chooseDatabase($command, $connection->name);
            return $connection;
        }

        return null;

        //TODO Check if connection have ssh tunnel
    }

    public function chooseConnection($command)
    {
        $choices = Database::all()->pluck('name')->toArray();

        if (empty($choices)) {
            return null;
        }

        $default = array_search('homestead', $choices) ?: 0;
        $user_choice = $command->choice('Choose a server to connect:', $choices, $default);
        return Database::whereName($user_choice)->firstOrFail();
    }

    public function chooseDatabase($command, $user_choice)
    {
        $hideDatabases = [
            'mysql',
            'sys',
            'tmp',
            'innodb',
            'information_schema',
            'performance_schema',
            '#mysql50#lost+found',
        ];

        $input = "mysql --login-path=$user_choice -e 'show databases;'";
        $process = $this->runProcess($input);

        $choices = explode(PHP_EOL, $process->getOutput());
        array_shift($choices);
        array_pop($choices);

        foreach ($hideDatabases as $database) {
            $index = array_search($database, $choices);
            if ($index !== false) {
                unset($choices[$index]);
            }
        }

        $choices = array_values($choices);

        $user_choice = $command->choice('Choose a database to connect:', $choices, 0);

        return $user_choice;
    }
}
