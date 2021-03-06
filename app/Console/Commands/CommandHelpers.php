<?php

namespace App\Console\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CommandHelpers
{
    public function runProcess($input)
    {
        $process = Process::fromShellCommandline($input);
        $process->setTimeout(3600);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

    public function streamProcess($input)
    {
        $process = Process::fromShellCommandline($input);
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
}
