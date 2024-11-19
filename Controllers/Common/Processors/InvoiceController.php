<?php
/**
 * Common Invoice Processor
 * @package FleetManagement
 * @note Variables prefixed with 'local' are not used in templates
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Common\Processors;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Invoice\InvoicesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Payment\PaymentMethod;
use FleetManagement\Models\Payment\PaymentMethodsObserver;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Settings\SettingsObserver;
use FleetManagement\Views\PageView;

final class InvoiceController
{
    private $conf                       = null;
    private $lang 	                    = null;
    private $view 	                    = null;
    private $dbSets	                    = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set database settings
        $this->dbSets = new SettingsObserver($this->conf, $this->lang);
        $this->dbSets->setAll();

        // Initialize the page view and set it's conf and lang objects
        $this->view = new PageView();
        $this->view->extCode = $this->conf->getExtCode();
        $this->view->extName = $this->conf->getExtName();
        $this->view->extURL_Prefix = $this->conf->getExtURL_Prefix();
        $this->view->extCSS_Prefix = $this->conf->getExtCSS_Prefix();
        $this->view->staticURLs =array_merge($this->conf->getRouting()->getFolderURLs(), array('GALLERY' => $this->conf->getGlobalGalleryURL()));
        $this->view->lang = $this->lang->getAll();
        $this->view->settings = $this->dbSets->getAll();
        $this->view->objConf = $this->conf;
        $this->view->objSettings = $this->dbSets;
    }

    protected function fillSearchFieldsView()
    {
        // Search fields visibility settings
        $this->view->pickupLocationVisible = $this->dbSets->getSearchFieldStatus("pickup_location", "VISIBLE");
        $this->view->pickupDateVisible = $this->dbSets->getSearchFieldStatus("pickup_date", "VISIBLE");
        $this->view->returnLocationVisible = $this->dbSets->getSearchFieldStatus("return_location", "VISIBLE");
        $this->view->returnDateVisible = $this->dbSets->getSearchFieldStatus("return_date", "VISIBLE");
        $this->view->partnerVisible = $this->dbSets->getSearchFieldStatus("partner", "VISIBLE");
        $this->view->manufacturerVisible = $this->dbSets->getSearchFieldStatus("manufacturer", "VISIBLE");
        $this->view->classVisible = $this->dbSets->getSearchFieldStatus("body_type", "VISIBLE");
        $this->view->attribute1Visible = $this->dbSets->getSearchFieldStatus("fuel_type", "VISIBLE");
        $this->view->attribute2Visible = $this->dbSets->getSearchFieldStatus("transmission_type", "VISIBLE");
        $this->view->couponCodeVisible = $this->dbSets->getSearchFieldStatus("coupon_code", "VISIBLE");

        // Search fields requirement settings
        $this->view->pickupLocationRequired = $this->dbSets->getSearchFieldStatus("pickup_location", "REQUIRED");
        $this->view->pickupDateRequired = $this->dbSets->getSearchFieldStatus("pickup_date", "REQUIRED");
        $this->view->returnLocationRequired = $this->dbSets->getSearchFieldStatus("return_location", "REQUIRED");
        $this->view->returnDateRequired = $this->dbSets->getSearchFieldStatus("return_date", "REQUIRED");
        $this->view->partnerRequired = $this->dbSets->getSearchFieldStatus("partner", "REQUIRED");
        $this->view->manufacturerRequired = $this->dbSets->getSearchFieldStatus("manufacturer", "REQUIRED");
        $this->view->classRequired = $this->dbSets->getSearchFieldStatus("body_type", "VISIBLE");
        $this->view->attribute1Required = $this->dbSets->getSearchFieldStatus("fuel_type", "REQUIRED");
        $this->view->attribute2Required = $this->dbSets->getSearchFieldStatus("transmission_type", "REQUIRED");
        $this->view->couponCodeRequired = $this->dbSets->getSearchFieldStatus("coupon_code", "REQUIRED");
    }

    public function fillCustomerFieldsView()
    {
        // Customer fields visibility settings
        $this->view->customerTitleVisible = $this->dbSets->getCustomerFieldStatus("title", "VISIBLE");
        $this->view->customerFirstNameVisible = $this->dbSets->getCustomerFieldStatus("first_name", "VISIBLE");
        $this->view->customerLastNameVisible = $this->dbSets->getCustomerFieldStatus("last_name", "VISIBLE");
        $this->view->customerBirthdateVisible = $this->dbSets->getCustomerFieldStatus("birthdate", "VISIBLE");
        $this->view->customerStreetAddressVisible = $this->dbSets->getCustomerFieldStatus("street_address", "VISIBLE");
        $this->view->customerCityVisible = $this->dbSets->getCustomerFieldStatus("city", "VISIBLE");
        $this->view->customerStateVisible = $this->dbSets->getCustomerFieldStatus("state", "VISIBLE");
        $this->view->customerZIP_CodeVisible = $this->dbSets->getCustomerFieldStatus("zip_code", "VISIBLE");
        $this->view->customerCountryVisible = $this->dbSets->getCustomerFieldStatus("country", "VISIBLE");
        $this->view->customerPhoneVisible = $this->dbSets->getCustomerFieldStatus("phone", "VISIBLE");
        $this->view->customerEmailVisible = $this->dbSets->getCustomerFieldStatus("email", "VISIBLE");
        $this->view->customerCommentsVisible = $this->dbSets->getCustomerFieldStatus("comments", "VISIBLE");

        // If it is not visible, then if will not be required (function will always return false of 'required+not visible')
        $this->view->boolCustomerBirthdateRequired = $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED") ? true : false;
        $this->view->boolCustomerEmailRequired = $this->dbSets->getCustomerFieldStatus("email", "REQUIRED") ? true : false;

        $this->view->customerTitleRequired = $this->dbSets->getCustomerFieldStatus("title", "REQUIRED") ? ' required' : '';
        $this->view->customerFirstNameRequired = $this->dbSets->getCustomerFieldStatus("first_name", "REQUIRED") ? ' required' : '';
        $this->view->customerLastNameRequired = $this->dbSets->getCustomerFieldStatus("last_name", "REQUIRED") ? ' required' : '';
        $this->view->customerBirthdateRequired = $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED") ? ' required' : '';
        $this->view->customerStreetAddressRequired = $this->dbSets->getCustomerFieldStatus("street_address", "REQUIRED") ? ' required' : '';
        $this->view->customerCityRequired = $this->dbSets->getCustomerFieldStatus("city", "REQUIRED") ? ' required' : '';
        $this->view->customerStateRequired = $this->dbSets->getCustomerFieldStatus("state", "REQUIRED") ? ' required' : '';
        $this->view->customerZIP_CodeRequired = $this->dbSets->getCustomerFieldStatus("zip_code", "REQUIRED") ? ' required' : '';
        $this->view->customerCountryRequired = $this->dbSets->getCustomerFieldStatus("country", "REQUIRED") ? ' required' : '';
        $this->view->customerPhoneRequired = $this->dbSets->getCustomerFieldStatus("phone", "REQUIRED") ? ' required' : '';
        $this->view->customerEmailRequired = $this->dbSets->getCustomerFieldStatus("email", "REQUIRED") ? ' required' : '';
        $this->view->customerCommentsRequired = $this->dbSets->getCustomerFieldStatus("comments", "REQUIRED") ? ' required' : '';
    }

    /**
     * @note - use of $this->objSearch is forbidden in this method
     * @param int $paramOrderId
     * @param int $paramCustomerId
     * @param int $paramPaymentMethodId
     * @param array $paramPriceSummary
     * @return array
     * @throws \Exception
     */
    public function createInvoice($paramOrderId, $paramCustomerId, $paramPaymentMethodId, array $paramPriceSummary)
    {
        // Create mandatory local objects
        $objInvoicesObserver = new InvoicesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPaymentMethodsObserver = new PaymentMethodsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->dbSets->getAll(), $paramPaymentMethodId);

        // For output only: start
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);
        $paymentMethodDetails = $objPaymentMethod->getDetails();
        $pickupsHTML = $objLocationsObserver->getPrintPickups($paramPriceSummary['pickup_location_id'], $paramPriceSummary['expected_pickup_day_of_week']);
        $returnsHTML = $objLocationsObserver->getPrintReturns($paramPriceSummary['return_location_id'], $paramPriceSummary['expected_return_day_of_week']);
        $showLocationFees = false;
        if(is_array($paramPriceSummary['pickup']) && $paramPriceSummary['pickup']['unit']['current_pickup_fee'] > 0.00)
        {
            // Show location fees if pick-up fee used at pick-up time is more than 0 (regular / after-hours)
            $showLocationFees = true;
        } else if(is_array($paramPriceSummary['return']) && $paramPriceSummary['return']['unit']['current_return_fee'] > 0.00)
        {
            // Show location fees if return fee used at return time is more than 0 (regular / after-hours)
            $showLocationFees = true;
        }

        $customerDetails = $objCustomer->getDetails(true); // Will always return the data, even if the customer not exists
        // For output only: end

        // Set the view variables for invoice
        $this->fillSearchFieldsView(); // Fill search fields view
        $this->fillCustomerFieldsView(); // Fill customer fields view
        // NOTE objSearch for invoice table is not needed
        $this->view->orderCode = $objOrder->getPrintCode();
        $this->view->couponCode = $objOrder->getPrintCouponCode();
        $this->view->payNowText = $this->lang->getText($objPaymentMethod->isOnlinePayment() ? 'LANG_STEP5_PAY_ONLINE_TEXT' : 'LANG_STEP5_PAY_AT_PICKUP_TEXT');
        $this->view->customer = $customerDetails;
        $this->view->pickupLocations = $objLocationsObserver->getPrintPickups($paramPriceSummary['pickup_location_id'], $paramPriceSummary['expected_pickup_day_of_week']);
        $this->view->returnLocations = $objLocationsObserver->getPrintReturns($paramPriceSummary['return_location_id'], $paramPriceSummary['expected_return_day_of_week']);
        $this->view->pickupMainColspan = $this->dbSets->getSearchFieldStatus("return_location", "VISIBLE") ? 1 : 3;
        $this->view->returnMainColspan = $this->dbSets->getSearchFieldStatus("pickup_location", "VISIBLE") ? 2 : 3;
        $this->view->pickupColspan = $this->dbSets->getSearchFieldStatus("return_date", "VISIBLE") ? 1 : 3;
        $this->view->returnColspan = $this->dbSets->getSearchFieldStatus("pickup_date", "VISIBLE") ? 1 : 2;
        $this->view->showPaymentDetails = $this->dbSets->get('conf_prepayment_enabled') == 1 && $objPaymentMethodsObserver->getTotalEnabled() > 0;
        $this->view->paymentMethodName = isset($paymentMethodDetails['payment_method_name']) ? $paymentMethodDetails['print_translated_payment_method_name'] : "";
        $this->view->priceSummary = $paramPriceSummary;
        $this->view->showLocationFees = $showLocationFees;

        // Get the final invoice HTML
        $templateRelPathAndFileName = 'InvoiceTable.php';
        $finalInvoiceHTML = $this->view->render($this->conf->getRouting()->getCommonTemplatesPath($templateRelPathAndFileName));

        // Save invoice
        $existingInvoiceId = $objInvoicesObserver->getIdByParams('OVERALL', $paramOrderId, -1);

        $objInvoice = new Invoice($this->conf, $this->lang, $this->dbSets->getAll(), $existingInvoiceId);
        $invoiceParams = array(
            'order_id' => $paramOrderId,
            'customer_name' => $customerDetails['print_full_name'],
            'customer_email' => $customerDetails['email'],
            'grand_total' => $paramPriceSummary['overall_print']['grand_total'],
            'fixed_deposit' => $paramPriceSummary['overall_print']['fixed_deposit'],
            'total_pay_now' => $paramPriceSummary['overall_print']['total_pay_now'],
            'total_pay_later' => $paramPriceSummary['overall_print']['total_pay_later'],

            'pickup_location' => $pickupsHTML,

            'return_location' => $returnsHTML,

            'invoice' => $finalInvoiceHTML,
        );
        $objInvoice->save($invoiceParams);

        // Get new invoice id
        $finalInvoiceId = $objInvoice->getId();

        return array(
            'invoice_id' => $finalInvoiceId,
            'errors' => $objInvoice->getErrorMessages(),
        );
    }
}