<?php

namespace Nassirian\GitHubDeployer\Tests\Middleware;

use Illuminate\Support\Facades\Config;
use Nassirian\GitHubDeployer\Middleware\ReleaseThrottleMiddleware;
use Nassirian\GitHubDeployer\Traits\DeployableJob;
use Nassirian\GitHubDeployer\Tests\TestCase;

class ReleaseThrottleMiddlewareTest extends TestCase
{
    /** @test */
    public function it_releases_deployable_job_if_picked_by_wrong_queue()
    {
        Config::set('github-deployer.queue_name', 'deploy');
        Config::set('github-deployer.deploy_release_throttle', [
            'base_delay' => 5,
            'max_attempts' => 5,
        ]);

        $job = new class {
            use DeployableJob;
            public $queue = 'wrong_queue';
            public $releasedWithDelay = null;
            public function release($delay = 0)
            {
                $this->releasedWithDelay = $delay;
            }
            public function attempts()
            {
                return 2; // simulate second retry
            }
        };

        $middleware = new ReleaseThrottleMiddleware();
        $middleware->handle($job, function () {
            $this->fail('Deployable job on wrong queue should not reach next()');
        });

        $this->assertEquals(10, $job->releasedWithDelay, 'Expected delay 10 seconds on second attempt');
    }

    /** @test */
    public function it_runs_deployable_job_normally_if_queue_is_correct()
    {
        Config::set('github-deployer.queue_name', 'deploy');

        $job = new class {
            use DeployableJob;
            public $queue = 'deploy';
        };

        $called = false;

        $middleware = new ReleaseThrottleMiddleware();
        $middleware->handle($job, function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($called, 'Deployable job on correct queue should reach next()');
    }

    /** @test */
    public function it_runs_non_deployable_jobs_normally()
    {
        $job = new class {
            public $queue = 'default';
        };

        $called = false;

        $middleware = new ReleaseThrottleMiddleware();
        $middleware->handle($job, function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($called, 'Non-deployable job should not be blocked.');
    }

    /** @test */
    public function it_caps_release_delay_at_max_attempts()
    {
        Config::set('github-deployer.queue_name', 'deploy');
        Config::set('github-deployer.deploy_release_throttle', [
            'base_delay' => 5,
            'max_attempts' => 5,
        ]);

        $job = new class {
            use DeployableJob;
            public $queue = 'wrong_queue';
            public $releasedWithDelay = null;
            public function release($delay = 0)
            {
                $this->releasedWithDelay = $delay;
            }
            public function attempts()
            {
                return 10; // simulate very high retry count
            }
        };

        $middleware = new ReleaseThrottleMiddleware();
        $middleware->handle($job, function () {
            $this->fail('Deployable job on wrong queue should not reach next()');
        });

        // base_delay * max_attempts = 5 * 5 = 25 seconds (should be capped)
        $this->assertEquals(25, $job->releasedWithDelay, 'Expected delay to cap at 25 seconds');
    }


    /** @test */
    public function test_it_caches_trait_lookup_for_jobs()
    {
        Config::set('github-deployer.queue_name', 'deploy');
        Config::set('github-deployer.deploy_release_throttle', [
            'base_delay' => 5,
            'max_attempts' => 5,
        ]);

        // A fake deployable job
        $job = new class {
            use \Nassirian\GitHubDeployer\Traits\DeployableJob;
            public $queue = 'wrong_queue';
            public $releasedWithDelay = null;
            public function release($delay = 0) { $this->releasedWithDelay = $delay; }
            public function attempts() { return 1; }
        };

        $middleware = new \Nassirian\GitHubDeployer\Middleware\ReleaseThrottleMiddleware();

        // First call: caches the trait lookup
        $middleware->handle($job, function () {
            $this->fail('Deployable job on wrong queue should not reach next()');
        });

        $cache = (new \ReflectionClass($middleware))->getProperty('classTraitCache');
        $cache->setAccessible(true);

        $cachedTraits = $cache->getValue($middleware);

        $this->assertArrayHasKey(get_class($job), $cachedTraits, 'Expected trait lookup cache to have the job class');
        $this->assertTrue($cachedTraits[get_class($job)], 'Expected the trait to be detected as used');
    }


}
