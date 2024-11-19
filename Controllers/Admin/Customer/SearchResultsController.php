<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Customer;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\User\User;
use FleetManagement\Models\Validation\StaticValidator;

final class SearchResultsController extends AbstractController
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
        // Create mandatory object instances
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // Get back to url params
        $paramAccountId = isset($_GET['account_id']) ? $_GET['account_id'] : -1;
        $paramDateType = isset($_GET['date_type']) ? $_GET['date_type'] : '';
        $paramFromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
        $paramTillDate = isset($_GET['till_date']) ? $_GET['till_date'] : '';

        $validDateType = StaticValidator::getValidCode($paramDateType, '', true, false, false);

        $userName = "";
        if($paramAccountId== 0)
        {
            $userName = $this->lang->getText('LANG_GUEST_NO_ACCOUNT_TEXT');
        } else if($paramAccountId > 0)
        {
            $objUser = new User($this->conf, $this->lang, $paramAccountId);
            $userName = $objUser->getDisplayName();
        }

        if($paramFromDate != '')
        {
            $localBackFromDate      = StaticValidator::getValidDate($paramFromDate, $this->dbSets->get('conf_short_date_format'), '');
            $localISO_FromDate      = StaticValidator::getValidISO_Date($paramFromDate, $this->dbSets->get('conf_short_date_format'));
            $localFromTimestamp     = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($localISO_FromDate, '00:00:00');
            $fromDateI18n           = date_i18n($this->dbSets->get('conf_short_date_format'), $localFromTimestamp + get_option('gmt_offset') * 3600, true);
        } else
        {
            $localBackFromDate      = '';
            $localFromTimestamp     = -1;
            $fromDateI18n           = '';
        }
        if($paramTillDate != '')
        {
            $localBackTillDate      = StaticValidator::getValidDate($paramTillDate, $this->dbSets->get('conf_short_date_format'), '');
            $localISO_TillDate      = StaticValidator::getValidISO_Date($paramTillDate, $this->dbSets->get('conf_short_date_format'));
            $localTillTimestamp     = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($localISO_TillDate, '23:59:59');
            $tillDateI18n           = date_i18n($this->dbSets->get('conf_short_date_format'), $localTillTimestamp + get_option('gmt_offset') * 3600, true);
        } else
        {
            $localBackTillDate      = '';
            $localTillTimestamp     = -1;
            $tillDateI18n           = '';
        }

        $backToURL_Part = "";
        $backToURL_Part .= "&back_page={$this->conf->getExtURL_Prefix()}customer-search-results";
        $backToURL_Part .= "&back_tab=customers";
        $backToURL_Part .= "&back_account_id=".intval($paramAccountId);
        $backToURL_Part .= "&back_date_type=".$validDateType;
        $backToURL_Part .= "&back_from_date={$localBackFromDate}";
        $backToURL_Part .= "&back_till_date={$localBackTillDate}";

        // Customer list: Start
        if($paramDateType == "DATE_CREATED")
        {
            $trustedAdminCustomerListHTML = $objCustomersObserver->getTrustedAdminListByDateCreatedHTML($paramAccountId, $localFromTimestamp, $localTillTimestamp, $backToURL_Part);
        } else
        {
            $trustedAdminCustomerListHTML = $objCustomersObserver->getTrustedAdminListByLastUsedHTML($paramAccountId, $localFromTimestamp, $localTillTimestamp, $backToURL_Part);
        }
        // Customer list: End

        // Set the view variables
        $this->view->backToCustomerListURL = 'admin.php?page='.$this->conf->getExtURL_Prefix().'customer-manager&tab=customers';
        $this->view->userName = $userName;
        $this->view->dateType = $validDateType;
        $this->view->fromDateI18n = $fromDateI18n;
        $this->view->tillDateI18n = $tillDateI18n;
        $this->view->trustedAdminCustomerListHTML = $trustedAdminCustomerListHTML;

        // Print the template
        $templateRelPathAndFileName = 'Customer'.DIRECTORY_SEPARATOR.'SearchResultsTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
