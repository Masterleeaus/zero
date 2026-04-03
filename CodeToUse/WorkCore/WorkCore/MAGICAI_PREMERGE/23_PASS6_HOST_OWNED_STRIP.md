# Pass 6 host-owned strip

This pass removes remaining standalone WorkCore host/admin/settings surfaces that MagicAI should own.

- removed paths: 40
- focus: auth/settings/admin/front/update/profile/theme/storage layers

## Removed paths
- `app/Http/Controllers/AppSettingController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/DatabaseBackupSettingController.php`
- `app/Http/Controllers/LanguageSettingController.php`
- `app/Http/Controllers/PaymentGatewayCredentialController.php`
- `app/Http/Controllers/ProfileSettingController.php`
- `app/Http/Controllers/SecuritySettingController.php`
- `app/Http/Controllers/SignUpSettingController.php`
- `app/Http/Controllers/SocialAuthSettingController.php`
- `app/Http/Controllers/StorageSettingController.php`
- `app/Http/Controllers/ThemeSettingController.php`
- `app/Http/Controllers/UpdateAppController.php`
- `app/Models/DatabaseBackupSetting.php`
- `app/Models/GlobalSetting.php`
- `app/Models/LanguageSetting.php`
- `app/Models/PackageUpdateNotify.php`
- `app/Models/PushNotificationSetting.php`
- `app/Models/PusherSetting.php`
- `app/Models/SignUpSetting.php`
- `app/Models/SmtpSetting.php`
- `app/Models/SocialAuthSetting.php`
- `app/Models/StorageSetting.php`
- `app/Models/ThemeSetting.php`
- `resources/views/app-settings`
- `resources/views/auth`
- `resources/views/company-settings`
- `resources/views/database-backup-settings`
- `resources/views/errors`
- `resources/views/front`
- `resources/views/language-settings`
- `resources/views/payment-gateway-settings`
- `resources/views/profile-settings`
- `resources/views/security-settings`
- `resources/views/sign-up-settings`
- `resources/views/social-login-settings`
- `resources/views/storage-settings`
- `resources/views/super-admin`
- `resources/views/theme-settings`
- `resources/views/update-settings`
- `public/sample-import`
