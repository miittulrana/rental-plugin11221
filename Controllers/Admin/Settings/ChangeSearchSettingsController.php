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

final class ChangeSearchSettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_enabled');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_booking_model');
        $objSetting->saveNumber(1, array(1, 2));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_pickup_location_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_pickup_location_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_pickup_date_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_pickup_date_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_return_location_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_return_location_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_return_date_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_return_date_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_partner_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_partner_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_manufacturer_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_manufacturer_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_body_type_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_body_type_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_transmission_type_visible');
        $objSetting->saveCheckbox();
        // NOTE: Attributes are NEVER required

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_fuel_type_visible');
        $objSetting->saveCheckbox();
        // NOTE: Attributes are NEVER required

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_coupon_code_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_search_coupon_code_required');
        $objSetting->saveCheckbox();

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_SEARCH_SETTINGS_UPDATED_TEXT')));

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=search-settings');
        exit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $retSettings = array();

        if($this->dbSets->get('conf_search_enabled') == 1)
        {
            $selectSearchEnabled  = '<option value="0">'.$this->lang->escHTML('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectSearchEnabled .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_ENABLED_TEXT').'</option>'."\n";
        } else
        {
            $selectSearchEnabled  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectSearchEnabled .= '<option value="1">'.$this->lang->escHTML('LANG_ENABLED_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_search_enabled_dropdown_options_html'] = $selectSearchEnabled;


        if($this->dbSets->get('conf_booking_model') == 1)
        {
            $selectSearchMultimode   = '<option value="1" selected="selected">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectSearchMultimode  .= '<option value="2">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>'."\n";
        } else
        {
            $selectSearchMultimode  = '<option value="1">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectSearchMultimode .= '<option value="2" selected="selected">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_search_multimode_dropdown_options_html'] = $selectSearchMultimode;

        return $retSettings;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_search_settings']))  { $this->processSave(); }
    }
}
