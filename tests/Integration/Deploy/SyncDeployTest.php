<?php

namespace Nassirian\GitHubDeployer\Tests\Integration\Deploy;

use Nassirian\GitHubDeployer\Deployers\SyncDeployer;
use Nassirian\GitHubDeployer\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class SyncDeployTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Config::set('github-deployer.mode', 'sync');
    }
    /** @test */
    public function test_it_executes_real_commands_successfully()
    {
        Config::set('github-deployer.pre_deploy_commands', ['echo "Pre Deploy Successful"']);
        Config::set('github-deployer.deploy_commands', ['echo "Deploy Successful"']);
        Config::set('github-deployer.post_deploy_commands', ['echo "Post Deploy Successful"']);



        $deployer = app(SyncDeployer::class);

        $output = $deployer->deploy();

        $this->assertEquals('completed', $output['status']);
        $this->assertStringContainsString('Pre Deploy Successful', $output['pre_deploy']['echo "Pre Deploy Successful"']);
        $this->assertStringContainsString('Deploy Successful', $output['deploy']['echo "Deploy Successful"']);
        $this->assertStringContainsString('Post Deploy Successful', $output['post_deploy']['echo "Post Deploy Successful"']);
    }

    /** @test */
    public function test_it_stops_if_a_real_command_fails()
    {
        Config::set('github-deployer.deploy_commands', ['non_existing_command_should_fail']);

        $deployer = app(SyncDeployer::class);

        $this->expectException(\RuntimeException::class);
        $deployer->deploy();
    }

    /** @test */
    public function test_it_executes_only_post_deploy_commands()
    {
        $command = 'echo "Post Deploy Only Success"';
        Config::set('github-deployer.pre_deploy_commands', []);
        Config::set('github-deployer.deploy_commands', []);
        Config::set('github-deployer.post_deploy_commands', [$command]);
        $deployer = app(SyncDeployer::class);

        $output = $deployer->deploy();
        $this->assertEquals('completed', $output['status']);
        $this->arrayHasKey('post_deploy');
        $this->assertStringContainsString('Post Deploy Only Success', $output['post_deploy'][$command]);
    }
}
