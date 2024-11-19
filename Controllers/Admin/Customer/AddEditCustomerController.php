<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Customer;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Country\CountriesObserver;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Order\OrderNotificationsObserver;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\User\UsersObserver;
use FleetManagement\Models\Validation\StaticValidator;

final class AddEditCustomerController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramCustomerId)
    {
        // Create mandatory instances
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);
        $deleted = $objCustomer->delete();
        if($deleted)
        {
            // Cancel upcoming orders by this customer
            // Note: we do not do deletions here, as they have to be performed manually if needed,
            //       because that is related to invoicing history, and should be taken care very precisely
            $orderIds = $objOrdersObserver->getIndependentUpcomingIdsByCustomerId($paramCustomerId);
            foreach($orderIds AS $orderId)
            {
                $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $orderId);
                if($objOrder->isCancelled() === false)
                {
                    // Cancel order and send cancellation e-mails
                    $objOrder->cancel();
                    if($this->dbSets->get('conf_send_emails') == 1)
                    {
                        $objOrderNotificationsObserver->sendOrderCancelledNotifications($objOrder->getId(), false);
                    }
                }

                StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
                StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
                StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objOrdersObserver->getSavedDebugMessages());
            StaticSession::cacheHTML_Array('admin_debug_html', $objOrderNotificationsObserver->getSavedDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objOrdersObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objOrderNotificationsObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objOrdersObserver->getSavedErrorMessages());
            StaticSession::cacheValueArray('admin_error_message', $objOrderNotificationsObserver->getSavedErrorMessages());
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objCustomer->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objCustomer->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objCustomer->getErrorMessages());

        // Get back to url params
        $sanitizedBackPage = isset($_GET['back_page']) ? sanitize_key($_GET['back_page']) : '';
        $sanitizedBackTab = isset($_GET['back_tab']) ? sanitize_key($_GET['back_tab']) : ''; // pickups, returns, orders
        $validBackDateType = isset($_GET['back_date_type']) ? StaticValidator::getValidCode($_GET['back_date_type'], '', true, false, false) : "DATE_CREATED";
        $validBackFromDate = isset($_GET['back_from_date']) ? StaticValidator::getValidDate($_GET['back_from_date'], $this->dbSets->get('conf_short_date_format'), '') : '';
        $validBackTillDate = isset($_GET['back_till_date']) ? StaticValidator::getValidDate($_GET['back_till_date'], $this->dbSets->get('conf_short_date_format'), '') : '';

        // Create back to URL
        $backToURL = "admin.php";
        $backToURL .= "?page={$sanitizedBackPage}";
        $backToURL .= "&tab={$sanitizedBackTab}";
        $backToURL .= "&date_type={$validBackDateType}";
        $backToURL .= "&from_date={$validBackFromDate}";
        $backToURL .= "&till_date={$validBackTillDate}";

        wp_safe_redirect($backToURL);
        exit;
    }

    private function processSave($paramCustomerId)
    {
        // Create mandatory instances
        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);

        $customerParams = $_POST;
        if(isset($_POST['birth_year'], $_POST['birth_month'], $_POST['birth_day']))
        {
            $customerParams['birthdate'] = StaticValidator::getValidISO_Date("{$_POST['birth_year']}-{$_POST['birth_month']}-{$_POST['birth_day']}");
        }
        // NOTE: For admins all field requirement checks is bypassed
        $objCustomer->save($customerParams);

        StaticSession::cacheHTML_Array('admin_debug_html', $objCustomer->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objCustomer->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objCustomer->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'customer-manager&tab=customers');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_GET['delete_customer'])) { $this->processDelete($_GET['delete_customer']); }
        if(isset($_POST['save_customer'], $_POST['customer_id'])) { $this->processSave($_POST['customer_id']); }

        // Process params
        $paramCustomerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : 0;

        // Create mandatory instances
        $objCountriesObserver = new CountriesObserver($this->conf, $this->lang);
        $objCountriesObserver->setAll();
        $objUsersObserver = new UsersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);
        $localDetails = $objCustomer->getDetails();

        // Set the view variables - Customer fields visibility settings
        $this->view->titleVisible = $this->dbSets->getCustomerFieldStatus("title", "VISIBLE");
        $this->view->firstNameVisible = $this->dbSets->getCustomerFieldStatus("first_name", "VISIBLE");
        $this->view->lastNameVisible = $this->dbSets->getCustomerFieldStatus("last_name", "VISIBLE");
        $this->view->birthdateVisible = $this->dbSets->getCustomerFieldStatus("birthdate", "VISIBLE");
        $this->view->streetAddressVisible = $this->dbSets->getCustomerFieldStatus("street_address", "VISIBLE");
        $this->view->cityVisible = $this->dbSets->getCustomerFieldStatus("city", "VISIBLE");
        $this->view->stateVisible = $this->dbSets->getCustomerFieldStatus("state", "VISIBLE");
        $this->view->zipCodeVisible = $this->dbSets->getCustomerFieldStatus("zip_code", "VISIBLE");
        $this->view->countryVisible = $this->dbSets->getCustomerFieldStatus("country", "VISIBLE");
        $this->view->phoneVisible = $this->dbSets->getCustomerFieldStatus("phone", "VISIBLE");
        $this->view->emailVisible = $this->dbSets->getCustomerFieldStatus("email", "VISIBLE");
        $this->view->commentsVisible = $this->dbSets->getCustomerFieldStatus("comments", "VISIBLE");

        // Set the view variables - If it is not visible, then if will not be required (function will always return false of 'required+not visible')
        $this->view->titleRequired = $this->dbSets->getCustomerFieldStatus("title", "REQUIRED") ? ' required' : '';
        $this->view->firstNameRequired = $this->dbSets->getCustomerFieldStatus("first_name", "REQUIRED") ? ' required' : '';
        $this->view->lastNameRequired = $this->dbSets->getCustomerFieldStatus("last_name", "REQUIRED") ? ' required' : '';
        $this->view->birthdateRequired = $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED") ? ' required' : '';
        $this->view->streetAddressRequired = $this->dbSets->getCustomerFieldStatus("street_address", "REQUIRED") ? ' required' : '';
        $this->view->cityRequired = $this->dbSets->getCustomerFieldStatus("city", "REQUIRED") ? ' required' : '';
        $this->view->stateRequired = $this->dbSets->getCustomerFieldStatus("state", "REQUIRED") ? ' required' : '';
        $this->view->zipCodeRequired = $this->dbSets->getCustomerFieldStatus("zip_code", "REQUIRED") ? ' required' : '';
        $this->view->countryRequired = $this->dbSets->getCustomerFieldStatus("country", "REQUIRED") ? ' required' : '';
        $this->view->phoneRequired = $this->dbSets->getCustomerFieldStatus("phone", "REQUIRED") ? ' required' : '';
        $this->view->emailRequired = $this->dbSets->getCustomerFieldStatus("email", "REQUIRED") ? ' required' : '';
        $this->view->commentsRequired = $this->dbSets->getCustomerFieldStatus("comments", "REQUIRED") ? ' required' : '';

        // Set the view variables - other
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'customer-manager&tab=customers');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-customer&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->customerId = $localDetails['customer_id'];
            $this->view->existingCustomer = $localDetails['existing_customer'] > 0 ? true : false;
            $this->view->trustedTitlesDropdownOptionsHTML = $objCustomer->getTrustedTitlesDropdownOptionsHTML($localDetails['title'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
            $this->view->firstName = $localDetails['first_name'];
            $this->view->lastName = $localDetails['last_name'];
            $this->view->trustedBirthYearDropdownOptionsHTML = StaticFormatter::generateDropdownOptions(current_time("Y") - 80, current_time("Y") - 10, $localDetails['birth_year'], "", $this->lang->getText('LANG_YEAR_SELECT_TEXT'), true);
            $this->view->trustedBirthMonthDropdownOptionsHTML = StaticFormatter::generateDropdownOptions(1, 12, $localDetails['birth_month'], "", $this->lang->getText('LANG_MONTH_SELECT_TEXT'), true);
            $this->view->trustedBirthDayDropdownOptionsHTML = StaticFormatter::generateDropdownOptions(1, 31, $localDetails['birth_day'], "", $this->lang->getText('LANG_DAY_SELECT_TEXT'), true);
            $this->view->streetAddress = $localDetails['street_address'];
            $this->view->city = $localDetails['city'];
            $this->view->state = $localDetails['state'];
            $this->view->zipCode = $localDetails['zip_code'];
            $this->view->country = $localDetails['country'];
            $this->view->phone = $localDetails['phone'];
            $this->view->email = $localDetails['email'];
            $this->view->comments = $localDetails['comments'];
            $this->view->ip = $localDetails['ip'];
        } else
        {
            $this->view->customerId = 0;
            $this->view->existingCustomer = false;
            $this->view->trustedTitlesDropdownOptionsHTML = $objCustomer->getTrustedTitlesDropdownOptionsHTML($localDetails['title'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
            $this->view->firstName =  "";
            $this->view->lastName = "";
            $this->view->trustedBirthYearDropdownOptionsHTML = StaticFormatter::generateDropdownOptions(current_time("Y") - 80, current_time("Y") - 10, "", "", $this->lang->getText('LANG_YEAR_SELECT_TEXT'), true);
            $this->view->trustedBirthMonthDropdownOptionsHTML = StaticFormatter::generateDropdownOptions(1, 12, "", "", $this->lang->getText('LANG_MONTH_SELECT_TEXT'), true);
            $this->view->trustedBirthDayDropdownOptionsHTML = StaticFormatter::generateDropdownOptions(1, 31, "", "", $this->lang->getText('LANG_DAY_SELECT_TEXT'), true);
            $this->view->streetAddress = "";
            $this->view->city = "";
            $this->view->state = "";
            $this->view->zipCode = "";
            $this->view->country = "";
            $this->view->phone = "";
            $this->view->email = "";
            $this->view->comments = "";
            $this->view->ip = "";
        }

        // Print the template
        $templateRelPathAndFileName = 'Customer'.DIRECTORY_SEPARATOR.'AddEditCustomerForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
