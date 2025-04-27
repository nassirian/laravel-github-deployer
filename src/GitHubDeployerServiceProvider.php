<?php

namespace Nassirian\GitHubDeployer;

use Illuminate\Support\ServiceProvider;
use Nassirian\GitHubDeployer\Contracts\DeployerInterface;
use Nassirian\GitHubDeployer\Services\GitPullAndDockerDeployer;
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

        $this->app->bind(DeployerInterface::class, GitPullAndDockerDeployer::class);
    }
}