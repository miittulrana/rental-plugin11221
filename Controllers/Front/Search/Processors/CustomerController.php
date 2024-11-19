<?php
/**
 * Customer processor
 * @package FleetManagement
 * @note Variables prefixed with 'local' are not used in templates
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Search\Processors;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Settings\SettingsObserver;
use FleetManagement\Models\Validation\StaticValidator;

final class CustomerController
{
    private $conf                       = null;
    private $lang 	                    = null;
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
    }

    public function processData(array $params)
    {
        // Set defaults
        $errorMessages = array();
        $customerId = 0;
        $canSaveCustomer = true;

        // Customer params
        $paramCustomerTitle = '';
        $paramCustomerFirstName = '';
        $paramCustomerLastName = '';
        $paramCustomerBirthdate = '0000-00-00';
        $paramCustomerStreetAddress = '';
        $paramCustomerCity = '';
        $paramCustomerState = '';
        $paramCustomerZIP_Code = '';
        $paramCustomerCountryCode = '';
        $paramCustomerPhone = '';
        $paramCustomerEmail = '';
        $paramCustomerComments = '';
        if(isset($params['title'])) { $paramCustomerTitle = $params['title']; }
        if(isset($params['first_name'])) { $paramCustomerFirstName = $params['first_name']; }
        if(isset($params['last_name'])) { $paramCustomerLastName = $params['last_name']; }
        if(isset($params['birthdate'])) { $paramCustomerBirthdate = $params['birthdate']; }
        if(isset($params['street_address'])) { $paramCustomerStreetAddress = $params['street_address']; }
        if(isset($params['city'])) { $paramCustomerCity = $params['city']; }
        if(isset($params['state'])) { $paramCustomerState = $params['state']; }
        if(isset($params['zip_code'])) { $paramCustomerZIP_Code = $params['zip_code']; }
        if(isset($params['country'])) { $paramCustomerCountryCode = $params['country']; }
        if(isset($params['phone'])) { $paramCustomerPhone = $params['phone']; }
        if(isset($params['email'])) { $paramCustomerEmail = $params['email']; }
        if(isset($params['comments'])) { $paramCustomerComments = $params['comments']; }

        // Create mandatory instances
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // Fourth - get existing customer id from e-mail & birthdate, if exists
        $existingCustomerId = 0;
        if ($paramCustomerEmail != '')
        {
            $existingCustomerId = $objCustomersObserver->getIdByEmailAndBirthdate($paramCustomerEmail, $paramCustomerBirthdate);
        }

        // Sixth - validate customer fields
        if($this->dbSets->isFieldRequired('customer_title') && sanitize_text_field($paramCustomerTitle) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_TITLE_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_first_name') && sanitize_text_field($paramCustomerFirstName) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_FIRST_NAME_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_last_name') && sanitize_text_field($paramCustomerLastName) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_LAST_NAME_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_birthdate') && ($paramCustomerBirthdate == '0000-00-00' || StaticValidator::isDate($paramCustomerBirthdate, 'Y-m-d') == false))
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_BIRTHDATE_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_street_address') && sanitize_text_field($paramCustomerStreetAddress) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_STREET_ADDRESS_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_city') && sanitize_text_field($paramCustomerCity) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_CITY_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_state') && sanitize_text_field($paramCustomerState) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_STATE_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_zip_code') && sanitize_text_field($paramCustomerZIP_Code) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_ZIP_CODE_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_country') && sanitize_text_field($paramCustomerCountryCode) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_COUNTRY_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_phone') && sanitize_text_field($paramCustomerPhone) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_PHONE_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_email') && sanitize_email($paramCustomerEmail) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_EMAIL_REQUIRED_ERROR_TEXT');
        }

        if($this->dbSets->isFieldRequired('customer_comments') && sanitize_text_field($paramCustomerComments) == '')
        {
            $canSaveCustomer = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_COMMENTS_REQUIRED_ERROR_TEXT');
        }

        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $existingCustomerId);
        $customerDetails = $objCustomer->getDetails();
        if(!is_null($customerDetails))
        {
            $sanitizedCustomerFirstName = sanitize_text_field($paramCustomerFirstName);
            $sanitizedCustomerLastName = sanitize_text_field($paramCustomerLastName);

            // NOTE: If we are already here, it means that the Customer ID was already found by e-mail & birthdate,
            //       so we do not need to check here if it is matching or now - we already know that it matched some account

            if($customerDetails['first_name'] != '' && $sanitizedCustomerFirstName != $customerDetails['first_name'])
            {
                $canSaveCustomer = false;
                $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_FIRST_NAME_DOES_NOT_MATCH_ERROR_TEXT');
            }
            if($customerDetails['last_name'] != '' && $sanitizedCustomerLastName != $customerDetails['last_name'])
            {
                $canSaveCustomer = false;
                $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_LAST_NAME_DOES_NOT_MATCH_ERROR_TEXT');
            }

            if($canSaveCustomer)
            {
                // Update existing customer
                $customerUpdated = $objCustomer->save($params);

                if($customerUpdated !== false)
                {
                    $customerId = $objCustomer->getId();

                    // Update last visit
                    $objCustomer->updateLastUsed();
                }
            }
        } else
        {
            // Add new customer
            $customerAdded = $objCustomer->save($params);
            if($customerAdded)
            {
                $customerId = $objCustomer->getId();

                // Update last visit
                $objCustomer->updateLastUsed();
            }
        }
        $errorMessages = array_merge($errorMessages, $objCustomer->getErrorMessages());

        return array(
            "customer_id" => $customerId,
            "errors" => $errorMessages,
        );
    }
}