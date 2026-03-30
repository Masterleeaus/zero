<?php

namespace Modules\BusinessSettingsModule\Providers;

use Modules\BusinessSettingsModule\Console\ActivateModuleCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class BusinessSettingsModuleServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'BusinessSettingsModule';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'businesssettingsmodule';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ActivateModuleCommand::class,
            ]);
        }

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        try {
            $config = function_exists('business_config') ? business_config('email_config', 'email_config') : null;
            if ($config != null && ((is_array($config) && ($config['is_active'] ?? null) == 1) || (is_object($config) && ($config->is_active ?? null) == 1))) {
                Config::set('mail', [
                    'driver' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['driver'],
                    'host' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['host'],
                    'port' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['port'],
                    'username' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['user_name'],
                    'password' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['password'],
                    'encryption' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['encryption'],
                    'from' => array('address' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['email_id'], 'name' => (is_object($config) ? $config->live_values : ($config['live_values'] ?? []))['mailer_name']),
                    'sendmail' => '/usr/sbin/sendmail -bs',
                    'pretend' => false,
                ]);
            }

            $timezone = function_exists('business_config') ? business_config('time_zone', 'business_information') : null;
            if ($timezone) {
                Config::set('app.timezone', $timezone->live_values);
                date_default_timezone_set($timezone->live_values);
            }
        } catch (\Exception $exception) {
            info($exception);
        }

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Load module helper functions (required by WorkSuite boot order)
        $helpers = module_path($this->moduleName, 'Lib/Business.php');
        if (file_exists($helpers)) {
            require_once $helpers;
        }

        $this->app->register(RouteServiceProvider::class);
    }

/**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
