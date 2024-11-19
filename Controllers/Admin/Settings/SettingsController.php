<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Settings;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Country\CountriesObserver;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Style\StylesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class SettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Tab - global settings
        $objStylesObserver = new StylesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $this->view->globalSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-global-settings&noheader=true');
        $this->view->trustedSystemStylesDropdownOptionsHTML = $objStylesObserver->getTrustedDropdownOptionsHTML($this->dbSets->get('conf_system_style'));
        $this->view->arrGlobalSettings = (new ChangeGlobalSettingsController($this->conf, $this->lang))->getSettings();
        $this->view->siteUrl = get_site_url();


        // Tab - tracking settings
        $this->view->trackingSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-tracking-settings&noheader=true');
        $this->view->arrTrackingSettings = (new ChangeTrackingSettingsController($this->conf, $this->lang))->getSettings();


        // Tab - security settings
        $this->view->securitySettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-security-settings&noheader=true');
        $this->view->arrSecuritySettings = (new ChangeSecuritySettingsController($this->conf, $this->lang))->getSettings();


        // Tab - customer settings
        $this->view->customerSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-customer-settings&noheader=true');
        $this->view->titleVisibleChecked = $this->dbSets->getCustomerFieldStatus("title", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->firstNameVisibleChecked = $this->dbSets->getCustomerFieldStatus("first_name", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->lastNameVisibleChecked = $this->dbSets->getCustomerFieldStatus("last_name", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->birthdateVisibleChecked = $this->dbSets->getCustomerFieldStatus("birthdate", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->streetAddressVisibleChecked = $this->dbSets->getCustomerFieldStatus("street_address", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->cityVisibleChecked = $this->dbSets->getCustomerFieldStatus("city", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->stateVisibleChecked = $this->dbSets->getCustomerFieldStatus("state", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->zipCodeVisibleChecked = $this->dbSets->getCustomerFieldStatus("zip_code", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->countryVisibleChecked = $this->dbSets->getCustomerFieldStatus("country", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->phoneVisibleChecked = $this->dbSets->getCustomerFieldStatus("phone", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->emailVisibleChecked = $this->dbSets->getCustomerFieldStatus("email", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->commentsVisibleChecked = $this->dbSets->getCustomerFieldStatus("comments", "VISIBLE") ? ' checked="checked"' : '';

        $this->view->titleRequiredChecked = $this->dbSets->getCustomerFieldStatus("title", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->firstNameRequiredChecked = $this->dbSets->getCustomerFieldStatus("first_name", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->lastNameRequiredChecked = $this->dbSets->getCustomerFieldStatus("last_name", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->birthdateRequiredChecked = $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->streetAddressRequiredChecked = $this->dbSets->getCustomerFieldStatus("street_address", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->cityRequiredChecked = $this->dbSets->getCustomerFieldStatus("city", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->stateRequiredChecked = $this->dbSets->getCustomerFieldStatus("state", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->zipCodeRequiredChecked = $this->dbSets->getCustomerFieldStatus("zip_code", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->countryRequiredChecked = $this->dbSets->getCustomerFieldStatus("country", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->phoneRequiredChecked = $this->dbSets->getCustomerFieldStatus("phone", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->emailRequiredChecked = $this->dbSets->getCustomerFieldStatus("email", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->commentsRequiredChecked = $this->dbSets->getCustomerFieldStatus("comments", "REQUIRED") ? ' checked="checked"' : '';


        // Tab - search settings
        $this->view->searchSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-search-settings&noheader=true');
        $this->view->arrSearchSettings = (new ChangeSearchSettingsController($this->conf, $this->lang))->getSettings();
        $this->view->pickupLocationVisibleChecked = $this->dbSets->getSearchFieldStatus("pickup_location", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->pickupDateVisibleChecked = $this->dbSets->getSearchFieldStatus("pickup_date", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->returnLocationVisibleChecked = $this->dbSets->getSearchFieldStatus("return_location", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->returnDateVisibleChecked = $this->dbSets->getSearchFieldStatus("return_date", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->partnerVisibleChecked = $this->dbSets->getSearchFieldStatus("partner", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->manufacturerVisibleChecked = $this->dbSets->getSearchFieldStatus("manufacturer", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->classVisibleChecked = $this->dbSets->getSearchFieldStatus("body_type", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->attribute2VisibleChecked = $this->dbSets->getSearchFieldStatus("transmission_type", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->attribute1VisibleChecked = $this->dbSets->getSearchFieldStatus("fuel_type", "VISIBLE") ? ' checked="checked"' : '';
        $this->view->couponCodeVisibleChecked = $this->dbSets->getSearchFieldStatus("coupon_code", "VISIBLE") ? ' checked="checked"' : '';

        $this->view->pickupLocationRequiredChecked = $this->dbSets->getSearchFieldStatus("pickup_location", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->pickupDateRequiredChecked = $this->dbSets->getSearchFieldStatus("pickup_date", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->returnLocationRequiredChecked = $this->dbSets->getSearchFieldStatus("return_location", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->returnDateRequiredChecked = $this->dbSets->getSearchFieldStatus("return_date", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->partnerRequiredChecked = $this->dbSets->getSearchFieldStatus("partner", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->manufacturerRequiredChecked = $this->dbSets->getSearchFieldStatus("manufacturer", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->classRequiredChecked = $this->dbSets->getSearchFieldStatus("body_type", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->attribute2RequiredChecked = $this->dbSets->getSearchFieldStatus("transmission_type", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->attribute1RequiredChecked = $this->dbSets->getSearchFieldStatus("fuel_type", "REQUIRED") ? ' checked="checked"' : '';
        $this->view->couponCodeRequiredChecked = $this->dbSets->getSearchFieldStatus("coupon_code", "REQUIRED") ? ' checked="checked"' : '';


        // Tab - order settings
        $this->view->orderSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-order-settings&noheader=true');
        $this->view->arrOrderSettings = (new ChangeOrderSettingsController($this->conf, $this->lang))->getSettings();


        // Tab - company settings
        $objCountriesObserver = new CountriesObserver($this->conf, $this->lang);
        $objCountriesObserver->setAll();
        $this->view->companySettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-company-settings&noheader=true');


        // Tab - price settings
        $this->view->priceSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-price-settings&noheader=true');
        $this->view->arrPriceSettings = (new ChangePriceSettingsController($this->conf, $this->lang))->getSettings();


        // Tab - notification settings
        $this->view->notificationSettingsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'change-notification-settings&noheader=true');
        $this->view->arrNotificationSettings = (new ChangeNotificationSettingsController($this->conf, $this->lang))->getSettings();


        // Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'global-settings', 'tracking-settings', 'security-settings', 'customer-settings', 'search-settings',
            'order-settings', 'company-settings', 'price-settings', 'notification-settings'
        ), 'global-settings', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Print the template
        $templateRelPathAndFileName = 'Settings'.DIRECTORY_SEPARATOR.'Tabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
