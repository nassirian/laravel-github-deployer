<?php

namespace Nassirian\GitHubDeployer\Tests;

use Nassirian\GitHubDeployer\Jobs\RunDeploymentJob;
use Illuminate\Support\Facades\Config;

class RunDeploymentJobTest extends TestCase
{
    /** @test */
    public function test_it_executes_all_stages_successfully()
    {
        Config::set('github-deployer.pre_deploy_commands', ['echo "Pre deploy successful"']);
        Config::set('github-deployer.deploy_commands', ['echo "Deploy successful"']);
        Config::set('github-deployer.post_deploy_commands', ['echo "Post deploy successful"']);
        Config::set('github-deployer.mode', 'queue');

        $job = new RunDeploymentJob();

        $this->assertNull($job->handle()); // no exception thrown = success
    }

    /** @test */
    public function test_it_fails_if_a_command_fails_in_run_deployment_job()
    {
        Config::set('github-deployer.deploy_commands', ['non_existing_command_should_fail']);
        Config::set('github-deployer.mode', 'queue');

        $job = new RunDeploymentJob();

        $this->expectException(\RuntimeException::class);
        $job->handle();
    }
}
