<?php
return [

    // Commands to run *before* anything else (optional)
    'pre_deploy_commands' => [
    ],

    // Main deploy commands
    'deploy_commands' => [
        'git pull origin main',
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