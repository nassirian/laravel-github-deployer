<?php

namespace Nassirian\GitHubDeployer\Tests\Integration;

use Nassirian\GitHubDeployer\Services\GitPullAndDockerDeployer;
use Nassirian\GitHubDeployer\Tests\TestCase;

class DeployIntegrationTest extends TestCase
{
    /** @test */
    public function test_it_executes_real_commands_successfully()
    {
        config([
            'github-deployer.pre_deploy_commands' => ['echo "Pre Deploy Successful"'],
            'github-deployer.deploy_commands' => ['echo "Deploy Successful"'],
            'github-deployer.post_deploy_commands' => ['echo "Post Deploy Successful"'],
        ]);

        $deployer = $this->app->make(GitPullAndDockerDeployer::class);

        $output = $deployer->deploy();

        $this->assertStringContainsString('Pre Deploy Successful', $output);
        $this->assertStringContainsString('Deploy Successful', $output);
        $this->assertStringContainsString('Post Deploy Successful', $output);
    }

    /** @test */
    public function test_it_stops_if_a_real_command_fails()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Command failed');

        config([
            'github-deployer.pre_deploy_commands' => ['echo "Pre Deploy Successful"'],
            'github-deployer.deploy_commands' => ['non_existing_command_should_fail'],
            'github-deployer.post_deploy_commands' => [],
        ]);

        $deployer = $this->app->make(GitPullAndDockerDeployer::class);

        $deployer->deploy();
    }
}
