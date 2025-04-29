<?php

namespace Nassirian\GitHubDeployer\Tests\Integration\Webhook;

use Nassirian\GitHubDeployer\Jobs\RunDeploymentJob;
use Nassirian\GitHubDeployer\Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;

class WebhookQueueModeTest extends TestCase
{
    /** @test */
    public function test_it_dispatches_queue_deploy_on_webhook()
    {
        Config::set('github-deployer.mode', 'queue');
        Config::set('github-deployer.queue_name', 'deploy');
        Config::set('app.github_webhook_secret', 'secret');

        Queue::fake();

        $payload = ['some' => 'payload'];
        $payloadJson = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $payloadJson, 'secret');

        $response = $this->postJson('/github/webhook', $payload, [
            'X-Hub-Signature-256' => $signature,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'success']);

        Queue::assertPushed(RunDeploymentJob::class);
    }
}
