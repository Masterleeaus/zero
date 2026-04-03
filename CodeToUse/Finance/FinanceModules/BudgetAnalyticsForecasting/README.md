# Budget Analytics & Forecasting (nWidart Module)

AI-powered budget predictions & scenarios.

## Install
1. Place `Modules/BudgetAnalyticsForecasting` into your project root (or unzip the provided full ZIP there).
2. `composer dump-autoload`
3. Ensure env/config:
   ```env
   AI_ENABLED=true
   AI_PROVIDER=openai
   OPENAI_API_KEY=sk-...
   AI_MODEL=gpt-4o-mini
   BUDGET_AI_MAX_MONTHS=12
   ```
4. Migrate & seed (if needed):
   ```bash
   php artisan migrate
   php artisan module:enable BudgetAnalyticsForecasting
   # optional seeders if provided in donor
   ```
5. Test AI:
   ```bash
   php artisan ai:smoke --months=3
   ```
6. Browse:
   - `/budgets` (Add/Create)
   - `/budgets/create` (Create with AI)
   - `/admin/budgets/ai-settings` (Admin AI Settings)

Keys are loaded from `.env` or `config/ai.php` (env-backed). Never hard-coded.
