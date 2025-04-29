<?php

namespace Nassirian\GitHubDeployer\Tests\Integration;

use Illuminate\Support\Facades\Config;
use Illuminate\Queue\Events\JobProcessing;
use Nassirian\GitHubDeployer\Tests\TestCase;
use Nassirian\GitHubDeployer\Traits\DeployableJob;

class QueueEventListenerTest extends TestCase
{
    /** @test */
    public function test_it_applies_release_throttle_via_queue_before_event()
    {
        Config::set('github-deployer.queue_name', 'deploy');
        Config::set('github-deployer.deploy_release_throttle', [
            'base_delay' => 5,
            'max_attempts' => 5,
        ]);

        // Simulate a fake job that uses DeployableJob trait
        $fakeJob = new class {
            use \Nassirian\GitHubDeployer\Traits\DeployableJob;
            public $queue = 'wrong_queue';
            public $releasedWithDelay = null;
            public function release($delay = 0) { $this->releasedWithDelay = $delay; }
            public function attempts() { return 2; }
            public function payload() { return []; } // ADD THIS for Laravel 11+
        };

        // Manually dispatch JobProcessing event
        event(new JobProcessing('redis', $fakeJob));

        // After event triggered, middleware should have released it
        $this->assertEquals(10, $fakeJob->releasedWithDelay, 'Expected job to be released with 10s delay');
    }
}
