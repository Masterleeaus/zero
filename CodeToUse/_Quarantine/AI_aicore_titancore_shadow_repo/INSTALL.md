# Installation notes

If your environment ships with `ext-redis` 5.x, `composer install` can fail because `symfony/cache` requires Redis ≥ 6.1. Use the provided helper script to ignore the Redis platform check:

```sh
composer run install:no-redis
```

For GitHub rate limits or private dependencies, set `GITHUB_TOKEN`/`COMPOSER_AUTH` in the environment so dist downloads do not fall back to interactive Git clones.

## Local Development Setup

1. Clone the repository.
2. Copy the example environment file and fill in the required values:
   ```sh
   cp .env.example .env
   ```
3. Install PHP dependencies (use `composer run install:no-redis` if your Redis extension is < 6.1).
4. Generate the application key:
   ```sh
   php artisan key:generate
   ```
5. Run database migrations:
   ```sh
   php artisan migrate
   ```
6. Install frontend assets and start the dev build:
   ```sh
   npm install
   npm run dev
   ```
7. Start the application server:
   ```sh
   php artisan serve
   ```
