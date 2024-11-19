<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\API;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Counter\Counter;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\Country\CountriesObserver;
use FleetManagement\Models\Log\Log;
use FleetManagement\Models\Log\LogsObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Settings\SettingsObserver;

final class API_CustomerLookupController
{
    protected $conf         = null;
    protected $lang 	    = null;
    protected $dbSets	    = null;
    
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set database settings
        $this->dbSets = new SettingsObserver($this->conf, $this->lang);
        $this->dbSets->setAll();
    }

    public function processAndGetResponse()
    {
        // Read params
        $paramCustomerId = isset($_POST['customer_id']) ? $_POST['customer_id'] : 0;
        $paramCustomerEmail = isset($_POST['customer_email']) ? $_POST['customer_email'] : "";
        $paramISO_CustomerBirthdate = isset($_POST['iso_customer_birthdate']) ? $_POST['iso_customer_birthdate'] : "0000-00-00";

        if($paramCustomerId > 0)
        {
            // Lookup by id (only for logged in users)
            $arrResponse = $this->processAndGetResponseById($paramCustomerId);
        } else
        {
            // Lookup by birthdate and e-mail
            $arrResponse = $this->processAndGetResponseByEmailAndBirthdate($paramCustomerEmail, $paramISO_CustomerBirthdate);
        }

        return $arrResponse;
    }

    private function processAndGetResponseById($paramCustomerId)
    {
        // 2. Create mandatory instances
        $objCountriesObserver = new CountriesObserver($this->conf, $this->lang);
        $objCountriesObserver->setAll();

        // 3. Set defaults
        $canProcess = true;
        $found = false;
        $customer = array();
        $errorMessages = array();

        if(is_user_logged_in() === false)
        {
            // Login is required
            $canProcess = false;
            $errorMessages[] = $this->lang->getText('LANG_USER_PLEASE_LOGIN_FIRST_ERROR_TEXT');
        }

        // Check if account id is valid
        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);
        if($objCustomer->getAccountId() != get_current_user_id())
        {
            // Account ID mismatch
            $canProcess = false;
            $errorMessages[] = $this->lang->getText('LANG_USER_NOT_ALLOWED_TO_ACCESS_THIS_CUSTOMER_ERROR_TEXT');
        }

        $customerDetails = $objCustomer->getDetails();
        if ($canProcess == true && !is_null($customerDetails))
        {
            $found = true;
            $customer = array(
                "titles_dropdown_options" => $objCustomer->getTrustedTitlesDropdownOptionsHTML(
                    $customerDetails['title'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                ),
                "first_name" => esc_attr($customerDetails['first_name']),
                "last_name" => esc_attr($customerDetails['last_name']),
                "birth_year" => esc_attr($customerDetails['birth_year']),
                "birth_month" => esc_attr($customerDetails['birth_month']),
                "birth_day" => esc_attr($customerDetails['birth_day']),
                "street_address" => esc_attr($customerDetails['street_address']),
                "city" => esc_attr($customerDetails['city']),
                "state" => esc_attr($customerDetails['state']),
                "zip_code" => esc_attr($customerDetails['zip_code']),
                "country" => esc_attr($customerDetails['country']),
                "phone" => esc_attr($customerDetails['phone']),
                "email" => esc_attr($customerDetails['email']),
                "comments" => esc_textarea($customerDetails['comments']),
            );
        } else
        {
            // NOTE: For better security to protect from email database attacks, we don't want to disclose that the year is not valid.
            // That's why we just give default message
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_DETAILS_NOT_FOUND_ERROR_TEXT');
        }

        return array(
            'found' => $found ? 1 : 0,
            'customer' => $customer,
            'errors' => $errorMessages,
        );
    }

    private function processAndGetResponseByEmailAndBirthdate($paramCustomerEmail, $paramISO_CustomerBirthdate)
    {
        // 1. Create mandatory instances
        $objLogsObserver = new LogsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objLogsObserver->deleteExpired();
        $objCountriesObserver = new CountriesObserver($this->conf, $this->lang);
        $objCountriesObserver->setAll();
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // 2. Set defaults
        $canProcess = true;
        $blocked = false;
        $found = false;
        $customer = array();
        $errorMessages = array();

        if($canProcess && ConfigurationInterface::GUEST_CUSTOMER_LOOKUP_ALLOWED == 0)
        {
            // Guest customer lookup is disabled
            $canProcess = false;
            $errorMessages[] = $this->lang->getText('LANG_GUEST_CUSTOMER_LOOKUP_IS_DISABLED_ERROR_TEXT');
        }

        if($canProcess && is_user_logged_in() === true)
        {
            // Only for guests. Please logout first
            $canProcess = false;
            $errorMessages[] = $this->lang->getText('LANG_GUESTS_ONLY_FEATURE_PLEASE_LOGOUT_FIRST_ERROR_TEXT');
        }

        // 3. Check for remaining requests
        $objCounter = new Counter($this->conf, $this->lang, $this->dbSets->getAll());
        $totalRequestsLeft = $objCounter->getTotalRequestsLeft();
        $failedRequestsLeft = $objCounter->getFailedRequestsLeft();
        $emailAttemptsLeft = $objCounter->getFailedEmailAttemptsLeft($paramCustomerEmail);

        if($totalRequestsLeft == 0 || $failedRequestsLeft == 0 || $emailAttemptsLeft == 0)
        {
            // Customer lookup requests hourly limit exceeded
            $canProcess = false;
            $blocked = true;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_EXCEEDED_LOOKUP_ATTEMPTS_ERROR_TEXT');
        }

        // 4. Validate required fields
        if($canProcess)
        {
            if($paramISO_CustomerBirthdate == "0000-00-00")
            {
                // For API search customer birthdate ALWAYS MUST be provided, so there is requirement check!
                $canProcess = false;
                $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_BIRTHDATE_REQUIRED_ERROR_TEXT');
            } else if($paramCustomerEmail == '')
            {
                // For API search customer e-mail ALWAYS must be provided, so there is requirement check!
                $canProcess = false;
                $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_EMAIL_REQUIRED_ERROR_TEXT');
            }
        }

        $customerId = 0;
        if($paramCustomerEmail != '')
        {
            // Find customer id by birthdate & e-mail
            $customerId = $objCustomersObserver->getIdByEmailAndBirthdate($paramCustomerEmail, $paramISO_CustomerBirthdate);
        }

        if ($customerId > 0)
        {
            $found = true;
            $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $customerId);
            $customerDetails = $objCustomer->getDetails();
            $customer = array(
                "titles_dropdown_options" => $objCustomer->getTrustedTitlesDropdownOptionsHTML(
                    $customerDetails['title'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                ),
                "first_name" => esc_attr($customerDetails['first_name']),
                "last_name" => esc_attr($customerDetails['last_name']),
                "birth_year" => esc_attr($customerDetails['birth_year']),
                "birth_month" => esc_attr($customerDetails['birth_month']),
                "birth_day" => esc_attr($customerDetails['birth_day']),
                "street_address" => esc_attr($customerDetails['street_address']),
                "city" => esc_attr($customerDetails['city']),
                "state" => esc_attr($customerDetails['state']),
                "zip_code" => esc_attr($customerDetails['zip_code']),
                "country" => esc_attr($customerDetails['country']),
                "phone" => esc_attr($customerDetails['phone']),
                "email" => esc_attr($customerDetails['email']),
                "comments" => esc_textarea($customerDetails['comments']),
            );
        } else
        {
            // NOTE: For better security to protect from email database attacks, we don't want to disclose that the year is not valid.
            // That's why we just give default message
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_DETAILS_NOT_FOUND_ERROR_TEXT');
        }

        // 6. Always create logs
        $paramCustomerYearOfBirth = stristr($paramISO_CustomerBirthdate,"-",true);
        $requireYearOfBirth = $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED") ? true : false;
        $status = 0; // 0 - BLOCKED, 1 - FAILED, 2 - PASSED
        if($blocked == false)
        {
            $status = $found ? 1 : 2;
        }
        $logParams = array(
            'action' => 'customer-lookup',

            'dimension_1' => $this->lang->getText('LANG_CUSTOMER_EMAIL_TEXT'),
            'value_1' => $paramCustomerEmail,

            'dimension_2' => $this->lang->getText('LANG_DATE_OF_BIRTH_TEXT'),
            'value_2' => $paramCustomerYearOfBirth,

            'dimension_3' => 'Birthdate required',
            'value_3' => $requireYearOfBirth,

            'dimension_4' => 'Total requests left',
            'value_4' => $totalRequestsLeft,

            'dimension_5' => 'Failed requests left',
            'value_5' => $totalRequestsLeft,

            'dimension_6' => 'E-mail attempts left',
            'value_6' => $emailAttemptsLeft,

            'errors' => implode("\n", $errorMessages),
            'debug_log' => 'Found: '.var_export($found, true).', Customer Id: '.$customerId.', Customer Details: '.print_r($customer, true), // Note: do not translate debug
            'status' => $status,
        );
        $objLog = new Log($this->conf, $this->lang, $this->dbSets->getAll(), 0);
        $objLog->save($logParams);

        return array(
            'found' => $found ? 1 : 0,
            'customer' => $customer,
            'errors' => $errorMessages,
        );
    }
}
