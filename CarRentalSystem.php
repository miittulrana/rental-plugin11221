<?php
/**
 * Plugin Name: Car Rental System
 * Plugin URI: https://codecanyon.net/item/car-rental-system-native-wordpress-plugin/11758680
 * Description: It’s a high quality, native and responsive WordPress plugin to rent a car, created by experienced Silicon Valley engineers. 100% of it’s code is written by using native WordPress functions, so it much faster and secure than other similar plugins. Also – we made it compatible with WordPress Multisite, WPML & Multi-language setup with native support for WordPress date, email & time settings. Plus – we love mobile-first designs – that’s why we used Bootstrap, Font Awesome icons, Slick Slider, CSS3 and HTML5 techniques with smooth image resizing to 4 different sizes to make sure that your cars would look great on all mobile devices, tablets, full-screen previews and pages with many WordPress free & Premium designs. Now both – your car rental business and website can run smoothly, by accepting online reservations and managing your entire fleet, from one control panel. By offering highly-customizable reservation system, your customers will be able to see vehicles availability, and make online reservations with a few clicks.
 * Version: 5.0.6
 * Semver: 5.0.6
 * Author: KestutisIT
 * Author URI: https://codecanyon.net/user/kestutisit
 * Text Domain: car-rental-system
 * Domain Path: /Languages/CarRental
 * License: See Legal/License.txt for details.
 */
namespace FleetManagement;

defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

// Require missing WordPress core functions
require_once 'formatting.php';
require_once 'pluggable.php';

// Require mandatory models
require_once 'Models/Configuration/ConfigurationInterface.php';
require_once 'Models/Routing/RoutingInterface.php';
require_once 'Models/Configuration/Configuration.php';
require_once 'Models/Semver/SemverInterface.php';
require_once 'Models/Semver/Semver.php';
require_once 'Models/Validation/StaticValidator.php';

// Require autoloader and main plugin controller
require_once 'Models/Load/AutoLoad.php';
require_once 'Controllers/MainController.php';

use FleetManagement\Models\Configuration\Configuration;
use FleetManagement\Controllers\MainController;

if(!class_exists('FleetManagement\CarRentalSystem'))
{
    final class CarRentalSystem
    {
        // Configuration
        const REQUIRED_PHP_VERSION = '7.0';
        const REQUIRED_WP_VERSION = 5.1;
        const OLDEST_COMPATIBLE_PLUGIN_SEMVER = '5.0.0';
        const PLUGIN_SEMVER = '5.0.6';

        // Settings
        /**
         * @var array - Extension settings. We don't use constant here, because it is supported only since PHP 5.6
         */
        private static $params = array(
            'plugin_prefix' => 'fleet_management_',
            'ext_api_namespace' => '__fleet_management_api', // Replace with crs/v1 later
            'plugin_handle_prefix' => 'fleet-management-',
            'plugin_css_prefix' => 'fleet-management-',
            'gallery_folder_name' => 'CarRentalGallery', // NOTE: With 5.1 replace with 'FleetManagementGallery'
            'theme_ui_folder_name' => 'FleetManagementUI', // Folder in your current theme path, that may override plugin’s UI
            'post_type_prefix' => 'car_rental_', /* LENGTH: 0-12 CHARS (WP CORE LIMITATION) */
            'ext_code' => 'CAR_RENTAL', /* LENGTH: 1-20 CHARS */
            'ext_name' => 'Car Rental',
            'ext_folder_name' => 'CarRental',
            'ext_prefix' => 'car_rental_',
            'ext_url_prefix' => 'car-rental-',
            'ext_css_prefix' => 'car-rental-',
            'shortcode' => 'car_rental_system',
            'text_domain' => 'car-rental-system',
            'demo_wp_user_login' => 'carrental_demo',

            // Extension Params
            'item_model_param' => 'car_model',
            'order_code_param' => 'reservation_code',

            // Extension display values
            'item_model_display_value' => 'car-model',
            'item_models_display_value' => 'car-models',
            'item_model_prices_display_value' => 'car-model-prices',
            'item_models_availability_display_value' => 'car-models-availability',
            'change_order_display_value' => 'change-reservation',

            // Extension Prefixes
            'wp_username_prefix' => 'crsu_', /* LOWERCASE ONLY, LENGTH: 0-9 CHARS */
            'item_model_sku_prefix' => 'CM_', /* LENGTH: 0-9 CHARS */
            'extra_sku_prefix' => 'EX_', /* LENGTH: 0-9 CHARS */
            'location_unique_identifier_prefix' => 'LUID_', /*LENGTH: 0-9 CHARS */
            'order_code_prefix' => 'R', /* LENGTH: 0-3 (!) CHARS */
            'payment_method_code_prefix' => 'PM_', /*LENGTH: 0-9 CHARS */

            // BB codes
            'order_code_bbcode' => 'RESERVATION_CODE',
            'change_order_url_bbcode' => 'CHANGE_RESERVATION_URL',
        );

        /**
         * @var Configuration - Conf Without Routing
         */
        private static $objConfiguration = null;

        /**
         * @var MainController - Main Controller
         */
        private static $objMainController = null;

        private static $uninstallHookRegistered = false;

        /**
         * @return Configuration
         */
        public static function getConfiguration()
        {
            if(is_null(static::$objConfiguration) || !(static::$objConfiguration instanceof Configuration))
            {
                // Create an instance of plugin configuration model
                static::$objConfiguration = new Configuration(
                    $GLOBALS['wpdb'],
                    get_current_blog_id(),
                    static::REQUIRED_PHP_VERSION, phpversion(),
                    static::REQUIRED_WP_VERSION, $GLOBALS['wp_version'],
                    static::OLDEST_COMPATIBLE_PLUGIN_SEMVER, static::PLUGIN_SEMVER,
                    __FILE__,
                    static::$params
                );
            }
            return static::$objConfiguration;
        }

        /**
         * Creates new or returns existing instance of plugin main controller
         * NOTE: It should not throw an exception, that has to be handled in the controller
         * @return MainController
         */
        public static function getMainController()
        {
            if(is_null(static::$objMainController) || !(static::$objMainController instanceof MainController))
            {
                static::$objMainController = new MainController(static::getConfiguration());
            }

            return static::$objMainController;
        }

        /**
         * Registers plugin uninstall hook
         * NOTE #1: separated from dynamic objects, because uninstall hook can be called in static context only!
         */
        public static function registerUninstallHook()
        {
            if(static::$uninstallHookRegistered === false)
            {
                static::$uninstallHookRegistered = true;

                register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));
            }
        }

        public static function uninstall()
        {
            // This check allows us to use plugin only in the correct way
            if(static::$uninstallHookRegistered === true)
            {

                static::getMainController()->uninstall();
            }
        }
    }

    // Register static hooks
    CarRentalSystem::registerUninstallHook();

    // Run the plugin
    CarRentalSystem::getMainController()->run();
}