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

final class ChangeCustomerSettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_title_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_title_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_first_name_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_first_name_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_last_name_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_last_name_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_birthdate_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_birthdate_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_street_address_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_street_address_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_city_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_city_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_state_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_state_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_zip_code_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_zip_code_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_country_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_country_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_phone_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_phone_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_email_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_email_required');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_comments_visible');
        $objSetting->saveCheckbox();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_customer_comments_required');
        $objSetting->saveCheckbox();

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_CUSTOMER_SETTINGS_UPDATED_TEXT')));

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=customer-settings');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_customer_settings'])) { $this->processSave(); }
    }
}
