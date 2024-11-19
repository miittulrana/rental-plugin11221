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
use FleetManagement\Models\Invoice\InvoicesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Customer\Customer;

final class ViewOrderController extends AbstractController
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
        // Create mandatory instances
        $objInvoicesObserver = new InvoicesObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // Process params
        $paramOrderId = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
        // For order view we always take an 'OVERALL' invoice
        $overallFinalInvoiceId = $objInvoicesObserver->getIdByParams('OVERALL', $paramOrderId, -1);
        $objOverallFinalInvoice = new Invoice($this->conf, $this->lang, $this->dbSets->getAll(), $overallFinalInvoiceId);
        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $objOrder->getCustomerId());
        $customerDetails = $objCustomer->getDetails(true); // Returns in both cases - if customer exists or not exists
        $orderDetails = $objOrder->getDetails(true); // Returns in both cases - if customer exists or not exists
        $localOverallFinalInvoiceDetails = $objOverallFinalInvoice->getDetails();

        if($objOrder->canEdit() == false)
        {
            // Current user is not allowed to edit current booking
            // Note - we don't use here wp_safe_redirect, because headers already sent, so we have to use a redirect Javascript code in content
            $redirectToPage = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'order-manager&tab=orders');
            echo '<script type="text/javascript">window.location="'.$redirectToPage.'"</script>';
            exit;
        }

        // Set the view variables - Customer fields visibility settings
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

        // Set the view variables - other
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'order-search-results&customer_id='.$customerDetails['customer_id']);
        $this->view->customer = $customerDetails;
        $this->view->order = $orderDetails;
        if(!is_null($orderDetails) && !is_null($localOverallFinalInvoiceDetails))
        {
            $this->view->trustedInvoiceHTML = $localOverallFinalInvoiceDetails['invoice'];
        } else
        {
            $this->view->trustedInvoiceHTML = '';
        }

        // Print the template
        $templateRelPathAndFileName = 'Order'.DIRECTORY_SEPARATOR.'ViewOrderDetails.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
