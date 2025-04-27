# Laravel GitHub Deployer

**Simple Laravel package** to automate server deployment via **GitHub Webhooks**.
Every push to your GitHub repo can trigger automatic:
- Git pull
- Composer install
- Docker container restart
- Laravel migrations & cache clearing

✅ No external server, no SSH needed — all handled by a secure webhook.

---

## 📦 Installation

Install the package via Composer:

```bash
composer require nassirian/laravel-github-deployer
```

---

## ⚙️ Publish Config (optional)

You can publish the config file if you want to customize the commands:

```bash
php artisan vendor:publish --tag=config
```

It will create `config/github-deployer.php`:

```php
return [
    'pre_deploy_commands' => [
        // Example: 'php artisan down',
    ],
    'deploy_commands' => [
        'git pull origin main',
        'composer install --no-interaction --prefer-dist --optimize-autoloader',
        'docker-compose pull',
        'docker-compose up -d',
    ],
    'post_deploy_commands' => [
        'php artisan migrate --force',
        'php artisan config:cache',
        'php artisan route:cache',
    ],
];
```

---

## 🛠️ Setting Up the Webhook

1. **Expose your server's webhook endpoint**:  
   Your app will listen at:

   ```
   POST /github/webhook
   ```

2. **Create a GitHub Webhook**:
    - Go to your GitHub repository → **Settings** → **Webhooks** → **Add webhook**.
    - Payload URL:
      ```
      https://your-domain.com/github/webhook
      ```
    - Content type: `application/json`
    - Secret: (choose any secret key)
    - Events: **Just the Push event**

3. **Set the webhook secret** in your Laravel `.env`:

```env
GITHUB_WEBHOOK_SECRET=your-secret-here
```

---

## ✅ How It Works

1. GitHub sends a push event to your server.
2. Laravel verifies the signature.
3. If valid:
    - It runs all `pre_deploy_commands` first (optional)
    - It runs all `deploy_commands` (git pull, composer install, docker-compose, etc.)
    - It runs all `post_deploy_commands` (Laravel migrations, cache clear)
4. Deployment complete ✅

---

## 📚 Configuration Notes

- **You control everything** inside `config/github-deployer.php`.
- If you need to add `npm install && npm run build`, just add to `deploy_commands`.
- If you need to restart queues or clear extra caches, add to `post_deploy_commands`.

---

## 📚 Requirements

- PHP 8.0+
- Laravel 8.x, 9.x, 10.x
- Composer installed on your server
- (Optional) Docker installed if you use `docker-compose`

---

## 💼 License

This package is open-sourced under the [MIT license](LICENSE).

---

# ✨ Quick Example

**Default deploy flow:**

```plaintext
GitHub Push →
Webhook triggered →
git pull →
composer install →
docker-compose pull →
docker-compose up -d →
php artisan migrate →
php artisan config:cache →
php artisan route:cache
```

Fully automated!
