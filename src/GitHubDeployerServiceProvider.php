<?php

namespace Nassirian\GitHubDeployer;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Nassirian\GitHubDeployer\Contracts\DeployerInterface;
use Nassirian\GitHubDeployer\Deployers\QueueDeployer;
use Nassirian\GitHubDeployer\Deployers\SyncDeployer;
use Nassirian\GitHubDeployer\Middleware\ReleaseThrottleMiddleware;

class GitHubDeployerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');

        $this->publishes([
            __DIR__.'/../config/github-deployer.php' => config_path('github-deployer.php'),
        ], 'config');

        Queue::before(fn (JobProcessing $event) => (new ReleaseThrottleMiddleware())->handle($event->job, fn () => null));

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/github-deployer.php', 'github-deployer'
        );

        $this->app->bind(DeployerInterface::class, function () {
            $mode = config('github-deployer.mode', 'queue');

            if ($mode === 'sync') {
                return new SyncDeployer();
            }
            return new QueueDeployer();
        });
    }
}