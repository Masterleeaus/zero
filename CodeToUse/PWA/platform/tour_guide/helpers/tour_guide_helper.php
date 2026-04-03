<?php

defined('BASEPATH') or exit('No direct script access allowed');

use TourGuide\TourGuideDB;
use TourGuide\Helper\TourGuideDefaultHelper;
use TourGuide\Helper\TourGuideHelper;
use TourGuide\TourGuideRepository;
use TourGuide\TourGuideUIHelper;
use TourGuide\TourGuideTranslator;

/**
 * Class TourGuideHelper
 * A helper class for Tour Guide functionalities providing static methods for common operations.
 */
class AppTourGuideHelper extends TourGuideDefaultHelper
{
    /**
     * Get the TourGuideDB instance.
     *
     * @return TourGuideDB
     */
    public static function dbInstance(): ?TourGuideDB
    {
        static $instance = null; // Static variable to hold the instance

        // Create a new instance if it doesn't exist
        if ($instance === null) {
            $CI = &get_instance(); // Get the CodeIgniter super object
            $db = $CI->db; // Retrieve the database configuration

            // Extract necessary details
            $db_host = $db->hostname;
            $db_user = $db->username;
            $db_pass = $db->password;
            $db_name = $db->database;
            $db_prefix = $db->dbprefix; // Optional: Use prefix if needed
            $db_port = isset($db->port) ? $db->port : null; // Optional: Set your database port

            // Create the instance of TourGuideDB
            $instance = new TourGuideDB($db_host, $db_user, $db_pass, $db_name, $db_prefix, $db_port);
        }

        return $instance; // Return the existing instance
    }

    /**
     * Get the URL for the admin section.
     *
     * @param string $path
     * @return string
     */
    public static function adminUrl($path = ''): string
    {
        return self::baseUrl(TOUR_GUIDE_MODULE_NAME . '/admin/' . $path);
    }

    /**
     * Get the URL for the user section.
     *
     * @param string $path
     * @return string
     */
    public static function userUrl($path = ''): string
    {
        return self::baseUrl(TOUR_GUIDE_MODULE_NAME . '/' . $path);
    }

    /**
     * @inheritDoc
     */
    public static function baseUrl($path = ''): string
    {
        return base_url($path);
    }

    /**
     * Manage session data.
     *
     * @param string $key
     * @param mixed|null $value
     * @return mixed|null
     */
    public static function session($key, $value = null)
    {
        $CI = &get_instance(); // Get the CodeIgniter super object

        if ($value === null) {
            return $CI->session->userdata($key);
        }

        if ($value === false) {
            return $CI->session->unset_userdata($key);
        }

        return $CI->session->set_userdata($key, $value);
    }

    /**
     * Get available languages.
     *
     * @return array [['code'=>'en', 'name'=>'english']...]
     */
    public static function supportedLanguages(): array
    {
        $languages = get_instance()->app->get_available_languages();
        $lang_compare_index = 'name';

        // Filter
        $supported_languages = TourGuideTranslator::getLanguages();
        foreach ($supported_languages as $key => $row) {
            if (!in_array(strtolower($row[$lang_compare_index]), $languages))
                unset($supported_languages[$key]);
        }

        return $supported_languages;
    }

    /**
     * Get the default language of the application in two letter code format
     *
     * @return string
     */
    public static function systemLocale(): string
    {
        static $system_locale = '';
        if (empty($system_locale)) {

            $default_language = get_option('active_language');
            if (empty($default_language)) $default_language = 'english';
            $locale = self::getLocaleFromLanguage(strtolower($default_language));
        }

        return $locale;
    }

    /**
     * @inheritDoc
     */
    public static function activeUserLocale(): string
    {
        static $user_locale;

        if (empty($user_locale)) {

            $default_language = is_staff_logged_in() ? get_staff_default_language() : get_client_default_language();
            $default_language = ($default_language != '') ? $default_language : get_option('active_language');
            if (empty($default_language)) {
                $default_language = 'english';
            }

            $user_locale = empty($default_language) ? self::systemLocale() : self::getLocaleFromLanguage($default_language);
        }

        if (empty($user_locale))
            $user_locale = self::systemLocale();

        return $user_locale;
    }

    /**
     * Get user roles.
     *
     * @return array
     */
    public static function userRoles(): array
    {
        return ["Admin", "Staff", "Client", "Guest"];
    }

    /**
     * Get the active user role.
     *
     * @return string
     */
    public static function activeUserRole(): string
    {
        if (is_client_logged_in()) {
            return 'Client';
        }
        if (is_staff_logged_in()) {
            return is_admin() ? 'Admin' : 'Staff';
        }
        return 'Guest';
    }

    /**
     * Get the active user details.
     *
     * @return array
     */
    public static function activeUser(): array
    {
        $user = self::getLoggedInUser();
        $userId = $user->id ?? $user->staffid ?? '';
        $userMetadata = [];

        if (empty($userId)) {
            $user = new stdClass;
        } else {
            $tourGuideRepository = new TourGuideRepository();
            $userMetadata = (array)$tourGuideRepository->getUserMetadata($userId);
        }

        return [
            'locale' => self::activeUserLocale(), // i.e 'en'
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'email' => $user->email ?? '',
            'id' => $userId,
            'role' => self::activeUserRole(),
            'finished_tours' => $userMetadata['finished_tours'] ?? [],
            'last_view' => $userMetadata['last_view'] ?? []
        ];
    }

    /**
     * Get the asset URL for the vendors.
     *
     * @param string $path
     * @return string
     */
    public static function asset($path): string
    {
        $path = ltrim($path, '/');
        if (defined('ENVIRONMENT') && ENVIRONMENT == 'development') {
            self::publishAssets();
        }
        return module_dir_url('tour_guide', 'vendor/assets/' . $path);
    }

    /**
     * Get the asset URL for the module.
     *
     * @param string $path
     * @return string
     */
    public static function module_asset($path): string
    {
        $path = ltrim($path, '/');
        return module_dir_url('tour_guide', 'assets/' . $path);
    }

    /**
     * @inheritDoc
     */
    public static function translate($key, $label = ''): string
    {
        return _l($key, $label, false);
    }

    public static function publishAssets()
    {
        $assetDir = module_dir_path(TOUR_GUIDE_MODULE_NAME, 'vendor/assets');
        file_put_contents($assetDir . '/.htaccess', 'Allow from all');
        \TourGuide\TourGuideUIHelper::publishAssets($assetDir);
    }

    public static function getLoggedInUser()
    {
        static $user = null;
        if (empty($user)) {
            if (is_client_logged_in()) {
                $user = get_instance()->db->get_where(db_prefix() . 'contacts', ['id' => get_contact_user_id()])->row();
            } else if (is_staff_logged_in()) {
                $user = get_staff(get_staff_user_id());
            }
        }
        return $user;
    }
}

// Inject the instance
TourGuideHelper::setInstance(new AppTourGuideHelper());