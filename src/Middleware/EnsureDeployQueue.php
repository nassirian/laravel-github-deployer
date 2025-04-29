<?php

namespace Nassirian\GitHubDeployer\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class EnsureDeployQueue
{

    public function handle($job, Closure $next): mixed
    {
        $expectedQueue = config('github-deployer.deploy_queue', 'deploy');

        if ($job->queue !== $expectedQueue) {
            Log::warning("Deploy job picked up by wrong queue: {$job->queue} (expected: {$expectedQueue})");
            $job->delete();
            return;
        }

        return $next($job);
    }
}