<?php
/**
 * Initializer class to load admin section
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin;
use FleetManagement\Controllers\Admin\Status\NetworkController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class NetworkMenuController
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $errorMessages          = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * @param int $paramMenuPosition
     */
    public function addMenu($paramMenuPosition = 99)
    {
        $validMenuPosition = intval($paramMenuPosition);
        $iconURL = $this->conf->getRouting()->getAdminImagesURL('Plugin.png');
        $urlPrefix = $this->conf->getExtURL_Prefix();

        // For admins only - update_plugins are official WordPress role for updates
        add_menu_page(
            $this->lang->getText('EXT_NAME'), $this->lang->getText('EXT_NAME'),
            "update_plugins", "{$urlPrefix}network-menu", array($this, "printNetworkStatus"), $iconURL, $validMenuPosition
        );
            add_submenu_page(
                "{$urlPrefix}network-menu", $this->lang->getText('LANG_STATUS_NETWORK_TEXT'), $this->lang->getText('LANG_STATUS_NETWORK_TEXT'),
                "update_plugins", "{$urlPrefix}network-status", array($this, "printNetworkStatus")
            );
        remove_submenu_page("{$urlPrefix}network-menu", "{$urlPrefix}network-menu");
    }

    // Network Status
    public function printNetworkStatus()
    {
        try
        {
            $objStatusController = new NetworkController($this->conf, $this->lang);
            $objStatusController->printContent();
        }
        catch (\Exception $e)
        {
            $this->processError(__FUNCTION__, $e->getMessage());
        }
    }

	/******************************************************************************************/
	/* Other methods                                                                          */
	/******************************************************************************************/
    /**
     * @param $paramName
     * @param $paramErrorMessage
     */
    private function processError($paramName, $paramErrorMessage)
    {
        if(StaticValidator::inWP_Debug())
        {
            $sanitizedName = sanitize_text_field($paramName);
            $sanitizedErrorMessage = sanitize_text_field($paramErrorMessage);
            // Load errors only in local or global debug mode
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_ERROR_IN_S_METHOD_S_TEXT'), $sanitizedName, $sanitizedErrorMessage);

            // 'add_action('admin_notices', ...)' doesn't work here (maybe due to fact, that 'admin_notices' has to be registered not later than X point in code)

            // Works
            $errorMessageHTML = '<div id="message" class="error"><p>'.esc_br_html($sanitizedErrorMessage).'</p></div>';
            _doing_it_wrong(esc_html($sanitizedName), $errorMessageHTML, $this->conf->getPluginSemver());
        }
    }
}