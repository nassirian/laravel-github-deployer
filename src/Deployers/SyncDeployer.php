<?php

namespace Nassirian\GitHubDeployer\Deployers;

use Nassirian\GitHubDeployer\Contracts\DeployerInterface;
use Nassirian\GitHubDeployer\Supports\CommandRunner;

class SyncDeployer implements DeployerInterface
{
    public function deploy(): array
    {
        $output = [];
        $preDeployCommands = config('github-deployer.pre_deploy_commands', []);
        $deploy = config('github-deployer.deploy_commands', []);
        $postDeployCommands = config('github-deployer.post_deploy_commands', []);

        foreach ($preDeployCommands as $preCommand) {
            $output['pre_deploy'][$preCommand] = CommandRunner::run($preCommand).PHP_EOL;
        }

        foreach ($deploy as $deployCommand) {
            $output['deploy'][$deployCommand]  = CommandRunner::run($deployCommand).PHP_EOL;
        }

        foreach ($postDeployCommands as $postCommand) {
            $output['post_deploy'][$postCommand] = CommandRunner::run($postCommand).PHP_EOL;
        }
        $output['status'] = 'completed';
        return $output;
    }
}