<?php

namespace Nassirian\GitHubDeployer\Tests\Integration\Deploy;

use Nassirian\GitHubDeployer\Deployers\QueueDeployer;
use Nassirian\GitHubDeployer\Jobs\RunDeploymentJob;
use Nassirian\GitHubDeployer\Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;

class QueueDeployTest extends TestCase
{
    /** @test */
    public function test_it_dispatches_run_deployment_job_successfully()
    {
        Config::set('github-deployer.mode', 'queue');
        Config::set('github-deployer.queue_name', 'deploy');

        Queue::fake();

        $deployer = app(QueueDeployer::class);

        $output = $deployer->deploy();

        $this->assertEquals('queued', $output['status']);
        $this->assertStringContainsString('Deployment job has been queued', $output['message']);

        Queue::assertPushed(RunDeploymentJob::class);
    }
}
