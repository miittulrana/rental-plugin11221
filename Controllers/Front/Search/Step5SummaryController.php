<?php
/**
 * Search step no. 5
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Search;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Payment\PaymentMethodsObserver;
use FleetManagement\Models\Payment\PaymentMethod;
use FleetManagement\Models\Search\FrontEndSearchManager;
use FleetManagement\Models\Search\SearchItemModelsManager;
use FleetManagement\Controllers\Front\AbstractController;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\User\User;

final class Step5SummaryController extends AbstractController
{
    private $objSearch          = null;
    private $additionalErrors   = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @param string $paramFailureLayout
     * @param string $paramFailureStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "Table", $paramStyle = "", $paramFailureLayout = "Details", $paramFailureStyle = "")
    {
        // Create local mandatory instances
        $objSearch = new FrontEndSearchManager($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrdersObserver->cancelExpired();
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPaymentMethodsObserver = new PaymentMethodsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // DEBUG
        //echo "INITIAL REQUEST VARS: ".nl2br(print_r($_REQUEST, true));
        //echo "INITIAL SESSION VARS: ".nl2br(print_r($_SESSION, true));
        //echo "INITIAL COOKIE VARS: ".nl2br(print_r($_COOKIE, true));

        // Second - process the order code if provided
        $orderId = $objOrdersObserver->getIdByCode($this->orderCode);
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $orderId);
        $orderDetails = $objOrder->getDetails(true); // It will always return data, even if it not exist
        if($this->orderCode != '' && $objOrder->isFrozen() === false)
        {
            $objSearch->setVariablesByOrderId($orderId);
        }

        // Third - set object variables - allow to override by _POST, _GET or _SESSION
        $objSearch->setVariables();

        // Fourth - validate time input
        $objSearch->validateInBeforeOut($objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp());
        $objSearch->validateTimeInput($objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp());

        // Fifth - validate pick-up
        $objSearch->validatePickupInput($objSearch->getPickupLocationId(), $objSearch->getExpectedPickupTimestamp());

        // Sixth - validate return
        $objSearch->validateReturnInput($objSearch->getReturnLocationId(), $objSearch->getExpectedReturnTimestamp());

        if ($objSearch->searchEnabled() && $objSearch->isValidSearch())
        {
            // Data defined successfully, now remove session variables
            $objSearch->unsetVariablesCache();

            // Set fresh session variables
            $objSearch->cacheVariables();
        }

        // DEBUG
        //echo "<br />UPDATED SESSION VARS: ".nl2br(print_r($_SESSION, true));
        //echo "<br />UPDATED COOKIE VARS: ".nl2br(print_r($_COOKIE, true));

        // This is only possible if we are editing the booking
        $paymentMethodId = $orderId > 0 ? $objPaymentMethodsObserver->getIdByCode($objOrder->getPaymentMethodCode()) : 0;
        $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->dbSets->getAll(), $paymentMethodId);
        $customerIdForExistingOrder = $objOrder->getCustomerId();
        $objCustomer = new Customer($this->conf, $this->lang, $this->dbSets->getAll(), $customerIdForExistingOrder);
        $customerDetails = $objCustomer->getDetails(true);

        // START: Are we booking only those items who has available units in stock?
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $taxPercentage = $objTaxManager->getTaxPercentage($objSearch->getPickupLocationId(), $objSearch->getReturnLocationId());
        $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $objSearch->getPickupLocationId());
        $locationUniqueIdentifier = $objLocation->getUniqueIdentifier();
        $objSearchItemModelsManager = new SearchItemModelsManager(
            $this->conf, $this->lang, $this->dbSets->getAll(), $taxPercentage, $locationUniqueIdentifier, $orderId, $objSearch->getCouponCode()
        );
        $availableItemModelIds = $objSearchItemModelsManager->getAvailableItemModelIds(
            $objSearch->getPickupLocationId(),
            $objSearch->getReturnLocationId(),
            $objSearch->getFleetPartnerId(),
            $objSearch->getManufacturerId(),
            $objSearch->getClassId(),
            $objSearch->getAttributeId1(),
            $objSearch->getAttributeId2()
        );
        $selectedItemModelIds = $objSearchItemModelsManager->getExistingSelectedItemModelIds($objSearch->getItemModelIds(), $availableItemModelIds);
        $itemModelsTotalSelectedUnits = $objSearch->getItemModelsTotalSelectedUnits();
        if($itemModelsTotalSelectedUnits == 0)
        {
            // Additional Error: Please select at least one item
            $this->additionalErrors[] = $this->lang->getText('LANG_ITEMS_PLEASE_SELECT_AT_LEAST_ONE_ITEM_ERROR_TEXT');
        }
        $availableOfSelectedItems = $objSearchItemModelsManager->getItemModelsWithPricesAndOptions(
            $selectedItemModelIds, $objSearch->getItemModelUnits(), $objSearch->getItemModelOptions(),
            $objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp(), false
        );

        $totalSelectedItemModels = sizeof($selectedItemModelIds);
        $totalAvailableOfSelectedItemModels = sizeof($availableOfSelectedItems);
        // END: Are we booking only those items who has available units in stock?

        // For output only: start
        if($this->orderCode != "")
        {
            $pageLabel = $this->lang->getText('LANG_ORDER_RENTAL_DETAILS_TEXT')." - ".$this->lang->getText('LANG_ORDER_CODE_TEXT')." ".$this->orderCode." ".$this->lang->getText('LANG_ORDER_EDIT_TEXT');
            if($objSearch->getCouponCode() != '')
            {
                $pageLabel .= '. '.$this->lang->getText('LANG_COUPON_TEXT').': '.$objSearch->getCouponCode();
            }
        } else
        {
            $pageLabel = $this->lang->getText('LANG_ORDER_RENTAL_DETAILS_TEXT');
            if($objSearch->getCouponCode() != '')
            {
                $pageLabel .= '. '.$this->lang->getText('LANG_COUPON_TEXT').': '.$objSearch->getCouponCode();
            }
        }

        $localBoolCustomerTitleRequired = $this->dbSets->getCustomerFieldStatus("title", "REQUIRED") ? true : false;

        $localEnabledOnlineMethods = $objPaymentMethodsObserver->getTotalEnabledOnline();
        $localEnabledLocalMethods = $objPaymentMethodsObserver->getTotalEnabledLocally();
        if($localEnabledOnlineMethods > 0 && $localEnabledLocalMethods > 0)
        {
            $payNowText = $this->lang->getText('LANG_PAY_NOW_OR_AT_PICKUP_TEXT');
        } else if($localEnabledOnlineMethods > 0 && $localEnabledLocalMethods == 0)
        {
            $payNowText = $this->lang->getText('LANG_PAYMENT_PAY_NOW_TEXT');
        } else
        {
            $payNowText = $this->lang->getText('LANG_PAYMENT_PAY_AT_PICKUP_TEXT');
        }

        $selectedPaymentMethodName = "";
        $selectedPaymentMethodDescription = "";
        if($this->orderCode != "")
        {
            // For edit only
            $localPaymentMethodDetails = $objPaymentMethod->getDetails();
            if(!is_null($localPaymentMethodDetails))
            {
                $selectedPaymentMethodName = $localPaymentMethodDetails['print_translated_payment_method_name'];
                $selectedPaymentMethodDescription = $localPaymentMethodDetails['translated_payment_method_description_html'];
            }
        }
        $priceSummary = $objSearch->getPriceSummary($orderId);
        // We must make check for ReCaptcha site key, because otherwise it will throw runtime error and break our template load, and we don't want that to happen
        // NOTE: We always show ReCaptcha, despite it is new or existing order,
        //       because we also want to avoid mass-updating of existing orders
        $showReCaptcha = is_user_logged_in() === false && $this->dbSets->get('conf_recaptcha_enabled') == 1 && $this->dbSets->get('conf_recaptcha_site_key') != '';

        $showLocationFees = false;
        if(is_array($priceSummary['pickup']) && $priceSummary['pickup']['unit']['current_pickup_fee'] > 0.00)
        {
            $showLocationFees = true;
        } else if(is_array($priceSummary['return']) && $priceSummary['return']['unit']['current_return_fee'] > 0.00)
        {
            $showLocationFees = true;
        }


        $locationDetails = $objLocation->getDetails();
        if($locationDetails)
        {
            $onRemoteWebsite = $locationDetails['on_remote_website'];
        }
        else
        {
            $onRemoteWebsite = 0;
        }



        // For output only: end

        // Set the view variables
        $this->fillSearchFieldsView(); // Fill search fields view
        $this->fillCustomerFieldsView(); // Fill customer fields view
        $this->view->objSearch = $objSearch;
        $this->view->isLoggedIn = is_user_logged_in();
        $this->view->guestCustomerLookupAllowed = ConfigurationInterface::GUEST_CUSTOMER_LOOKUP_ALLOWED;
        $this->view->showLoginForm = ConfigurationInterface::SHOW_LOGIN_FORM;
        $this->view->orderCodeParam = $this->conf->getOrderCodeParam();
        $this->view->orderCode = $orderDetails['booking_code'];
        $this->view->couponCode = $objSearch->getCouponCode(); // In this step it still should be taken from search, not from order, as order may not yet be created
        $this->view->showAll = $objSearch->getShowAllArray();
        $this->view->newOrder = $this->orderCode != "" ? false : true;
        // NOTE: It should be always allowed to select a customer for logged-in user, despite if is a new or existing order
        $this->view->trustedCustomersDropdownOptionsHTML = $objCustomersObserver->getTrustedDropdownOptionsHTML(
            get_current_user_id(), $customerIdForExistingOrder, "", $this->lang->getText($this->orderCode == '' ? 'LANG_CUSTOMER_ADD_NEW2_TEXT' : 'LANG_CUSTOMER_ADD_EDIT2_TEXT'), "ANY", false
        );
        $this->view->trustedCustomerTitlesDropdownOptionsHTML = $objCustomer->getTrustedTitlesDropdownOptionsHTML(
            $customerDetails['title'], $localBoolCustomerTitleRequired
        );
        $this->view->customerFirstName = $customerDetails['first_name'];
        $this->view->customerLastName = $customerDetails['last_name'];
        $this->view->trustedCustomerBirthYearSearchDropdownOptionsHTML = StaticFormatter::generateTrustedNumberDropdownOptionsHTML(current_time("Y") - 99, current_time("Y") - 10, '0000', '', $this->lang->getText('LANG_YEAR_OF_BIRTH_TEXT'), true);
        $this->view->trustedCustomerBirthMonthSearchDropdownOptionsHTML = StaticFormatter::generateTrustedNumberDropdownOptionsHTML(1, 12, '00', "", $this->lang->getText('LANG_MONTH_SELECT_TEXT'), true);
        $this->view->trustedCustomerBirthDaySearchDropdownOptionsHTML = StaticFormatter::generateTrustedNumberDropdownOptionsHTML(1, 31, '00', "", $this->lang->getText('LANG_DAY_SELECT_TEXT'), true);
        $this->view->trustedCustomerBirthYearDropdownOptionsHTML = StaticFormatter::generateTrustedNumberDropdownOptionsHTML(current_time("Y") - 80, current_time("Y") - 10, $customerDetails['birth_year'], '', $this->lang->getText('LANG_YEAR_SELECT_TEXT'), true);
        $this->view->trustedCustomerBirthMonthDropdownOptionsHTML = StaticFormatter::generateTrustedNumberDropdownOptionsHTML(1, 12, $customerDetails['birth_month'], '', $this->lang->getText('LANG_MONTH_SELECT_TEXT'), true);
        $this->view->trustedCustomerBirthDayDropdownOptionsHTML = StaticFormatter::generateTrustedNumberDropdownOptionsHTML(1, 31, $customerDetails['birth_day'], '', $this->lang->getText('LANG_DAY_SELECT_TEXT'), true);
        $this->view->customerStreetAddress = $customerDetails['street_address'];
        $this->view->customerCity = $customerDetails['city'];
        $this->view->customerState = $customerDetails['state'];
        $this->view->customerZIP_Code = $customerDetails['zip_code'];
        $this->view->customerCountry = $customerDetails['country'];
        $this->view->customerPhone = $customerDetails['phone'];
        // NOTE: Customer e-mail on new order must match only for FM 5.0.1 and may not in FM 6.0.0+
        $this->view->customerEmail = ($this->orderCode == '' && is_user_logged_in()) ? (new User($this->conf, $this->lang, get_current_user_id()))->getEmail() : $customerDetails['email'];
        $this->view->customerComments = $customerDetails['comments'];

        $this->view->pageLabel = $pageLabel;
        $this->view->payNowText = $payNowText;
        $this->view->priceSummary = $priceSummary;
        $this->view->showLocationFees = $showLocationFees;
        $this->view->paymentMethods = ($onRemoteWebsite == 0) ? $objPaymentMethodsObserver->getPaymentMethods($paymentMethodId, $priceSummary['overall']['total_pay_now']) : array();
        $this->view->selectedPaymentMethodName = $selectedPaymentMethodName;
        $this->view->selectedPaymentMethodDescription = $selectedPaymentMethodDescription;
        $this->view->pickupLocations = $objLocationsObserver->getPrintPickups($objSearch->getPickupLocationId(), $objSearch->getLocalPickupDayOfWeek());
        $this->view->returnLocations = $objLocationsObserver->getPrintReturns($objSearch->getReturnLocationId(), $objSearch->getLocalReturnDayOfWeek());
        $this->view->pickupMainColspan = $this->dbSets->getSearchFieldStatus("return_location", "VISIBLE") ? 1 : 3;
        $this->view->returnMainColspan = $this->dbSets->getSearchFieldStatus("pickup_location", "VISIBLE") ? 2 : 3;
        $this->view->pickupColspan = $this->dbSets->getSearchFieldStatus("return_date", "VISIBLE") ? 1 : 3;
        $this->view->returnColspan = $this->dbSets->getSearchFieldStatus("pickup_date", "VISIBLE") ? 1 : 2;
        $this->view->showReCaptcha = $showReCaptcha;
        $this->view->reCaptchaSiteKey = sanitize_text_field($this->dbSets->get('conf_recaptcha_site_key'));
        $this->view->searchPageAction = $this->actionPageId > 0 ? $this->lang->getTranslatedURL($this->actionPageId) : '';
        $this->view->termsAndConditionsURL = $this->lang->getTranslatedURL($this->dbSets->get('conf_terms_and_conditions_page_id'));
        $this->view->goBackURL = $this->actionPageId > 0 ? $this->lang->getTranslatedURL($this->actionPageId) : site_url();
        $this->view->errorMessages = implode("\n\n", array_merge($objSearch->getErrorMessages(), $this->additionalErrors));

        // Get template name
        if($totalAvailableOfSelectedItemModels > 0 && $objSearch->isValidSearch() && $itemModelsTotalSelectedUnits > 0)
        {
            $templateName = 'Step5Summary';
            $layout = sanitize_text_field($paramLayout);
            $style = sanitize_text_field($paramStyle);
        } else if(!$totalSelectedItemModels == 0 && $objSearch->isValidSearch() && $itemModelsTotalSelectedUnits > 0)
        {
            $templateName = 'FailureWithSearchAll';
            $layout = sanitize_text_field($paramFailureLayout);
            $style = sanitize_text_field($paramFailureStyle);
        } else
        {
            $templateName = 'Failure'; // Failure template
            $layout = sanitize_text_field($paramFailureLayout);
            $style = sanitize_text_field($paramFailureStyle);
        }

        // Get the template
        $retContent = $objSearch->searchEnabled() ? $this->getTemplate('Search', $templateName, $layout, $style) : '';

        return $retContent;
    }
}