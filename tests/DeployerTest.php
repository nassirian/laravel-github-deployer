<?php

namespace Nassirian\GitHubDeployer\Tests;

use Nassirian\GitHubDeployer\Services\GitPullAndDockerDeployer;

class DeployerTest extends TestCase
{
    /** @test */
    public function test_it_runs_pre_deploy_and_deploy_and_post_deploy_commands()
    {
        config([
            'github-deployer.pre_deploy_commands' => ['echo "Pre Deploy Command"'],
            'github-deployer.deploy_commands' => ['echo "Deploy Command"'],
            'github-deployer.post_deploy_commands' => ['echo "Post Deploy Command"'],
        ]);

        $deployer = $this->getMockBuilder(GitPullAndDockerDeployer::class)
            ->onlyMethods(['runShellCommand'])
            ->getMock();

        $deployer->expects($this->any())
            ->method('runShellCommand')
            ->willReturn('Mocked Command Output');

        $output = $deployer->deploy();

        $this->assertStringContainsString('Mocked Command Output', $output);
    }

    /** @test */
    public function test_it_stops_deploy_if_command_fails()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Command failed');

        $deployer = $this->getMockBuilder(GitPullAndDockerDeployer::class)
            ->onlyMethods(['runShellCommand'])
            ->getMock();

        $deployer->expects($this->once())
            ->method('runShellCommand')
            ->willThrowException(new \RuntimeException('Command failed: git pull'));

        $deployer->deploy();
    }
}
