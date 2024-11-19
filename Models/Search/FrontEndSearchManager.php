<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Search;
use FleetManagement\Models\AdditionalFee\AdditionalFee;
use FleetManagement\Models\AdditionalFee\AdditionalFeesObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\AdditionalFee\AdditionalFeeManager;
use FleetManagement\Models\Order\Period;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Tax\TaxesObserver;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Location\LocationFeeManager;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Prepayment\PrepaymentManager;

final class FrontEndSearchManager extends AbstractSearchManager implements StackInterface, SearchManagerInterface
{
	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
		parent::__construct($paramConf, $paramLang, $paramSettings);
	}

    public function setVariablesByOrderId($paramOrderId)
    {
        $objOrder = new Order($this->conf, $this->lang, $this->settings, $paramOrderId);
        $orderDetails = $objOrder->getDetails();
        if(!is_null($orderDetails))
        {
            // These variables are the regular params
            $this->couponCode = $orderDetails['coupon_code']; // used in all steps
            $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->settings);
            $this->pickupLocationId = $objLocationsObserver->getIdByCode($orderDetails['pickup_location_code']); // used in all steps
            $this->returnLocationId = $objLocationsObserver->getIdByCode($orderDetails['return_location_code']); // used in all steps
            $this->expectedPickupTimestamp = $orderDetails['pickup_timestamp'];
            $this->expectedReturnTimestamp = $orderDetails['return_timestamp'];
            $this->fleetPartnerId = $orderDetails['partner_id']; // used in step 2 (3)
            $this->manufacturerId = $orderDetails['manufacturer_id']; // used in step 2 (3)
            $this->classId = $orderDetails['class_id']; // used in step 2 (3)
            $this->attributeId1 = $orderDetails['attribute_id1']; // used in step 2 (3)
            $this->attributeId2 = $orderDetails['attribute_id2']; // used in step 2 (3)
            // These arrays are the regular params
            $this->itemModelIds = $orderDetails['item_model_ids'];
            $this->itemModelUnits = $orderDetails['item_model_units'];
            $this->itemModelOptions = $orderDetails['item_model_options'];
            $this->extraIds = $orderDetails['extra_ids'];
            $this->extraUnits = $orderDetails['extra_units'];
            $this->extraOptions = $orderDetails['extra_options'];

            // DEBUG
            if($this->debugMode)
            {
                echo "<br />Class variables set from params. ";

                if($this->debugMode >= 2)
                {
                    echo "Class variables: ".nl2br(print_r($this->getInputDataArray(), true));
                }
            }
        }
    }

    public function cacheVariables()
    {
        // Note: we use full path here, because otherwise PhpStorm gives a warning for not used classes, and they can be wrongly deleted
        $class = "\\FleetManagement\\Models\\Cache\\".($this->useSessions ? 'StaticSession' : 'StaticCookie');
        if(method_exists($class, 'cacheValue') && method_exists($class, 'cacheArrayAsJSON'))
        {
            // -----------------------------------------------------
            // filled data in step 1, pre-filled in step 2, 3, 4, 5, 6

            $class::cacheValue('pickup_date', $this->getShortPickupDate());
            $class::cacheValue('pickup_time', $this->getISOPickupTime());
            $class::cacheValue('return_date', $this->getShortReturnDate());
            $class::cacheValue('return_time', $this->getISOReturnTime());

            // Pick-up coordinates
            // NOTE: Reversed order
            $class::cacheValue('pickup_location_id', $this->pickupLocationId);

            // Return coordinates
            // NOTE: Reversed order
            $class::cacheValue('return_location_id', $this->returnLocationId);

            // Coupon
            $class::cacheValue('coupon_code', $this->couponCode);


            // -----------------------------------------------------
            // Used only in step 1

            // Filters (arrays)
            $class::cacheValue('partner_id', $this->fleetPartnerId);
            $class::cacheValue('manufacturer_id', $this->manufacturerId);
            $class::cacheValue('class_id', $this->classId);
            $class::cacheValue('attribute_id1', $this->attributeId1);
            $class::cacheValue('attribute_id2', $this->attributeId2);

            // -----------------------------------------------------
            // filled in step 2 (or step 1 if booked from car page), pre-filled in step 3, 4, 5, 6
            $class::cacheArrayAsJSON('item_model_ids', $this->itemModelIds);
            $class::cacheArrayAsJSON('item_model_units', $this->itemModelUnits);
            $class::cacheArrayAsJSON('item_model_options', $this->itemModelOptions);


            // -----------------------------------------------------
            // filled in step 3, pre-filled in step 4, 5, 6
            $class::cacheArrayAsJSON('extra_ids', $this->extraIds);
            $class::cacheArrayAsJSON('extra_units', $this->extraUnits);
            $class::cacheArrayAsJSON('extra_options', $this->extraOptions);
        }

        // DEBUG
        if($this->debugMode >= 2)
        {
            echo "[CACHING] &#39;cacheValue&#39; METHOD EXISTS IN &#39;{$class}&#39;: ".var_export(method_exists($class, 'cacheValue'), true);
            echo "[CACHING] UPDATED {$this->storageType} VARS: ".nl2br(print_r($this->useSessions ? $_SESSION : $_COOKIE, true));
        }
    }

    public function unsetVariablesCache()
    {
        // Note: we use full path here, because otherwise PhpStorm gives a warning for not used classes, and they can be wrongly deleted
        $class = "\\FleetManagement\\Models\\Cache\\".($this->useSessions ? 'StaticSession' : 'StaticCookie');
        if(method_exists($class, 'unsetKey'))
        {
            // -----------------------------------------------------
            // Filled data in step1

            $class::unsetKey('pickup_date');
            $class::unsetKey('pickup_time');
            $class::unsetKey('return_date');
            $class::unsetKey('return_time');

            // Pick-up coordinates
            // NOTE: Reversed order
            $class::unsetKey('pickup_location_id');

            // Return coordinates
            // NOTE: Reversed order
            $class::unsetKey('return_location_id');

            // Coupon
            $class::unsetKey('coupon_code');


            // -----------------------------------------------------
            //  USE ONLY IN STEP1. After saving used in edit only

            // Filters (arrays)
            $class::unsetKey('partner_id');
            $class::unsetKey('manufacturer_id');
            $class::unsetKey('class_id');
            $class::unsetKey('attribute_id1');
            $class::unsetKey('attribute_id2');


            // -----------------------------------------------------
            // filled in step 2 (or step 1 if ordered from item model page)
            $class::unsetKey('item_model_ids');


            // -----------------------------------------------------
            // filled in step 3

            $class::unsetKey('item_model_units');
            $class::unsetKey('item_model_options');
            $class::unsetKey('extra_ids');
            $class::unsetKey('extra_units');
            $class::unsetKey('extra_options');
        }

        // DEBUG
        if($this->debugMode >= 2)
        {
            echo "[UNSETTING] &#39;unsetKey&#39; METHOD EXISTS IN &#39;{$class}&#39;: ".var_export(method_exists($class, 'cacheValue'), true);
            echo "[UNSETTING] UPDATED {$this->storageType} VARS: ".nl2br(print_r($this->useSessions ? $_SESSION : $_COOKIE, true));
        }
    }

    /**
     * Step no. 1 - Show reservation box. (optional) + show car
     * Step no. 2 - (optional) Select car, if no car provided
     * Step no. 3 - Select car extras
     * Step no. 4 - Show order details
     * Step no. 5 - Process order
     * Step no. 6 - PayPal payment
     */
    public function setVariables()
    {
        $stack = $this->useSessions ? $_SESSION : $_COOKIE;

        /*******************************************************************/
        /************* Get visible & required search fields list ***********/
        /*******************************************************************/
        if(isset(
            $this->settings['conf_search_pickup_date_visible'],
            $this->settings['conf_search_return_date_visible']
        )) {
            // Search fields visibility settings
            $pickupDateVisible = $this->settings['conf_search_pickup_date_visible'] == 1 ? true : false;
            $pickupTimeVisible = $pickupDateVisible;
            $returnDateVisible = $this->settings['conf_search_return_date_visible'] == 1 ? true : false;
            $returnTimeVisible = $returnDateVisible;
        } else
        {
            // Search fields visibility settings
            $pickupDateVisible = false;
            $pickupTimeVisible = false;
            $returnDateVisible = false;
            $returnTimeVisible = false;
        }

        if(isset(
            $this->settings['conf_search_pickup_date_required'],
            $this->settings['conf_search_return_date_required'],

            $this->settings['conf_search_pickup_location_required'],
            $this->settings['conf_search_return_location_required'],

            $this->settings['conf_search_coupon_code_required'],
            $this->settings['conf_search_partner_required'],
            $this->settings['conf_search_manufacturer_required'],
            $this->settings['conf_search_body_type_required']
        )) {
            // Search fields requirement settings
            $pickupDateRequired = $this->settings['conf_search_pickup_date_required'] == 1 ? true : false;
            $returnDateRequired = $this->settings['conf_search_return_date_required'] == 1 ? true : false;

            $pickupLocationRequired = $this->settings['conf_search_pickup_location_required'] == 1 ? true : false;
            $returnLocationRequired = $this->settings['conf_search_return_location_required'] == 1 ? true : false;

            $couponCodeRequired = $this->settings['conf_search_coupon_code_required'] == 1 ? true : false;
            $fleetPartnerRequired = $this->settings['conf_search_partner_required'] == 1 ? true : false;
            $manufacturerRequired = $this->settings['conf_search_manufacturer_required'] == 1 ? true : false;
            $classRequired = $this->settings['conf_search_body_type_required'] == 1 ? true : false;
            // NOTE: Attributes are NEVER required
        } else
        {
            // Search fields requirement settings
            $pickupDateRequired = false;
            $returnDateRequired = false;

            $pickupLocationRequired = false;
            $returnLocationRequired = false;

            $couponCodeRequired = false;
            $fleetPartnerRequired = false;
            $manufacturerRequired = false;
            $classRequired = false;
            // NOTE: Attributes are NEVER required
        }

        // Important: Process only if this is NOT _POST or _GET.
        /*******************************************************************/
        /********************** PARAMETERS FROM STEP1 **********************/
        /*******************************************************************/

        // 1 - Pick-up Date & Time
        if(($pickupDateVisible || $pickupTimeVisible) && (
                isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) ||
                isset($_REQUEST['pickup_date'], $_REQUEST['pickup_time']) ||
                isset($stack['pickup_date'], $stack['pickup_time']))
        ) {
            // We must use 'offset' here, because the noon time here is in local place, and we have to convert that time
            $noonTimeInUTC = date("H:i:s", strtotime($this->noonTime) - get_option( 'gmt_offset' ) * 3600);
            $defaultEarliestPickupTimestamp = time() + $this->minPeriodUntilPickup;
            $defaultNoonPickupTimestamp = strtotime(date("Y-m-d", time() + $this->minPeriodUntilPickup)." ".$noonTimeInUTC);
            // Use noon as pick-up time or use earliest time
            $defaultPickupTimestamp = $defaultNoonPickupTimestamp > $defaultEarliestPickupTimestamp ? $defaultNoonPickupTimestamp : $defaultEarliestPickupTimestamp;
            $defaultLocalPickupDate = date($this->shortDateFormat, $defaultPickupTimestamp + get_option( 'gmt_offset' ) * 3600);
            $defaultLocalPickupTime = date("H:i:s", $defaultPickupTimestamp + get_option( 'gmt_offset' ) * 3600);
            if($pickupDateVisible)
            {
                $customerInputPickupDate = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'pickup_date', $defaultLocalPickupDate, $this->shortDateFormat);
            } else
            {
                $customerInputPickupDate = $defaultLocalPickupDate;
            }
            if($pickupDateRequired == true && $customerInputPickupDate == "0000-00-00")
            {
                // Input is invalid, add error message
                $this->errorMessages[] = $this->lang->getText('LANG_SEARCH_PICKUP_DATE_REQUIRED_ERROR_TEXT');
            }
            // There is no need way to get error message for time, as 00:00:00 is also a valid time, so we don't do that
            if($pickupTimeVisible)
            {
                $customerInputPickupTime = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'pickup_time', $defaultLocalPickupTime, 'time_validation');
            } else
            {
                $customerInputPickupTime = $this->noonTime;
            }
            $this->expectedPickupTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($customerInputPickupDate, $customerInputPickupTime);
        } else if($pickupDateVisible == false)
        {
            // Pick-up date & time is not visible, so we set default value, which is current time plus minimum period until pickup
            $this->expectedPickupTimestamp = time() + $this->minPeriodUntilPickup;
        }

        // 2 - If order duration is passed, it will override return date and time
        if(($returnDateVisible || $returnTimeVisible) && (
                isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) ||
                isset($_REQUEST['expected_order_period']) || isset($stack['expected_order_period']) ||
                isset($_REQUEST['return_date'], $_REQUEST['return_time']) ||
                isset($stack['return_date'], $stack['return_time'])
            )) {
            if(isset($_REQUEST['expected_order_period']) || isset($stack['expected_order_period']))
            {
                // Return date is a integer duration in seconds, and has to be added to pickup date
                $validExpectedOrderPeriod = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'expected_order_period', 0, 'positive_integer');
                if($returnDateRequired == true && $validExpectedOrderPeriod == 0)
                {
                    // Input is invalid, add error message
                    $this->errorMessages[] = $this->lang->getText('LANG_ORDER_PERIOD_REQUIRED_ERROR_TEXT');
                }
                $this->expectedReturnTimestamp = $this->expectedPickupTimestamp + $validExpectedOrderPeriod;
            } else
            {
                // 6/7 - Return Date & Time
                // We must use 'offset' here, because the noon time here is in local place, and we have to convert that time
                $noonTimeInUTC = date_i18n("H:i:s", strtotime($this->noonTime) - get_option( 'gmt_offset' ) * 3600, true);
                $defaultEarliestReturnTimestamp = $this->expectedPickupTimestamp + $this->minOrderPeriod;
                $defaultNoonReturnTimestamp = strtotime(date("Y-m-d", $this->expectedPickupTimestamp + $this->minOrderPeriod)." ".$noonTimeInUTC);
                // Add +1 day to default noon timestamp, if it is earlier that earliest return timestamp
                $defaultNoonReturnTimestamp = $defaultNoonReturnTimestamp < $defaultEarliestReturnTimestamp ? $defaultNoonReturnTimestamp+86400 : $defaultNoonReturnTimestamp;
                // If pricing model is daily by date (=1), then use min order period, otherwise - use noon time
                $defaultReturnTimestamp = $this->timeCeiling == "BY_NOON_COUNT" ? $defaultEarliestReturnTimestamp : $defaultNoonReturnTimestamp;
                $defaultLocalReturnDate = date($this->shortDateFormat, $defaultReturnTimestamp + get_option( 'gmt_offset' ) * 3600);
                $defaultLocalReturnTime = date("H:i:s", $defaultReturnTimestamp + get_option( 'gmt_offset' ) * 3600);
                if($returnDateVisible)
                {
                    $customerInputReturnDate = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'return_date', $defaultLocalReturnDate, $this->shortDateFormat);
                } else
                {
                    $customerInputReturnDate = $defaultLocalReturnDate;
                }
                if($returnDateRequired == true && $customerInputReturnDate == "0000-00-00")
                {
                    // Input is invalid, add error message
                    $this->errorMessages[] = $this->lang->getText('LANG_SEARCH_RETURN_DATE_REQUIRED_ERROR_TEXT');
                }
                // There is no need way to get error message for time, as 00:00:00 is also a valid time, so we don't do that
                if($pickupTimeVisible)
                {
                    $customerInputReturnTime = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'return_time', $defaultLocalReturnTime, 'time_validation');
                } else
                {
                    $customerInputReturnTime = $defaultLocalReturnTime;
                }
                $this->expectedReturnTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($customerInputReturnDate, $customerInputReturnTime);
            }
        } else if($returnDateVisible == false && $returnTimeVisible == false)
        {
            // Return date & time is not visible, so we set default value, which is a pickup timestamp plus minimum order period
            $this->expectedReturnTimestamp = $this->expectedPickupTimestamp + $this->minOrderPeriod;
        }


        // ----------------------------------------------------------------------------------
        // ********* #3 - Pick-up (location, area, city, state, zip code, country)  *********
        // 3.6. - [REVERSED ORDER] Pick-up Location Id (used in step 3, 4, 5, 6 selected at step 2 or step 1)
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['pickup_location_id']) || isset($stack['pickup_location_id']))
        {
            $this->pickupLocationId = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'pickup_location_id', 0, 'positive_integer');
            if($pickupLocationRequired == true && $this->pickupLocationId <= 0)
            {
                // Input is invalid, add error message
                $this->errorMessages[] = $this->lang->getText('LANG_LOCATION_PICKUP_REQUIRED_ERROR_TEXT');
            }
        }


        // ----------------------------------------------------------------------------------
        // ********* #4 - Return (location, area, city, state, zip code, country) ***********

        // 4.6. - [REVERSED ORDER] Return Location Id (used in step 3, 4, 5, 6 selected at step 2 or step 1)
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['return_location_id']) || isset($stack['return_location_id']))
        {
            $this->returnLocationId = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'return_location_id', 0, 'positive_integer');
            if($returnLocationRequired == true && $this->returnLocationId <= 0)
            {
                // Input is invalid, add error message
                $this->errorMessages[] = $this->lang->getText('LANG_LOCATION_RETURN_REQUIRED_ERROR_TEXT');
            }
        }


        // ----------------------------------------------------------------------------------
        // ******************************** #7 - Coupon *************************************

        // 7 - Coupon Code
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['coupon_code']) || isset($stack['coupon_code']))
        {
            if(
                (isset($_REQUEST['coupon_code']) && in_array($_REQUEST['coupon_code'], array($this->lang->getText('LANG_COUPON_CODE_INPUT_TEXT'), $this->lang->getText('LANG_COUPON_CODE_INPUT2_TEXT')))) ||
                (isset($stack['coupon_code']) && in_array($stack['coupon_code'], array($this->lang->getText('LANG_COUPON_CODE_INPUT_TEXT'), $this->lang->getText('LANG_COUPON_CODE_INPUT2_TEXT'))))
            ) {
                // Flush coupon code
                $this->couponCode = "";
            } else
            {
                $this->couponCode = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'coupon_code', '', 'guest_text_validation');
                if($couponCodeRequired == true && $this->couponCode == "")
                {
                    // Input is invalid, add error message
                    $this->errorMessages[] = $this->lang->getText('LANG_SEARCH_COUPON_CODE_REQUIRED_ERROR_TEXT');
                }
            }
        }


        // ----------------------------------------------------------------------------------
        // ***************************** #8 - Filters (arrays) ******************************


        // 8.1. - Fleet Partner Id
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['partner_id']) || isset($stack['partner_id']))
        {
            $this->fleetPartnerId = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'partner_id', -1, 'intval');
            if($fleetPartnerRequired == true && $this->fleetPartnerId == -1)
            {
                // Input is invalid, add error message
                $this->errorMessages[] = $this->lang->getText('LANG_PARTNER_FLEET_REQUIRED_ERROR_TEXT');
            }
        }

        // 8.4. - Manufacturer Id
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['manufacturer_id']) || isset($stack['manufacturer_id']))
        {
            $this->manufacturerId = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'manufacturer_id', -1, 'intval');
            if($manufacturerRequired == true && $this->manufacturerId == -1)
            {
                // Input is invalid, add error message
                $this->errorMessages[] = $this->lang->getText('LANG_MANUFACTURER_REQUIRED_ERROR_TEXT');
            }
        }

        // 8.5. - Class Id
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['class_id']) || isset($stack['class_id']))
        {
            $this->classId = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'class_id', -1, 'intval');
            if($classRequired == true && $this->classId == -1)
            {
                // Input is invalid, add error message
                $this->errorMessages[] = $this->lang->getText('LANG_CLASS_REQUIRED_ERROR_TEXT');
            }
        }

        // 8.6. - Attribute Id 1
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['attribute_id1']) || isset($stack['attribute_id1']))
        {
            // NOTE: Attributes are NEVER required
            $this->attributeId1 = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'attribute_id1', -1, 'intval');
        }

        // 8.6. - Attribute Id 2
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['attribute_id2']) || isset($stack['attribute_id2']))
        {
            // NOTE: Attributes are NEVER required
            $this->attributeId2 = StaticValidator::getValidValueInput(array('POST', $this->storageType), 'attribute_id2', -1, 'intval');
        }

        //////////////////////////////////////////////////////////////////
        // FOR STEP3
        /******************* ITEM - IDs, UNITS, OPTIONS *****************/

        // 14 - Item Model Ids
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['item_model_ids']) || isset($stack['item_model_ids']))
        {
            // came back to step4 from step4->step3->step edit mode
            $this->itemModelIds = StaticValidator::getValidArrayInput(array('POST', $this->storageType), 'item_model_ids', 'positive_integer');
            // Never required, no error messages possible
        }

        // 15 - Item Model Units
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['item_model_units']) || isset($stack['item_model_units']))
        {
            // came back to step2 from step3
            // positive_integer validation here protects us from allowing to block all (-1) item units from front-end
            $this->itemModelUnits = StaticValidator::getValidArrayInput(array('POST', $this->storageType), 'item_model_units', 'positive_integer');
            // Never required, no error messages possible
        }

        // 16 - Item Model Options
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['item_model_options']) || isset($stack['item_model_options']))
        {
            // came back to step3 from step3->step2->step edit mode
            $this->itemModelOptions = StaticValidator::getValidArrayInput(array('POST', $this->storageType), 'item_model_options', 'positive_integer');
            // Never required, no error messages possible
        }

        /******************* EXTRA - IDs, UNITS, OPTIONS *****************/

        // 17 - Extra Ids
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['extra_ids']) || isset($stack['extra_ids']))
        {
            $this->extraIds = StaticValidator::getValidArrayInput(array('POST', $this->storageType), 'extra_ids', 'positive_integer');
            // Never required, no error messages possible
        }

        // 18 - Extra Units
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['extra_units']) || isset($stack['extra_units']))
        {
            // positive_integer validation here protects us from allowing to block all (-1) extra units from front-end
            $this->extraUnits = StaticValidator::getValidArrayInput(array('POST', $this->storageType), 'extra_units', 'positive_integer');
            // Never required, no error messages possible
        }

        // 18 - Extra Options
        if(isset($_REQUEST[$this->conf->getExtPrefix().'do_search5']) || isset($_REQUEST['extra_options']) || isset($stack['extra_options']))
        {
            $this->extraOptions = StaticValidator::getValidArrayInput(array('POST', $this->storageType), 'extra_options', 'positive_integer');
            // Never required, no error messages possible
        }

        if($this->debugMode)
        {
            echo "<br />Final stage of class variables after setRequestParams() was called.";
            echo "<br />".nl2br(print_r($this->getInputDataArray(), true));
        }
    }

    /**
     * @param int $paramOrderId
     * @return array
     */
    public function getPriceSummary($paramOrderId = 0)
    {
        // Set default values, to avoid not existing key errors
        $summary 											    = array();
        $summary['pickup_location_id']                          = $this->pickupLocationId;
        $summary['return_location_id']                          = $this->returnLocationId;
        $summary['item_models'] 							    = array();
        $summary['extras'] 								        = array();
        $summary['pickup']								        = array();
        $summary['return']								        = array();
        $summary['additional_fees']                             = array();
        $summary['additional_fee_ids']                          = array();
        // Counted defaults from price element
        $summary['tax_percentage']					            = 0.00;
        $summary['item_model_totals'] 							= array();
        $summary['extra_totals'] 							    = array();
        $summary['additional_fee_totals'] 						= array();
        $summary['additional_fee_totals']['total_with_tax']		= 0.00;
        $summary['overall'] 								    = array();
        // Counted default from price element
        $summary['overall']['discounted_total'] 			    = 0.00;
        $summary['overall']['discounted_tax_amount'] 		    = 0.00;
        $summary['overall']['discounted_total_with_tax'] 	    = 0.00;
        $summary['overall']['fixed_item_model_deposit'] 	    = 0.00;
        $summary['overall']['fixed_extra_deposit']	            = 0.00;
        $summary['overall']['fixed_deposit'] 		            = 0.00;
        
        // Counted prepayments
        $summary['prepayment']['item_model_pay_now']            = 0.00;
        $summary['prepayment']['item_model_deposit_pay_now']    = 0.00;
        $summary['prepayment']['extra_pay_now']                 = 0.00;
        $summary['prepayment']['extra_deposit_pay_now']         = 0.00;
        $summary['prepayment']['pickup_fee_pay_now']            = 0.00;
        $summary['prepayment']['additional_fees_pay_now']       = 0.00;
        $summary['prepayment']['return_fee_pay_now']            = 0.00;
        // Counted overalls
        $summary['overall']['gross_total']     			        = 0.00;
        $summary['overall']['total_tax']     				    = 0.00;
        $summary['overall']['grand_total']   				    = 0.00;
        $summary['overall']['total_pay_now']   			        = 0.00;
        $summary['overall']['total_pay_later']   			    = 0.00;


        // Create mandatory instances
        $objPrepaymentManager = new PrepaymentManager($this->conf, $this->lang, $this->settings);
        $prepaymentDetails = $objPrepaymentManager->getPrepaymentDetailsByInterval($this->expectedPickupTimestamp, $this->expectedReturnTimestamp);
        $objTaxesObserver = new TaxesObserver($this->conf, $this->lang, $this->settings);
        $objPickupLocation = new Location($this->conf, $this->lang, $this->settings, $this->pickupLocationId);
        $locationUniqueIdentifier = $objPickupLocation->getUniqueIdentifier(); // We use pickup location code for availability checks
        $objReturnLocation = new Location($this->conf, $this->lang, $this->settings, $this->returnLocationId);
        $objAdditionalFeesObserver = new AdditionalFeesObserver($this->conf, $this->lang, $this->settings);

        // Period
        $objPeriod = new Period($this->conf, $this->lang, $this->settings);

        // Get tax percentage
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->settings);
        $taxPercentage = $objTaxManager->getTaxPercentage($this->pickupLocationId, $this->returnLocationId);

        // Load search managers
        $objSearchItemModelsManager = new SearchItemModelsManager(
            $this->conf, $this->lang, $this->settings, $taxPercentage, $locationUniqueIdentifier,
            $paramOrderId, $this->couponCode
        );
        $objSearchExtrasManager = new SearchExtrasManager(
            $this->conf, $this->lang, $this->settings, $taxPercentage, $locationUniqueIdentifier,
            $paramOrderId, $this->itemModelIds
        );

        // Get available ids
        $availableItemModelIds = $objSearchItemModelsManager->getAvailableItemModelIds(
            $this->pickupLocationId,
            $this->returnLocationId,
            $this->fleetPartnerId,
            $this->manufacturerId,
            $this->classId,
            $this->attributeId1,
            $this->attributeId2
        );
        $availableExtraIds = $objSearchExtrasManager->getAvailableExtraIds();

        // Get selected ids
        $selectedItemModelIds = $objSearchItemModelsManager->getExistingSelectedItemModelIds($this->itemModelIds, $availableItemModelIds);
        $selectedExtraIds = $objSearchExtrasManager->getExistingSelectedExtraIds($this->extraIds, $availableExtraIds);

        $objPickupFeeManager = new LocationFeeManager($this->conf, $this->lang, $this->settings, $this->pickupLocationId, $taxPercentage);
        $objReturnFeeManager = new LocationFeeManager($this->conf, $this->lang, $this->settings, $this->returnLocationId, $taxPercentage);

        $pickupInAfterHours = false;
        if($this->pickupLocationId > 0)
        {
            // We do the check only if exact location is selected
            $pickupInAfterHours = $objPickupLocation->isAfterHoursTime($this->getLocalPickupDayOfWeek(), $this->getLocalPickupTime());
        }

        $returnInAfterHours = false;
        if($this->returnLocationId > 0)
        {
            // We do the check only if exact location is selected
            $returnInAfterHours = $objReturnLocation->isAfterHoursTime($this->getLocalReturnDayOfWeek(), $this->getLocalReturnTime());
        }

        /************************************************************************************************************/
        // Pre-calculate dates, times & duration
        $expectedPickupDayOfWeek	= StaticValidator::getLocalDateByTimestamp($this->expectedPickupTimestamp, "D");
        $expectedReturnDayOfWeek	= StaticValidator::getLocalDateByTimestamp($this->expectedReturnTimestamp, "D");
        $expectedPickupDateI18n	    = StaticValidator::getI18nDateByTimestamp($this->expectedPickupTimestamp);
        $expectedPickupTimeI18n	    = StaticValidator::getI18nTimeByTimestamp($this->expectedPickupTimestamp);
        $expectedReturnDateI18n	    = StaticValidator::getI18nDateByTimestamp($this->expectedReturnTimestamp);
        $expectedReturnTimeI18n	    = StaticValidator::getI18nTimeByTimestamp($this->expectedReturnTimestamp);
        $expectedOrderDurationText	= $objPeriod->getDurationText($this->expectedPickupTimestamp, $this->expectedReturnTimestamp);

        /************************************************************************************************************/
        // Load the main information - tax percentage, item models, extras, after-hours
        $summary['item_models'] = $objSearchItemModelsManager->getItemModelsWithPricesAndOptions(
            $selectedItemModelIds, $this->itemModelUnits, $this->itemModelOptions,
            $this->expectedPickupTimestamp, $this->expectedReturnTimestamp, true
        );
        $summary['extras'] = $objSearchExtrasManager->getExtrasWithPricesAndOptions(
            $selectedExtraIds, $this->extraUnits, $this->extraOptions,
            $this->expectedPickupTimestamp, $this->expectedReturnTimestamp,true
        );

        $additionalFeeSearchParams = array(
            "pickup_location_ids" => array(-1, $this->pickupLocationId),
            "return_location_ids" => array(-1, $this->returnLocationId),
        );
        $additionalFeeIds = $objAdditionalFeesObserver->getAllIdsByAnd($additionalFeeSearchParams);

        // Other params
        $summary['additional_fees']             = array();
        $summary['additional_fee_ids']          = $additionalFeeIds; // Add all ids to additional fees stack
        $summary['pickup_in_afterhours']        = $pickupInAfterHours;
        $summary['return_in_afterhours']        = $returnInAfterHours;
        $summary['expected_pickup_day_of_week']	= $expectedPickupDayOfWeek;
        $summary['expected_return_day_of_week']	= $expectedReturnDayOfWeek;
        $summary['expected_pickup_date_i18n']	= $expectedPickupDateI18n;
        $summary['expected_pickup_time_i18n']	= $expectedPickupTimeI18n;
        $summary['expected_return_date_i18n']	= $expectedReturnDateI18n;
        $summary['expected_return_time_i18n']	= $expectedReturnTimeI18n;
        $summary['order_duration_text']		    = $expectedOrderDurationText;
        $summary['tax_percentage']              = $taxPercentage;

        /************************************************************************************************************/
        // Count total selected units
        $itemModelsTotalSelectedUnits = 0;
        foreach($summary['item_models'] AS $itemModel)
        {
            $itemModelsTotalSelectedUnits += $itemModel['selected_quantity'];
        }

        /************************************************************************************************************/
        // Detect quote-only counters
        $summary['quote_only'] = false;
        // Iterate over counter item models only
        foreach($summary['item_models'] AS $itemModel)
        {
            if($itemModel['price_group_id'] == 0)
            {
                $summary['quote_only'] = true;
            }
        }

        /************************************************************************************************************/
        // Load pick-up and return
        $pickupFees = $objPickupFeeManager->getDetails(0.00, $itemModelsTotalSelectedUnits, $summary['pickup_in_afterhours']);
        $returnFees = $objReturnFeeManager->getDetails(0.00, $itemModelsTotalSelectedUnits, $summary['return_in_afterhours']);
        $summary['pickup'] = array_merge($objPickupLocation->getDetails(true), $pickupFees);
        $summary['return'] = array_merge($objReturnLocation->getDetails(true), $returnFees);

        /************************************************************************************************************/
        // Calculate additional fees
        // NOTE: Exact additional fee ids is already known
        foreach($summary['additional_fee_ids'] AS $additionalFeeId)
        {
            $objAdditionalFeeManager = new AdditionalFeeManager($this->conf, $this->lang, $this->settings, $additionalFeeId, $taxPercentage);
            $objAdditionalFee = new AdditionalFee($this->conf, $this->lang, $this->settings, $additionalFeeId);
            $additionalFeeDetails = $objAdditionalFee->getDetails(true); // Always return
            // NOTE: Load all taxes, as even it they are 0,00 we still want to show them
            $additionalFeeCalculations = $objAdditionalFeeManager->getFeeDetails($this->expectedPickupTimestamp, $this->expectedReturnTimestamp, $itemModelsTotalSelectedUnits);
            $summary['additional_fees'][] = array_merge($additionalFeeDetails, $additionalFeeCalculations);
        }

        /************************************************************************************************************/
        // Update the overall price array by adding current element multiplied prices to overall prices

        // Add all item models prices
        foreach($summary['item_models'] AS $itemModel)
        {
            foreach($itemModel['multiplied'] AS $key => $multipliedPrice)
            {
                if(!isset($summary['overall'][$key]))
                {
                    // Set first price to that specific key
                    $summary['overall'][$key] = $multipliedPrice;
                } else
                {
                    // Add prices one by one to that specific key
                    $summary['overall'][$key] += $multipliedPrice;
                }

                // We use item_model_totals only for hover details text
                if(!isset($summary['item_model_totals'][$key]))
                {
                    $summary['item_model_totals'][$key] = $multipliedPrice;
                } else
                {
                    $summary['item_model_totals'][$key] += $multipliedPrice;
                }
            }
        }

        // Add all extras prices
        foreach($summary['extras'] AS $extra)
        {
            foreach($extra['multiplied'] AS $key => $multipliedPrice)
            {
                if(!isset($summary['overall'][$key]))
                {
                    // Set first price to that specific key
                    $summary['overall'][$key] = $multipliedPrice;
                } else
                {
                    // Add prices one by one to that specific key
                    $summary['overall'][$key] += $multipliedPrice;
                }

                // We use extra_totals only for hover details text
                if(!isset($summary['extra_totals'][$key]))
                {
                    $summary['extra_totals'][$key] = $multipliedPrice;
                } else
                {
                    $summary['extra_totals'][$key] += $multipliedPrice;
                }
            }
        }

        // Add all additional fees
        foreach($summary['additional_fees'] AS $additionalFee)
        {
            foreach($additionalFee['multiplied_per_period'] AS $key => $multipliedFee)
            {
                // We use additional_fee_totals only for hover details text
                if(!isset($summary['additional_fee_totals'][$key]))
                {
                    $summary['additional_fee_totals'][$key] = $multipliedFee;
                } else
                {
                    $summary['additional_fee_totals'][$key] += $multipliedFee;
                }
            }
        }

        // Deposits blank items set for both scenarios - default value if no items/extras selected, and number if exist
        if(isset($summary['item_model_totals']['fixed_deposit']))
        {
            $summary['overall']['fixed_item_model_deposit'] 	= $summary['item_model_totals']['fixed_deposit'];
        }
        if(isset($summary['extra_totals']['fixed_deposit']))
        {
            $summary['overall']['fixed_extra_deposit'] = $summary['extra_totals']['fixed_deposit'];
        }

        /* -------------------------------- Calculate totals ------------------------------------ */
        // Add total item+extras discounted total to totals
        $summary['overall']['gross_total']  = $summary['overall']['discounted_total'];
        $summary['overall']['total_tax']    = $summary['overall']['discounted_tax_amount'];
        $summary['overall']['grand_total']  = $summary['overall']['discounted_total_with_tax'];

        // Add total pickup fee to totals
        if(is_array($summary['pickup']['multiplied']))
        {
            $summary['overall']['gross_total']  += $summary['pickup']['multiplied']['current_pickup_fee'];
            $summary['overall']['total_tax']    += $summary['pickup']['multiplied']['current_pickup_tax_amount'];
            $summary['overall']['grand_total']  += $summary['pickup']['multiplied']['current_pickup_fee_with_tax'];
        }

        // Add total return fee to totals
        if(is_array($summary['return']['multiplied']))
        {
            $summary['overall']['gross_total']  += $summary['return']['multiplied']['current_return_fee'];
            $summary['overall']['total_tax']    += $summary['return']['multiplied']['current_return_tax_amount'];
            $summary['overall']['grand_total']  += $summary['return']['multiplied']['current_return_fee_with_tax'];
        }


        // Add additional fees to totals
        foreach($summary['additional_fees'] AS $additionalFee)
        {
            if(is_array($additionalFee['multiplied_per_period']))
            {
                $summary['overall']['gross_total']  += $additionalFee['multiplied_per_period']['total'];
                $summary['overall']['total_tax']    += $additionalFee['multiplied_per_period']['tax_amount'];
                $summary['overall']['grand_total']  += $additionalFee['multiplied_per_period']['total_with_tax'];
            }
        }
        /* -------------------------------- Calculate taxes ------------------------------------ */
        $summary['taxes'] = $objTaxesObserver->getTaxesForPrice($this->pickupLocationId, $this->returnLocationId, $summary['overall']['gross_total']);

        /* -------------------------------- Calculate prepayment ------------------------------------ */
        if($objPrepaymentManager->isPrepaymentEnabled())
        {
            if(isset($prepaymentDetails['prepayment_percentage']) && $prepaymentDetails['prepayment_percentage'] > 0)
            {
                $prepaymentPercentage = $prepaymentDetails['prepayment_percentage'];
                if($prepaymentDetails['item_prices_included'] == 1 && sizeof($summary['item_model_totals']) > 0)
                {
                    $summary['prepayment']['item_model_pay_now'] = $summary['item_model_totals']['discounted_total_with_tax'] * ($prepaymentPercentage / 100);
                }
                if($prepaymentDetails['item_deposits_included'] == 1 && sizeof($summary['item_model_totals']) > 0)
                {
                    $summary['prepayment']['item_model_deposit_pay_now'] = $summary['item_model_totals']['fixed_deposit'] * ($prepaymentPercentage / 100);
                }
                if($prepaymentDetails['extra_prices_included'] == 1 && sizeof($summary['extra_totals']) > 0)
                {
                    $summary['prepayment']['extra_pay_now'] = $summary['extra_totals']['discounted_total_with_tax'] * ($prepaymentPercentage / 100);
                }
                if($prepaymentDetails['extra_deposits_included'] == 1 && sizeof($summary['extra_totals']) > 0)
                {
                    $summary['prepayment']['extra_deposit_pay_now'] = $summary['extra_totals']['fixed_deposit'] * ($prepaymentPercentage / 100);
                }
                if($prepaymentDetails['pickup_fees_included'] == 1 && is_array($summary['pickup']['multiplied']))
                {
                    // NOTE: Must be 'current_' to correctly get data based on dynamic daytime (regular or afterhours)
                    $summary['prepayment']['pickup_fee_pay_now'] = $summary['pickup']['multiplied']['current_pickup_fee_with_tax'] * ($prepaymentPercentage / 100);
                }
                if($prepaymentDetails['return_fees_included'] == 1 && is_array($summary['return']['multiplied']))
                {
                    // NOTE: Must be 'current_' to correctly get data based on dynamic daytime (regular or afterhours)
                    $summary['prepayment']['return_fee_pay_now'] = $summary['return']['multiplied']['current_return_fee_with_tax'] * ($prepaymentPercentage / 100);
                }
                if($prepaymentDetails['additional_fees_included'] == 1 && is_array($summary['additional_fee_totals']))
                {
                    $summary['prepayment']['additional_fees_pay_now'] = $summary['additional_fee_totals']['total_with_tax'] * ($prepaymentPercentage / 100);
                }
            }
            // Add all prepayments to one
            foreach($summary['prepayment'] AS $key => $payNow)
            {
                $summary['overall']['total_pay_now'] += $payNow;
            }
        }

        // Total Pay Later = Grand Total + Fixed Deposit - Total Pay Now
        $summary['overall']['total_pay_later'] = $summary['overall']['grand_total'] - $summary['overall']['total_pay_now'];

        // We allow only positive overall amounts
        foreach($summary['overall'] AS $key => $amount)
        {
            $summary['overall'][$key] = StaticValidator::getValidPositiveFloat($amount, 0.00);
        }

        // Overall price prints
        $summary['overall_tiny_print'] = $this->getFormattedPriceArray($summary['overall'], "tiny", $summary['quote_only']);
        $summary['overall_tiny_without_fraction_print'] = $this->getFormattedPriceArray($summary['overall'], "tiny_without_fraction", $summary['quote_only']);
        $summary['overall_print'] = $this->getFormattedPriceArray($summary['overall'], "regular", $summary['quote_only']);
        $summary['overall_without_fraction_print'] = $this->getFormattedPriceArray($summary['overall'], "regular_without_fraction", $summary['quote_only']);
        $summary['overall_long_print'] = $this->getFormattedPriceArray($summary['overall'], "long", $summary['quote_only']);
        $summary['overall_long_without_fraction_print'] = $this->getFormattedPriceArray($summary['overall'], "long_without_fraction", $summary['quote_only']);

        // Percentage (not escaped)
        $summary['formatted_tax_percentage'] = StaticFormatter::getFormattedPercentage($summary['tax_percentage'], "regular");

        // Print totals (for hover text)
        $summary['item_model_totals_print'] = $this->getFormattedPriceArray($summary['item_model_totals'], "regular", false);
        $summary['extra_totals_print'] = $this->getFormattedPriceArray($summary['extra_totals'], "regular", false);
        $summary['additional_fee_totals_print'] = $this->getFormattedPriceArray($summary['additional_fee_totals'], "regular", false);

        // DEBUG
        if($this->debugMode)
        {
            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Main information:</span>';
            echo '<br />Is quote-only: '.var_export($summary['quote_only'], true);
            echo '<br />Item model total selected units: '.esc_html($itemModelsTotalSelectedUnits);
            echo '<br />';

            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Item model totals:</span>';
            echo '<br />'.esc_br_html(print_r($summary['item_model_totals'], true));

            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Pick-up totals:</span>';
            echo '<br />'.esc_br_html(print_r($summary['pickup']['multiplied'], true));

            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Return totals:</span>';
            echo '<br />'.esc_br_html(print_r($summary['return']['multiplied'], true));

            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Additional fee totals:</span>';
            echo '<br />'.esc_br_html(print_r($summary['additional_fee_totals'], true));

            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Extra totals:</span>';
            echo '<br />'.esc_br_html(print_r($summary['extra_totals'], true));

            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Price totals:</span>';
            echo '<br />'.esc_br_html(print_r($summary['overall'], true));

            echo '<br /><span style="font-weight: bold; color: black; font-size: 18px;">Prepayments:</span>';
            echo '<br />'.esc_br_html(print_r($summary['prepayment'], true));

            if($this->debugMode >= 2)
            {
                echo esc_br_html(print_r($summary, true));
            }
        }

        return $summary;
    }
}