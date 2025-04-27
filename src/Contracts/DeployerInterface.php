<?php

namespace Nassirian\GitHubDeployer\Contracts;

interface DeployerInterface
{
    /**
     * @return string
     */
    public function deploy(): string;
}