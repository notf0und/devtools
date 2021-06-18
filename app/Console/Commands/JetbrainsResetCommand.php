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
    protected $description = 'Reset evaluation period oof a JetBrain product';

    /**
     * Default JetBrains config path.
     */
    protected $configPath;

    /**
     * Default JetBrains user preferences path.
     */
    protected $userPreferencesPath;

    /**
     * Available JetBrains products.
     */
    protected $products;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->configPath = getenv("HOME") . '/.config/JetBrains';
        $this->userPreferencesPath = getenv("HOME") . '/.java/.userPrefs';

        $iterator = new DirectoryIterator($this->configPath);
        $this->products = collect();
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isDot()) {
                $this->products->push($fileInfo->getFilename());
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $product = $this->choice('Choose a database directory:', $this->products->toArray(), 0);
        $this->delete($this->userPreferencesPath . '/prefs.xml');
        $this->delete($this->userPreferencesPath . '/jetbrains');
        $this->delete($this->configPath . "/$product/eval");
        $this->delete($this->configPath . "/$product/options/other.xml");

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
