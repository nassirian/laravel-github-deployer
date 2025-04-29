<?php

namespace Nassirian\GitHubDeployer\Tests\Integration\Webhook;

use Nassirian\GitHubDeployer\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class WebhookSyncModeTest extends TestCase
{
    /** @test */
    public function test_it_triggers_sync_deploy_on_webhook()
    {
        Config::set('github-deployer.mode', 'sync');
        Config::set('github-deployer.pre_deploy_commands', []);
        Config::set('github-deployer.deploy_commands', ['echo "Deploy Success"']);
        Config::set('github-deployer.post_deploy_commands', []);
        Config::set('app.github_webhook_secret', 'secret');

        $payload = ['some' => 'payload'];
        $payloadJson = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $payloadJson, 'secret');

        $response = $this->postJson('/github/webhook', $payload, [
            'X-Hub-Signature-256' => $signature,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'success']);
    }
}
