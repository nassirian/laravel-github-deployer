<?php

namespace Nassirian\GitHubDeployer\Tests;

use Nassirian\GitHubDeployer\Deployers\QueueDeployer;
use Nassirian\GitHubDeployer\Jobs\RunDeploymentJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;

class QueueDeployerTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        Config::set('github-deployer.mode', 'queue');
        Config::set('github-deployer.queue_name', 'deploy');
    }
    /** @test */
    public function test_it_dispatches_run_deployment_job_in_queue_mode()
    {


        Queue::fake();

        $deployer = app(QueueDeployer::class);
        Config::set('github-deployer.pre_deploy_commands', []);
        Config::set('github-deployer.deploy_commands', ['echo "Deploy Success"']);
        Config::set('github-deployer.post_deploy_commands', []);
        $output = $deployer->deploy();

        Queue::assertPushed(RunDeploymentJob::class);
        $this->assertEquals('queued', $output['status']);
        $this->assertStringContainsString('Deployment job has been queued', $output['message']);
    }
}
