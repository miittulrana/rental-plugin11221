<?php
/**
 * Main controller
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @description This file is the main entry point to the plugin that will handle all requests from WordPress
 * and add actions, filters, etc. as necessary. So we simply declare the class and add a constructor.
 * @note 1: In this class we use full qualifiers (without 'use', except for Configuration, which is already included).
 *          We do this, to ensure, that nobody will try to use any of these classes before the autoloader is called.
 * @note 2: This class must not depend on any static model
 * @note 3: All Controllers and Models should have full path in the class
 * @note 4: Fatal errors on this file cannot be translated
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers;
// This class file was statically included, so that's why we can use here the keyword 'use'.
// The rest class files are loaded dynamically, and SHOULD NOT be listed bellow with keyword 'use' for code quality reasons.
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Load\AutoLoad;
use FleetManagement\Models\Validation\StaticValidator;

final class MainController
{
    // Because loading of language text is not allowed in the very early time, we use constants to simulate language text behavior, just the text is English
    const LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT = 'Error in __clone() method: Cloning instances of the class in the Fleet Management is forbidden.';
    const LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT = 'Error in __wakeup() method: Unserializing instances of the class in the Fleet Management is forbidden.';
    const LANG_ERROR_SESSIONS_ARE_DISABLED_IN_SERVER_TEXT = 'Warning: Sessions are disabled in your server configuration. Please enabled sessions. As a slower & less secure workaround you can use virtual session via cookies, but that is not recommended.';
    const LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT = 'Sorry, %s requires PHP %s or higher. Your current PHP version is %s. Please upgrade your server PHP version.';
    const LANG_ERROR_PLEASE_UPGRADE_WP_TEXT = 'Sorry, %s requires WordPress %s or higher. Your current WordPress version is %s. Please upgrade your WordPress setup.';
    const LANG_ERROR_UNKNOWN_NAME_TEXT = 'Unknown name';
    const LANG_ERROR_SPL_AUTOLOAD_REGISTRATION_FAILED_TEXT = 'SPL autoload registration failed!';
    const LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT = 'Dependencies are not loaded!';
    const LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT = '$confWithoutRouting is null!';
    const LANG_ERROR_CONF_IS_NULL_TEXT = '$conf is null!';
    const LANG_ERROR_LANG_IS_NULL_TEXT = '$lang is null!';
    const LANG_ERROR_IN_S_METHOD_S_TEXT = 'Error in \'%s\' method: %s!';

    // Configuration object reference
    private $confWithoutRouting         = null;
    private $conf                       = null;
    private $lang                       = null;
    private $expectedLocale             = ""; // Expected locale
    private $canProcess                 = false;
    private static $dependenciesLoaded  = false;
    // DEBUG NOTE: Make sure that WP_DEBUG is enabled and 'debug.log' file is creatable in plugin's folder

    /**
     * NOTE: Here we must NOT support passing by reference, as it comes from static object
     * @param ConfigurationInterface $paramConfWithoutRouting
     * @throws \Exception
     */
    public function __construct(ConfigurationInterface $paramConfWithoutRouting)
    {
        // This is very important to set it here
        $this->canProcess = true;

        if(StaticValidator::wpDebugLog())
        {
            $this->logToFile(__CLASS__ ."::". __FUNCTION__ .": Constructor loaded\n");
        }

        // We assign it to variable to avoid passing by reference warning for non-variables
        $this->confWithoutRouting = $paramConfWithoutRouting;

        //
        // 1. Check plug-in requirements - if not passed, then exit
        //
        if(is_null($this->confWithoutRouting))
        {
            // $confWithoutRouting is not null
            add_action('admin_notices', array($this, 'displayConfWithoutRoutingIsNullNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logConfWithoutRoutingIsNullNotice(__CLASS__ ."::". __FUNCTION__);
            }
            $this->canProcess = false;
        }
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentPHP_Version(), $this->confWithoutRouting->getRequiredPHP_Version(), '>=') === false)
        {
            // PHP version does not meet plugin requirements
            add_action('admin_notices', array($this, 'displayPHP_VersionRequirementNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logPHP_VersionRequirementNotice(__CLASS__ ."::". __FUNCTION__);
            }
            $this->canProcess = false;
        }
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentWP_Version(), $this->confWithoutRouting->getRequiredWP_Version(), '>=') === false)
        {
            // WordPress version does not meet plugin requirements
            add_action('admin_notices', array($this, 'displayWP_VersionRequirementNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logWP_VersionRequirementNotice(__CLASS__ ."::". __FUNCTION__);
            }
            $this->canProcess = false;
        }

        //
        // 2. Load dependencies. Autoloader. This must be in constructor to know the file paths.
        // NOTE: Singleton pattern used.
        //
        if($this->canProcess && static::$dependenciesLoaded === false)
        {
            // Load dependencies
            try
            {
                $objAutoload = new AutoLoad($this->confWithoutRouting);
                spl_autoload_register(array($objAutoload, 'includeClassFile'));
                static::$dependenciesLoaded = true;
            } catch (\Exception $e)
            {
                // SPL Autoload registration failed
                add_action('admin_notices', array($this, 'displaySPL_AutoloadRegistrationFailedNotice'));
                if(StaticValidator::wpDebugLog())
                {
                    $this->logSPL_AutoloadRegistrationFailedNotice(__CLASS__ ."::". __FUNCTION__);
                }
                $this->canProcess = false;
            }
        } else if($this->canProcess === false && static::$dependenciesLoaded === false)
        {
            // NOTE: Both 'IF' params are mandatory here for testing
            // Nor we can process, nor Dependencies are not loaded!
            add_action('admin_notices', array($this, 'displayDependenciesAreNotLoadedNotice'));
            if(StaticValidator::wpDebugLog())
            {
                $this->logDependenciesAreNotLoadedNotice(__CLASS__ ."::". __FUNCTION__);
            }
        }

        //
        // 3. Activation Hooks
        //
        // ATTENTION: This is *only* done during plugin activation hook!
        // NOTE #1: Initialize the two lines bellow for every extension!
        // NOTE #2: Only check here for routing definition, nothing else, or install will crash
        if(!is_null($this->confWithoutRouting))
        {
            register_activation_hook($this->confWithoutRouting->getPluginPathWithFilename(), array($this, 'networkOrSingleActivate'));
            register_deactivation_hook($this->confWithoutRouting->getPluginPathWithFilename(), array($this, 'networkDeactivate'));
            add_filter('network_admin_plugin_action_links_'.$this->confWithoutRouting->getPluginBasename(), array($this, 'modifyNetworkActionLinks'));
            add_filter('plugin_action_links_'.$this->confWithoutRouting->getPluginBasename(), array($this, 'modifyActionLinks'));
            // Add links bellow plugin description
            add_filter('plugin_row_meta', array($this, 'modifyInfoLinks'), 10, 2);
        }
    }

    /**
     * Note: Do not add try {} catch {} for this block, as this method includes WordPress hooks.
     *   For those hooks handling we have individual methods in this class bellow, where the try {} catch {} is used.
     */
    public function run()
    {
        if($this->canProcess)
        {
            //
            // 4. API Hooks
            //
            add_action('parse_request', array($this, 'frontEndAPI_Callback'), 0);

            //
            // 5. Admin / Network Admin page hooks
            //
            // Check whether the current request is for an administrative interface page, and check if we not doing admin ajax
            // More at: https://codex.wordpress.org/AJAX_in_Plugins
            if(is_admin() || is_network_admin())
            {
                // Add network admin menu items
                add_action('network_admin_menu', array($this, 'loadNetworkAdmin'));
                // Remove admin footer text
                add_filter('admin_footer_text', array($this, 'removeAdminFooterText'));
                // Remove network admin footer text
                add_filter('network_admin_menu', array($this, 'removeAdminFooterVersion'));
            }
            if(is_admin())
            {
                // Add network / regular admin menu items
                add_action('admin_menu', array($this, 'loadAdmin'));
                // Remove admin footer text
                add_filter('admin_footer_text', array($this, 'removeAdminFooterText'));
                // Remove admin footer text
                add_filter('admin_menu', array($this, 'removeAdminFooterVersion'));

                // Admin AJAX must be inside is_admin() if case to prevent security risks
                // of non-admins running admin ajax queries and getting results
                add_action('wp_ajax_'.$this->confWithoutRouting->getExtPrefix().'admin_api', array($this, 'adminAPI_Callback'));
            }

            //
            // 6. New site insertion hook
            // After WP 5.1.0 'wp_insert_site' (before was 'wpmu_new_blog') is an action triggered whenever a new blog is created within a multisite network
            //
            add_action( 'wp_insert_site', array($this, 'newSiteAdded'), 10, 6);

            //
            // 7. Site deletion hook (fired every time when new blog is deleted in multisite)
            // More: https://rudrastyh.com/wordpress-multisite/delete-blog-deprecated.html
            // (old) More: https://developer.wordpress.org/reference/hooks/delete_blog/
            // More: http://wordpress.stackexchange.com/questions/82961/perform-action-on-wpmu-blog-deletion
            // More: https://codex.wordpress.org/Plugin_API/Action_Reference/delete_blog
            // More: http://wordpress.stackexchange.com/questions/130462/is-there-a-hook-or-a-function-for-multisite-blog-deactivate-or-delete
            // Should be replaced by 'wpmu_delete_blog' (since WP 4.8 https://wpseek.com/function/wpmu_delete_blog/ )
            // https://developer.wordpress.org/reference/hooks/delete_blog/
            // Fires before a site is deleted.
            //
            add_action('wp_uninitialize_site', array($this, 'siteDeleted'), 10, 6);

            //
            // 8. Run on init - internationalization, custom post type and visitor session registration
            //
            add_action('init', array($this, 'runOnInit'), 0);
        }
    }

    /**
     * Modify network links
     * @param array $paramExistingNetworkActionLinks
     * @return array
     */
    public function modifyNetworkActionLinks(array $paramExistingNetworkActionLinks)
    {
        $modifiedNetworkActionLinks = array();
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // NOTE: These IF/ELSE statements should be here to clearly separate the process from model
                $objStatus = new \FleetManagement\Models\Status\NetworkStatus($conf, $lang);
                $additionalLinks = $objStatus->getAdditionalActionLinks();

                // Appends additional links to the existing network action links
                $modifiedNetworkActionLinks = array_merge($paramExistingNetworkActionLinks, $additionalLinks);
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $modifiedNetworkActionLinks;
    }

    /**
     * Modify locally-enabled plugin links
     * @param array $paramExistingActionLinks
     * @return array
     */
    public function modifyActionLinks(array $paramExistingActionLinks)
    {
        $modifiedActionLinks = array();

        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instance
                $objStatus = new \FleetManagement\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());

                $additionalLinks = $objStatus->getActionLinks();

                // Appends additional links to the existing action links
                $modifiedActionLinks = array_merge($paramExistingActionLinks, $additionalLinks);
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $modifiedActionLinks;
    }

    /**
     * Modify info links next to plugin description
     * @param array $paramExistingInfoLinks
     * @param string $paramPluginBasename
     * @return array
     */
    public function modifyInfoLinks(array $paramExistingInfoLinks, $paramPluginBasename)
    {
        $modifiedInfoLinks = array();

        if($this->canProcess && $paramPluginBasename == $this->confWithoutRouting->getPluginBasename())
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Show additional info links only if the plugin is locally enabled
                if(is_network_admin())
                {
                    // Create mandatory instance
                    $objStatus = new \FleetManagement\Models\Status\NetworkStatus($conf, $lang);

                    // Get additional links to be displayed next to network plugin description
                    $additionalLinks = $objStatus->getInfoLinks();
                } else
                {
                    // Create mandatory instance
                    $objStatus = new \FleetManagement\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());

                    // Get additional links to be displayed next to local plugin description
                    $additionalLinks = $objStatus->getInfoLinks();
                }

                // Appends additional links to the existing info links
                $modifiedInfoLinks = array_merge($paramExistingInfoLinks, $additionalLinks);
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $modifiedInfoLinks;
    }

    /**
     * Activate (enable+install or enable only) plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     * @param bool $networkWideActivation - if the activation is 'network enabled' or 'locally enabled' (even if multisite is enabled)
     */
    public function networkOrSingleActivate($networkWideActivation)
    {
        // NOTE: Temporary workaround while #36406 WordPress bug will be fixed
        //       Read more at https://core.trac.wordpress.org/ticket/36406
        $requestComingFromNetworkAdmin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "/network") !== false ? true : false;
        if(is_multisite() && ($networkWideActivation || $requestComingFromNetworkAdmin))
        {
            // A workaround until WP will get fixed
            // SHOULD be 'networkActivate' but WordPress does not yet support that feature,
            // so this means as long as the 'MULTISITE' constant is defined in wp-config, we use that method

            // LOCAL DEBUG
            // trigger_error('Network wide activation (referer: '.$_SERVER['HTTP_REFERER'].').', E_USER_ERROR);

            $this->multisiteActivate();
        } else
        {
            // A workaround until WP will get fixed

            // LOCAL DEBUG
            // trigger_error('Regular activation (non-multisite or multisite\'s local activation, referer: '.$_SERVER['HTTP_REFERER'].').', E_USER_ERROR);

            $this->activate();
        }
    }

    /**
     * Process the plugin activation requirements
     * @throws \Exception
     */
    private function processActivationRequirements()
    {
        // Check PHP version
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentPHP_Version(), $this->confWithoutRouting->getRequiredPHP_Version(), '>=') === false)
        {
            // WordPress version does not meet plugin requirements
            $errorMessage = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                $this->confWithoutRouting->getExtName(), $this->confWithoutRouting->getRequiredPHP_Version(), $this->confWithoutRouting->getCurrentPHP_Version()
            );
            throw new \Exception($errorMessage);
        }

        // Check WordPress version
        // Note - we don't need to check here for function 'get_sites' or class 'WP_Site_Query' as it is related to WP version check, and them were introduced in Wp 4.6+
        if(!is_null($this->confWithoutRouting) && version_compare($this->confWithoutRouting->getCurrentWP_Version(), $this->confWithoutRouting->getRequiredWP_Version(), '>=') === false)
        {
            // WordPress version does not meet plugin requirements
            $errorMessage = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                $this->confWithoutRouting->getExtName(), $this->confWithoutRouting->getRequiredWP_Version(), $this->confWithoutRouting->getCurrentWP_Version()
            );
            throw new \Exception($errorMessage);
        }
    }

    /**
     * Activate (enable+install or enable only) plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     */
    public function multisiteActivate()
    {
        try
        {
            $this->processActivationRequirements();

            // DEBUG: FOR INSTALLATION EXCEPTION TESTING PURPOSES, LETS RAISE A CRITICAL ERROR.
            // throw new \Exception('Multisite activation started. And now we are killing it');

            // Assign routing to conf, only if it is not yet assigned
            $conf = $this->conf();

            // Note: Don't move $lang to parameter below, or WordPress will generate an installation warning
            $lang = $this->i18n();

            if(is_null($conf))
            {
                throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
            }
            if(is_null($lang))
            {
                throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
            }

            // For network-install we only create tables, the rest is done by populating the data (for all blogs or for individual blog)
            $objInstaller = new \FleetManagement\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
            $objInstaller->setTables();
        } catch (\Exception $e)
        {
            if(StaticValidator::inWP_Debug())
            {
                // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                $error = sprintf(static::LANG_ERROR_IN_S_METHOD_S_TEXT, __FUNCTION__, $e->getMessage());
                trigger_error($error, E_USER_ERROR);
            }
        }

        // DEBUG: LAST CHANCE TO KILL THE NETWORK-INSTALL PROCESS AND SEE THE INSTALL OUTPUT
        // NOTE: It is ver useful for inspection if 'headers already sent' notice is seen after install
        // trigger_error('KILLED AT THE END OF NETWORK-ACTIVATE.', E_USER_ERROR);
    }

    public function activate()
    {
        try
        {
            $this->processActivationRequirements();

            // DEBUG: FOR INSTALLATION EXCEPTION TESTING PURPOSES, LETS RAISE A CRITICAL ERROR.
            // throw new \Exception('Single activation started. And now we are killing it');

            // Assign routing to conf, only if it is not yet assigned
            $conf = $this->conf();

            // Note: Don't move $lang to parameter below, or WordPress will generate an installation warning
            $lang = $this->i18n();

            if(is_null($conf))
            {
                throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
            }
            if(is_null($lang))
            {
                throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
            }

            // Install plugin for single site
            $objInstaller = new \FleetManagement\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
            // Install
            $objInstaller->setTables();
            $objInstaller->setCustomRoles();
            $objInstaller->setCustomCapabilities();
            $objInstaller->setCustomWP_RestAPI_Prefix();
            $objInstaller->setCustomPostTypes();
            // NOTE: This plugin does not use any custom taxonomies registration
            $objInstaller->setContent();
            $objInstaller->replaceResettableContent();
            $objInstaller->registerAllForTranslation();
        } catch (\Exception $e)
        {
            if(StaticValidator::inWP_Debug())
            {
                // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                $error = sprintf(static::LANG_ERROR_IN_S_METHOD_S_TEXT, __FUNCTION__, $e->getMessage());
                trigger_error($error, E_USER_ERROR);
            }
        }

        // DEBUG: LAST CHANCE TO KILL THE INSTALL PROCESS AND SEE THE INSTALL OUTPUT
        // NOTE: It is ver useful for inspection if 'headers already sent' notice is seen after install
        // trigger_error('KILLED AT THE END OF SINGLE ACTIVATE.', E_USER_ERROR);
    }

    /**
     * Deactivate plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     */
    public function networkDeactivate()
    {
        if($this->canProcess && is_multisite() && function_exists('get_sites') && class_exists('WP_Site_Query'))
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }

                $sites = get_sites();
                foreach ($sites AS $site)
                {
                    $blogId = $site->blog_id;
                    switch_to_blog($blogId);
                    flush_rewrite_rules();
                }

                // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
                switch_to_blog($conf->getBlogId());
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_S_METHOD_S_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        } else if($this->canProcess && is_multisite() === false)
        {
            // A workaround until WP will get fixed
            $this->deactivate();
        }
    }

    public function deactivate()
    {
        if($this->canProcess)
        {
            try
            {
                flush_rewrite_rules();
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_S_METHOD_S_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        }
    }

    /**
     * newSiteAdded is an action triggered whenever a new site is created within a multisite network
     * @mote1 - https://developer.wordpress.org/reference/functions/wp_insert_site/
     * @note2 - https://developer.wordpress.org/reference/hooks/wp_insert_site/
     * @note3 - https://developer.wordpress.org/reference/classes/wp_site/
     * @note4 - Before deprecated https://developer.wordpress.org/reference/hooks/wpmu_new_blog/
     * OLD PARAMS (Before WP 5.1):
     *          int $paramNewBlogId -  Blog ID
     *          int $paramUserId -  User ID
     *          string $paramDomain - Site domain
     *          string $paramPath - Site domain
     *          int $paramSiteId - Site ID. Only relevant on multi-network installs
     *          array $paramMeta -  Meta data. Used to set initial site options.
     * Example return of \WP_Site Object:
     *      (
     *          [blog_id] => 2
     *          [domain] => localhost
     *          [path] => /m2/sagres/
     *          [site_id] => 1
     *          [registered] => 2018-03-23 13:49:37
     *          [last_updated] => 2019-03-05 15:52:10
     *          [public] => 0
     *          [archived] => 0
     *          [mature] => 0
     *          [spam] => 0
     *          [deleted] => 0
     *          [lang_id] => 0
     *      )
     * @param \WP_Site $objNewSite - NewSite object
     */
    public function newSiteAdded(\WP_Site $objNewSite)
    {
        // Do nothing. Not used by this plugin. All data is added from each site individually if needed
    }

    /**
     * OLD PARAMS (Before WP 5.1):
     *      int $paramBlogIdToDelete Blog ID to delete
     *      bool $paramDropBlogTables True if blog's table should be dropped. Default is false.
     * Example return of \WP_Site Object:
     *      (
     *          [blog_id] => 2
     *          [domain] => localhost
     *          [path] => /m2/sagres/
     *          [site_id] => 1
     *          [registered] => 2018-03-23 13:49:37
     *          [last_updated] => 2019-03-05 15:52:10
     *          [public] => 0
     *          [archived] => 0
     *          [mature] => 0
     *          [spam] => 0
     *          [deleted] => 0
     *          [lang_id] => 0
     *      )
     * @param \WP_Site $objOldSite object
     */
    public function siteDeleted(\WP_Site $objOldSite)
    {
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }

                if($conf->isNetworkEnabled())
                {
                    $oldBlogId = $conf->getInternalWPDB()->blogid;
                    $siteIdToDelete = $objOldSite->id;
                    switch_to_blog($siteIdToDelete);

                    $lang = new \FleetManagement\Models\Language\Language(
                        $conf->getTextDomain(), $conf->getGlobalPluginLangPath(),
                        $conf->getLocalLangPath(), $conf->getExtFolderName(), get_locale(), false
                    );

                    if(is_null($lang))
                    {
                        throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                    }

                    // Delete the plugin data for across the whole network
                    $objUninstaller = new \FleetManagement\Controllers\Admin\InstallController($conf, $lang, $siteIdToDelete);
                    $objUninstaller->deleteContent();
                    $objUninstaller->removeCustomRoles();
                    $objUninstaller->removeCustomCapabilities();

                    switch_to_blog($oldBlogId);
                }
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_S_METHOD_S_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        }
    }

    public function uninstall()
    {
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                if($conf->isNetworkEnabled())
                {
                    $objNetworkUninstaller = new \FleetManagement\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
                    $sites = get_sites();
                    foreach ($sites AS $site)
                    {
                        $blogId = $site->blog_id;
                        switch_to_blog($blogId);

                        // Delete all content and uninstall for specific blog id
                        $objUninstaller = new \FleetManagement\Controllers\Admin\InstallController($conf, $lang, $blogId);
                        $objUninstaller->deleteContent();
                        $objUninstaller->removeCustomRoles();
                        $objUninstaller->removeCustomCapabilities();
                    }
                    // Drop the tables
                    // NOTE: things like 'network roles' are not used by the plugin
                    $objNetworkUninstaller->dropTables();

                    // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
                    switch_to_blog($conf->getBlogId());
                } else
                {
                    // Delete all content and uninstall
                    $objUninstaller = new \FleetManagement\Controllers\Admin\InstallController($conf, $lang, $conf->getBlogId());
                    $objUninstaller->deleteContent();
                    $objUninstaller->removeCustomRoles();
                    $objUninstaller->removeCustomCapabilities();
                    $objUninstaller->dropTables();
                }
            } catch (\Exception $e)
            {
                if(StaticValidator::inWP_Debug())
                {
                    // In WP activation we can kill the install only via 'trigger_error' with 'E_USER_ERROR' param
                    $error = sprintf(static::LANG_ERROR_IN_S_METHOD_S_TEXT, __FUNCTION__, $e->getMessage());
                    trigger_error($error, E_USER_ERROR);
                }
            }
        }
    }

    /**
     * This method handles request then generates response using WP_Ajax_Response or standard JSON
     */
    public function adminAPI_Callback()
    {
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();

                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                $objAdminAPI_Controller = new \FleetManagement\Controllers\Admin\API_Controller($conf, $lang);
                $processedSuccessfully = $objAdminAPI_Controller->handleAPI_Request();

                // If API request was processed successfully
                if($processedSuccessfully)
                {
                    // Then stop any further load of WordPress

                    // NOTE: Notice the use of  wp_die(), instead of die() or exit().
                    //       Most of the time you should be using wp_die() in your Ajax callback function.
                    //       This provides better integration with WordPress and makes it easier to test your code.
                    //       Don't forget to stop execution afterward:
                    //       This is required to terminate immediately and return a proper response
                    wp_die();
                }
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    /**
     * Sniff Requests
     * This is where we hijack all API requests
     * If $_REQUEST['__<EXT_API_NAMESPACE>'] is set, we kill WP and serve our data
     * die if API request
     */
    public function frontEndAPI_Callback()
    {
        try
        {
            // Assign routing to conf, only if it is not yet assigned
            $conf = $this->conf();

            if($this->canProcess && isset($_REQUEST[$conf->getExtAPI_Namespace()]) && $_REQUEST[$conf->getExtAPI_Namespace()] == 1)
            {
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                $objAPIController = new \FleetManagement\Controllers\Front\API_Controller($conf, $lang);
                $processedSuccessfully = $objAPIController->handleAPI_Request();

                // If API request was processed successfully
                if($processedSuccessfully)
                {
                    // Then stop any further load of WordPress
                    // NOTE: For front-end we don't use wp_die();
                    die();
                }
            }
        } catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

    public function loadNetworkAdmin()
    {
        if($this->canProcess)
        {
            try
            {
                // Set session cookie before any headers will be sent. Start the session, because:
                // 1. Search uses session to save progress
                // 2. NS admin has ok/error messages saved in sessions
                // Note: Requires Php 5.4+
                if(session_status() !== PHP_SESSION_ACTIVE)
                {
                    session_start(); // Starts a new session or resumes an existing session
                }

                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instance
                $objAssetController = new \FleetManagement\Controllers\Admin\AssetController($conf, $lang);
                $objMenuController = new \FleetManagement\Controllers\Admin\NetworkMenuController($conf, $lang);

                // Enqueue global main JS
                add_action('admin_head', array($objAssetController, 'enqueueMandatoryPlainJS'));
                // First - register network admin scripts
                $objAssetController->registerScripts();
                // Second - register network admin styles
                $objAssetController->registerStyles();
                // Finally load the network admin menu and register all admin pages
                $objMenuController->addMenu(99);

                // Print a warning if sessions are not supported in the server, and suggest to use _COOKIES instead
                if(session_status() == PHP_SESSION_DISABLED)
                {
                    add_action('admin_notices', array($this, 'displaySessionsAreDisabledInServerNotice'));
                    if(StaticValidator::wpDebugLog())
                    {
                        $this->logSessionsAreDisabledInServerNotice(__CLASS__ ."::". __FUNCTION__);
                    }
                }
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    public function loadAdmin()
    {
        if($this->canProcess)
        {
            try
            {
                // Set session cookie before any headers will be sent. Start the session, because:
                // 1. Search uses session to save order progress
                // 2. NS admin has ok/error messages saved in sessions
                // Note: Requires Php 5.4+
                if(session_status() !== PHP_SESSION_ACTIVE)
                {
                    session_start(); // Starts a new session or resumes an existing session
                }

                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Set the theme and child theme to config

                // Create mandatory instance
                $objAssetController = new \FleetManagement\Controllers\Admin\AssetController($conf, $lang);
                $objStatus = new \FleetManagement\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());
                $objMenuController = new \FleetManagement\Controllers\Admin\SingleMenuController($conf, $lang);

                // Enqueue global main JS
                add_action('admin_head', array($objAssetController, 'enqueueMandatoryPlainJS'));
                // First - register single-site admin scripts
                $objAssetController->registerScripts();
                // Second - register single-site admin styles
                $objAssetController->registerStyles();
                // Finally load the single-site admin menu and register all admin pages
                if($objStatus->isExtDataUpToDateInDatabase())
                {
                    // Regular admin menu
                    $objMenuController->addRegularMenu(99);
                } else
                {
                    // Status menu
                    $objMenuController->addStatusMenu(99);
                }

                // Print a warning if sessions are not supported in the server, and suggest to use _COOKIE
                if(session_status() == PHP_SESSION_DISABLED)
                {
                    add_action('admin_notices', array($this, 'displaySessionsAreDisabledInServerNotice'));
                    if(StaticValidator::wpDebugLog())
                    {
                        $this->logSessionsAreDisabledInServerNotice(__CLASS__ ."::". __FUNCTION__);
                    }
                }
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    /**
     * Remove admin footer text - 'Thank you for creating with WordPress'
     * @note - this mostly helps our invoice print to look much more clean
     */
    public function removeAdminFooterText()
    {
        echo '';
    }

    /**
     * Remove admin footer WordPress version
     */
    public function removeAdminFooterVersion()
    {
        remove_filter( 'update_footer', 'core_update_footer' );
    }

    /**
     * Starts the plug-in main functionality
     */
    public function runOnInit()
    {
        if($this->canProcess)
        {
            try
            {
                // Set session cookie before any headers will be sent. Start the session, because:
                // 1. Search uses session to save order progress
                // 2. NS admin has ok/error messages saved in sessions
                // Note: Requires Php 5.4+
                if(session_status() !== PHP_SESSION_ACTIVE)
                {
                    session_start(); // Starts a new session or resumes an existing session
                }

                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instances
                $objStatus = new \FleetManagement\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());
                $objAssetController = new \FleetManagement\Controllers\Front\AssetController($conf, $lang);
                $objPostTypesObserver = new \FleetManagement\Models\PostType\PostTypesObserver($conf, $lang);

                // Process only if the plugin is installed, is updated to the latest version and there is data for this extension on this blog
                if ($objStatus->checkExtDataExistsOf($conf->getPluginSemver()))
                {
                    // Register post types
                    // Note: it hooks to 'init' so that the post type registration would not be necessarily executed.
                    // Note: Initialize line bellow for every extension!
                    $objPostTypesObserver->registerAll();
                    // INFO: This plugin does not register any taxonomies

                    // Enqueue global main JS
                    add_action('wp_head', array($objAssetController, 'enqueueMandatoryPlainJS'));
                    // Enqueue global mandatory scripts
                    $objAssetController->enqueueMandatoryScripts();
                    // Enqueue global mandatory styles
                    $objAssetController->enqueueMandatoryStyles();

                    // NOTE: FOR CROSS-EXTENSION SUPPORT OF JS VARIABLES we should register scripts here
                    // Register front-end scripts
                    $objAssetController->registerScripts();
                    // Register front-end styles
                    $objAssetController->registerStyles();

                    // User hook
                    if (!is_user_logged_in())
                    {
                        // Enable the user with no privileges to run ajax_login() in AJAX
                        add_action('wp_ajax_nopriv_ajaxlogin', array($this, 'ajaxLogin'));
                    }

                    // Add Shortcode hook
                    // @note - Unlike a Theme, a Plugin is run at a very early stage of the loading process thus requiring us
                    //         to postpone the adding of our shortcode until WordPress has been initialized.
                    //         So it is recommended to add it inside a hook for init action.
                    // @see -  https://developer.wordpress.org/plugins/shortcodes/basic-shortcodes/#in-a-plugin
                    add_shortcode($conf->getShortcode(), array($this, 'parseShortcode'));
                }
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    /**
     *
     */
    public function ajaxLogin()
    {
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded
                $lang = $this->i18n();

                // First check the nonce, if it fails the function will break
                check_ajax_referer($conf->getPluginHandlePrefix().'front-ajax-nonce', 'ajax_security');

                // Nonce is checked, get the POST data and sign user on
                $params = array();
                $paramWP_UserIdOrEmail = isset($_POST['login_account_id_or_email']) ? $_POST['login_account_id_or_email'] : 0;
                $userField = is_email($paramWP_UserIdOrEmail) ? 'email' : 'id';
                $objUser = get_user_by($userField, $paramWP_UserIdOrEmail);
                $params['user_login'] = $objUser !== false ? $objUser->user_login : '';
                $params['user_password'] = isset($_POST['login_password']) ? $_POST['login_password'] : '';
                $params['remember'] = true;

                $objWPSignOn = wp_signon($params, false);
                if (is_wp_error($objWPSignOn))
                {
                    $jsonParams = array(
                        'loggedIn' => false,
                        'message'=> $lang->escJS('LANG_USER_ACCOUNT_ID_OR_PASSWORD_ERROR_TEXT'),
                    );
                } else
                {
                    $jsonParams = array(
                        'loggedIn' => true,
                        'message'=> $lang->escJS('LANG_USER_SUCCESSFULLY_LOGGED_IN_TEXT'),
                    );
                }

                echo json_encode($jsonParams);
                die();
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }
    }

    /**
     * Parses plugin shortcode and returns the content
     * @note - we do not use a 2nd parameter $content = null here, as it is not enclosing shortcode
     * @param array $attributes
     * @return string
     */
    public function parseShortcode($attributes = array())
    {
        $retContent = '';
        if($this->canProcess)
        {
            try
            {
                // Assign routing to conf, only if it is not yet assigned
                $conf = $this->conf();
                // Load the language file, only if it is not yet loaded, support forced locale
                $sanitizedLocale = isset($attributes['locale']) ? sanitize_text_field($attributes['locale']) : '';
                $lang = $this->i18n($sanitizedLocale);

                if(is_null($conf))
                {
                    throw new \Exception(static::LANG_ERROR_CONF_IS_NULL_TEXT);
                }
                if(is_null($lang))
                {
                    throw new \Exception(static::LANG_ERROR_LANG_IS_NULL_TEXT);
                }

                // Create mandatory instances
                $objStatus = new \FleetManagement\Models\Status\SingleStatus($conf, $lang, $conf->getBlogId());
                $objShortcodeController = new \FleetManagement\Controllers\Front\ShortcodeController($conf, $lang);

                // Process only if the plugin is installed and there is data for this blog
                if ($objStatus->checkExtDataExistsOf($conf->getPluginSemver()))
                {
                    // Finally - parse the shortcode
                    $retContent = $objShortcodeController->parse($attributes);
                }
                $this->throwExceptionOnFailure($objStatus->getErrorMessages(), $objStatus->getDebugMessages());
            } catch (\Exception $e)
            {
                $this->processError(__FUNCTION__, $e->getMessage());
            }
        }

        return $retContent;
    }

    /**
     * Configuration with Routing.
     * Add routing to configuration, only if it is not yet added
     *
     * @access private
     * @return null|\FleetManagement\Models\Configuration\ConfigurationInterface
     * @throws \Exception
     */
    private function conf()
    {
        if(static::$dependenciesLoaded === false)
        {
            throw new \Exception(static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT);
        }

        if(is_null($this->confWithoutRouting))
        {
            throw new \Exception(static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT);
        }

        // Singleton pattern - load the extension configuration, only if it is not yet loaded
        if(is_null($this->conf) && !is_null($this->confWithoutRouting) && static::$dependenciesLoaded === true)
        {
            $pluginPath = $this->confWithoutRouting->getPluginPath();
            $pluginURL = $this->confWithoutRouting->getPluginURL();
            $themeUI_FolderName = $this->confWithoutRouting->getThemeUI_FolderName();
            $extFolderName = $this->confWithoutRouting->getExtFolderName();
            $routing = new \FleetManagement\Models\Routing\UI_Routing($pluginPath, $pluginURL, $themeUI_FolderName, $extFolderName);
            // This is fine to clone here without cloning it's sub-objects like wpdb, because we only want to to differ by routing object
            $conf = clone $this->confWithoutRouting;
            $conf->setRouting($routing);
            $this->conf = $conf;
        }

        return $this->conf;
    }

    /**
     * Internationalization.
     * Load the language file, only if it is not yet loaded
     *
     * @access private
     * @param string $paramForcedLocale - primary used for shortcodes, if overriding of default needed (i.e. for PolyLang plugin)
     * @return null|\FleetManagement\Models\Language\Language
     * @throws \Exception
     */
    private function i18n($paramForcedLocale = "")
    {
        if(static::$dependenciesLoaded === false)
        {
            throw new \Exception(static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT);
        }

        // NOTE: For i18n() we do not need to assign $conf at all, as it is not used here
        if(is_null($this->confWithoutRouting))
        {
            throw new \Exception(static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT);
        }

        // Singleton pattern - load the language file, only if it is not yet loaded
        if((is_null($this->lang) || ($paramForcedLocale != "" && $this->expectedLocale != $paramForcedLocale)) && static::$dependenciesLoaded === true)
        {
            if($paramForcedLocale != "")
            {
                $expectedLocale = preg_replace('/[^a-zA-Z0-9_\-]/', '', $paramForcedLocale); // I.e. en_US
            } else
            {
                // Traditional WordPress plugin locale filter
                // Note 1: We don't want to include the rows bellow to language model class, as they are a part of controller
                // Note 2: Keep in mind that, if the translation do not exist, plugin will load a default english translation file
                $expectedLocale = apply_filters('plugin_locale', get_locale(), $this->confWithoutRouting->getTextDomain());
            }
            // Set the expected locale (even if it is not exist)
            $this->expectedLocale = $expectedLocale;

            // Load textdomain
            // Loads MO file into the list of domains.
            // Note 1: If the domain already exists, the inclusion will fail. If the MO file is not readable, the inclusion will fail.
            // Note 2: On success, the MO file will be placed in the $l10n global by $domain and will be an gettext_reader object.

            // See 1: http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
            // See 2: https://ulrich.pogson.ch/load-theme-plugin-translations
            // wp-content/languages/<PLUGIN_FOLDER_NAME>/<EXT_FOLDER_NAME>/lt_LT.mo
            load_textdomain($this->confWithoutRouting->getTextDomain(), $this->confWithoutRouting->getGlobalExtLangPath().$expectedLocale.'.mo');
            // wp-content/plugins/<PLUGIN_FOLDER_NAME>/Languages/<EXT_FOLDER_NAME>/lt_LT.mo
            load_plugin_textdomain($this->confWithoutRouting->getTextDomain(), false, $this->confWithoutRouting->getLocalExtLangRelPath());

            $this->lang = new \FleetManagement\Models\Language\Language(
                $this->confWithoutRouting->getTextDomain(), $this->confWithoutRouting->getGlobalPluginLangPath(),
                $this->confWithoutRouting->getLocalLangPath(), $this->confWithoutRouting->getExtFolderName(), $expectedLocale, false
            );
        }

        return $this->lang;
    }

    /*******************************************************************************/
    /**************************** KERNEL-LEVEL METHODS *****************************/
    /*******************************************************************************/

    /**
     * Throw error on object clone.
     *
     * Cloning instances of the class is forbidden.
     *
     * @since 1.0
     * @return void
     */
    public function __clone()
    {
        add_action('admin_notices', array($this, 'displayCloningIsForbiddenNotice'));
        if(StaticValidator::wpDebugLog())
        {
            $this->logCloningIsForbiddenNotice(__CLASS__ ."::". __FUNCTION__);
        }
    }

    /**
     * Disable unserializing of the class
     *
     * Unserializing instances of the class is forbidden.
     *
     * @since 1.0
     * @return void
     */
    public function __wakeup()
    {
        add_action('admin_notices', array($this, 'displayUnserializingIsForbiddenNotice'));
        if(StaticValidator::wpDebugLog())
        {
            $this->logUnserializingIsForbiddenNotice(__CLASS__ ."::". __FUNCTION__);
        }
    }



    /**
     * Display dependencies are not loaded notice
     * NOTE: This is important to have this notice,
     *       as this would get us to long troubleshooting on error otherwise
     *
     * @access static
     */
    public function displayDependenciesAreNotLoadedNotice()
    {
        echo '<div id="message" class="error"><p><strong>';
        echo static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT;
        echo '</strong></p></div>';
    }

    /**
     * Log dependencies are not loaded notice
     * NOTE: This is important to have this notice,
     *       as this would get us to long troubleshooting on error otherwise
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logSPL_AutoloadRegistrationFailedNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_SPL_AUTOLOAD_REGISTRATION_FAILED_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display dependencies are not loaded notice
     * NOTE: This is important to have this notice,
     *       as this would get us to long troubleshooting on error otherwise
     *
     * @access static
     */
    public function displaySPL_AutoloadRegistrationFailedNotice()
    {
        echo '<div id="message" class="error"><p><strong>';
        echo static::LANG_ERROR_SPL_AUTOLOAD_REGISTRATION_FAILED_TEXT;
        echo '</strong></p></div>';
    }

    /**
     * Log dependencies are not loaded notice
     * NOTE: This is important to have this notice,
     *       as this would get us to long troubleshooting on error otherwise
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logDependenciesAreNotLoadedNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_DEPENDENCIES_ARE_NOT_LOADED_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display $confWithoutRouting is null notice
     *
     * @access static
     */
    public function displayConfWithoutRoutingIsNullNotice()
    {
        echo '<div id="message" class="error"><p><strong>';
        echo static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT;
        echo '</strong></p></div>';
    }

    /**
     * Log $confWithoutRouting is null notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logConfWithoutRoutingIsNullNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_CONF_WITHOUT_ROUTING_IS_NULL_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display PHP version requirement notice
     *
     * @access static
     */
    public function displayPHP_VersionRequirementNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                $this->confWithoutRouting->getExtName(),
                $this->confWithoutRouting->getRequiredPHP_Version(),
                $this->confWithoutRouting->getCurrentPHP_Version()
            );
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is null
            echo '<div id="message" class="error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            echo '</strong></p></div>';
        }
    }

    /**
     * Log PHP version requirement notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logPHP_VersionRequirementNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        if(!is_null($this->confWithoutRouting))
        {
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                $this->confWithoutRouting->getExtName(),
                $this->confWithoutRouting->getRequiredPHP_Version(),
                $this->confWithoutRouting->getCurrentPHP_Version()
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        } else
        {
            // $confWithoutRouting is null
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_PHP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        }
    }

    /**
     * Display WordPress version requirement notice
     *
     * @access static
     */
    public function displayWP_VersionRequirementNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                $this->confWithoutRouting->getExtName(),
                $this->confWithoutRouting->getRequiredWP_Version(),
                $this->confWithoutRouting->getCurrentWP_Version()
            );
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is null
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            echo '</strong></p></div>';
        }
    }

    /**
     * Log WordPress version requirement notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logWP_VersionRequirementNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        if(!is_null($this->confWithoutRouting))
        {
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                $this->confWithoutRouting->getExtName(),
                $this->confWithoutRouting->getRequiredWP_Version(),
                $this->confWithoutRouting->getCurrentWP_Version()
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        } else
        {
            // $confWithoutRouting is null
            $output = sprintf(
                static::LANG_ERROR_PLEASE_UPGRADE_WP_TEXT,
                static::LANG_ERROR_UNKNOWN_NAME_TEXT,
                0.0,
                0.0
            );
            $this->logToFile("{$validClassAndMethodName}: {$output}\n");
        }
    }

    /**
     * Display cloning is forbidden notice
     *
     * @access static
     */
    public function displayCloningIsForbiddenNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo static::LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is null
            echo '<div id="message" class="error"><p><strong>';
            echo static::LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        }
    }

    /**
     * Log cloning is forbidden notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logCloningIsForbiddenNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_CLONING_IS_FORBIDDEN_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display unserializing is forbidden notice
     *
     * @access static
     */
    public function displayUnserializingIsForbiddenNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is null
            echo '<div id="message" class="error"><p><strong>';
            echo static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
            echo '</strong></p></div>';
        }
    }

    /**
     * Log unserializing is forbidden notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logUnserializingIsForbiddenNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * Display sessions are disabled notice
     *
     * @access static
     */
    public function displaySessionsAreDisabledInServerNotice()
    {
        if(!is_null($this->confWithoutRouting))
        {
            echo '<div id="message" class="error '.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><p><strong>';
            echo static::LANG_ERROR_SESSIONS_ARE_DISABLED_IN_SERVER_TEXT;
            echo '</strong></p></div>';
        } else
        {
            // $confWithoutRouting is null
            echo '<div id="message" class="error"><p><strong>';
            echo static::LANG_ERROR_SESSIONS_ARE_DISABLED_IN_SERVER_TEXT;
            echo '</strong></p></div>';
        }
    }

    /**
     * Log sessions are disabled notice
     *
     * @param string $paramClassAndMethodName
     * @access static
     */
    public function logSessionsAreDisabledInServerNotice($paramClassAndMethodName)
    {
        $validClassAndMethodName = esc_html(sanitize_text_field($paramClassAndMethodName));
        $output = static::LANG_ERROR_UNSERIALIZING_IS_FORBIDDEN_TEXT;
        $this->logToFile("{$validClassAndMethodName}: {$output}\n");
    }

    /**
     * @param array $paramErrorMessages
     * @param array $paramDebugMessages
     * @throws \Exception
     */
    private function throwExceptionOnFailure(array $paramErrorMessages, array $paramDebugMessages)
    {
        $errorMessagesToAdd = array();
        $debugMessagesToAdd = array();
        foreach($paramErrorMessages AS $paramErrorMessage)
        {
            $errorMessagesToAdd[] = sanitize_text_field($paramErrorMessage);
        }
        foreach($paramDebugMessages AS $paramDebugMessage)
        {
            // HTML is allowed here
            $debugMessagesToAdd[] = wp_kses_post($paramDebugMessage);
        }

        if(sizeof($errorMessagesToAdd) > 0)
        {
            $throwMessage = implode('<br />', $errorMessagesToAdd);
            if(StaticValidator::inWP_Debug() && sizeof($debugMessagesToAdd) > 0)
            {
                $throwMessage .= '<br />'.implode('<br />', $debugMessagesToAdd);
            }

            throw new \Exception($throwMessage);
        }
    }

    private function processError($paramMethodName, $paramErrorMessage)
    {
        if(StaticValidator::inWP_Debug())
        {
            // Load errors only in local or global debug mode

            // NOTE: 'add_action('admin_notices', ...)' doesn't always work - maybe due to fact, that 'admin_notices'
            //       has to be registered not later than X point in code. So we use '_doing_it_wrong' instead
            // Works
            if(!is_null($this->confWithoutRouting))
            {
                // Based on WP Coding Standards ticket #340, the WordPress '_doing_it_wrong' method does not escapes the HTML by default,
                // so this has to be done by us. Read more: https://github.com/WordPress/WordPress-Coding-Standards/pull/340
                $errorMessageHTML = '<div class="'.$this->confWithoutRouting->getPluginCSS_Prefix().'error"><div id="message" class="error"><p>'.esc_br_html($paramErrorMessage).'</p></div></div>';
                _doing_it_wrong(esc_html($paramMethodName), $errorMessageHTML, $this->confWithoutRouting->getPluginSemver());
            } else
            {
                // $confWithoutRouting is null

                // Based on WP Coding Standards ticket #340, the WordPress '_doing_it_wrong' method does not escapes the HTML by default,
                // so this has to be done by us. Read more: https://github.com/WordPress/WordPress-Coding-Standards/pull/340
                $errorMessageHTML = '<div id="message" class="error"><p>'.esc_br_html($paramErrorMessage).'</p></div>';
                _doing_it_wrong(esc_html($paramMethodName), $errorMessageHTML, 0.0);
            }
        }
    }

    private function logToFile($trustedOutput)
    {
        // NOTE #1: We explicitly use here 'dirname' function and '__DIR__' constant to reduce failure possibilities
        // NOTE #2: We do not perform 'is_writable' check as that file may not yet exist & we do want to
        //          reduce failure possibilities
        file_put_contents(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'debug.log',
            $trustedOutput, FILE_APPEND
        );
    }
}