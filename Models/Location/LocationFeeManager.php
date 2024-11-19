<?php
/**
 * Location Fee Manager
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Location;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;

final class LocationFeeManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings 	            = array();
    protected $locationId 	            = 0;
    protected $pickupLocationId 	    = 0;
    protected $returnLocationId 	    = 0;

    protected $currencySymbol		    = '$';
    protected $currencyCode			    = 'USD';
    protected $currencySymbolLocation	= 0;
    // Dynamic tax percentage
    protected $taxPercentage		    = 0.00;
    protected $showPriceWithTaxes	    = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramLocationId, $paramTaxPercentage)
    {
        $this->locationId = StaticValidator::getValidValue($paramLocationId, 'positive_integer', 0);
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        $this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
        // Dynamic tax percentage
        $this->taxPercentage = floatval($paramTaxPercentage);
        $this->showPriceWithTaxes = StaticValidator::getValidSetting($paramSettings, 'conf_show_price_with_taxes', 'positive_integer', 1, array(0, 1));
    }


    /**
     * Get location fees from MySQL database
     * @param int $paramLocationId - primary it's this class unique id, with some exceptions when we call for afterhours id
     * @return mixed
     */
    private function getFeesById($paramLocationId)
    {
        $validLocationId = StaticValidator::getValidPositiveInteger($paramLocationId);
        $sqlQuery = "
			SELECT
				pickup_fee, return_fee, afterhours_pickup_fee, afterhours_return_fee
			FROM {$this->conf->getPrefix()}locations
			WHERE location_id='{$validLocationId}'
		";
        $retFees = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retFees;
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

    public function getUnitDetails($paramAdditionalFee = 0.00, $paramIsAfterHours = false)
    {
        return $this->getDetails($paramAdditionalFee, 1, $paramIsAfterHours);
    }

    public function getDetails($paramAdditionalFee = 0.00, $paramMultiplier = 1, $paramIsAfterHours = false)
    {
        $validMultiplier = StaticValidator::getValidPositiveInteger($paramMultiplier, 1);

        $locationFees = $this->getFeesById($this->locationId);
        // Calculate pickup, return and distance fees with taxes
        if(!is_null($locationFees))
        {
            $pickupFee = $locationFees['pickup_fee'];
            $returnFee = $locationFees['return_fee'];
            $afterHoursPickupFee = $locationFees['afterhours_pickup_fee'];
            $afterHoursReturnFee = $locationFees['afterhours_return_fee'];
        } else
        {
            $pickupFee = 0.00;
            $returnFee = 0.00;
            $afterHoursPickupFee = 0.00;
            $afterHoursReturnFee = 0.00;
        }

        $pickupFeeWithTax = $pickupFee * (1 + $this->taxPercentage / 100);
        $pickupFeeDynamic = $this->showPriceWithTaxes == 1 ? $pickupFeeWithTax : $pickupFee;
        $pickupTaxAmount = $pickupFeeWithTax - $pickupFee;

        $returnFeeWithTax = $returnFee * (1 + $this->taxPercentage / 100);
        $returnFeeDynamic = $this->showPriceWithTaxes == 1 ? $returnFeeWithTax : $returnFee;
        $returnTaxAmount = $returnFeeWithTax - $returnFee;

        $afterHoursPickupFeeWithTax = $afterHoursPickupFee * (1 + $this->taxPercentage / 100);
        $afterHoursPickupFeeDynamic = $this->showPriceWithTaxes == 1 ? $afterHoursPickupFeeWithTax : $afterHoursPickupFee;
        $afterHoursPickupTaxAmount = $afterHoursPickupFeeWithTax - $afterHoursPickupFee;

        $afterHoursReturnFeeWithTax = $afterHoursReturnFee * (1 + $this->taxPercentage / 100);
        $afterHoursReturnFeeDynamic = $this->showPriceWithTaxes == 1 ? $afterHoursReturnFeeWithTax : $afterHoursReturnFee;
        $afterHoursReturnTaxAmount = $afterHoursReturnFeeWithTax - $afterHoursReturnFee;

        $currentPickupFee = $paramIsAfterHours ? $afterHoursPickupFee : $pickupFee;
        $currentPickupFeeWithTax = $currentPickupFee * (1 + $this->taxPercentage / 100);
        $currentPickupFeeDynamic = $this->showPriceWithTaxes == 1 ? $currentPickupFeeWithTax : $currentPickupFee;
        $currentPickupTaxAmount = $currentPickupFeeWithTax - $currentPickupFee;

        $currentReturnFee = $paramIsAfterHours ? $afterHoursReturnFee : $returnFee;
        $currentReturnFeeWithTax = $currentReturnFee * (1 + $this->taxPercentage / 100);
        $currentReturnFeeDynamic = $this->showPriceWithTaxes == 1 ? $currentReturnFeeWithTax : $currentReturnFee;
        $currentReturnTaxAmount = $currentReturnFeeWithTax - $currentReturnFee;

        // Fee details
        $printCurrentPickupFeeDetails = "";
        $printCurrentReturnFeeDetails = "";
        $printMultipliedCurrentPickupFeeDetails = "";
        $printMultipliedCurrentReturnFeeDetails = "";
        if($this->taxPercentage > 0)
        {
            $printCurrentPickupFeeDetails .= $this->getFormattedPrice($currentPickupFee, 'regular').' '.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').' + ';
            $printCurrentPickupFeeDetails .= $this->getFormattedPrice($currentPickupTaxAmount, 'regular').' '.$this->lang->getText('LANG_TAX_SHORT_TEXT').' = ';
            $printCurrentPickupFeeDetails .= $this->getFormattedPrice($currentPickupFeeWithTax, 'regular');

            $printCurrentReturnFeeDetails .= $this->getFormattedPrice($currentReturnFee, 'regular').' '.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').' + ';
            $printCurrentReturnFeeDetails .= $this->getFormattedPrice($currentReturnTaxAmount, 'regular').' '.$this->lang->getText('LANG_TAX_SHORT_TEXT').' = ';
            $printCurrentReturnFeeDetails .= $this->getFormattedPrice($currentReturnFeeWithTax, 'regular');

            $printMultipliedCurrentPickupFeeDetails .= $this->getFormattedPrice($currentPickupFee * $validMultiplier, 'regular').' '.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').' + ';
            $printMultipliedCurrentPickupFeeDetails .= $this->getFormattedPrice($currentPickupTaxAmount * $validMultiplier, 'regular').' '.$this->lang->getText('LANG_TAX_SHORT_TEXT').' = ';
            $printMultipliedCurrentPickupFeeDetails .= $this->getFormattedPrice($currentPickupFeeWithTax * $validMultiplier, 'regular');

            $printMultipliedCurrentReturnFeeDetails .= $this->getFormattedPrice($currentReturnFee * $validMultiplier, 'regular').' '.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').' + ';
            $printMultipliedCurrentReturnFeeDetails .= $this->getFormattedPrice($currentReturnTaxAmount * $validMultiplier, 'regular').' '.$this->lang->getText('LANG_TAX_SHORT_TEXT').' = ';
            $printMultipliedCurrentReturnFeeDetails .= $this->getFormattedPrice($currentReturnFeeWithTax * $validMultiplier, 'regular');
        }


        // Fee details
        $retFees = array();
        $retFees['unit'] = array(
            "pickup_fee" => $pickupFee,
            "pickup_fee_with_tax" => $pickupFeeWithTax,
            "pickup_fee_dynamic" => $pickupFeeDynamic,
            "pickup_tax_amount" => $pickupTaxAmount,

            "return_fee" => $returnFee,
            "return_fee_with_tax" => $returnFeeWithTax,
            "return_fee_dynamic" => $returnFeeDynamic,
            "return_tax_amount" => $returnTaxAmount,

            "afterhours_pickup_fee" => $afterHoursPickupFee,
            "afterhours_pickup_fee_with_tax" => $afterHoursPickupFeeWithTax,
            "afterhours_pickup_fee_dynamic" => $afterHoursPickupFeeDynamic,
            "afterhours_pickup_tax_amount" => $afterHoursPickupTaxAmount,

            "afterhours_return_fee" => $afterHoursReturnFee,
            "afterhours_return_fee_with_tax" => $afterHoursReturnFeeWithTax,
            "afterhours_return_fee_dynamic" => $afterHoursReturnFeeDynamic,
            "afterhours_return_tax_amount" => $afterHoursReturnTaxAmount,

            "current_pickup_fee" => $currentPickupFee,
            "current_pickup_fee_with_tax" => $currentPickupFeeWithTax,
            "current_pickup_fee_dynamic" => $currentPickupFeeDynamic,
            "current_pickup_tax_amount" => $currentPickupTaxAmount,

            "current_return_fee" => $currentReturnFee,
            "current_return_fee_with_tax" => $currentReturnFeeWithTax,
            "current_return_fee_dynamic" => $currentReturnFeeDynamic,
            "current_return_tax_amount" => $currentReturnTaxAmount,
        );

        $retFees['tax_percentage'] = $this->taxPercentage;
        $retFees['multiplier'] = $validMultiplier;
        $retFees['multiplied'] = StaticFormatter::getMultipliedNumberArray($retFees['unit'], $validMultiplier);

        // Unit prints
        $retFees['unit_tiny_print'] = $this->getFormattedPriceArray($retFees['unit'], "tiny");
        $retFees['unit_tiny_without_fraction_print'] = $this->getFormattedPriceArray($retFees['unit'], "tiny_without_fraction");
        $retFees['unit_print'] = $this->getFormattedPriceArray($retFees['unit'], "regular");
        $retFees['unit_without_fraction_print'] = $this->getFormattedPriceArray($retFees['unit'], "regular_without_fraction");
        $retFees['unit_long_print'] = $this->getFormattedPriceArray($retFees['unit'], "long");
        $retFees['unit_long_without_fraction_print'] = $this->getFormattedPriceArray($retFees['unit'], "long_without_fraction");

        // Multiplied prints
        $retFees['multiplied_tiny_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "tiny");
        $retFees['multiplied_tiny_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "tiny_without_fraction");
        $retFees['multiplied_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "regular");
        $retFees['multiplied_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "regular_without_fraction");
        $retFees['multiplied_long_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "long");
        $retFees['multiplied_long_without_fraction_print'] = $this->getFormattedPriceArray($retFees['multiplied'], "long_without_fraction");

        // Percentages (not-escaped)
        $retFees['formatted_tax_percentage'] = StaticFormatter::getFormattedPercentage($retFees['tax_percentage'], "regular");

        // Additional prints
        $retFees['print_current_pickup_fee_details'] = $printCurrentPickupFeeDetails;
        $retFees['print_current_return_fee_details'] = $printCurrentReturnFeeDetails;
        $retFees['print_multiplied_current_pickup_fee_details'] = $printMultipliedCurrentPickupFeeDetails;
        $retFees['print_multiplied_current_return_fee_details'] = $printMultipliedCurrentReturnFeeDetails;

        return $retFees;
    }
}