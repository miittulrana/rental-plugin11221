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

final class ChangeSecuritySettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_recaptcha_site_key');
        $objSetting->saveText();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_recaptcha_secret_key');
        $objSetting->saveText();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_recaptcha_enabled');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_api_max_requests_per_period');
        $objSetting->saveNumber(0);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_api_max_failed_requests_per_period');
        $objSetting->saveNumber(0);

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_SECURITY_SETTINGS_UPDATED_TEXT')));

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=security-settings');
        exit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $retSettings = array();

        if($this->dbSets->get('conf_recaptcha_enabled') == 1)
        {
            $select_recaptcha_enabled  = '<option value="1" selected="selected">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $select_recaptcha_enabled .= '<option value="0">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        } else
        {
            $select_recaptcha_enabled  = '<option value="1">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>' . "\n";
            $select_recaptcha_enabled .= '<option value="0" selected="selected">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>' . "\n";
        }
        $retSettings['select_recaptcha_enabled'] = $select_recaptcha_enabled;

        $selectAPIMaxRequestsPerPeriod = '';
        $selected = $this->dbSets->get('conf_api_max_requests_per_period') == -1 ? ' selected="selected"' : '';
        $selectAPIMaxRequestsPerPeriod .= '<option value="-1"'.$selected.'>'.$this->lang->getText('LANG_UNLIMITED_TEXT').'</option>';
        for($requests = 10; $requests <= 200; $requests = $requests + 10)
        {
            $selected = $requests == $this->dbSets->get('conf_api_max_requests_per_period') ? ' selected="selected"' : '';
            $selectAPIMaxRequestsPerPeriod .= '<option value="'.$requests.'"'.$selected.'>'.$requests.' ';
            $selectAPIMaxRequestsPerPeriod .= mb_strtolower($this->lang->getText('LANG_REQUESTS_TEXT'), 'UTF-8').' / ';
            $selectAPIMaxRequestsPerPeriod .= $this->lang->getText('LANG_PRICING_PER_HOUR_TEXT').' / ';
            $selectAPIMaxRequestsPerPeriod .= $this->lang->getText('LANG_IP_TEXT').'</option>';
        }
        $retSettings['select_api_max_requests_per_period'] = $selectAPIMaxRequestsPerPeriod;

        $selectAPIMaxFailedRequestsPerPeriod = '';
        $selected = $this->dbSets->get('conf_api_max_failed_requests_per_period') == -1 ? ' selected="selected"' : '';
        $selectAPIMaxFailedRequestsPerPeriod .= '<option value="-1"'.$selected.'>'.$this->lang->getText('LANG_UNLIMITED_TEXT').'</option>';
        for($failedRequests = 1; $failedRequests <= 20; $failedRequests++)
        {
            $selected = $failedRequests == $this->dbSets->get('conf_api_max_failed_requests_per_period') ? ' selected="selected"' : '';
            $selectAPIMaxFailedRequestsPerPeriod .= '<option value="'.$failedRequests.'"'.$selected.'>'.$failedRequests.' ';
            $selectAPIMaxFailedRequestsPerPeriod .= mb_strtolower($this->lang->getText($failedRequests == 1 ? 'LANG_REQUEST_TEXT' : 'LANG_REQUESTS_TEXT'), 'UTF-8').' / ';
            $selectAPIMaxFailedRequestsPerPeriod .= $this->lang->getText('LANG_PRICING_PER_HOUR_TEXT').' / ';
            $selectAPIMaxFailedRequestsPerPeriod .=  $this->lang->getText('LANG_IP_TEXT').'</option>';
        }
        $retSettings['select_api_max_failed_requests_per_period'] = $selectAPIMaxFailedRequestsPerPeriod;

        return $retSettings;
    }

    /**
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_security_settings'])) { $this->processSave(); }
    }
}
