<?php

namespace Nassirian\GitHubDeployer\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Nassirian\GitHubDeployer\Contracts\DeployerInterface;
use Mockery;

class WebhookTest extends TestCase
{
    /** @test */
    public function test_it_handles_valid_webhook_and_triggers_deploy()
    {

        // Prepare a secret and sign payload
        $secret = 'secret';
        Config::set('app.github_webhook_secret', $secret);

        $payload = ['some' => 'payload'];
        $payloadJson = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $payloadJson, $secret);

        // Mock the DeployerInterface
        $deployerMock = Mockery::mock(DeployerInterface::class);
        $deployerMock->shouldReceive('deploy')
            ->once()
            ->andReturn('Deploy executed');

        $this->app->instance(DeployerInterface::class, $deployerMock);

        // Now call the endpoint
        $response = $this->postJson('/github/webhook', $payload, [
            'X-Hub-Signature-256' => $signature,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'output' => 'Deploy executed',
        ]);
    }

    /** @test */
    public function test_it_rejects_invalid_signature_webhook()
    {


        $secret = 'secret';
        Config::set('app.github_webhook_secret', $secret);

        $payload = ['some' => 'payload'];
        $payloadJson = json_encode($payload);

        // Wrong signature (change secret slightly)
        $wrongSignature = 'sha256=' . hash_hmac('sha256', $payloadJson, 'wrongsecret');


        $response = $this->postJson('/github/webhook', $payload, [
            'X-Hub-Signature-256' => $wrongSignature,
        ]);
        $response->assertStatus(403); // Forbidden
        $response->assertSee('Unauthorized'); // From abort(403, 'Unauthorized.');
    }
}