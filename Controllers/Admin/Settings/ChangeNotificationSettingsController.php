<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Settings;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Settings\Setting;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class ChangeNotificationSettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_send_emails');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_company_notification_emails');
        $objSetting->saveNumber(0, array(0, 1));

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_NOTIFICATION_SETTINGS_UPDATED_TEXT')));

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=notification-settings');
        exit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $retSettings = array();

        if($this->dbSets->get('conf_send_emails') == 1)
        {
            $trustedSendNotificationsDropdownOptionsHTML  = '<option value="1" selected="selected">'.$this->lang->getText('LANG_YES_TEXT').'</option>' . "\n";
            $trustedSendNotificationsDropdownOptionsHTML .= '<option value="0">'.$this->lang->getText('LANG_NO_TEXT').'</option>' . "\n";
        } else
        {
            $trustedSendNotificationsDropdownOptionsHTML  = '<option value="1">'.$this->lang->getText('LANG_YES_TEXT').'</option>' . "\n";
            $trustedSendNotificationsDropdownOptionsHTML .= '<option value="0" selected="selected">'.$this->lang->getText('LANG_NO_TEXT').'</option>' . "\n";
        }
        $retSettings['trusted_send_notifications_dropdown_options_html'] = $trustedSendNotificationsDropdownOptionsHTML;

        if($this->dbSets->get('conf_company_notification_emails') == 1)
        {
            $trustedSendCompanyNotificationsDropdownOptionsHTML  = '<option value="1" selected="selected">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $trustedSendCompanyNotificationsDropdownOptionsHTML .= '<option value="0">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        } else
        {
            $trustedSendCompanyNotificationsDropdownOptionsHTML  = '<option value="1">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $trustedSendCompanyNotificationsDropdownOptionsHTML .= '<option value="0" selected="selected">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        }
        $retSettings['trusted_send_company_notifications_dropdown_options_html'] = $trustedSendCompanyNotificationsDropdownOptionsHTML;

        return $retSettings;
    }

    /**
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_notification_settings'])) { $this->processSave(); }
    }
}
