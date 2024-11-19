<?php
/**
 * Search step no. 6
 * @package FleetManagement
 * @note Variables prefixed with 'local' are not used in templates
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Search;
use FleetManagement\Controllers\Common\Processors\InvoiceController;
use FleetManagement\Controllers\Front\Search\Processors\AccountController;
use FleetManagement\Controllers\Front\Search\Processors\CustomerController;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Order\OrderNotificationsObserver;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Payment\PaymentMethod;
use FleetManagement\Models\Payment\PaymentMethodsObserver;
use FleetManagement\Models\Search\FrontEndSearchManager;
use FleetManagement\Models\Search\SearchItemModelsManager;
use FleetManagement\Controllers\Front\AbstractController;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Transaction\Transaction;
use FleetManagement\Models\User\User;
use FleetManagement\Models\Validation\StaticValidator;

final class Step6ProcessController extends AbstractController
{
    private $objSearch	                = null;
    private $errorMessages              = array();
    private $debugMessages              = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
        $this->objSearch = new FrontEndSearchManager($this->conf, $this->lang, $this->dbSets->getAll());
    }

    /**
     * Are we ordering only those item models who has available units in stock?
     * @param $paramOrderId
     * @return int
     */
    private function getTotalAvailableUnitsOfSelectedItemModels($paramOrderId)
    {
        // Create mandatory instances
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $taxPercentage = $objTaxManager->getTaxPercentage($this->objSearch->getPickupLocationId(), $this->objSearch->getReturnLocationId());
        $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $this->objSearch->getPickupLocationId());
        $locationUniqueIdentifier = $objLocation->getUniqueIdentifier();

        $objSearchItemModelsManager = new SearchItemModelsManager(
            $this->conf, $this->lang, $this->dbSets->getAll(), $taxPercentage, $locationUniqueIdentifier,
                $paramOrderId, $this->objSearch->getCouponCode()
        );
        $availableItemModelIds = $objSearchItemModelsManager->getAvailableItemModelIds(
            $this->objSearch->getPickupLocationId(),
            $this->objSearch->getReturnLocationId(),
            $this->objSearch->getFleetPartnerId(),
            $this->objSearch->getManufacturerId(),
            $this->objSearch->getClassId(),
            $this->objSearch->getAttributeId1(),
            $this->objSearch->getAttributeId2()
        );
        $selectedItemModelIds = $objSearchItemModelsManager->getExistingSelectedItemModelIds($this->objSearch->getItemModelIds(), $availableItemModelIds);

        $itemModelsTotalSelectedUnits = $this->objSearch->getItemModelsTotalSelectedUnits();
        if($itemModelsTotalSelectedUnits == 0)
        {
            // Additional Error: Please select at least one item
            $this->errorMessages[] = $this->lang->getText('LANG_ITEMS_PLEASE_SELECT_AT_LEAST_ONE_ITEM_ERROR_TEXT');
        }

        $availableUnitsOfSelectedItemModels = $objSearchItemModelsManager->getItemModelsWithPricesAndOptions(
            $selectedItemModelIds, $this->objSearch->getItemModelUnits(), $this->objSearch->getItemModelOptions(),
            $this->objSearch->getExpectedPickupTimestamp(), $this->objSearch->getExpectedReturnTimestamp(), false
        );

        $totalAvailableUnitsOfSelectedItemModels = sizeof($availableUnitsOfSelectedItemModels);

        // Add to debug
        $this->debugMessages[] = "Item models total selected units: ".$itemModelsTotalSelectedUnits;

        return $totalAvailableUnitsOfSelectedItemModels;
    }

    /**
     * @param string $paramProcessingLayout
     * @param string $paramProcessingStyle
     * @param string $paramReceivedLayout
     * @param string $paramReceivedStyle
     * @param string $paramUpdatedLayout
     * @param string $paramUpdatedStyle
     * @param string $paramFailureLayout
     * @param string $paramFailureStyle
     * @return string
     * @throws \Exception
     */
    public function getContent(
        $paramProcessingLayout = "Details", $paramProcessingStyle = "",
        $paramReceivedLayout = "Details", $paramReceivedStyle = "",
        $paramUpdatedLayout = "Details", $paramUpdatedStyle = "",
        $paramFailureLayout = "Details", $paramFailureStyle = ""
    ) {
        // Create mandatory instances
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrdersObserver->cancelExpired();
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPaymentMethodsObserver = new PaymentMethodsObserver($this->conf, $this->lang, $this->dbSets->getAll());


        // Process customer params
        $customerParams = array();
        if(isset($_POST['customer_title'])) { $customerParams['title'] = $_POST['customer_title']; }
        if(isset($_POST['customer_first_name'])) { $customerParams['first_name'] = $_POST['customer_first_name']; }
        if(isset($_POST['customer_last_name'])) { $customerParams['last_name'] = $_POST['customer_last_name']; }
        if(isset($_POST['customer_birth_year'], $_POST['customer_birth_month'], $_POST['customer_birth_day']))
        {
            $customerParams['birthdate'] = StaticValidator::getValidISO_Date("{$_POST['customer_birth_year']}-{$_POST['customer_birth_month']}-{$_POST['customer_birth_day']}");
        }
        if(isset($_POST['customer_street_address'])) { $customerParams['street_address'] = $_POST['customer_street_address']; }
        if(isset($_POST['customer_city'])) { $customerParams['city'] = $_POST['customer_city']; }
        if(isset($_POST['customer_state'])) { $customerParams['state'] = $_POST['customer_state']; }
        if(isset($_POST['customer_zip_code'])) { $customerParams['zip_code'] = $_POST['customer_zip_code']; }
        if(isset($_POST['customer_country'])) { $customerParams['country'] = $_POST['customer_country']; }
        if(isset($_POST['customer_phone'])) { $customerParams['phone'] = $_POST['customer_phone']; }
        // NOTE: Customer e-mail for new order must match only for FM 5.0.1 and may not in FM 6.0.0+ (we do not still block that for order edit, to allow other admins to edit their client orders)
        if($this->orderCode == '' && is_user_logged_in())
        {
            $customerParams['email'] = (new User($this->conf, $this->lang, get_current_user_id()))->getEmail();
        } else if(isset($_POST['customer_email']))
        {
            $customerParams['email'] = $_POST['customer_email'];
        }
        if(isset($_POST['customer_comments'])) { $customerParams['comments'] = $_POST['customer_comments']; }

        // Process payment method param
        $paramPaymentMethodId = isset($_POST['payment_method_id']) ? $_POST['payment_method_id'] : 0;

        // Set default
        $processingPageOutput = '';
        $allNotificationsSent = true;
        $accountId = 0;
        $customerId = 0;
        $canProcess = true; // By default we can proceed if no errors occured later on the way
        $completed = false;
        $isValidReCaptcha = true;
        $existingCouponCode = '';

        //echo "INITIAL REQUEST VARS: ".nl2br(print_r($_REQUEST, true));
        //echo "INITIAL SESSION VARS: ".nl2br(print_r($_SESSION, true));
        //echo "INITIAL COOKIE VARS: ".nl2br(print_r($_COOKIE, true));

        // Second - process the order code if provided
        $existingOrderId = $objOrdersObserver->getIdByCode($this->orderCode);
        $objExistingOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $existingOrderId);
        if($this->orderCode != '' && $objExistingOrder->isFrozen() === false)
        {
            // Can proceed
            $existingCouponCode = $objExistingOrder->getCouponCode();
            $this->objSearch->setVariablesByOrderId($existingOrderId);
        }

        // Third - set object variables - allow to override by _POST, _GET or _SESSION (or _COOKIE if sessions are not used)
        $this->objSearch->setVariables();

        // Fourth - validate time input
        $this->objSearch->validateInBeforeOut($this->objSearch->getExpectedPickupTimestamp(), $this->objSearch->getExpectedReturnTimestamp());
        $this->objSearch->validateTimeInput($this->objSearch->getExpectedPickupTimestamp(), $this->objSearch->getExpectedReturnTimestamp());

        // Fifth - validate pick-up
        $this->objSearch->validatePickupInput($this->objSearch->getPickupLocationId(), $this->objSearch->getExpectedPickupTimestamp());

        // Sixth - validate return
        $this->objSearch->validateReturnInput($this->objSearch->getReturnLocationId(), $this->objSearch->getExpectedReturnTimestamp());

        if ($this->objSearch->searchEnabled() && $this->objSearch->isValidSearch())
        {
            // Data defined successfully, now remove session variables
            $this->objSearch->unsetVariablesCache();

            // Set fresh session variables
            $this->objSearch->cacheVariables();
        }

        // First - get the prices summary
        // NOTE: Order is important for placing an order, which can be saved with saveOrderData(..) only after objSearch->getPriceSummary() was called.
        // This is because if we would save data first, and we would have only 1 car in database for that period,
        // then our price summary would not be able to pull that item as available for ordering
        $priceSummary = $this->objSearch->getPriceSummary($existingOrderId);

        // Second - Validate ReCaptcha - this has to be done before any data saving to database, to avoid spam
        // NOTE: For non-logged in WP Users only
        if(is_user_logged_in() === false && $this->orderCode == "" && $this->dbSets->get('conf_recaptcha_enabled') == 1)
        {
            $fileToInclude = $this->conf->getLibrariesPath().'ReCaptchaToFleetManagementTranspiler.php';
            require_once $fileToInclude;
            $objReCaptcha = new \ReCaptchaToFleetManagementTranspiler($this->conf, $this->lang, $this->dbSets->getAll());
            $reCaptchaResponse= isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
            $isValidReCaptcha = $objReCaptcha->isValid($reCaptchaResponse);
            if($isValidReCaptcha === false)
            {
                $canProcess = false;
            }
            $this->errorMessages = array_merge($this->errorMessages, $objReCaptcha->getErrorMessages());
        }

        $selectedItemModelsHasAvailableUnits = $this->getTotalAvailableUnitsOfSelectedItemModels($objExistingOrder->getId()) > 0 ? true : false;
        $isValid = $this->objSearch->isValidSearch() && ($this->orderCode == '' || ($this->orderCode != '' && $objExistingOrder->isUpcoming()));

        // Third - check if we can still proceed
        if ($canProcess)
        {
            if($this->objSearch->searchEnabled() === false || $selectedItemModelsHasAvailableUnits === false || $isValid === false || sizeof($this->errorMessages) > 0)
            {
                $canProcess = false;
            }
        }

        // Fourth - validate payment method
        if($existingOrderId > 0)
        {
            $paymentMethodId = $objPaymentMethodsObserver->getIdByCode($objExistingOrder->getPaymentMethodCode());
        } else
        {
            $paymentMethodId = StaticValidator::getValidPositiveInteger($paramPaymentMethodId, 0);
        }
        $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->dbSets->getAll(), $paymentMethodId);

         $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $this->objSearch->getPickupLocationId());

         $locationDetails = $objLocation->getDetails();

        if($locationDetails)
        {
            $onRemoteWebsite = $locationDetails['on_remote_website'];
        }
        else
        {
            $onRemoteWebsite = 0;
        }

         if($onRemoteWebsite == 0)
         {
             $pmRequired = $this->dbSets->get('conf_prepayment_enabled') == 1 && $objPaymentMethodsObserver->getTotalEnabled() > 0 ? true : false;
         } else
         {
             $pmRequired = false;
         }

        $pmExists = true;
        $pmEnabled = true;
        if($canProcess && $pmRequired)
        {
            $pmExists = $objPaymentMethodsObserver->checkExists($paymentMethodId);
            if($pmExists === false)
            {
                $canProcess = false;
                $this->errorMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_DOES_NOT_EXIST_ERROR_TEXT');
            }
            if($pmExists && $objPaymentMethod->isEnabled() === false)
            {
                $pmEnabled = false;
                $canProcess = false;
                $this->errorMessages[] = $this->lang->getText('LANG_PAYMENT_METHOD_DISABLED_ERROR_TEXT');
            }
        }

        // Fifth - process customer
        if($canProcess)
        {
            if(isset($customerParams['birthdate'], $customerParams['email']))
            {
                $customerId = $objCustomersObserver->getIdByEmailAndBirthdate($customerParams['email'], $customerParams['birthdate']);
            }

            $customerData = (new CustomerController($this->conf, $this->lang))->processData($customerParams);
            $customerId = isset($customerData['customer_id']) ? $customerData['customer_id'] : 0;
            if($customerId == 0 || sizeof($customerData['errors']) > 0)
            {
                $canProcess = false;
            }

            // Add the rest errors
            $this->errorMessages = array_merge($this->errorMessages, $customerData['errors']);
        }

        // Sixth - process account
        if($canProcess)
        {
            $accountData = (new AccountController($this->conf, $this->lang))->processData($customerId);
            $accountId = isset($accountData['account_id']) ? $accountData['account_id'] : 0;
            if(($accountId == 0 && ConfigurationInterface::AUTOMATICALLY_CREATE_ACCOUNT == 1) || sizeof($accountData['errors']) > 0)
            {
                $canProcess = false;
            }

            // Add the rest errors
            $this->errorMessages = array_merge($this->errorMessages, $accountData['errors']);
            // Add the rest debug messages
            $this->debugMessages = array_merge($this->debugMessages, $accountData['debug_messages']);
        }

        // Validate if age pass limitations for all item models, but only if birthdate is required
        $isAllowedAgeForAllItemModels = true;
        if($canProcess && $this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED"))
        {
            $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
            // Customer was already updated, so the bellow is a correct call
            $customerAge = (new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $customerId))->getAge();
            $isAllowedAgeForAllItemModels = $objItemModelsObserver->validateAgeForItemModels($this->objSearch->getItemModelIds(), $customerAge);
            if($isAllowedAgeForAllItemModels === false)
            {
                $canProcess = false;
            }

            $this->errorMessages = array_merge($this->errorMessages, $objItemModelsObserver->getSavedErrorMessages());
            $this->debugMessages = array_merge($this->debugMessages, $objItemModelsObserver->getSavedDebugMessages());
        }

        // Add additional debug messages to stack
        $additionalDebugMessages = array();
        $additionalDebugMessages[] = "<strong>INTERMEDIATE DEBUG LOG BEFORE NEW/EXISTING ORDER IS MODIFIED</strong>";
        $additionalDebugMessages[] = "Existing order ID: ".intval($existingOrderId);
        $additionalDebugMessages[] = "Existing coupon code: ".sanitize_text_field($existingCouponCode);
        $additionalDebugMessages[] = ""; // Extra line break
        $additionalDebugMessages[] = "Is new order: ".($this->orderCode == "" ? "Yes" : "No");
        $additionalDebugMessages[] = "ReCaptcha enabled: ".($this->dbSets->get('conf_recaptcha_enabled') == 1 ? "Yes" : "No");
        if($this->dbSets->get('conf_recaptcha_enabled') == 1)
        {
            $additionalDebugMessages[] = "Is valid ReCaptcha: ".var_export($this->orderCode == "" ? $isValidReCaptcha : "SKIP", true);
        }
        $additionalDebugMessages[] = "Selected item models has available units: ".var_export($selectedItemModelsHasAvailableUnits, true);
        $additionalDebugMessages[] = "Search enabled: ".var_export($this->objSearch->searchEnabled(), true);
        $additionalDebugMessages[] = ""; // Extra line break
        $additionalDebugMessages[] = "Was the order code provided by customer, param or via URL: ".($this->orderCode != '' ? "Yes" : "No");
        $additionalDebugMessages[] = "Is valid search / order: ".var_export($isValid, true);
        $additionalDebugMessages[] = "Payment method ID: ".intval($paymentMethodId);
        $additionalDebugMessages[] = "Is payment method required: ".($pmRequired ?  "Yes" : "No");
        if($pmRequired)
        {
            $additionalDebugMessages[] = "Payment method exists: ".($pmExists ?  "Yes" : "No");
        }
        if($pmRequired && $pmExists)
        {
            $additionalDebugMessages[] = "Payment method enabled: ".($pmEnabled ?  "Yes" : "No");
        }
        $additionalDebugMessages[] = ""; // Extra line break
        $additionalDebugMessages[] = "Customer birthdate: ".(isset($customerParams['birthdate']) ? sanitize_text_field($customerParams['birthdate']) : "N/A");
        $additionalDebugMessages[] = "Customer e-mail: ".(isset($customerParams['email']) ? sanitize_text_field($customerParams['email']) : "N/A");
        $additionalDebugMessages[] = "Customer ID: ".intval($customerId);
        $additionalDebugMessages[] = "Automatically create WordPress account, if not exist for e-mail: ".(ConfigurationInterface::AUTOMATICALLY_CREATE_ACCOUNT == 1  ? "Yes" : "No");
        $additionalDebugMessages[] = "Account ID: ".intval($accountId);
        $additionalDebugMessages[] = "Birthdate required: ".var_export($this->dbSets->getCustomerFieldStatus("birthdate", "REQUIRED"), true);
        $additionalDebugMessages[] = "Is allowed age for all item models (customer id - ".intval($customerId)."): ".var_export($isAllowedAgeForAllItemModels, true);
        $additionalDebugMessages[] = "Total errors till this moment: ".sizeof($this->errorMessages);
        $additionalDebugMessages[] = "Can process: ".var_export($canProcess, true);
        $additionalDebugMessages[] = "----------------------------------------------";

        // Add to debug log stack
        $this->debugMessages = array_merge($this->debugMessages, $additionalDebugMessages);

        if(StaticValidator::inWP_Debug())
        {
            // Print debug messages to screen
            echo '<br /><em><strong>(WP_DEBUG &amp;&amp; WP_DEBUG_DISPLAY IS ENABLED)</strong></em>';
            echo '<br />'.wp_kses_post(nl2br(implode("\n", $this->debugMessages)));
            // We can die here, if we want to keep our _SESSION data without destroying yet for repeated debug for next page refresh with F5
            //die();
        }

        $finalOrderId = 0;
        if($canProcess)
        {
            // If that is online payment - we need to cancel old order before
            $isOnlinePayment = $objPaymentMethod->isOnlinePayment();
            if ($existingOrderId > 0 && $isOnlinePayment)
            {
                // Cancel previous online booking
                $objExistingOrder->cancel();
                if($this->dbSets->get('conf_send_emails') == 1)
                {
                    $objOrderNotificationsObserver->sendOrderCancelledNotifications($existingOrderId, true);
                }

                if(StaticValidator::inWP_Debug())
                {
                    $arrMessages = array_merge(
                        $objExistingOrder->getOkayMessages(), $objOrderNotificationsObserver->getSavedOkayMessages(),
                        $objExistingOrder->getErrorMessages(), $objOrderNotificationsObserver->getSavedErrorMessages()
                    );
                    // Put notes of existing booking
                    echo '<br />Old order message list:<br />'.StaticFormatter::getPrintMessage($arrMessages);
                }
            }

            $objPickupLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $this->objSearch->getPickupLocationId());
            $objReturnLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $this->objSearch->getReturnLocationId());

            // Pass all search data and save the order (order is important, because we use order's customer id in order manager
            // NOTE: Order is important, the order can only be saved after objCustomer->saveCustomerData(..) was called to not to loose customer id
            $orderParams = $this->objSearch->getInputDataArray();
            $orderParams['customer_id'] = $customerId;
            $orderParams['payment_method_code'] = $objPaymentMethod->getCode();
            $orderParams['pickup_location_unique_identifier'] = $objPickupLocation->getUniqueIdentifier();
            $orderParams['return_location_unique_identifier'] = $objReturnLocation->getUniqueIdentifier();

            $finalOrderId = $objOrdersObserver->saveOrder_ItsOptions_AndGetSavedOrderId($existingOrderId, $isOnlinePayment, $orderParams);
            if($finalOrderId == 0)
            {
                $canProcess = false;
            }
        }

        $finalOrderCode = "";
        $finalOrderCouponCode = "";
        if($canProcess)
        {
            $completed = true;

            // We have to create a new order object here, as the order was updated
            $objFinalOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $finalOrderId);

            // NOTE: Order is important, order invoice html can be created only after OrderManager->saveOrderData(..) was called
            // Fifth - Create the invoice
            $objInvoiceController = new InvoiceController($this->conf, $this->lang);
            $retData = $objInvoiceController->createInvoice(
                $finalOrderId, $customerId, $paymentMethodId, $priceSummary
            );
            if(isset($retData['errors']) && is_array($retData['errors']) && sizeof($retData['errors']) > 0)
            {
                // Invoice creating had failed
                $completed = false;

                // Add errors to stack
                $this->errorMessages = array_merge($this->errorMessages, $retData['errors']);
            }
            $finalOrderCode = $objFinalOrder->getCode();
            $finalOrderCouponCode = $objFinalOrder->getCouponCode();
        }

        // Sixth - Process payment method
        // NOTE #2: We do not fail to process it even if we have payment issues, as for payment we suppose to be able to retry later,
        //          but for more clear use, we have it on separate step
        $paymentErrorMessages = array();
        $paymentDebugMessages = array();
        $processedPaymentData = array();
        $paymentCompletedTransactionId = 0;
        if($completed)
        {
            $processedPaymentData = $objPaymentMethod->getProcessingPage($finalOrderCode, $priceSummary['overall']['total_pay_now']);
            $processingPageOutput = isset($processedPaymentData['trusted_output_html']) ? $processedPaymentData['trusted_output_html'] : '';
            $paymentCompletedTransactionId = isset($processedPaymentData['payment_completed_transaction_id']) ? $processedPaymentData['payment_completed_transaction_id'] : 0;

            // Retrieve error messages to log transaction
            $paymentErrorMessages = array_merge(
                $objPaymentMethod->getErrorMessages()
            );

            // Add debug messages to log transaction
            $paymentDebugMessages = array_merge(
                $objPaymentMethod->getOkayMessages(),
                $objPaymentMethod->getErrorMessages(),
                $objPaymentMethod->getDebugMessages()
            );

            // Add all errors & debug
            $this->errorMessages = array_merge($this->errorMessages, $paymentErrorMessages);
            $this->debugMessages = array_merge($this->debugMessages, $paymentDebugMessages);
        }

        // Seventh - If there was a payment transaction processed on this page (used by some payment methods, i.e. Stripe)
        // NOTE #1: On processing page only 'PAYMENT' transaction can be processed
        // NOTE #2: We do not fail to process it even if we have payment issues, as for payment we suppose to be able to retry later,
        //          but for more clear use, we have it on separate step
        if($completed && $paymentCompletedTransactionId > 0)
        {
            // For processing page transactions we always create a log if transaction was created
            $objTransaction = new Transaction($this->conf, $this->lang, $this->dbSets->getAll(), $paymentCompletedTransactionId);
            $objTransaction->createLog($processedPaymentData, $paymentErrorMessages, $paymentDebugMessages);
        }

        // Eighth - Send phone & e-mail notification about received order
        if($completed && $this->dbSets->get('conf_send_emails') == 1)
        {
            $allOrderNotificationsSent = $objOrderNotificationsObserver->sendOrderReceivedNotifications($finalOrderId, true);
            if($allOrderNotificationsSent === false)
            {
                // Set all notifications sent to false
                $allNotificationsSent = false;
            }
        }

        // Add all errors
        $orderErrorMessages = $objOrdersObserver->getSavedErrorMessages();
        $notificationErrorMessages = $objOrderNotificationsObserver->getSavedErrorMessages();
        $this->errorMessages = array_merge($this->errorMessages, $orderErrorMessages, $notificationErrorMessages);

        // Add debug
        $orderDebugMessages = array_merge(
            $objOrdersObserver->getSavedOkayMessages(),
            $objOrdersObserver->getSavedErrorMessages(),
            $objOrdersObserver->getSavedDebugMessages()
        );
        $notificationDebugMessages = array_merge(
            $objOrderNotificationsObserver->getSavedOkayMessages(),
            $objOrderNotificationsObserver->getSavedErrorMessages(),
            $objOrderNotificationsObserver->getSavedDebugMessages()
        );
        $this->debugMessages = array_merge(
            $this->debugMessages,
            $orderDebugMessages,
            $notificationDebugMessages
        );

        // Add additional debug messages to stack
        $additionalDebugMessages = array();
        $additionalDebugMessages[] = "<strong>LAST DEBUG LOG AFTER ORDER IS SAVED, BUT BEFORE SESSION DESTROY</strong>";
        $additionalDebugMessages[] = "Final order id: ".sanitize_text_field($finalOrderId);
        $additionalDebugMessages[] = "Final order code: ".sanitize_text_field($finalOrderCode);
        $additionalDebugMessages[] = "Final order coupon code: ".sanitize_text_field($finalOrderCouponCode);
        $additionalDebugMessages[] = "Completed: ".var_export($completed, true); // Do not translate debug
        $additionalDebugMessages[] = "All notifications sent: ".var_export($allNotificationsSent, true);
        $additionalDebugMessages[] = "PAYMENT ERRORS: ".print_r($paymentErrorMessages, true);
        $additionalDebugMessages[] = "PAYMENT DEBUG MESSAGES: ".print_r($paymentDebugMessages, true);
        $additionalDebugMessages[] = ""; // Extra line break
        $additionalDebugMessages[] = "ORDER ERRORS: ".print_r($orderErrorMessages, true);
        $additionalDebugMessages[] = "ORDER DEBUG MESSAGES: ".print_r($orderDebugMessages, true);
        $additionalDebugMessages[] = "NOTIFICATION ERRORS: ".print_r($notificationErrorMessages, true);
        $additionalDebugMessages[] = "NOTIFICATION DEBUG MESSAGES: ".print_r($notificationDebugMessages, true);
        $additionalDebugMessages[] = ""; // Extra line break
        $additionalDebugMessages[] = "PROCESSING PAGE OUTPUT (ESCAPED HTML): ".esc_html($processingPageOutput);
        $additionalDebugMessages[] = "----------------------------------------------";

        // Add to debug log stack
        $this->debugMessages = array_merge($this->debugMessages, $additionalDebugMessages);

        if(StaticValidator::inWP_Debug())
        {
            // Print debug messages to screen
            echo '<br /><em><strong>(WP_DEBUG &amp;&amp; WP_DEBUG_DISPLAY IS ENABLED)</strong></em>';
            echo '<br />'.wp_kses_post(nl2br(implode("\n", $additionalDebugMessages)));
            // We can die here, if we want to keep our _SESSION data without destroying yet, but with data saved to database already for repeated debug for next page refresh with F5
            //die();
        }

        if($canProcess)
        {
            // Unset the variables cache
            $this->objSearch->unsetVariablesCache();

            // Finally, destroy the session
            // Note: Requires PHP 5.4+
            if(session_status() === PHP_SESSION_ACTIVE)
            {
                session_destroy();
            }
        }

        // Set the view variables
        $this->view->priceSummary = $priceSummary; // We need this for Enhanced Ecommerce
        $this->view->processingPageOutput = $processingPageOutput;
        $this->view->newOrder = $this->orderCode != "" ? false : true; // We need this for Enhanced Ecommerce
        $this->view->orderCode = $finalOrderCode; // We need this for Order Confirmed Page and Enhanced Ecommerce
        $this->view->couponCode = $finalOrderCouponCode; // We need this for Enhanced Ecommerce
        $this->view->errorMessages = implode("\n\n", array_merge($this->errorMessages, $this->objSearch->getErrorMessages()));

        // Get the template
        // NOTE! Do not use 'sizeof(items)' in verification bellow, because car was just booked and removed from available db
        $isOnlinePayment = $objPaymentMethod->isOnlinePayment();
        if($this->objSearch->searchEnabled() && $canProcess && $isOnlinePayment === true)
        {
            $retContent = $this->getTemplate('Search', 'Step6Processing', $paramProcessingLayout, $paramProcessingStyle);
        } else if($this->objSearch->searchEnabled() && $canProcess && $isOnlinePayment === false && $this->orderCode == "")
        {
            // New order received
            $retContent = $this->getTemplate('Order', 'Received', $paramReceivedLayout, $paramReceivedStyle);
        } else if($this->objSearch->searchEnabled() && $canProcess && $isOnlinePayment === false && $this->orderCode != "")
        {
            // Existing order updated
            $retContent = $this->getTemplate('Order', 'Updated', $paramUpdatedLayout, $paramUpdatedStyle);
        } else if($this->objSearch->searchEnabled() && $canProcess === false)
        {
            $retContent = $this->getTemplate('Search', 'Failure', $paramFailureLayout, $paramFailureStyle);
        } else
        {
            $retContent = '';
        }

        return $retContent;
    }
}