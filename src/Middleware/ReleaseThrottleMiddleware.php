<?php

namespace Nassirian\GitHubDeployer\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Nassirian\GitHubDeployer\Traits\DeployableJob;

class ReleaseThrottleMiddleware
{
    protected int $baseDelay;
    protected int $maxAttempts;
    protected static array $classTraitCache = [];

    public function __construct()
    {
        $this->baseDelay = config('github-deployer.deploy_release_throttle.base_delay', 5);
        $this->maxAttempts = config('github-deployer.deploy_release_throttle.max_attempts', 5);
    }

    public function handle($job, Closure $next): mixed
    {
        if (!$this->isDeployableJob($job)) {
            return $next($job);
        }

        $deployQueue = config('github-deployer.queue_name', 'deploy');

        if ($job->queue !== $deployQueue) {
            $attempts = method_exists($job, 'attempts') ? $job->attempts() : 1;
            $delay = $this->baseDelay * min($attempts, $this->maxAttempts);

            Log::warning("Deployable job picked by wrong queue: {$job->queue} (expected: {$deployQueue}). Releasing with {$delay}s delay. Attempts: {$attempts}");

            $job->release($delay);

            return null;
        }

        return $next($job);
    }

    protected function isDeployableJob(object $job): bool
    {
        $class = get_class($job);

        if (!isset(self::$classTraitCache[$class])) {
            self::$classTraitCache[$class] = in_array(DeployableJob::class, class_uses_recursive($class));
        }

        return self::$classTraitCache[$class];
    }
}
