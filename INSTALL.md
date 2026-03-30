# Installation notes

If your environment ships with `ext-redis` 5.x, `composer install` can fail because `symfony/cache` requires Redis ≥ 6.1. Use the provided helper script to ignore the Redis platform check:

```sh
composer run install:no-redis
```

For GitHub rate limits or private dependencies, set `GITHUB_TOKEN`/`COMPOSER_AUTH` in the environment so dist downloads do not fall back to interactive Git clones.
