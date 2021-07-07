<?php

namespace App\Console\Commands;

use DirectoryIterator;

class JetbrainsResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jetbrains:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset evaluation period of a JetBrain product';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $configPath = getenv('HOME') . '/.config/JetBrains';
        $userPreferencesPath = getenv('HOME') . '/.java/.userPrefs';

        $iterator = new DirectoryIterator($configPath);
        $products = collect();
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isDot()) {
                $products->push($fileInfo->getFilename());
            }
        }

        $product = $this->choice('Choose a product:', $products->toArray(), 0);
        $this->delete($userPreferencesPath . '/prefs.xml');
        $this->delete($userPreferencesPath . '/jetbrains');
        $this->delete($configPath . "/$product/eval");
        $this->delete($configPath . "/$product/options/other.xml");

        return  0;
    }

    private function delete($path): void
    {
        if (empty($path) || $path === '/' || !file_exists($path)) {
            return;
        }

        $this->info("Deleting $path");

        is_file($path) ?
            unlink($path) :
            exec("rm -rf {$path}");
    }
}
