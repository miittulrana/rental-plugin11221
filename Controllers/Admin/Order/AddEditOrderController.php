<?php
/**
 * @package FleetManagement
 * @note Variables prefixed with 'local' are not used in templates
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Order;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Invoice\InvoicesObserver;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Order\OrderNotificationsObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;


final class AddEditOrderController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramOrderId, $paramBackToURL)
    {
        $objInvoicesObserver = new InvoicesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
        if($objOrder->isCancelled() === false)
        {
            // First - cancel
            // And send e-mails to disappointed customers if needed
            $objOrder->cancel();
            if($this->dbSets->get('conf_send_emails') == 1)
            {
                $objOrderNotificationsObserver->sendOrderCancelledNotifications($objOrder->getId(), false);
            }
        }

        // Delete all related invoices to this order
        $invoiceIds = $objInvoicesObserver->getAllIds("ANY", $paramOrderId, -1);
        foreach ($invoiceIds AS $invoiceId)
        {
            $objInvoice = new Invoice($this->conf, $this->lang, $this->dbSets->getAll(), $invoiceId);
            $objInvoice->delete();

            StaticSession::cacheHTML_Array('admin_debug_html', $objInvoice->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objInvoice->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objInvoice->getErrorMessages());
        }

        $objOrder->delete();
        $objOrder->deleteAllOptions();

        StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
        StaticSession::cacheHTML_Array('admin_debug_html', $objOrderNotificationsObserver->getSavedDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrderNotificationsObserver->getSavedOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrderNotificationsObserver->getSavedErrorMessages());

        wp_safe_redirect($paramBackToURL);
        exit;
    }

    private function processCancel($paramOrderId, $paramBackToURL)
    {
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
        $objOrder->cancel();
        if($this->dbSets->get('conf_send_emails') == 1)
        {
            $objOrderNotificationsObserver->sendOrderCancelledNotifications($objOrder->getId(), false);
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
        StaticSession::cacheHTML_Array('admin_debug_html', $objOrderNotificationsObserver->getSavedDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrderNotificationsObserver->getSavedOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrderNotificationsObserver->getSavedErrorMessages());

        wp_safe_redirect($paramBackToURL);
        exit;
    }

    private function processConfirm($paramOrderId, $paramBackToURL)
    {
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
        $objOrder->confirm();
        if($this->dbSets->get('conf_send_emails') == 1)
        {
            $objOrderNotificationsObserver->sendOrderConfirmedNotifications($objOrder->getId(), false);
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
        StaticSession::cacheHTML_Array('admin_debug_html', $objOrderNotificationsObserver->getSavedDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrderNotificationsObserver->getSavedOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrderNotificationsObserver->getSavedErrorMessages());

        wp_safe_redirect($paramBackToURL);
        exit;
    }

    private function processCompletedEarly($paramOrderId, $paramBackToURL)
    {
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
        $objOrder->markCompletedEarly();

        StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());

        wp_safe_redirect($paramBackToURL);
        exit;
    }
	 private function updateVehicleRegistration($paramOrderId, $paramBackToURL, $vehicleRegistrationNumber)
    {
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
       $upd = $objOrder->updateVehicleRegistrationNumber($vehicleRegistrationNumber);
        StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());
       wp_safe_redirect($paramBackToURL);
        exit;
    }
	
	
	

    private function processRefund($paramOrderId, $paramBackToURL)
    {
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
        $objOrder->refund();
        if($this->dbSets->get('conf_send_emails') == 1)
        {
            $objOrderNotificationsObserver->sendOrderCancelledNotifications($paramOrderId, false);
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
        StaticSession::cacheHTML_Array('admin_debug_html', $objOrderNotificationsObserver->getSavedDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrderNotificationsObserver->getSavedOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrderNotificationsObserver->getSavedErrorMessages());

        wp_safe_redirect($paramBackToURL);
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
	
	private function updateTotalAmount($paramOrderId,$paramBackToURL,$finalInvoiceHTML,$grand_total,$total_pay_later,$return_timestamp){
		        $objInvoicesObserver = new InvoicesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $existingInvoiceId = $objInvoicesObserver->getIdByParams('OVERALL', $paramOrderId, -1);
		 $objInvoice = new Invoice($this->conf, $this->lang, $this->dbSets->getAll(), $existingInvoiceId);
        $invoiceParams = array(
            'order_id' => $paramOrderId,
			'invoice' => $finalInvoiceHTML,
			'invoiceId' => $existingInvoiceId,
			'grand_total' => $grand_total,
            'total_pay_later' => $total_pay_later,
        );
        $objInvoice->updateInvoice($invoiceParams);
		
		$objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId);
       $upd = $objOrder->updateReturnTime($return_timestamp);
		StaticSession::cacheHTML_Array('admin_debug_html', $objInvoice->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objInvoice->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objInvoice->getErrorMessages());
		
       	wp_safe_redirect($paramBackToURL);
		
exit;
    }
	
	
    public function printContent()
    {
        // Get back to url params
        $sanitizedBackPage = isset($_GET['back_page']) ? sanitize_key($_GET['back_page']) : '';
        $sanitizedBackTab = isset($_GET['back_tab']) ? sanitize_key($_GET['back_tab']) : ''; // pickups, returns, orders
        $paramBackFromDate = isset($_GET['back_from_date']) ? $_GET['back_from_date'] : '';
        $paramBackTillDate = isset($_GET['back_till_date']) ? $_GET['back_till_date'] : '';
        $validBackCustomerId = isset($_GET['back_customer_id']) ? StaticValidator::getValidInteger($_GET['back_customer_id'], -1) : -1;
        $validBackISO_FromDate = $paramBackFromDate != '' ? StaticValidator::getValidISO_Date($paramBackFromDate, $this->dbSets->get('conf_short_date_format')) : '';
        $validBackISO_TillDate = $paramBackTillDate != '' ? StaticValidator::getValidISO_Date($paramBackTillDate, $this->dbSets->get('conf_short_date_format')) : '';

        // Create back to URL
        $backToURL = "admin.php";
        $backToURL .= "?page={$sanitizedBackPage}";
        $backToURL .= "&tab={$sanitizedBackTab}";
        $backToURL .= "&from_date={$validBackISO_FromDate}";
        $backToURL .= "&till_date={$validBackISO_TillDate}";
        $backToURL .= "&customer_id={$validBackCustomerId}";

        // Process actions
        if(isset($_GET['delete_order'])) { $this->processDelete($_GET['delete_order'], $backToURL); }
        if(isset($_GET['cancel_order'])) { $this->processCancel($_GET['cancel_order'], $backToURL); }
        if(isset($_GET['confirm_order'])) { $this->processConfirm($_GET['confirm_order'], $backToURL); }
        if(isset($_GET['mark_completed_early'])) { $this->processCompletedEarly($_GET['mark_completed_early'], $backToURL); }
        if(isset($_GET['refund_order'])) { $this->processRefund($_GET['refund_order'], $backToURL); }
        if(isset($_GET['update_vehicle_registration'])) {
			$this->updateVehicleRegistration($_GET['order_id'], $backToURL, $_GET['number_vehicle_registration']); 
		}
		if(isset($_POST['invoice'])) {$this->updateTotalAmount($_POST['order_id'],$_POST['back_url'], $_POST['invoice'], $_POST['grand_total'], $_POST['total_pay_later'], $_POST['return_timestamp']); 
		}

        // There is no content for order edit
        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'order-manager&tab=orders');
    }
}