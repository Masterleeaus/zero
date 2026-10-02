<div align="center">

# Titan Zero Legacy Laravel Platform

**A Laravel business application and source archive that predates the current TypeScript field-service workforce platform.**

</div>

> **Status: legacy source repository; active maintenance status unverified.** The inspected repository contains a large Laravel application and migration notes. The canonical current field-service product is [Titan Zero Field Service Workforce](https://github.com/Masterleeaus/Titan-Zero-Field-Service-Workforce).

## Purpose and relationship

This repository preserves a Laravel application based on MagicAI/WorkCore-era code and includes migration and federation planning documents. It is useful as a source reference where provenance and migration history are retained. It must not be assumed to be the current TypeScript system or a currently deployable product without a fresh build and runtime verification.

## Installation

See [INSTALL.md](INSTALL.md) for repository-specific setup notes. In summary, the project uses PHP 8.2+, Composer, a configured `.env`, database migrations, and npm for frontend assets.

```bash
cp .env.example .env
composer run install:no-redis
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

Some environments can use `composer install` directly. Follow the repository\’s own Composer scripts and environment requirements for your host.

## Repository map

- `app/`, `Modules/`, `packages/` — Laravel application and modular code
- `INSTALL.md` — local setup notes
- `WORKCORE_*.md` — historical migration and architecture planning
- `CodeToUse/` — retained source material; inspect provenance before reuse

## Verification

The repository defines Composer scripts for tests and linting. Install dependencies and run:

```bash
composer test
composer test:lint
```

These commands were not executed as part of this README update. Treat successful installation and runtime behavior as unverified until the checks pass in a supported environment.

## Security and provenance

Use only local secrets in `.env`; never commit credentials, production data, or customer records. Review repository history and imported dependencies before reuse. Confirm applicable upstream licenses and notices.

## Portfolio classification

**Retain as a historical/source repository pending lineage review.** Compare against `Titan-BOS`, `TitanPro`, `cleanly`, `modules`, and the current workforce repository before deciding whether to archive or extract anything. No deletion or archive setting was changed.

## Banner

A verified project-specific banner has not been found in this repository, so this landing page uses a typographic header rather than an unrelated image.
