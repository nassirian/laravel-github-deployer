<?php

namespace Nassirian\GitHubDeployer\Supports;

class CommandRunner
{
    public static function run(string $command): string
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