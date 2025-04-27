<?php

namespace Nassirian\GitHubDeployer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Nassirian\GitHubDeployer\Contracts\DeployerInterface;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{

    public function __construct(protected DeployerInterface $deployer)
    {
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request): \Illuminate\Http\JsonResponse
    {
        $secret = config('app.github_webhook_secret', env('GITHUB_WEBHOOK_SECRET'));
        $signature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($signature, $request->header('X-Hub-Signature-256'))) {
            Log::warning('GitHub webhook signature mismatch.');
            abort(403, 'Unauthorized.');
        }

        $output = $this->deployer->deploy();

        Log::info('GitHub webhook triggered deployment.', ['output' => $output]);

        return response()->json(['status' => 'success', 'output' => $output]);
    }
}
