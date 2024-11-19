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

final class ChangeTrackingSettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_universal_analytics_events_tracking');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_universal_analytics_enhanced_ecommerce');
        $objSetting->saveNumber(0, array(0, 1));

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_TRACKING_SETTINGS_UPDATED_TEXT')));

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=tracking-settings');
        exit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $retSettings = array();

        if($this->dbSets->get('conf_universal_analytics_events_tracking') == 1)
        {
            $universal_analytics_events_tracking  = '<option value="1" selected="selected">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $universal_analytics_events_tracking .= '<option value="0">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        } else
        {
            $universal_analytics_events_tracking  = '<option value="1">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $universal_analytics_events_tracking .= '<option value="0" selected="selected">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        }
        $retSettings['select_universal_analytics_events_tracking'] = $universal_analytics_events_tracking;

        if($this->dbSets->get('conf_universal_analytics_enhanced_ecommerce') == 1)
        {
            $select_universal_analytics_enhanced_ecommerce  = '<option value="1" selected="selected">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $select_universal_analytics_enhanced_ecommerce .= '<option value="0">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        } else
        {
            $select_universal_analytics_enhanced_ecommerce  = '<option value="1">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $select_universal_analytics_enhanced_ecommerce .= '<option value="0" selected="selected">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        }
        $retSettings['select_universal_analytics_enhanced_ecommerce'] = $select_universal_analytics_enhanced_ecommerce;

        return $retSettings;
    }

    /**
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_tracking_settings'])) { $this->processSave(); }
    }
}
