<?php

namespace Nassirian\GitHubDeployer\Services;

use Nassirian\GitHubDeployer\Contracts\DeployerInterface;

class GitPullAndDockerDeployer implements DeployerInterface
{

    public function deploy(): string
    {
        $output = '';
        $preDeployCommands = config('github-deployer.pre_deploy_commands', []);
        $deploy = config('github-deployer.deploy_commands', []);
        $postDeployCommands = config('github-deployer.post_deploy_commands', []);

        foreach ($preDeployCommands as $preCommand) {
            $output .= $this->runShellCommand($preCommand) . PHP_EOL;
        }

        foreach ($deploy as $deployCommand) {
            $output .= $this->runShellCommand($deployCommand) . PHP_EOL;
        }

        foreach ($postDeployCommands as $postCommand) {
            $output .= $this->runShellCommand($postCommand) . PHP_EOL;
        }

        return $output;
    }


    protected function runShellCommand(string $command): string
    {
        $fullCommand = 'cd ' . base_path() . ' && ' . $command;

        $output = [];
        $returnVar = 0;

        exec($fullCommand . ' 2>&1', $output, $returnVar);

        $outputText = implode(PHP_EOL, $output);

        if ($returnVar !== 0) {
            throw new \RuntimeException("Command failed: {$command}\nOutput:\n{$outputText}");
        }

        return $outputText;
    }

}