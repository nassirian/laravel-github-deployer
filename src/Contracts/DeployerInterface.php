<?php

namespace Nassirian\GitHubDeployer\Contracts;

interface DeployerInterface
{
    /**
     * @return array
     */
    public function deploy(): array;
}