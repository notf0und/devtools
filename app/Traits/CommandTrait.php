<?php
namespace App\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait CommandTrait
{
    public function runProcess($input): Process
    {
        $process = Process::fromShellCommandline($input);
        $process->setTimeout(360000);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

    public function streamProcess($input): Process
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
}
