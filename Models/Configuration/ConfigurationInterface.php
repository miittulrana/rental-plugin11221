<?php
/**
 * Configuration class dependant on template
 * Note 1: This is a root class and do not depend on any other plugin classes

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Configuration;
use FleetManagement\Models\Routing\RoutingInterface;

interface ConfigurationInterface
{
    // Micro-Framework name (on what micro-framework this plugin is built on)
    const MICROFRAMEWORK_NAME       = 'SolidMVC';
    // Micro-Framework semver (on what semver of the given micro-framework, this plugin is built on)
    const MICROFRAMEWORK_SEMVER     = '6.2.0+Ext';
    // Rest API prefix, with pattern https://<YOUR-DOMAIN>.com/<REST-API-PREFIX>/<PLUGIN-API-NAMESPACE>
    const WP_REST_API_PREFIX        = 'rest-api';
    // Used mostly for an autoloader
    const PLUGIN_NAMESPACE          = 'FleetManagement';
    // In later versions of plugin this setting is in database
    const USE_SESSIONS              = true;
    // In later versions of plugin this setting is in database
    const DROPDOWN_STYLE            = 1; // 1 - '[ELEMENT]:', 2 - '- Select [ELEMENT] -'
    // In later versions of plugin this setting is in database
    const INPUT_STYLE               = 1; // 1 - '[TEXT]:', 2 - '- [TEXT] -'
    // In later versions of plugin this setting is in database
    const TIME_INTERVAL             = 1800; // '900' - 15 minutes, '1800' - 30 minutes or '3600' - 60 minutes
    // In later versions of plugin this setting is in database
    const TIME_CEILING              = 'BY_TIME_COUNT'; // 'BY_TIME_COUNT', 'BY_NOON_COUNT' or 'BY_DATE_COUNT'
    // In later versions of plugin this setting is in database
    const WEEKEND                   = 'SAT_SUN'; // 'FRI', 'FRI_SAT' or 'SAT_SUN'
    // In later versions of plugin this setting is in database
    const SHOW_LOGIN_FORM           = 0; // 0 or 1
    // In later versions of plugin this setting is in database
    const GUEST_CUSTOMER_LOOKUP_ALLOWED = 0; // 0 or 1
    // In later versions of plugin this setting is in database
    const AUTOMATICALLY_CREATE_ACCOUNT = 0; // 0 or 1
    // This setting is used as legacy until V6
    const __LEGACY__PARSE_PHONES    = true;

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
    );

    // Dependency Injection Methods

    /**
     * @param RoutingInterface $routing
     * @return void
     */
    public function setRouting(RoutingInterface $routing);
    /**
     * @return null|RoutingInterface
     */
    public function getRouting();

    // Core methods
    /**
     * @return \wpdb
     */
    public function getInternalWPDB();
    public function getBlogId();
    public function getBlogLocale($paramBlogId = -1);
    public function getRequiredPHP_Version();
    public function getCurrentPHP_Version();
    public function getRequiredWP_Version();
    public function getCurrentWP_Version();
    public function getOldestCompatiblePluginSemver();
    public function getPluginSemver();
    public function isNetworkEnabled();
    public function getPluginPrefix();
    public function getPluginHandlePrefix();
    public function getPluginCSS_Prefix();
    public function getGalleryFolderName();
    public function getGlobalGalleryPath();
    public function getGlobalGalleryPathWithoutEndSlash();
    public function getGlobalGalleryURL();
    public function getThemeUI_FolderName();
    public function getPostTypePrefix();
    public function getExtCode();
    public function getExtName();
    public function getExtFolderName();
    public function getExtAPI_Namespace();
    public function getExtPrefix();
    public function getExtURL_Prefix();
    public function getExtCSS_Prefix();
    public function getBlogPrefix($paramBlogId = -1);
    public function getWP_Prefix();
    public function getPrefix();
    public function getShortcode();
    public function getTextDomain();
    public function getDemoWP_UserLogin();

    // Extension param methods
    public function getItemModelParam();
    public function getOrderCodeParam();

    // Extension display value methods
    public function getItemModelDisplayValue();
    public function getItemModelsDisplayValue();
    public function getItemModelPricesDisplayValue();
    public function getItemModelsAvailabilityDisplayValue();
    public function getChangeOrderDisplayValue();

    // Extension prefix methods
    public function getWP_UsernamePrefix(); // lowercase only
    public function getItemModelSKU_Prefix();
    public function getExtraSKU_Prefix();
    public function getLocationUniqueIdentifierPrefix();
    public function getOrderCodePrefix();
    public function getPaymentMethodCodePrefix();

    // BB code methods
    public function getOrderCodeBBCode();
    public function getChangeOrderURL_BBCode();

    // Path methods
    public function getWP_PluginsPath();
    public function getPluginPathWithFilename();
    public function getPluginPath();
    public function getPluginBasename();
    public function getPluginFolderName();
    public function getLibrariesPath();
    public function getLocalLangPath();
    public function getGlobalLangPath();
    public function getLocalCommonLangPath();
    public function getLocalExtLangRelPath();
    public function getLocalExtLangPath();
    public function getGlobalPluginLangPath();
    public function getGlobalCommonLangPath();
    public function getGlobalExtLangPath();

    // URL methods
    public function getPluginURL();
}