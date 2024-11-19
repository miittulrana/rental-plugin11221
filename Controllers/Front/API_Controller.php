<?php
/**
 * API controller class to handle all API/payment callback requests
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front;
use FleetManagement\Controllers\Front\API\API_CustomerLookupController;
use FleetManagement\Controllers\Front\API\API_PaymentCallbackController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\Log\LogsObserver;
use FleetManagement\Models\Settings\SettingsObserver;
use FleetManagement\Models\Language\LanguageInterface;

final class API_Controller
{
    private $conf 	                = null;
    private $lang 		            = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * This is where we handle incoming api requests
     */
    public function handleAPI_Request()
    {
        // We use $_REQUEST here to support both - jQuery.get and jQuery.post AJAX
        $paramExtCode = isset($_REQUEST['ext_code']) ? $_REQUEST['ext_code'] : '';
        $paramAction = isset($_REQUEST['ext_action']) ? $_REQUEST['ext_action'] : "";

        if($paramExtCode != $this->conf->getExtCode())
        {
            // Process only if this is the handler for desired extension, otherwise return false
            // This IF case allows us to have more than one plugin enable, and return data based by the extension
            return false;
        }

        $objSettings = new SettingsObserver($this->conf, $this->lang);
        $objSettings->setAll();
        $objLogsObserver = new LogsObserver($this->conf, $this->lang, $objSettings->getAll());
        $objLogsObserver->deleteExpired();

        // The SWITCH
        switch($paramAction)
        {
            // 'customer-dropdown' checks the _nonce if the API key is not provided
            case "customers-dropdown":
                // This is local request, without API key, so we use _nonce to validate source
                // Check if the call is coming from right place. 'ajax_persistent_security' here is a _GET parameter to check encrypted nonce
                // Note #1 - dies on failure
                // Note #2 - we use 'check_persistent_ajax_referer' instead of 'check_ajax_referer' until #48356 issue will be solved
                //           Read more at: https://core.trac.wordpress.org/ticket/48356
                check_persistent_ajax_referer($this->conf->getPluginHandlePrefix().'front-ajax-nonce', 'ajax_persistent_security');

                if(is_user_logged_in())
                {
                    $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $objSettings->getAll());
                    // NOTE: We don't show customer id's in front-end
                    $trustedCustomersDropdownFormHTML = $objCustomersObserver->getTrustedDropdownFormWithCaptionHTML(get_current_user_id(), 0, "ANY", false);

                    $jsonParams = array(
                        'error' => 0,
                        'output' => $trustedCustomersDropdownFormHTML, // NOTE: Void of 'trusted' here as this is just assumption at this case after it was pulled to front-end
                        'message' => $this->lang->escJS('LANG_NO_ERRORS_TEXT'),
                    );
                } else
                {
                    // Error
                    $jsonParams = array(
                        'error' => 1,
                        'message' => $this->lang->escJS('LANG_USER_PLEASE_LOGIN_FIRST_ERROR_TEXT'),
                    );
                }

                echo json_encode($jsonParams);
                break;

            // 'customer-lookup' checks the _nonce if the API key is not provided
            // Used for both - logged-in and logged-out customers
            case "customer-lookup":
                // This is local request, without API key, so we use _nonce to validate source
                // Check if the call is coming from right place. 'ajax_persistent_security' here is a _GET parameter to check encrypted nonce
                // Note #1 - dies on failure
                // Note #2 - we use 'check_persistent_ajax_referer' instead of 'check_ajax_referer' until #48356 issue will be solved
                //           Read more at: https://core.trac.wordpress.org/ticket/48356
                check_persistent_ajax_referer($this->conf->getPluginHandlePrefix().'front-ajax-nonce', 'ajax_persistent_security');

                $objCustomerLookupController = new API_CustomerLookupController($this->conf, $this->lang);
                $arrResponse = $objCustomerLookupController->processAndGetResponse();

                if(isset($arrResponse['found']) && $arrResponse['found'] == 1)
                {
                    $jsonParams = array(
                        'error' => 0,
                        'customer' => isset($arrResponse['customer']) ? $arrResponse['customer'] : array(),
                        'message' => $this->lang->escJS('LANG_NO_ERRORS_TEXT'),
                    );
                } else
                {
                    // Error
                    $jsonParams = array(
                        'error' => 1,
                        'message' => isset($arrResponse['errors']) ? implode("\n", $arrResponse['errors']) : '',
                    );
                }

                echo json_encode($jsonParams);
                break;

            // For payment callback we DO NOT use _nonce
            case "payment-callback":
                $objPaymentCallbackController = new API_PaymentCallbackController($this->conf, $this->lang);
                $arrResponse = $objPaymentCallbackController->processAndGetResponse();

                // Output only if the output is not empty
                if(!empty($arrResponse['trusted_output_html']))
                {
                    echo $arrResponse['trusted_output_html'];
                }

                // NOTE: Counters are not used for payment callbacks
                break;

            default:
                $jsonParams = array(
                    "error" => 99,
                    "message" => $this->lang->escJS('LANG_UNKNOWN_ERROR_TEXT'),
                );
                echo json_encode($jsonParams);
        }

        // API request processed successfully
        return true;
    }
}