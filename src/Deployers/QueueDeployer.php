<?php
namespace Nassirian\GitHubDeployer\Deployers;

use Nassirian\GitHubDeployer\Contracts\DeployerInterface;
use Nassirian\GitHubDeployer\Jobs\RunDeploymentJob;

class QueueDeployer implements DeployerInterface
{
    /**
     * @return string[]
     */
    public function deploy(): array
    {
        $job = new RunDeploymentJob();
        $job->onQueue(config('github-deployer.queue_name', 'deploy'));
        dispatch($job);
        return [
            'status' => 'queued',
            'message' => 'Deployment job has been queued successfully.',
        ];
    }
}
