<?php
/**
 * Configuration class dependant on template
 * Note 1: This is a root class and do not depend on any other plugin classes
 * Note 2: Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Configuration;
use FleetManagement\Models\Routing\RoutingInterface;

final class Configuration implements ConfigurationInterface
{
    private $routing                            = null; // Dependency injection for routing interface

    private $internalWPDB                       = null;
    private $blogId                             = 1;
    private $debugMode                          = 0;

    private $requiredPHP_Version                = "5.6.0";
    private $currentPHP_Version                 = "0.0.0";
    private $requiredWP_Version                 = 4.6;
    private $currentWP_Version                  = 0.0;
    private $oldestCompatiblePluginSemver       = "0.0.0";
    private $pluginSemver                       = "0.0.0";
    private $pluginPrefix                       = "";
    private $pluginHandlePrefix                 = "";
    private $pluginCSS_Prefix                   = "";
    private $galleryFolderName                  = "";
    private $globalGalleryPath                  = "";
    private $globalGalleryPathWithoutEndSlash   = "";
    private $globalGalleryURL                   = "";
    private $themeUI_FolderName                 = "";
    private $postTypePrefix                     = "";
    private $extCode                            = "";
    private $extName                            = "";
    private $extFolderName                      = "";
    private $extAPI_Namespace                   = "";
    private $extPrefix                          = "";
    private $extURL_Prefix                      = "";
    private $extCSS_Prefix                      = "";
    private $blogPrefix                         = "";
    private $wpPrefix                           = "";
    private $prefix                             = "";
    private $networkEnabled                     = false;
    private $shortcode                          = "";
    private $textDomain                         = "";
    private $demoWP_UserLogin                   = "";

    // Extension params
    private $itemModelParam                     = "";
    private $orderCodeParam                     = "";

    // Extension display values
    private $itemModelDisplayValue              = "";
    private $itemModelsDisplayValue             = "";
    private $itemModelPricesDisplayValue        = "";
    private $itemModelsAvailabilityDisplayValue = "";
    private $changeOrderDisplayValue            = "";

    // Extension prefixes
    private $wpUsernamePrefix                   = "";
    private $itemModelSKU_Prefix                = "";
    private $extraSKU_Prefix                    = "";
    private $locationUniqueIdentifierPrefix     = "";
    private $orderCodePrefix                    = "";
    private $paymentMethodCodePrefix            = "";

    // BB codes
    private $orderCodeBBCode                    = "";
    private $changeOrderURL_BBCode              = "";

    // Paths
    private $wpPluginsPath                      = "";
    private $pluginPathWithFilename             = "";
    private $pluginPath                         = "";
    private $pluginBasename                     = "";
    private $pluginFolderName                   = "";
    private $librariesPath                      = "";
    private $localLangPath                      = "";
    private $globalLangPath                     = "";
    private $localCommonLangPath                = "";
    private $localExtLangRelPath                = "";
    private $localExtLangPath                   = "";
    private $globalPluginLangPath               = "";
    private $globalCommonLangPath               = "";
    private $globalExtLangPath                  = "";

    // URLs
    private $pluginURL                          = "";

    /**
     * @param \wpdb $paramWPDB
     * @param int $paramBlogId
     * @param string $paramRequiredPHP_Version
     * @param string $paramCurrentPHP_Version
     * @param float $paramRequiredWP_Version
     * @param float $paramCurrentWP_Version
     * @param string $paramOldestCompatiblePluginSemver
     * @param string $paramPluginSemver
     * @param string $paramPluginPathWithFilename
     * @param array $params
     */
    public function __construct(
        \wpdb &$paramWPDB, $paramBlogId, $paramRequiredPHP_Version, $paramCurrentPHP_Version, $paramRequiredWP_Version,
        $paramCurrentWP_Version, $paramOldestCompatiblePluginSemver, $paramPluginSemver, $paramPluginPathWithFilename, array $params
    ) {
        // Makes sure the plugin is defined before trying to use it, because by default it is available only for admin section
        if(!function_exists('is_plugin_active_for_network'))
        {
            require_once ABSPATH.'/wp-admin/includes/plugin.php';
        }

        $this->internalWPDB = $paramWPDB;
        $this->blogId = absint($paramBlogId);

        $this->requiredPHP_Version              = !is_array($paramRequiredPHP_Version) ? preg_replace('[^0-9\.,]', '', $paramRequiredPHP_Version) : '5.4.0';
        $this->currentPHP_Version               = !is_array($paramCurrentPHP_Version) ? preg_replace('[^0-9\.,]', '', $paramCurrentPHP_Version) : '0.0.0';
        $this->requiredWP_Version               = !is_array($paramRequiredWP_Version) ? preg_replace('[^0-9\.,]', '', $paramRequiredWP_Version) : 4.6;
        $this->currentWP_Version                = !is_array($paramCurrentWP_Version) ? preg_replace('[^0-9\.,]', '', $paramCurrentWP_Version) : 0.0;
        $this->oldestCompatiblePluginSemver     = !is_array($paramOldestCompatiblePluginSemver) ? preg_replace('[^0-9A-Za-z-\+\.]', '', $paramOldestCompatiblePluginSemver) : '0.0.0';
        $this->pluginSemver                     = !is_array($paramPluginSemver) ? preg_replace('[^0-9A-Za-z-\+\.]', '', $paramPluginSemver) : '0.0.0';

        // We must use plugin_basename here, despite that we used full path for activation hook, because in database the plugin is still saved UNIX like:
        // network_db_prefix_options:
        //      Row: active_plugins
        //      Value (in JSON): <..>;i:0;s:32:"FleetManagement/CarRentalSystem.php";<..>
        $this->networkEnabled                   = is_plugin_active_for_network(plugin_basename($paramPluginPathWithFilename));
        $this->pluginPrefix                     = isset($params['plugin_prefix']) ? sanitize_key($params['plugin_prefix']) : '';
        $this->pluginHandlePrefix               = isset($params['plugin_handle_prefix']) ? sanitize_key($params['plugin_handle_prefix']) : '';
        $this->pluginCSS_Prefix                 = isset($params['plugin_css_prefix']) ? sanitize_key($params['plugin_css_prefix']) : '';

        if(isset($params['gallery_folder_name']) && !is_array($params['gallery_folder_name']))
        {
            // No sanitization, uppercase needed
            $this->galleryFolderName            = preg_replace('[^\-_0-9a-zA-Z]', '', $params['gallery_folder_name']);
        } else
        {
            $this->galleryFolderName            = '';
        }

        // Extension gallery is always in one place, so it is static, and can be defined in the class constructor to safe resources later
        $uploadsDir = wp_upload_dir();
        if($this->galleryFolderName != "")
        {
            // This plugin has its own gallery folder
            $this->globalGalleryPath = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$this->galleryFolderName.DIRECTORY_SEPARATOR;
            $this->globalGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$this->galleryFolderName;
            $this->globalGalleryURL = $uploadsDir['baseurl'].'/'.$this->galleryFolderName.'/';
        } else
        {
            // Otherwise - either gallery is not needed, or we should use global gallery folder
            $this->globalGalleryPath = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR;
            $this->globalGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']);
            $this->globalGalleryURL = $uploadsDir['baseurl'].'/';
        }

        if(isset($params['theme_ui_folder_name']) && !is_array($params['theme_ui_folder_name']))
        {
            // No sanitization, uppercase chars needed
            $this->themeUI_FolderName           = preg_replace('[^_0-9a-zA-Z\-]', '', $params['theme_ui_folder_name']);

        } else
        {
            // No sanitization, uppercase chars needed
            $this->themeUI_FolderName           = 'UI';
        }
        // 0-12 chars long (WP core limitation)
        $this->postTypePrefix                   = isset($params['post_type_prefix']) ? substr(sanitize_key($params['ext_prefix']), 0, 12) : '';

        // Always capitalize, 1-20 chars long
        $this->extCode                          = isset($params['ext_code']) ? substr(strtoupper(sanitize_key($params['ext_code'])), 0, 20) : '';

        if(isset($params['ext_name']) && !is_array($params['ext_name']))
        {
            // No sanitization, uppercase chars and spaces needed
            $this->extName                      = preg_replace('[^_0-9a-zA-Z\- ]', '', $params['ext_name']);
        } else
        {
            $this->extName                      = '';
        }

        if(isset($params['ext_folder_name']) && !is_array($params['ext_folder_name']))
        {
            // No sanitization, uppercase chars needed
            $this->extFolderName                = preg_replace('[^_0-9a-zA-Z\-]', '', $params['ext_folder_name']);
        } else
        {
            // No sanitization, uppercase chars needed
            $this->extFolderName                = 'Common';
        }

        if(isset($params['ext_api_namespace']) && !is_array($params['ext_api_namespace']))
        {
            // No sanitization, uppercase chars needed, as well as slash is supported here
            $this->extAPI_Namespace          = preg_replace('[^_0-9a-zA-Z\-\/]', '', $params['ext_api_namespace']);
        } else
        {
            $this->extAPI_Namespace          = '';
        }

        $this->extPrefix                        = isset($params['ext_prefix']) ? sanitize_key($params['ext_prefix']) : '';
        $this->extURL_Prefix                    = isset($params['ext_url_prefix']) ? sanitize_key($params['ext_url_prefix']) : '';
        $this->extCSS_Prefix                    = isset($params['ext_css_prefix']) ? sanitize_key($params['ext_css_prefix']) : '';

        // We need this for multisite data for regular WordPress tables, i.e. 'posts'.
        $this->blogPrefix                       = $this->internalWPDB->get_blog_prefix($paramBlogId);
        // We don't use unique blog prefix here, as we want to all multisite to work, this means that all sites data should be under same blog id
        // So use internalWPDB->prefix here instead, as it automatically figures out for every site
        // NOTE: Appears that WordPress internalWPDB->prefix cannot figure out himself, so need to do that on our own
        if($this->networkEnabled)
        {
            // Plugin is network-enabled, so we use same blog id for all sites
            // NOTE: 'BLOG_ID_CURRENT_SITE' should be always defined in multisite mode, but we do this check here for 'just in case'
            $networkBlogId                      = defined('BLOG_ID_CURRENT_SITE') ? BLOG_ID_CURRENT_SITE : 1;
            $this->prefix                       = $this->internalWPDB->get_blog_prefix($networkBlogId).$this->extPrefix;
            $this->wpPrefix                     = $this->internalWPDB->get_blog_prefix($networkBlogId);
        } else
        {
            // Plugin is locally-enabled, so we use same blog id of current site
            $this->prefix                       = $this->internalWPDB->prefix.$this->extPrefix;
            $this->wpPrefix                     = $this->internalWPDB->prefix;
        }

        $this->shortcode                        = isset($params['shortcode']) ? sanitize_key($params['shortcode']) : '';
        $this->textDomain                       = isset($params['text_domain']) ? sanitize_key($params['text_domain']) : '';

        if(isset($params['demo_wp_user_login']) && !is_array($params['demo_wp_user_login']))
        {
            // Only lowercase alpha-numeric chars and underscores allowed
            $this->demoWP_UserLogin             = preg_replace('[^_0-9a-z]', '', $params['demo_wp_user_login']);
        } else
        {
            $this->demoWP_UserLogin             = 'demo';
        }


        /* ------------------------------------------------------------------------------------------------------- */
        /* Extension params                                                                                            */
        /* ------------------------------------------------------------------------------------------------------- */
        $this->itemModelParam                   = isset($params['item_model_param']) ? sanitize_key($params['item_model_param']) : '';
        $this->orderCodeParam                   = isset($params['order_code_param']) ? sanitize_key($params['order_code_param']) : '';


        /* ------------------------------------------------------------------------------------------------------- */
        /* Extension display values                                                                                            */
        /* ------------------------------------------------------------------------------------------------------- */
        $this->itemModelDisplayValue                = isset($params['item_model_display_value']) ? sanitize_key($params['item_model_display_value']) : '';
        $this->itemModelsDisplayValue               = isset($params['item_models_display_value']) ? sanitize_key($params['item_models_display_value']) : '';
        $this->itemModelPricesDisplayValue          = isset($params['item_model_prices_display_value']) ? sanitize_key($params['item_model_prices_display_value']) : '';
        $this->itemModelsAvailabilityDisplayValue   = isset($params['item_models_availability_display_value']) ? sanitize_key($params['item_models_availability_display_value']) : '';
        $this->changeOrderDisplayValue              = isset($params['change_order_display_value']) ? sanitize_key($params['change_order_display_value']) : '';


        /* ------------------------------------------------------------------------------------------------------- */
        /* Extension prefixes                                                                                          */
        /* ------------------------------------------------------------------------------------------------------- */
        // Always capitalize, 20-11 = 9
        $this->wpUsernamePrefix                 = isset($params['wp_username_prefix']) ? substr(strtoupper(sanitize_key($params['wp_username_prefix'])),0, 9) : '';

        // Always capitalize, 20-11 = 9
        $this->itemModelSKU_Prefix              = isset($params['item_model_sku_prefix']) ? substr(strtoupper(sanitize_key($params['item_model_sku_prefix'])),0, 9) : '';

        // Always capitalize, 20-11 = 9
        $this->extraSKU_Prefix                  = isset($params['extra_sku_prefix']) ? substr(strtoupper(sanitize_key($params['extra_sku_prefix'])),0, 9) : '';

        // Always capitalize, 20-11 = 9
        $this->locationUniqueIdentifierPrefix   = isset($params['location_unique_identifier_prefix']) ? substr(strtoupper(sanitize_key($params['location_unique_identifier_prefix'])),0, 9) : '';

        // Always capitalize, 20-(11+6) = 3 (!)
        $this->orderCodePrefix                  = isset($params['order_code_prefix']) ? substr(strtoupper(sanitize_key($params['order_code_prefix'])),0, 3) : '';

        // Always capitalize, 20-11 = 9
        $this->paymentMethodCodePrefix          = isset($params['payment_method_code_prefix']) ? substr(strtoupper(sanitize_key($params['payment_method_code_prefix'])),0, 9) : '';


        /* ------------------------------------------------------------------------------------------------------- */
        /* BB codes                                                                                                */
        /* ------------------------------------------------------------------------------------------------------- */
        $this->orderCodeBBCode                  = isset($params['order_code_bbcode']) ? strtoupper(sanitize_key($params['order_code_bbcode'])) : '';
        $this->changeOrderURL_BBCode             = isset($params['change_order_url_bbcode']) ? strtoupper(sanitize_key($params['change_order_url_bbcode'])) : '';


        /* ------------------------------------------------------------------------------------------------------- */
        /* Paths                                                                                                   */
        /* ------------------------------------------------------------------------------------------------------- */

        // Global Settings
        // Note 1: It's ok to use 'sanitize_text_field' function here,
        //       because this function does not escape or remove the '/' char in path.
        // Note 2: We use __FILE__ to make sure that we are not dependant on plugin folder name
        // Note 3: WordPress constants overview - http://wpengineer.com/2382/wordpress-constants-overview/
        // Demo examples (__FILE__ = $this->pluginPathWithFilename):
        // 1. __FILE__ => /GitHub/<REPOSITORY_NAME>/wp-content/plugins/FleetManagement/CarRentalSystem.php
        // 2. dirname(__FILE__, 1) => /GitHub/<REPOSITORY_NAME>/wp-content/plugins/FleetManagement/ [PHP7+]
        // 3. dirname(__FILE__, 2) => /GitHub/<REPOSITORY_NAME>/wp-content/plugins/ [PHP7+]
        // 4. plugin_dir_path(__FILE__) => /GitHub/<REPOSITORY_NAME>/wp-content/plugins/FleetManagement/ (with trailing slash at the end)
        // 5. plugin_basename(__FILE__) => FleetManagement/CarRentalSystem.php (used for active plugins list in WP database)
        // 6. dirname(plugin_basename((__FILE__)) => FleetManagement
        // 7. basename($this->pluginPath) => FleetManagement
        // 8. localLangRelPath used for load_textdomain, i.e. FleetManagement/Languages/CarRental/ (the correct example is WITH the ending trailing slash)
        $this->pluginPathWithFilename = sanitize_text_field($paramPluginPathWithFilename); // Leave directory separator UNIX like here, used in WP hooks

        // NOTE #1: The functions bellow must go after '$this->pluginPathWithFilename' retrieval
        // NOTE #2: WordPress 'wp_normalize_path(plugin_dir_path($this->pluginPathWithFilename))' would do the same as below,
        //       just it would always forward-slash the path (even in Windows Environment),
        //       and do not use DIRECTORY_SEPARATOR constant, that is always recommended to use
        // @see - https://stackoverflow.com/questions/26881333/when-to-use-directory-separator-in-php-code
        if(version_compare($this->currentPHP_Version, '7.0.0', '>='))
        {
            $this->pluginPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(dirname($this->pluginPathWithFilename, 1), '/\\').DIRECTORY_SEPARATOR);
            $this->wpPluginsPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(dirname($this->pluginPathWithFilename, 2), '/\\').DIRECTORY_SEPARATOR);
        } else
        {
            // PHP 5.6 backwards compatibility
            $this->pluginPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(php7_dirname($this->pluginPathWithFilename, 1), '/\\').DIRECTORY_SEPARATOR);
            $this->wpPluginsPath = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), rtrim(php7_dirname($this->pluginPathWithFilename, 2), '/\\').DIRECTORY_SEPARATOR);
        }

        // Leave directory separator UNIX like here, used in WP database
        // Note: It is mostly used for add_filter calls and comparisons of plugin basename saved in WP options db table
        $this->pluginBasename = plugin_basename($this->pluginPathWithFilename);

        // Basename - Returns only the folder name of the path (or filename, if the filename is given)
        $this->pluginFolderName = basename($this->pluginPath);
        $this->librariesPath = $this->pluginPath.'Libraries'.DIRECTORY_SEPARATOR;
        $this->localLangPath = $this->pluginPath.'Languages'.DIRECTORY_SEPARATOR;
        $this->localCommonLangPath = $this->pluginPath.'Languages'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;
        $this->localExtLangRelPath = $this->pluginFolderName.'/Languages/'.$this->extFolderName; // No slash at the end (!)
        $this->localExtLangPath = $this->pluginPath.'Languages'.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;
        $wpLangDir = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),WP_LANG_DIR);
        $this->globalLangPath = $wpLangDir.DIRECTORY_SEPARATOR;
        $this->globalPluginLangPath = $wpLangDir.DIRECTORY_SEPARATOR.$this->pluginFolderName.DIRECTORY_SEPARATOR;
        $this->globalCommonLangPath = $wpLangDir.DIRECTORY_SEPARATOR.$this->pluginFolderName.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR;
        $this->globalExtLangPath = $wpLangDir.DIRECTORY_SEPARATOR.$this->pluginFolderName.DIRECTORY_SEPARATOR.$this->extFolderName.DIRECTORY_SEPARATOR;


        /* ------------------------------------------------------------------------------------------------------- */
        /* URLs                                                                                                    */
        /* ------------------------------------------------------------------------------------------------------- */

        // esc_url replaces ' and & chars with &#39; and &amp; - but because we know that exact path,
        // we know it does not contains them, so we don't need to have two versions esc_url and esc_url_raw
        // Demo examples (__FILE__ = $this->pluginFolderAndFile):
        // 1. plugin_dir_url(__FILE__) => https://nativerental.com/wp-content/plugins/FleetManagement/
        $this->pluginURL = esc_url(plugin_dir_url($this->pluginPathWithFilename));


        // DEBUG
        if($this->debugMode == 1)
        {
            echo "<br />[Configuration] WP REST API Prefix: ".static::WP_REST_API_PREFIX."\n";
            echo "<br />[Configuration] Plugin Namespace: ".static::PLUGIN_NAMESPACE."\n";
            echo "<br />[Configuration] Blog Id: {$this->blogId}\n";
            echo "<br />[Configuration] Required PHP Version: {$this->requiredPHP_Version}\n";
            echo "<br />[Configuration] Current PHP Version: {$this->currentPHP_Version}\n";
            echo "<br />[Configuration] Required WP Version: {$this->requiredWP_Version}\n";
            echo "<br />[Configuration] Current WP Version: {$this->currentWP_Version}\n";
            echo "<br />[Configuration] Plugin Semver: {$this->pluginSemver}\n";
            echo "<br />[Configuration] Network Enabled: ".var_export($this->networkEnabled, true)."\n";
            echo "<br />[Configuration] Plugin Path With Filename: {$this->pluginPathWithFilename}\n";
            echo "<br />[Configuration] Plugin Path: {$this->pluginPath}\n";
            echo "<br />[Configuration] Plugin Basename: {$this->pluginBasename}\n";
            echo "<br />[Configuration] Plugin Folder Name: {$this->pluginFolderName}\n";
            echo "<br />[Configuration] Local Lang Path: {$this->localLangPath}\n";
            echo "<br />[Configuration] Global Lang Path: {$this->globalLangPath}\n";
            echo "<br />[Configuration] Local Ext Lang Rel Path: {$this->localExtLangRelPath}\n";
            echo "<br />[Configuration] Local Ext Lang Path: {$this->localExtLangPath}\n";
            echo "<br />[Configuration] Global Plugin Lang Path: {$this->globalPluginLangPath}\n";
            echo "<br />[Configuration] Global Ext Lang Path: {$this->globalExtLangPath}\n";
            echo "<br />[Configuration] Plugin URL: {$this->pluginURL}\n";
        }
    }

    /**
     * This is late state (setter) dependency injection. We need to make sure that routing is always set
     * We can use for that either Dependency injection container
     * Or to use try{} catch{} statements for null. Because we need only one variable here,
     * we choose to go with exception handling scenario
     * @see #1 https://codeinphp.github.io/post/dependency-injection-in-php/
     * @see #2 http://krasimirtsonev.com/blog/article/Dependency-Injection-in-PHP-example-how-to-DI-create-your-own-dependency-injection-container
     * @param RoutingInterface $routing
     * @return void
     */
    public function setRouting(RoutingInterface $routing)
    {
        $this->routing = $routing;
    }

    /**
     * @return null|RoutingInterface
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * @return \wpdb
     */
    public function getInternalWPDB()
    {
        return $this->internalWPDB;
    }

    public function getBlogId()
    {
        return $this->blogId;
    }

    /**
     * Get's blog locale for early calls AS get_locale() is not allowed to process in install process
     *
     * @param int $paramBlogId
     * @return string
     */
    public function getBlogLocale($paramBlogId = -1)
    {
        if($paramBlogId == -1)
        {
            // Skip blog id overriding
            $validBlogPrefix = $this->blogPrefix;
        } else
        {
            $validBlogPrefix = $this->internalWPDB->get_blog_prefix($paramBlogId);
        }
        // A workaround, that does a direct call to WP table
        $sqlQuery = "SELECT option_value FROM `{$validBlogPrefix}options` WHERE option_name='WPLANG'";
        $blogLocaleResult = $this->internalWPDB->get_var($sqlQuery);
        $blogLocale = !is_null($blogLocaleResult) && $blogLocaleResult != '' ? $blogLocaleResult : 'en_US';

        // DEBUG
        if($this->debugMode == 1)
        {
            echo "<br />[Configuration] BLOG ID:".intval($paramBlogId).", LOCALE (STATIC FOR INSTALL): ".get_locale().", locale via WPLANG from DB: ".$blogLocale."\n";
        }

        return $blogLocale;
    }

    public function getRequiredPHP_Version()
    {
        return $this->requiredPHP_Version;
    }

    public function getCurrentPHP_Version()
    {
        return $this->currentPHP_Version;
    }

    public function getRequiredWP_Version()
    {
        return $this->requiredWP_Version;
    }

    public function getCurrentWP_Version()
    {
        return $this->currentWP_Version;
    }

    public function getOldestCompatiblePluginSemver()
    {
        return $this->oldestCompatiblePluginSemver;
    }

    public function getPluginSemver()
    {
        return $this->pluginSemver;
    }

    public function isNetworkEnabled()
    {
        return $this->networkEnabled;
    }

    public function getPluginPrefix()
    {
        return $this->pluginPrefix;
    }

    public function getPluginHandlePrefix()
    {
        return $this->pluginHandlePrefix;
    }

    public function getPluginCSS_Prefix()
    {
        return $this->pluginCSS_Prefix;
    }

    public function getGalleryFolderName()
    {
        return $this->galleryFolderName;
    }

    public function getGlobalGalleryPath()
    {
        return $this->globalGalleryPath;
    }

    public function getGlobalGalleryPathWithoutEndSlash()
    {
        return $this->globalGalleryPathWithoutEndSlash;
    }

    public function getGlobalGalleryURL()
    {
        return $this->globalGalleryURL;
    }

    public function getThemeUI_FolderName()
    {
        return $this->themeUI_FolderName;
    }

    public function getPostTypePrefix()
    {
        return $this->postTypePrefix;
    }

    public function getExtCode()
    {
        return $this->extCode;
    }

    public function getExtName()
    {
        return $this->extName;
    }

    public function getExtFolderName()
    {
        return $this->extFolderName;
    }

    public function getExtAPI_Namespace()
    {
        return $this->extAPI_Namespace;
    }

    public function getExtPrefix()
    {
        return $this->extPrefix;
    }

    public function getExtURL_Prefix()
    {
        return $this->extURL_Prefix;
    }

    public function getExtCSS_Prefix()
    {
        return $this->extCSS_Prefix;
    }

    /**
     * @note - Differently to plugin full prefix, the blog prefix may be different for sites, as pages can be inserted in different _posts tables
     * @param int $paramBlogId
     * @return string
     */
    public function getBlogPrefix($paramBlogId = -1)
    {
        if($paramBlogId == -1)
        {
            // Skip blog id overriding
            return $this->blogPrefix;
        } else
        {
            return $this->internalWPDB->get_blog_prefix($paramBlogId);
        }
    }

    /**
     * @note - we never use blog_id param here, as the prefix for the site is always the same - despite even if it is multisite and plugin is network enabled
     * @return string
     */
    public function getWP_Prefix()
    {
        return $this->wpPrefix;
    }

    /**
     * @note - we never use blog_id param here, as the prefix for the site is always the same - despite even if it is multisite and plugin is network enabled
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getShortcode()
    {
        return $this->shortcode;
    }

    public function getTextDomain()
    {
        return $this->textDomain;
    }

    public function getDemoWP_UserLogin()
    {
        return $this->demoWP_UserLogin;
    }


    /* ------------------------------------------------------------------------------------------------------- */
    /* Extension param methods                                                                                 */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getItemModelParam()
    {
        return $this->itemModelParam;
    }

    public function getOrderCodeParam()
    {
        return $this->orderCodeParam;
    }

    /* ------------------------------------------------------------------------------------------------------- */
    /* Extension display value methods                                                                         */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getItemModelDisplayValue()
    {
        return $this->itemModelDisplayValue;
    }

    public function getItemModelsDisplayValue()
    {
        return $this->itemModelsDisplayValue;
    }

    public function getItemModelPricesDisplayValue()
    {
        return $this->itemModelPricesDisplayValue;
    }

    public function getItemModelsAvailabilityDisplayValue()
    {
        return $this->itemModelsAvailabilityDisplayValue;
    }

    public function getChangeOrderDisplayValue()
    {
        return $this->changeOrderDisplayValue;
    }


    /* ------------------------------------------------------------------------------------------------------- */
    /* Extension prefix methods                                                                                    */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getWP_UsernamePrefix()
    {
        return $this->wpUsernamePrefix;
    }

    public function getItemModelSKU_Prefix()
    {
        return $this->itemModelSKU_Prefix;
    }

    public function getExtraSKU_Prefix()
    {
        return $this->extraSKU_Prefix;
    }

    public function getLocationUniqueIdentifierPrefix()
    {
        return $this->locationUniqueIdentifierPrefix;
    }

    public function getOrderCodePrefix()
    {
        return $this->orderCodePrefix;
    }

    public function getPaymentMethodCodePrefix()
    {
        return $this->paymentMethodCodePrefix;
    }


    /* ------------------------------------------------------------------------------------------------------- */
    /* BB code methods                                                                                         */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getOrderCodeBBCode()
    {
        return $this->orderCodeBBCode;
    }

    public function getChangeOrderURL_BBCode()
    {
        return $this->changeOrderURL_BBCode;
    }


    /* ------------------------------------------------------------------------------------------------------- */
    /* Path methods                                                                                            */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getWP_PluginsPath()
    {
        return $this->wpPluginsPath;
    }

    public function getPluginPathWithFilename()
    {
        return $this->pluginPathWithFilename;
    }

    public function getPluginPath()
    {
        return $this->pluginPath;
    }

    public function getPluginBasename()
    {
        return $this->pluginBasename;
    }

    public function getPluginFolderName()
    {
        return $this->pluginFolderName;
    }

    public function getLibrariesPath()
    {
        return $this->librariesPath;
    }

    public function getLocalLangPath()
    {
        return $this->localLangPath;
    }

    public function getGlobalLangPath()
    {
        return $this->globalLangPath;
    }

    public function getLocalCommonLangPath()
    {
        return $this->localCommonLangPath;
    }

    /**
     * localExtLangRelPath used for load_textdomain (without slash at the end), i.e. FleetManagement/Languages/<EXT_FOLDER_NAME>
     * @note - Do not use DIRECTORY_SEPARATOR for this file, as it used for WP-TEXT-DOMAIN definition and always should be the same
     * @return string
     */
    public function getLocalExtLangRelPath()
    {
        return $this->localExtLangRelPath;
    }

    public function getLocalExtLangPath()
    {
        return $this->localExtLangPath;
    }

    public function getGlobalPluginLangPath()
    {
        return $this->globalPluginLangPath;
    }

    public function getGlobalCommonLangPath()
    {
        return $this->globalCommonLangPath;
    }

    public function getGlobalExtLangPath()
    {
        return $this->globalExtLangPath;
    }


    /* ------------------------------------------------------------------------------------------------------- */
    /* URL methods                                                                                             */
    /* ------------------------------------------------------------------------------------------------------- */

    public function getPluginURL()
    {
        return $this->pluginURL;
    }
}