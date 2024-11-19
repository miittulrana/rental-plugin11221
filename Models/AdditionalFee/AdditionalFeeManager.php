<?php
/**
 * Additional Fee Manager
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\AdditionalFee;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;

final class AdditionalFeeManager
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $settings 	            = array();
    private $additionalFeeId 	    = 0;

    private $timeCeiling            = 'BY_TIME_COUNT';
    private $noonTime               = '12:00:00';
    private $currencySymbol		    = '$';
    private $currencyCode			= 'USD';
    private $currencySymbolLocation	= 0;
    // Dynamic tax percentage
    private $taxPercentage		    = 0.00;
    private $showPriceWithTaxes	    = 0;

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param array $paramSettings
     * @param int $paramAdditionalFeeId
     * @param float $paramTaxPercentage
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramAdditionalFeeId, $paramTaxPercentage)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        $this->additionalFeeId = StaticValidator::getValidValue($paramAdditionalFeeId, 'positive_integer', 0);
        $this->timeCeiling = ConfigurationInterface::TIME_CEILING;
        $this->noonTime = StaticValidator::getValidSetting($paramSettings, 'conf_noon_time', "time_format", "12:00:00");
        $this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));

        // Dynamic tax percentage
        $this->taxPercentage = floatval($paramTaxPercentage);
        $this->showPriceWithTaxes = StaticValidator::getValidSetting($paramSettings, 'conf_show_price_with_taxes', 'positive_integer', 1, array(0, 1));
    }

    /**
     * Get single fee from MySQL database
     * @note - MUST BE PRIVATE. FOR INTERNAL USE ONLY
     * @return float
     */
    public function getSingleFee()
    {
        $retFee = 0.00;

        // For all items reservation
        $validAdditionalFeeId = StaticValidator::getValidPositiveInteger($this->additionalFeeId);
        $sqlQuery = "
			SELECT distance_fee AS additional_fee
			FROM {$this->conf->getPrefix()}distances
			WHERE distance_id='{$validAdditionalFeeId}'
			AND blog_id='{$this->conf->getBlogId()}'
		";
        $dbFee = $this->conf->getInternalWPDB()->get_var($sqlQuery);

        if(!is_null($dbFee))
        {
            $retFee = $dbFee;
        }

        return $retFee;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    protected function getFormattedPrice($paramPrice, $paramFormatType)
    {
        return StaticFormatter::getFormattedPrice($paramPrice, $paramFormatType, $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
    }

    protected function getFormattedPriceArray($paramArray, $paramFormatType)
    {
        $retArray = array();
        foreach($paramArray AS $key => $price)
        {
            $retArray[$key] = StaticFormatter::getFormattedPrice($price, $paramFormatType, $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
        }

        return $retArray;
    }

    /**
     * @param int $paramPickupTimestamp - supports [-1]
     * @param int $paramReturnTimestamp - supports [-1]
     * @return array
     */
    public function getSingleFeeDetails($paramPickupTimestamp = -1, $paramReturnTimestamp = -1)
    {
        return $this->getFeeDetails($paramPickupTimestamp, $paramReturnTimestamp, 1);
    }

    /**
     * NOTE #1: There should be no "unit" function, as the additional fee can be "per order" and $paramItemCount can be equal to 0
     * NOTE #2: We pass here pickup_timestamp and return_timestamp, because timeframe value in seconds can be different based on the given year or month
     * NOTE #3: We use $paramItemsCount (as it is not always will be multiplied if it is set ONCE and not PER_ITEM)
     *
     * @param int $paramPickupTimestamp - supports [-1]
     * @param int $paramReturnTimestamp - supports [-1]
     * @param int $paramItemCount - supports [0]
     * @return array
     */
    public function getFeeDetails($paramPickupTimestamp = -1, $paramReturnTimestamp = -1, $paramItemCount = 0)
    {
        $validItemCount = StaticValidator::getValidPositiveInteger($paramItemCount, 0);

        // Set defaults
        $additionalFee = 0.00;
        $timeframe = "ONCE";
        $applications = 1;
        $periodMultiplier = 1; // Supports [-1] timestamps

        // For all items reservation
        $validAdditionalFeeId = StaticValidator::getValidPositiveInteger($this->additionalFeeId);

        // Exact query with ID (no EXT_CODE or BLOG_ID needed)
        $sqlQuery = "
			SELECT distance_fee AS additional_fee,
                'PER_ITEM' AS fee_application,
                'ONCE' AS timeframe
			FROM {$this->conf->getPrefix()}distances
			WHERE distance_id='{$validAdditionalFeeId}'
		";
        $additionalFeeData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        if(!is_null($additionalFeeData))
        {
            // Fee is multiplied by the amount of matching items or fee is applied once per order
            $applications = $additionalFeeData['fee_application'] == "PER_ITEM" ? $validItemCount : 1;
            $timeframe = $additionalFeeData['timeframe'];
            $additionalFee = $additionalFeeData['additional_fee'];
        }

        if($paramPickupTimestamp >= 0 && $paramReturnTimestamp >= 0)
        {
            $periodMultiplier = $this->getPeriodMultiplierByParams($timeframe, $paramPickupTimestamp, $paramReturnTimestamp);
        }

        // For all periods
        $singleTotal = $additionalFee * $periodMultiplier;
        $singleTotalWithTax = $singleTotal * (1 + $this->taxPercentage / 100);
        $singleTotalDynamic = $this->showPriceWithTaxes == 1 ? $singleTotalWithTax : $singleTotal;
        $taxAmount = $singleTotalWithTax - $singleTotal;

        // Per one period
        $periodUnitTotal = $additionalFee * 1;
        $periodUnitTotalWithTax = $periodUnitTotal * (1 + $this->taxPercentage / 100);
        $periodUnitTotalDynamic = $this->showPriceWithTaxes == 1 ? $periodUnitTotalWithTax : $periodUnitTotal;
        $periodTaxAmount = $periodUnitTotalWithTax - $periodUnitTotal;

        // Fee details
        $retFees = array();
        // NOTE: For additional fees we use word 'singe', not 'unit',
        //       as it is the best word to describe fees (that may exist or not, and are not physical objects)
        // NOTE #2: We must round fees with 2 chars after comma, to avoid issues with multiplied fees print
        $retFees = array();
        $retFees['single'] = array(
            "total" => round($singleTotal, 2),
            "total_with_tax" => round($singleTotalWithTax, 2),
            "total_dynamic" => round($singleTotalDynamic, 2),
            "tax_amount" => round($taxAmount, 2),
        );
        $retFees['single_per_period'] = array(
            "total" => round($periodUnitTotal, 2),
            "total_with_tax" => round($periodUnitTotalWithTax, 2),
            "total_dynamic" => round($periodUnitTotalDynamic, 2),
            "tax_amount" => round($periodTaxAmount, 2),
        );

        $retFees['tax_percentage'] = $this->taxPercentage;
        // NOTE: for additional fees the word is 'applications', not 'multiplier'
        $retFees['applications'] = $applications;
        $retFees['period_multiplier'] = $periodMultiplier;
        $retFees['multiplied'] = StaticFormatter::getMultipliedNumberArray($retFees['single'], $applications);
        $retFees['multiplied_per_period'] = StaticFormatter::getMultipliedNumberArray($retFees['single_per_period'], $applications);

        // Single prints
        $retFees['single_tiny_print'] = $this->getFormattedPriceArray($retFees['single'], "tiny");
        $retFees['single_tiny_without_fraction_print'] = $this->getFormattedPriceArray($retFees['single'], "tiny_without_fraction");
        $retFees['single_print'] = $this->getFormattedPriceArray($retFees['single'], "regular");
        $retFees['single_without_fraction_print'] = $this->getFormattedPriceArray($retFees['single'], "regular_without_fraction");
        $retFees['single_long_print'] = $this->getFormattedPriceArray($retFees['single'], "long");
        $retFees['single_long_without_fraction_print'] = $this->getFormattedPriceArray($retFees['single'], "long_without_fraction");

        // Single per period prints
        $retFees['single_per_period_tiny_print'] = $this->getFormattedPriceArray($retFees['single_per_period'], "tiny");
        $retFees['single_per_period_tiny_without_fraction_print'] = $this->getFormattedPriceArray($retFees['single_per_period'], "tiny_without_fraction");
        $retFees['single_per_period_print'] = $this->getFormattedPriceArray($retFees['single_per_period'], "regular");
        $retFees['single_per_period_without_fraction_print'] = $this->getFormattedPriceArray($retFees['single_per_period'], "regular_without_fraction");
        $retFees['single_per_period_long_print'] = $this->getFormattedPriceArray($retFees['single_per_period'], "long");
        $retFees['single_per_period_long_without_fraction_print'] = $this->getFormattedPriceArray($retFees['single_per_period'], "long_without_fraction");

        // Multiplied prints
        $retFees['multiplied_tiny_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "tiny");
        $retFees['multiplied_tiny_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "tiny_without_fraction");
        $retFees['multiplied_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "regular");
        $retFees['multiplied_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "regular_without_fraction");
        $retFees['multiplied_long_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "long");
        $retFees['multiplied_long_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "long_without_fraction");

        // Multiplied per period prints
        $retFees['multiplied_per_period_tiny_print'] = $this->getFormattedPriceArray($retFees['multiplied_per_period'], "tiny");
        $retFees['multiplied_per_period_tiny_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied_per_period'], "tiny_without_fraction");
        $retFees['multiplied_per_period_print'] = $this->getFormattedPriceArray($retFees['multiplied_per_period'], "regular");
        $retFees['multiplied_per_period_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied_per_period'], "regular_without_fraction");
        $retFees['multiplied_per_period_long_print'] = $this->getFormattedPriceArray($retFees['multiplied_per_period'], "long");
        $retFees['multiplied_per_period_long_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied_per_period'], "long_without_fraction");

        // Word prints
        $arrPriceDetails['time_ext_print'] = esc_html($this->getPricePeriodText("SHORT"));
        $arrPriceDetails['time_ext_long_print'] = esc_html($this->getPricePeriodText("LONG"));

        // Percentages (non-escaped)
        $retPrices['formatted_tax_percentage'] = StaticFormatter::getFormattedPercentage($retFees['tax_percentage'], "regular");

        return $retFees;
    }

    /**
     * @param string $paramType - SHORT OR LONG
     * @return string
     */
    public function getPricePeriodText($paramType = "LONG")
    {
        $text = "";

        $validAdditionalFeeId = StaticValidator::getValidPositiveInteger($this->additionalFeeId, 0);
        $extraData = $this->conf->getInternalWPDB()->get_row("
			SELECT 'ONCE' AS timeframe
			FROM {$this->conf->getPrefix()}distances
			WHERE distance_id='{$validAdditionalFeeId}'
		", ARRAY_A);
        if(!is_null($extraData))
        {
            switch($extraData['timeframe'])
            {
                case "EVERY_MINUTE":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_PER_MINUTE_TEXT' : 'LANG_PRICING_PER_MINUTE_SHORT_TEXT');
                    break;
                case "HOURLY":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_PER_HOUR_TEXT' : 'LANG_PRICING_PER_HOUR_SHORT_TEXT');
                    break;
                case "DAILY":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_PER_DAY_TEXT' : 'LANG_PRICING_PER_DAY_SHORT_TEXT');
                    break;
                case "NIGHTLY":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_PER_NIGHT_TEXT' : 'LANG_PRICING_PER_NIGHT_SHORT_TEXT');
                    break;
                case "WEEKLY":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_PER_WEEK_TEXT' : 'LANG_PRICING_PER_WEEK_SHORT_TEXT');
                    break;
                case "MONTHLY":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_PER_MONTH_TEXT' : 'LANG_PRICING_PER_MONTH_SHORT_TEXT');
                    break;
                case "YEARLY":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_PER_YEAR_TEXT' : 'LANG_PRICING_PER_YEAR_SHORT_TEXT');
                    break;
                case "ONCE":
                    $text = $this->lang->getText($paramType == 'LONG' ? 'LANG_PRICING_ONCE_TEXT' : 'LANG_PRICING_ONCE_SHORT_TEXT');
                    break;
                default:
                    $text = "";
                    break;
            }
        }

        return $text;
    }

    private function getPeriodMultiplierByParams($paramTimeframe, $paramPickupTimestamp, $paramReturnTimestamp)
    {
        // Set defaults
        $periodMultiplier = 0;

        switch ($paramTimeframe)
        {
            case "EVERY_MINUTE":
                $period = StaticValidator::getPeriod($paramPickupTimestamp, $paramReturnTimestamp, false);
                // Always up-round to the next integer number
                $periodMultiplier = ceil($period / 60);
                break;

            case "HOURLY":
                $period = StaticValidator::getPeriod($paramPickupTimestamp, $paramReturnTimestamp, false);
                // Always up-round to the next integer number
                $periodMultiplier = ceil($period / 3600);
                break;

            case "DAILY":
            case "NIGHTLY":
                $periodMultiplier = StaticValidator::getDateCount($this->timeCeiling, $paramPickupTimestamp, $paramReturnTimestamp, $this->noonTime);
                break;

            case "WEEKLY":
                $dateCount = StaticValidator::getDateCount($this->timeCeiling, $paramPickupTimestamp, $paramReturnTimestamp, $this->noonTime);
                $periodMultiplier = ceil($dateCount / 7);
                break;

            case "MONTHLY":
                $modifiedReturnTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($this->timeCeiling, $paramPickupTimestamp, $paramReturnTimestamp, $this->noonTime);
                // Always up-round to the next integer number
                $periodMultiplier = StaticValidator::getCeilTotalMonthsBetweenTwoTimestamps($paramPickupTimestamp, $modifiedReturnTimestamp);
                break;

            case "YEARLY":
                $modifiedReturnTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($this->timeCeiling, $paramPickupTimestamp, $paramReturnTimestamp, $this->noonTime);
                // Always up-round to the next integer number
                $periodMultiplier = StaticValidator::getCeilTotalYearsBetweenTwoTimestamps($paramPickupTimestamp, $modifiedReturnTimestamp);
                break;

            case "ONCE":
                $periodMultiplier = 1;
                break;
        }

        return $periodMultiplier;
    }
}