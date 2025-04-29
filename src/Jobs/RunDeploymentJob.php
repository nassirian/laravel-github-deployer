<?php

namespace Nassirian\GitHubDeployer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Nassirian\GitHubDeployer\Supports\CommandRunner;
use Nassirian\GitHubDeployer\Traits\DeployableJob;

class RunDeploymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DeployableJob;

    public $queue;
    public function __construct()
    {
        $this->queue  = config('github-deployer.queue_name', 'deploy');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->runCommandSet(config('github-deployer.pre_deploy_commands', []), 'Pre-Deploy');
        $this->runCommandSet(config('github-deployer.deploy_commands', []), 'Deploy');
        $this->runCommandSet(config('github-deployer.post_deploy_commands', []), 'Post-Deploy');

        Log::info('✅ Deployment completed successfully.');
    }

    /**
     * @return array
     */
    public function middleware(): array
    {
        $middlewareClasses = config('github-deployer.middleware', []);

        return array_map(fn ($middleware) => app($middleware), $middlewareClasses);
    }

    protected function runCommandSet(array $commands, string $stage): void
    {
        if (empty($commands)) {
            Log::info("ℹ️ No {$stage} commands to run.");
            return;
        }

        Log::info("🔵 Starting {$stage} stage...");

        foreach ($commands as $command) {
            try {
                Log::info("▶️ Executing: $command");
                $output = CommandRunner::run($command);

                foreach (explode(PHP_EOL, $output) as $line) {
                    Log::info($line);
                }
            } catch (\Throwable $e) {
                Log::error("❌ {$stage} failed on command: {$command}");
                Log::error($e->getMessage());
                throw new \RuntimeException("Deployment failed during {$stage}: " . $e->getMessage());
            }
        }

        Log::info("✅ {$stage} stage completed.");
    }

}