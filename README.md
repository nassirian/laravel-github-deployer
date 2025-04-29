# Laravel GitHub Deployer
[![Latest Version](https://img.shields.io/packagist/v/nassirian/laravel-github-deployer.svg?style=flat-square)](https://packagist.org/packages/nassirian/laravel-github-deployer)
[![Build Status](https://github.com/nassirian/laravel-github-deployer/actions/workflows/tests.yml/badge.svg)](https://github.com/nassirian/laravel-github-deployer/actions)
[![License](https://img.shields.io/github/license/nassirian/laravel-github-deployer.svg?style=flat-square)](https://github.com/nassirian/laravel-github-deployer/blob/main/LICENSE)

**Simple and flexible Laravel package** to automate deployments using **GitHub Webhooks**.

This package supports both:

- 🔁 Synchronous deployments (runs directly on the server)
- 🧵 Queue-based deployments (offloaded to dedicated queue workers — e.g., Docker container or remote worker)

### ✅ Features

- Git pull
- Composer install
- Docker container restart
- Laravel migrations, route/config caching
- Dedicated `deploy` queue support
- Secure signature verification from GitHub

---

## 📦 Installation

```bash
composer require nassirian/laravel-github-deployer
```

---

## ⚙️ Configuration (Optional)

Publish the config file to customize deployment behavior:

```bash
php artisan vendor:publish --tag=config
```

This creates: `config/github-deployer.php`

```php
return [

    // 'sync' will run directly on server, 'queue' will dispatch a job to deploy queue
    'mode' => env('DEPLOY_MODE', 'queue'),

    // Deploy queue name for queue-based mode
    'queue_name' => env('DEPLOY_QUEUE', 'deploy'),

    // Optional job middleware (e.g., throttle or queue isolation)
    'middleware' => [
        \Nassirian\GitHubDeployer\Middleware\EnsureDeployQueue::class,
    ],

    // Commands to run before deploy
    'pre_deploy_commands' => [],

    // Main deploy commands
    'deploy_commands' => [
        'git pull origin main',
        'composer install --no-interaction --prefer-dist --optimize-autoloader',
        'docker-compose pull',
        'docker-compose up -d',
    ],

    // Post-deploy artisan commands
    'post_deploy_commands' => [
        'php artisan migrate --force',
        'php artisan config:cache',
        'php artisan route:cache',
    ],

    // Optional throttle behavior for wrong queue workers
    'deploy_release_throttle' => [
        'base_delay' => 5,
        'max_attempts' => 5,
    ],
];
```

---

## 📡 GitHub Webhook Setup

1. **Expose your webhook route**

Laravel will respond to:

```
POST /github/webhook
```

2. **Add Webhook in GitHub**:
   - Go to **Repo Settings → Webhooks → Add Webhook**
   - Payload URL: `https://your-domain.com/github/webhook`
   - Content type: `application/json`
   - Secret: `your-random-string`
   - Events: ✅ Push event only

3. **Set secret in `.env`:**

```env
GITHUB_WEBHOOK_SECRET=your-random-string
```

---

## ⚙️ Sync vs Queue Mode

| Mode | Description |
|------|-------------|
| `sync` | Runs deployment commands immediately in the HTTP request (for small projects or simple VPS) |
| `queue` | Dispatches a background job to the `deploy` queue (best for Docker, Horizon, supervisors) |

Set this in your `.env`:

```env
DEPLOY_MODE=queue
DEPLOY_QUEUE=deploy
```

Then, in `supervisord` or Docker, run:

```bash
php artisan queue:work --queue=deploy
```

---

## ✅ How It Works

1. GitHub sends a webhook → `/github/webhook`
2. Laravel verifies the HMAC signature
3. Based on config mode:
   - `sync`: Runs the deploy shell commands right away
   - `queue`: Dispatches a background job to the `deploy` queue
4. Commands are run in 3 phases:
   - `pre_deploy_commands`
   - `deploy_commands`
   - `post_deploy_commands`

---

## 📚 Tips & Extensions

- Need `npm run build`? Add it to `deploy_commands`
- Want zero-downtime? Use `php artisan down` / `up` in `pre/post`
- Using Horizon? Just isolate `deploy` workers separately

---

## 🔐 Security

- Verifies GitHub `X-Hub-Signature-256`
- Jobs can self-check queue name (`EnsureDeployQueue`)
- Workers on wrong queues will back off with throttling

---

## 📋 Requirements

- PHP 8.1+
- Laravel 9.x → 12.x
- GitHub webhook support
- Docker (optional)
- Supervisor or Horizon (for queue mode)

---

## 💼 License

This package is open-sourced under the [MIT license](LICENSE).

---

## ✨ Deployment Flow Example

```plaintext
GitHub Push →
Webhook triggered →
Dispatch Job →
Run:
  git pull
  composer install
  docker-compose pull
  docker-compose up -d
  php artisan migrate
  php artisan config:cache
  php artisan route:cache
```

Fully automated. No manual SSH. No downtime.  
🔥 Your deployments are now modern and effortless.
