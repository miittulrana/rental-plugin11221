<?php
/**
 * Abstract classes can't be created with new instance. It is only possible if they are extended by childs
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
namespace FleetManagement\Models\Search;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Location\Location;

abstract class AbstractSearchManager implements StackInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings                 = array();
    protected $debugMode 	            = 0; // 1 - standard,  2 - deep
    protected $debugMessages            = array();
    protected $okayMessages             = array();
    protected $errorMessages            = array();

	// settings from DB
	protected $searchEnabled 		    = false;
    protected $useSessions 		        = true;
    protected $storageType 		        = "SESSION"; // "SESSION" or "COOKIE"
	/**
	 * @var int - admin setting in seconds - minimum block period between two orders for same car
	 */
	protected $minOrderPeriod 	        = 0;
	/**
	* @var int - admin setting in seconds - maximum reservation period
	*/
	protected $maxOrderPeriod 	        = 0;
	/**
	 * @var int - admin setting in seconds - minimum period until pickup
	 */
	protected $minPeriodUntilPickup	    = 0;
    protected $timeCeiling              = 'BY_TIME_COUNT';
    /**
     * @var int - price calculation: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
     */
    protected $priceCalculationType     = 1;
    protected $noonTime                 = '12:00:00';

	// pre-filled in step 1
	protected $expectedPickupTimestamp  = 0;
	protected $expectedReturnTimestamp 	= 0;

    // Pick-up coordinates
	protected $pickupLocationId   	    = 0;

    // Return coordinates
	protected $returnLocationId  	    = 0;

    protected $couponCode  	            = "";

    // Filter params
    protected $fleetPartnerId      	    = -1; // USE ONLY IN STEP1
	protected $manufacturerId      	    = -1; // USE ONLY IN STEP1
	protected $classId         	        = -1; // USE ONLY IN STEP1
    protected $attributeId1     		= -1; // USE ONLY IN STEP1
	protected $attributeId2 	        = -1; // USE ONLY IN STEP1

	// pre-filled in step 2 (or step1 if booked from car page)
	protected $itemModelIds            	= array();
	protected $itemModelUnits        	= array();
	protected $itemModelOptions        	= array();

	// pre-filled in step 3
	protected $extraIds		   		    = array();
	protected $extraUnits      		    = array();
	protected $extraOptions    		    = array();

	// Extra settings used for location manager, not by the class itself
	protected $shortDateFormat		    = 'Y-m-d';
	protected $currencySymbol		    = '$';
	protected $currencyCode			    = 'USD';
    protected $currencySymbolLocation	= 0;

	/**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
	 * @param array $paramSettings
	 */
	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        // Set search enabled status
        if(isset($paramSettings['conf_search_enabled']))
        {
            $this->searchEnabled = $paramSettings['conf_search_enabled'] == 1 ? true : false;
        }
        $this->useSessions = ConfigurationInterface::USE_SESSIONS == 1 ? true : false;
        $this->storageType = ConfigurationInterface::USE_SESSIONS == 1 ? 'SESSION' : 'COOKIE';

		$this->minOrderPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_minimum_booking_period', 'positive_integer', 0);
		$this->maxOrderPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_maximum_booking_period', 'positive_integer', 0);
        $this->minPeriodUntilPickup = StaticValidator::getValidSetting($paramSettings, 'conf_minimum_period_until_pickup', 'positive_integer', 0);
        $this->timeCeiling = ConfigurationInterface::TIME_CEILING;
        $this->priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1, array(1, 2, 3));
        $this->noonTime = StaticValidator::getValidSetting($paramSettings, 'conf_noon_time', "time_format", "12:00:00");

		// Extra settings used for location manager, not by the class itself
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
		$this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
		$this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
	}

	/**************************************************************/
	/************************* SETTINGS ***************************/
	/**************************************************************/

	public function inDebug()
	{
		return ($this->debugMode >= 1 ? true : false);
	}

	public function searchEnabled()
    {
        return $this->searchEnabled;
    }

	public function isValidSearch()
	{
	    if(sizeof($this->errorMessages) == 0)
        {
            return true;
        } else
        {
            return false;
        }
	}

    public function getAllMessages()
    {
        return array(
            'debug' => $this->debugMessages,
            'okay' => $this->okayMessages,
            'error' => $this->errorMessages,
        );
    }

    public function flushMessages()
    {
        $this->debugMessages = array();
        $this->okayMessages = array();
        $this->errorMessages = array();
    }

    public function getDebugMessages()
    {
        return $this->debugMessages;
    }

    public function getOkayMessages()
    {
        return $this->okayMessages;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

	/**************************************************************/
	/************************** GET PRINTS ************************/
	/**************************************************************/

	public function getI18nExpectedPickupDate()
	{
		// WordPress bug
		// BAD: return date_i18n(get_option('date_format'), $this->pickupTimestamp);
		// OK: return date(get_option('date_format'), $this->pickupTimestamp + get_option( 'gmt_offset' ) * 3600);

		// WordPress bug WorkAround
		return date_i18n(get_option('date_format'), $this->expectedPickupTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

	public function getI18nExpectedPickupTime()
	{
		// WordPress bug
		// BAD: return date_i18n(get_option('time_format'), $this->pickupTimestamp);
		// OK: return date(get_option('time_format'), $this->pickupTimestamp + get_option( 'gmt_offset' ) * 3600);
		return date_i18n(get_option('time_format'), $this->expectedPickupTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

	public function getI18nExpectedReturnDate()
	{
		// WordPress bug
		// BAD: return date_i18n(get_option('date_format'), $this->returnTimestamp);
		// OK: return date(get_option('date_format'), $this->returnTimestamp + get_option( 'gmt_offset' ) * 3600);
		return date_i18n(get_option('date_format'), $this->expectedReturnTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

	public function getI18nExpectedReturnTime()
	{
		// WordPress bug
		// BAD: return date_i18n(get_option('time_format'), $this->returnTimestamp);
		// OK: return date(get_option('time_format'), $this->returnTimestamp + get_option( 'gmt_offset' ) * 3600);
		return date_i18n(get_option('time_format'), $this->expectedReturnTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

    /**
     * Can be used for edit, or as a raw data.
     * @return string
     */
	public function getShortPickupDate()
	{
		// WordPress workaround
		return date_i18n($this->shortDateFormat, $this->expectedPickupTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

    /**
     * Can be used for edit, or as a raw data.
     * @return string
     */
	public function getISOPickupTime()
	{
		// WordPress workaround
		return date_i18n("H:i:s", $this->expectedPickupTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

    /**
     * Can be used for edit, or as a raw data.
     * @return string
     */
	public function getShortReturnDate()
	{
		// WordPress workaround
		return date_i18n($this->shortDateFormat, $this->expectedReturnTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

    /**
     * Can be used for edit, or as a raw data.
     * @return string
     */
	public function getISOReturnTime()
	{
		// WordPress workaround
		return date_i18n("H:i:s", $this->expectedReturnTimestamp + get_option( 'gmt_offset' ) * 3600, true);
	}

	/**************************************************************/
	/************** Just an methods abbreviation ******************/
	/**************************************************************/
	public function getLocalPickupDate()
	{
		return StaticValidator::getLocalDateByTimestamp($this->expectedPickupTimestamp, "Y-m-d");
	}

	public function getLocalPickupTime()
	{
		return StaticValidator::getLocalDateByTimestamp($this->expectedPickupTimestamp, "H:i:s");
	}

	public function getLocalReturnDate()
	{
		return StaticValidator::getLocalDateByTimestamp($this->expectedReturnTimestamp, "Y-m-d");
	}

	public function getLocalReturnTime()
	{
		return StaticValidator::getLocalDateByTimestamp($this->expectedReturnTimestamp, "H:i:s");
	}

	public function getLocalPickupDayOfWeek()
	{
		return StaticValidator::getLocalDateByTimestamp($this->expectedPickupTimestamp, "D");
	}

	public function getLocalReturnDayOfWeek()
	{
		return StaticValidator::getLocalDateByTimestamp($this->expectedReturnTimestamp, "D");
	}

	/**********************************************************************************************/
	/************************************* ACTUAL ELEMENTS ****************************************/
	/**********************************************************************************************/

	public function getExpectedPickupTimestamp()
	{
		return $this->expectedPickupTimestamp;
	}

	public function getExpectedReturnTimestamp()
	{
		return $this->expectedReturnTimestamp;
	}

	/**************************************************************/
	/********* Methods to retrieve order location, item, ********/
	/********* customer and order details ***********************/
	/**************************************************************/

    public function getCouponCode()
    {
        return $this->couponCode;
    }

    public function getPrintCouponCode()
    {
        return esc_html($this->couponCode);
    }

    public function getEditCouponCode()
    {
        return esc_attr($this->couponCode);
    }

	public function getPickupLocationId()
	{
		return $this->pickupLocationId;
	}

    public function getReturnLocationId()
	{
		return $this->returnLocationId;
	}

    public function getFleetPartnerId()
    {
        return $this->fleetPartnerId;
    }

    public function getManufacturerId()
    {
        return $this->manufacturerId;
    }
    
	public function getClassId()
	{
		return $this->classId;
	}

	public function getAttributeId2()
	{
		return $this->attributeId2;
	}

	public function getAttributeId1()
	{
		return $this->attributeId1;
	}

    public function getInputDataArray()
    {
        $searchInput = array(
            "pickup_timestamp" => $this->expectedPickupTimestamp,
            "return_timestamp" => $this->expectedReturnTimestamp,

            // Pick-up coordinates
            // NOTE: Reversed order
            "pickup_location_id" => $this->pickupLocationId,

            // Return coordinates
            // NOTE: Reversed order
            "return_location_id" => $this->returnLocationId,

            // Coupon
            "coupon_code" => $this->couponCode,

            // Filters (arrays)
            "partner_id" => $this->fleetPartnerId,
            "manufacturer_id" => $this->manufacturerId,
            "class_id" => $this->classId,
            "attribute_id1" => $this->attributeId1,
            "attribute_id2" => $this->attributeId2,

            "item_model_ids" => $this->itemModelIds,
            "item_model_units" => $this->itemModelUnits,
            "item_model_options" => $this->itemModelOptions,
            "extra_ids" => $this->extraIds,
            "extra_units" => $this->extraUnits,
            "extra_options" => $this->extraOptions,
        );

        return $searchInput;
    }

    public function getShowAllArray()
    {
        $arrShowAll = array(
            "pickup_date" => $this->getShortPickupDate(),
            "pickup_time" => $this->getISOPickupTime(),
            "return_date" => $this->getShortReturnDate(),
            "return_time" => $this->getISOReturnTime(),

            // Pick-up coordinates
            // NOTE: Reversed order
            "pickup_location_id" => $this->pickupLocationId,

            // Return coordinates
            // NOTE: Reversed order
            "return_location_id" => $this->returnLocationId,

            // Coupon
            "coupon_code" => esc_attr($this->couponCode),

            // Filters (arrays)
            "fleet_partner_ids" => array(),
            "location_partner_ids" => array(),
            "location_type_ids" => array(),
            "manufacturer_ids" => array(),
            "class_ids" => array(),
            "attribute_ids" => array(),
        );

        return $arrShowAll;
    }

    public function getItemModelIds()
    {
        return $this->itemModelIds;
    }

    public function getItemModelUnits()
    {
        return $this->itemModelUnits;
    }

    public function getItemModelOptions()
    {
        return $this->itemModelOptions;
    }

    public function getExtraIds()
    {
        return $this->extraIds;
    }

    public function getExtraUnits()
    {
        return $this->extraUnits;
    }

    public function getExtraOptions()
    {
        return $this->extraOptions;
    }

    public function getItemQuantity($paramItemModelId)
    {
        return isset($this->itemModelUnits[$paramItemModelId]) ? $this->itemModelUnits[$paramItemModelId] : 0;
    }

    public function getItemModelOption($paramItemModelId)
    {
        return isset($this->itemModelOptions[$paramItemModelId]) ? $this->itemModelOptions[$paramItemModelId] : 0;
    }

    public function getExtraQuantity($paramExtraId)
    {
        return isset($this->extraUnits[$paramExtraId]) ? $this->extraUnits[$paramExtraId] : 0;
    }

    public function getExtraOption($paramExtraId)
    {
        return isset($this->extraOptions[$paramExtraId]) ? $this->extraOptions[$paramExtraId] : 0;
    }

	/**************************************************************/
	/******************** Advanced methods 1 **********************/
	/**************************************************************/

    public function getItemModelsTotalSelectedUnits()
    {
        $totalUnitsSelected = 0;
        foreach($this->itemModelIds AS $itemModelId)
        {
            $itemModelUnitsSelected = isset($this->itemModelUnits[$itemModelId]) ? $this->itemModelUnits[$itemModelId] : 0;
            // Add current units amount
            $totalUnitsSelected += $itemModelUnitsSelected;
        }

        return $totalUnitsSelected;
    }

    public function getExtrasTotalSelectedUnits()
    {
        $totalUnitsSelected = 0;
        foreach($this->extraIds AS $extraId)
        {
            $extraUnitsSelected = isset($this->extraUnits[$extraId]) ? $this->extraUnits[$extraId] : 0;
            // Add current units amount
            $totalUnitsSelected += $extraUnitsSelected;
        }

        return $totalUnitsSelected;
    }

    /**
     * @param $paramArray
     * @param $paramFormatType
     * @param bool $paramQuoteOnly
     * @return array
     */
	protected function getFormattedPriceArray($paramArray, $paramFormatType, $paramQuoteOnly = false)
	{
        $retArray = array();
        foreach($paramArray AS $key => $price)
        {
            $showLongText = in_array($paramFormatType, array('long', 'long_without_fraction')) ? true : false;
            if($key == "fixed_deposit" && $price == 0.00)
            {
                $formattedPrice = $this->lang->getText($showLongText ? 'LANG_NOT_REQUIRED_TEXT' : 'LANG_NOT_REQ_TEXT');
            } else if(in_array($key, array("grand_total", "total_pay_later")) && $paramQuoteOnly)
            {
                $formattedPrice = $this->lang->getText($showLongText ? 'LANG_PRICING_GET_A_QUOTE_TEXT' : 'LANG_PRICING_INQUIRE_TEXT');
            } else
            {
                $formattedPrice = StaticFormatter::getFormattedPrice($price, $paramFormatType, $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
            }
            $retArray[$key] = $formattedPrice;
        }

        return $retArray;
	}

    public function validateInBeforeOut($paramPickupTimestamp, $paramReturnTimestamp)
    {
        // We use isValidSearch check here to avoid too many errors printed out for customers
        if($this->isValidSearch())
        {
            if($paramReturnTimestamp - $paramPickupTimestamp < 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_SEARCH_OUT_BEFORE_IN_ERROR_TEXT');
            }
        }

        return $this->isValidSearch();
    }

	/**
	 * @param $paramPickupTimestamp
	 * @param $paramReturnTimestamp
	 * @return bool
	 */
    public function validateTimeInput($paramPickupTimestamp, $paramReturnTimestamp)
	{
        // We use isValidSearch check here to avoid too many errors printed out for customers
        if($this->isValidSearch())
        {
            if($paramReturnTimestamp - $paramPickupTimestamp < $this->minOrderPeriod)
            {
                $errorMessage = sprintf($this->lang->getText('LANG_SEARCH_MINIMUM_DURATION_CANT_BE_LESS_THAN_S_ERROR_TEXT'), human_time_diff(0, $this->minOrderPeriod));
                $errorMessage .= ' '.$this->lang->getText('LANG_SEARCH_ERROR_PLEASE_MODIFY_YOUR_SEARCH_CRITERIA_TEXT');
                $this->errorMessages[] = $errorMessage;
            } else if($paramReturnTimestamp - $paramPickupTimestamp > $this->maxOrderPeriod)
            {
                $errorMessage = sprintf($this->lang->getText('LANG_SEARCH_MAXIMUM_DURATION_CANT_BE_MORE_THAN_S_ERROR_TEXT'), human_time_diff(0, $this->maxOrderPeriod));
                $errorMessage .= ' '.$this->lang->getText('LANG_SEARCH_ERROR_PLEASE_MODIFY_YOUR_SEARCH_CRITERIA_TEXT');
                $this->errorMessages[] = $errorMessage;
            }

            // Both members bellow are in GMT+0
            if($this->expectedPickupTimestamp < time())
            {
                $currentDateTime = date_i18n(get_option('date_format').' '.get_option('time_format'));
                $errorMessage = $this->lang->getText('LANG_ERROR_PICKUP_IS_NOT_POSSIBLE_ON_TEXT')." ";
                $errorMessage .= $this->getI18nExpectedPickupDate()." ".$this->getI18nExpectedPickupTime().". ";
                $errorMessage .= $this->lang->getText('LANG_ERROR_PLEASE_MODIFY_YOUR_PICKUP_TIME_BY_WEBSITE_TIME_TEXT')."\n";
                $errorMessage .= $this->lang->getText('LANG_ERROR_CURRENT_DATE_TIME_TEXT') ." ".$currentDateTime;
if($_GET["car_rental_change_order"] == "Change reservation"){
		// $this->errorMessages[] = $errorMessage;

}else{
	 // $this->errorMessages[] = $errorMessage;
}
                
            } else if($this->expectedPickupTimestamp < (time() + $this->minPeriodUntilPickup))
            {
                $earliestPossiblePickupLocalTime = time() + $this->minPeriodUntilPickup + get_option('gmt_offset') * 3600;
                $earliestPossiblePickupDateTime = date_i18n(get_option('date_format').' '.get_option('time_format'), $earliestPossiblePickupLocalTime, true);
                $errorMessage = $this->lang->getText('LANG_ERROR_PICKUP_IS_NOT_POSSIBLE_ON_TEXT')." ";
                $errorMessage .= $this->getI18nExpectedPickupDate()." ".$this->getI18nExpectedPickupTime().".\n";
                $errorMessage .= $this->lang->getText('LANG_ERROR_EARLIEST_POSSIBLE_PICKUP_DATE_TIME_TEXT')." ".$earliestPossiblePickupDateTime." ";
                $errorMessage .= $this->lang->getText('LANG_ERROR_OR_NEXT_BUSINESS_HOURS_OF_PICKUP_LOCATION_TEXT').".";
                if($this->debugMode)
                {
                    $errorMessage .= ' ('.$earliestPossiblePickupLocalTime.')';
                }

                $this->errorMessages[] = $errorMessage;
            }
        }

		return $this->isValidSearch();
	}

    public function validatePickupInput($paramLocationId, $paramTimestamp)
	{
		return $this->validateLocationInput("PICKUP", $paramLocationId, $paramTimestamp);
	}

	public function validateReturnInput($paramLocationId, $paramTimestamp)
	{
		return $this->validateLocationInput("RETURN", $paramLocationId, $paramTimestamp);
	}

	/**
	 * Checks the location
	 * @param string $paramType - "PICKUP" or "RETURN"
	 * @param $paramLocationId
	 * @param $paramTimestamp
	 * @return bool
	 */
	private function validateLocationInput($paramType, $paramLocationId, $paramTimestamp)
	{
		$validType = $paramType == "RETURN" ? "RETURN" : "PICKUP";
		$validTimestamp = StaticValidator::getValidPositiveInteger($paramTimestamp);
        $isoDate = StaticValidator::getLocalDateByTimestamp($validTimestamp, "Y-m-d");
        $isoTime = StaticValidator::getLocalDateByTimestamp($validTimestamp, "H:i:s");
        $dayOfWeek = StaticValidator::getLocalDateByTimestamp($validTimestamp, "D");
		$lowercaseType = strtolower($validType);

        // WordPress bug WorkAround
        $printDate = date_i18n(get_option('date_format'), $validTimestamp + get_option( 'gmt_offset' ) * 3600, true);
        $printTime = date_i18n(get_option('time_format'), $validTimestamp + get_option( 'gmt_offset' ) * 3600, true);

		/********************************************************************/
        $field = $validType == "RETURN" ? 'conf_search_return_location_required' : 'conf_search_pickup_location_required';
        $locationRequired = false;
        if(isset($this->settings[$field]))
        {
            $locationRequired = $this->settings[$field] == 1 ? true : false;
        }
		/********************************************************************/

		// Location validation
		$objLocation = new Location($this->conf, $this->lang, $this->settings, $paramLocationId);
        $validationProcessed = false;

		// We use isValidSearch check here to avoid too many errors printed out for customers
		if($this->isValidSearch() && ($objLocation->getId() > 0 || $locationRequired))
        {
            $validationProcessed = true;
            // Get location details
            $locationDetails = $objLocation->getDetailsByDayOfWeek($dayOfWeek);
            if(is_null($locationDetails))
            {
                // Selected location do not exists

                // As because this is probably a hack, we don't give any error notice of 'location does not exists'
                $this->errorMessages[] = $this->lang->getText($paramType == 'RETURN' ? 'LANG_LOCATION_RETURN_SELECT_ERROR_TEXT' : 'LANG_LOCATION_PICKUP_SELECT_ERROR_TEXT');
            } else if($objLocation->isOpenAtDate($isoDate) === false)
            {
                // Location is closed at selected date, because this date is marked as holidays 'Holidays day'

                // Add new error message to error messages stack
                if($paramType == "RETURN")
                {
                    $errorText = $this->lang->getText('LANG_ERROR_RETURN_LOCATION_IS_CLOSED_AT_THIS_DATE_TEXT');
                } else
                {
                    $errorText = $this->lang->getText('LANG_ERROR_PICKUP_LOCATION_IS_CLOSED_AT_THIS_DATE_TEXT');
                }
                $this->errorMessages[]  = sprintf(
                    $errorText,
                    $locationDetails['print_translated_location_name'], $locationDetails['print_full_address'], $printDate
                );



            } else if($objLocation->isOpenAtTime($isoTime, $dayOfWeek) === false)
            {
                if($locationDetails['afterhours_'.$lowercaseType.'_allowed'] == 0)
                {
                    // Afterhours pickup/return is not allowed, and current time is in afterhours
                    $businessHoursText = $objLocation->getBusinessHoursWithDayNameText();
                    $lunchHoursText = $objLocation->getLunchHoursText();

                    if($paramType == "RETURN")
                    {
                        $errorTimeText = $this->lang->getText('LANG_ERROR_RETURN_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT');
                        $errorNotAllowedText = $this->lang->getText('LANG_ERROR_AFTERHOURS_RETURN_IS_NOT_ALLOWED_AT_LOCATION_TEXT');
                    } else
                    {
                        $errorTimeText = $this->lang->getText('LANG_ERROR_PICKUP_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT');
                        $errorNotAllowedText = $this->lang->getText('LANG_ERROR_AFTERHOURS_PICKUP_IS_NOT_ALLOWED_AT_LOCATION_TEXT');
                    }
                    $errorMessage = "";
                    $errorMessage .= sprintf(
                        $errorTimeText,
                        $locationDetails['translated_location_name'], $locationDetails['print_full_address'], $printTime
                    );
                    $errorMessage .= "\n".$errorNotAllowedText;
                    $errorMessage .= "\n".sprintf(
                        $this->lang->getText('LANG_ERROR_LOCATION_OPEN_HOURS_ARE_TEXT'),
                        $dayOfWeek, $printDate, $locationDetails['print_open_hours']
                    );
                    $errorMessage .= "\n".$this->lang->getText('LANG_ERROR_LOCATION_WEEKLY_OPEN_HOURS_ARE_TEXT')."\n";
                    $errorMessage .= $businessHoursText;
                    $errorMessage .= $lunchHoursText != '' ? "\n-----------------------------\n".$lunchHoursText : "";

                    // Add new error message to error messages stack
                    $this->errorMessages[] = $errorMessage;
                } else if($locationDetails['afterhours_'.$lowercaseType.'_allowed'] == 1 && $locationDetails['afterhours_'.$lowercaseType.'_location_id'] == 0)
                {
                    // All ok - open 24/7 in same location in afterhours time
                    // No error
                } else if($locationDetails['afterhours_'.$lowercaseType.'_allowed'] == 1 && $locationDetails['afterhours_'.$lowercaseType.'_location_id'] > 0)
                {
                    // Afterhours pickup/return is allowed, and current time is in afterhours
                    $objAfterHoursLocation = new Location($this->conf, $this->lang, $this->settings, $paramLocationId);

                    if($objAfterHoursLocation->isOpenAtTime($isoTime, $dayOfWeek) === false)
                    {
                        // This is ok here to use Weekday details for after-hours knowing given purpose
                        $afterHoursLocationDetails = $objAfterHoursLocation->getDetailsByDayOfWeek(StaticValidator::getLocalDateByTimestamp($validTimestamp, "D"));

                        if(!is_null($afterHoursLocationDetails) && $locationDetails['afterhours_'.$lowercaseType.'_allowed'] == 1 && $afterHoursLocationDetails['afterhours_'.$lowercaseType.'_location_id'] == 0)
                        {
                            // All ok - afterhours location works 24/7
                            // No error
                        } else
                        {
                            // Afterhours location not exist or is not working 24/7 in that place and is closed during search hours
                            $businessHoursText = $objLocation->getBusinessHoursWithDayNameText();
                            $lunchHoursText = $objLocation->getLunchHoursText();

                            if($paramType == "RETURN")
                            {
                                $errorLocationTimeText = $this->lang->getText('LANG_ERROR_RETURN_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT');
                                $errorAfterHoursNotAllowedText = $this->lang->getText('LANG_ERROR_AFTERHOURS_RETURN_IS_NOT_ALLOWED_AT_LOCATION_TEXT');
                                $errorAfterHoursLocationTimeText = $this->lang->getText('LANG_ERROR_AFTERHOURS_RETURN_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT');
                                $errorAfterHoursLocationOpenHoursText = $this->lang->getText('LANG_ERROR_AFTERHOURS_RETURN_LOCATION_OPEN_HOURS_ARE_TEXT');
                            } else
                            {
                                $errorLocationTimeText = $this->lang->getText('LANG_ERROR_PICKUP_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT');
                                $errorAfterHoursNotAllowedText = $this->lang->getText('LANG_ERROR_AFTERHOURS_PICKUP_IS_NOT_ALLOWED_AT_LOCATION_TEXT');
                                $errorAfterHoursLocationTimeText = $this->lang->getText('LANG_ERROR_AFTERHOURS_PICKUP_LOCATION_IS_CLOSED_AT_THIS_TIME_TEXT');
                                $errorAfterHoursLocationOpenHoursText = $this->lang->getText('LANG_ERROR_AFTERHOURS_PICKUP_LOCATION_OPEN_HOURS_ARE_TEXT');
                            }
                            
                            $errorMessage = "";
                            $errorMessage .= sprintf(
                                $errorLocationTimeText,
                                $locationDetails['translated_location_name'], $locationDetails['print_full_address'], $printTime
                            );
                            if(is_null($afterHoursLocationDetails))
                            {
                                $errorMessage .= "\n".esc_html($errorAfterHoursNotAllowedText);
                            }
                            $errorMessage .= "\n".sprintf(
                                    $this->lang->getText('LANG_ERROR_LOCATION_OPEN_HOURS_ARE_TEXT'),
                                    $dayOfWeek, $printDate, $locationDetails['print_open_hours']
                                );
                            $errorMessage .= "\n".$this->lang->escHTML('LANG_ERROR_LOCATION_WEEKLY_OPEN_HOURS_ARE_TEXT')."\n";
                            $errorMessage .= $businessHoursText;
                            $errorMessage .= $lunchHoursText != '' ? "\n----------------------------\n".$lunchHoursText : "";
                            if(!is_null($afterHoursLocationDetails))
                            {
                                $errorMessage .= "\n".sprintf(
                                    $errorAfterHoursLocationTimeText,
                                    $afterHoursLocationDetails['translated_location_name'],
                                    $afterHoursLocationDetails['print_full_address']
                                );
                                $errorMessage .= "\n".sprintf(
                                        $errorAfterHoursLocationOpenHoursText,
                                    $afterHoursLocationDetails['print_open_hours']
                                );
                            }

                            // Add new error message to error messages stack
                            $this->errorMessages[] = $errorMessage;
                        }
                    }
                }
            }
        }

		if($this->debugMode)
		{
            echo "<br /><strong>[VALIDATE]</strong> Location: ".intval($paramLocationId)." [{$validType}], Processed: ".var_export($validationProcessed, true).", ";
			echo "Date: ".StaticValidator::getLocalDateByTimestamp($validTimestamp, "Y-m-d").", ";
			echo "Time: ".StaticValidator::getLocalDateByTimestamp($validTimestamp, "H:i:s").", ";
			echo "Day of Week: ".StaticValidator::getLocalDateByTimestamp($validTimestamp, "D").", ";
            echo "Unix Timestamp: ".$validTimestamp."";
			//echo nl2br(print_r($locationData, true));
		}

		return $this->isValidSearch();
	}
}