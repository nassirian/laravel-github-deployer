<?php

namespace Nassirian\GitHubDeployer\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Nassirian\GitHubDeployer\GitHubDeployerServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            GitHubDeployerServiceProvider::class,
        ];
    }
}
