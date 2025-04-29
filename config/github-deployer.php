<?php
return [

    'mode' => env('DEPLOY_MODE', 'queue'), // 'queue' or 'sync'
    'queue_name' => env('DEPLOY_QUEUE', 'deploy'), // The name of the queue to use for deployments

    'deploy_release_throttle' => [
        'base_delay' => 5,
        'max_attempts' => 5,
    ],

    'middleware' => [
        \Nassirian\GitHubDeployer\Middleware\EnsureDeployQueue::class,
    ],

    // Commands to run *before* anything else (optional)
    'pre_deploy_commands' => [
    ],

    // Main deploy commands
    'deploy_commands' => [
        'git pull',
        'docker-compose pull',
        'composer install --no-interaction --prefer-dist --optimize-autoloader',
        'docker-compose up -d',
    ],

    // Laravel artisan commands after deploy
    'post_deploy_commands' => [
        'php artisan migrate --force',
        'php artisan config:cache',
        'php artisan route:cache',
    ],
];