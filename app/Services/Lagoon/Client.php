<?php
namespace App\Services\Lagoon;

use App\Console\Commands\CommandHelpers;
use App\Models\Ssh;
use App\Services\Lagoon\Graphql\Queries;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Client
{
    public function allProjects()
    {
        return $this->query(Queries::ALL_PROJECTS);
    }

    public function projectByName(string $name)
    {
        return $this->query(Queries::PROJECT_BY_NAME, compact('name'));
    }

    private function query(string $query, array $variables = [])
    {
        $endpoint = config('devtools.lagoon.graphql.endpoint');
        $token = $this->getToken();

        return Http::withHeaders(
            ['Content-Type' => 'application/json', 'Authorization' => "Bearer $token"]
        )->post($endpoint, compact('query', 'variables'))->json();
    }

    public function getToken()
    {
        return Cache::remember('devtools-lagoon-token', 3600, function () {
            $process = new CommandHelpers();
            $ssh = Ssh::isLagoon()->firstOrFail();
            $response = $process->runProcess($ssh->command('token'));
            $lines = explode(PHP_EOL, $response->getOutput());

            return $lines[0];
        });
    }
}
