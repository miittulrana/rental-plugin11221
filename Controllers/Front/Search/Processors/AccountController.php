<?php
/**
 * Order step no. 5
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
use FleetManagement\Models\User\UsersObserver;
use FleetManagement\Models\Validation\StaticValidator;

final class AccountController
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

    public function processData($paramCustomerId)
    {
        // Set defaults
        $errorMessages = array();
        $debugMessages = array();
        $canProcess = true;

        // Create mandatory instances
        $objUsersObserver = new UsersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        $originalAccountId = 0;
        $accountId = 0;
        $accountUsername = "";
        $accountPassword = "";
        $accountEmail = "";

        if($objCustomersObserver->checkExists($paramCustomerId) === false)
        {
            $canProcess = false;
            $errorMessages[] = $this->lang->getText('LANG_CUSTOMER_REQUIRED_ERROR_TEXT');
        }

        // Get account id from existing customer
        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);
        if($canProcess)
        {
            $originalAccountId = $objCustomer->getAccountId();
            $accountId = $originalAccountId;
        }

        // Original account either has to be equal to 0, or it has to match with current user id
        if(is_user_logged_in() && $originalAccountId != get_current_user_id() && $originalAccountId > 0)
        {
            $canProcess = false;
            $errorMessages[] = $this->lang->getText('LANG_USER_NOT_ALLOWED_TO_ACCESS_THIS_CUSTOMER_ERROR_TEXT');
        }

        // Get account id from logged-in user
        if($canProcess && $accountId == 0 && is_user_logged_in())
        {
            $accountId = get_current_user_id();
        }

        // Get account id by e-mail from existing customer
        if($canProcess && $accountId == 0)
        {
            $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $paramCustomerId);
            $accountEmail = $objCustomer->getEmail();

            // NOTE: In rental system we make username equal to account id
            $accountId = $objUsersObserver->getUserIdByEmail($accountEmail);
        }

        // If account id is not found yet (ACCOUNT_ID=0), create a new account
        // NOTE: Otherwise do nothing - user already exists & password got inherited
        if ($canProcess && $accountId == 0 && ConfigurationInterface::AUTOMATICALLY_CREATE_ACCOUNT == 1)
        {
            $accountUsername = $objUsersObserver->generateUniqueUsernameFromUserId();
            $accountPassword = wp_generate_password(12, false);

            // Validate customer params
            if($accountUsername == "")
            {
                $canProcess = false;
                $errorMessages[] = $this->lang->getText('LANG_USER_UNIQUE_USERNAME_CREATION_ERROR_TEXT');
            }
            if($accountEmail == "")
            {
                $canProcess = false;
                $errorMessages[] = $this->lang->getText('LANG_USER_EMAIL_IS_REQUIRED_TO_CREATE_A_NEW_ACCOUNT_ERROR_TEXT');
            }

            if($canProcess)
            {
                $accountIdOrWP_Error = wp_create_user($accountUsername, $accountPassword, $accountEmail);
                if($accountIdOrWP_Error == 0 || ($accountIdOrWP_Error instanceof \WP_Error) == false)
                {
                    $canProcess = false;
                    $errorMessages[] = $this->lang->getText('LANG_USER_ACCOUNT_CREATION_ERROR_TEXT');
                } else
                {
                    // OK
                    $accountId = $accountIdOrWP_Error;
                }
            }
        }

        // We set account id for only those customers that does not have it set yet
        if($canProcess && $accountId > 0 && $originalAccountId == 0)
        {
            $objCustomer->setAccountId($accountId);
        }
        $errorMessages = array_merge($errorMessages, $objCustomer->getErrorMessages());

        // Add debug messages to stack
        $debugMessages[] = "<strong>ACCOUNT CONTROLLER DEBUG</strong>";
        $debugMessages[] = "Username: ".sanitize_text_field($accountUsername);
        $debugMessages[] = "Password (if generated - for new accounts only): ".sanitize_text_field($accountPassword);
        $debugMessages[] = "Email: ".sanitize_text_field($accountEmail);

        return array(
            "account_id" => $accountId,
            "errors" => $errorMessages,
            "debug_messages" => $debugMessages,
        );
    }
}