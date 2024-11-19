<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Order;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\Validation\StaticValidator;

final class OrderSearchResultsController extends AbstractController
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
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // Get back to url params
        $paramBackTab = isset($_GET['back_tab']) ? $_GET['back_tab'] : ''; // pickups, returns, orders
        $paramFromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
        $paramTillDate = isset($_GET['till_date']) ? $_GET['till_date'] : '';
        $paramCustomerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : -1;

        if($paramFromDate != '')
        {
            $localISO_FromDate   = StaticValidator::getValidISO_Date($paramFromDate, $this->dbSets->get('conf_short_date_format'));
            $localFromTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($localISO_FromDate, '00:00:00');
            $fromDateI18n = date_i18n($this->dbSets->get('conf_short_date_format'), $localFromTimestamp + get_option('gmt_offset') * 3600, true);
        } else
        {
            $localISO_FromDate   = '';
            $localFromTimestamp = -1;
            $fromDateI18n = $this->lang->getText('LANG_PAST_TEXT');
        }
        if($paramTillDate != '')
        {
            $localISO_TillDate     = StaticValidator::getValidISO_Date($paramTillDate, $this->dbSets->get('conf_short_date_format'));
            $localTillTimestamp   = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($localISO_TillDate, '23:59:59');
            $tillDateI18n   = date_i18n($this->dbSets->get('conf_short_date_format'), $localTillTimestamp + get_option('gmt_offset') * 3600, true);
        } else
        {
            $localISO_TillDate     = '';
            $localTillTimestamp   = -1;
            $tillDateI18n   = $this->lang->getText('LANG_UPCOMING_TEXT');
        }

        $sanitizedBackTab = sanitize_key($paramBackTab);
        $validCustomerId = StaticValidator::getValidPositiveInteger($paramCustomerId, -1);

        $backToURL_Part = "";
        $backToURL_Part .= "&tab={$sanitizedBackTab}";
        $backToURL_Part .= "&back_page={$this->conf->getExtURL_Prefix()}order-search-results";
        $backToURL_Part .= "&back_tab={$sanitizedBackTab}";
        $backToURL_Part .= "&back_from_date={$localISO_FromDate}";
        $backToURL_Part .= "&back_till_date={$localISO_TillDate}";
        $backToURL_Part .= "&back_customer_id={$validCustomerId}";

        // Order list: Start
        $trustedAdminPickupListHTML = "";
        $trustedAdminReturnListHTML = "";
        $trustedAdminOrderListHTML = "";
        $printCustomerName = "";

        if(isset($_GET['search_pickup_date']))
        {
            $searchFor = "pickups";
            $trustedAdminPickupListHTML = $objOrdersObserver->getTrustedAdminPickupsHTML($localFromTimestamp, $localTillTimestamp, $paramCustomerId, $backToURL_Part);
        } else if(isset($_GET['search_return_date']))
        {
            $searchFor = "returns";
            $trustedAdminReturnListHTML = $objOrdersObserver->getTrustedAdminReturnsHTML($localFromTimestamp, $localTillTimestamp, $paramCustomerId, $backToURL_Part);
        } else
        {
            // If this search came for orders, or from customer list
            $searchFor = "orders";
            $trustedAdminOrderListHTML = $objOrdersObserver->getTrustedAdminOrdersHTML($localFromTimestamp, $localTillTimestamp, $paramCustomerId, $backToURL_Part);
        }
        if($paramCustomerId > 0)
        {
            $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);
            $customerDetails = $objCustomer->getDetails();
            if(!is_null($customerDetails))
            {
                $printCustomerName = $customerDetails['print_full_name_with_title'];
            }
        }
        // Order list: End

        // Set the view variables
        $this->view->backToPickupListURL = 'admin.php?page='.$this->conf->getExtURL_Prefix().'order-manager&tab=pickups';
        $this->view->backToReturnListURL = 'admin.php?page='.$this->conf->getExtURL_Prefix().'order-manager&tab=returns';
        $this->view->backToOrderListURL = 'admin.php?page='.$this->conf->getExtURL_Prefix().'order-manager&tab=orders';
        $this->view->fromDateI18n = $fromDateI18n;
        $this->view->tillDateI18n = $tillDateI18n;
        $this->view->customerName = $printCustomerName;
        $this->view->trustedAdminPickupListHTML = $trustedAdminPickupListHTML;
        $this->view->trustedAdminReturnListHTML = $trustedAdminReturnListHTML;
        $this->view->trustedAdminOrderListHTML = $trustedAdminOrderListHTML;

        // Print the template
        if($searchFor == "pickups")
        {
            $templateRelPathAndFileName = 'Order'.DIRECTORY_SEPARATOR.'PickupSearchResultsTabs.php';
        } else if($searchFor == "returns")
        {
            $templateRelPathAndFileName = 'Order'.DIRECTORY_SEPARATOR.'ReturnSearchResultsTabs.php';
        } else
        {
            $templateRelPathAndFileName = 'Order'.DIRECTORY_SEPARATOR.'OrderSearchResultsTabs.php';
        }
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
