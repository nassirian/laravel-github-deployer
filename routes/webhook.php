<?php

use Illuminate\Support\Facades\Route;
use Nassirian\GitHubDeployer\Http\Controllers\WebhookController;

Route::post('/github/webhook', [WebhookController::class, 'handle']);
