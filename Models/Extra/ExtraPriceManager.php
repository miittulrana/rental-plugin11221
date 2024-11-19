<?php
/**
 * Extra's Price Manager

 * @package FleetManagement
 * @uses DepositManager, DiscountManager, PrepaymentManager
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtraDepositManager;
use FleetManagement\Models\Extra\ExtraDiscountManager;

final class ExtraPriceManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0; // 0 - off, 1 - regular, 2 - deep items, 3 - deep extras
    protected $settings                 = array();
    // Price calculation type: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
    protected $priceCalculationType     = 1;
    protected $currencySymbol		    = '$';
    protected $currencyCode			    = 'USD';
    protected $currencySymbolLocation	= 0;
    // Dynamic tax percentage
    protected $taxPercentage		    = 0.00;
    protected $showPriceWithTaxes	    = 0;
    protected $extraId			        = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramExtraId, $paramTaxPercentage)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        $this->extraId = StaticValidator::getValidValue($paramExtraId, 'positive_integer', 0);

        $this->priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1, array(1, 2, 3));
        $this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));

        // Dynamic tax percentage
        $this->taxPercentage = floatval($paramTaxPercentage);
        $this->showPriceWithTaxes = StaticValidator::getValidSetting($paramSettings, 'conf_show_price_with_taxes', 'positive_integer', 1, array(0, 1));
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @param int $paramPickupTimestamp
     * @param int $paramReturnTimestamp
     * @return float
     */
    public function getUnitPriceByInterval($paramPickupTimestamp, $paramReturnTimestamp)
    {
		$subTotalExtraPrice 	= 0;
		$validExtraId 			= StaticValidator::getValidPositiveInteger($this->extraId, 0);
		$validPickupTimestamp	= StaticValidator::getValidPositiveInteger($paramPickupTimestamp, 0);
		$validReturnTimestamp	= StaticValidator::getValidPositiveInteger($paramReturnTimestamp, 0);
		$validPeriod			= StaticValidator::getPeriod($validPickupTimestamp, $validReturnTimestamp, false);
		//$secondsOnLastDay 	= $validPeriod - floor($validPeriod / 86400) * 86400;

		$extraData = $this->conf->getInternalWPDB()->get_row("
			SELECT price, price_type
			FROM {$this->conf->getPrefix()}extras
			WHERE extra_id='{$validExtraId}'
		", ARRAY_A);
		if(!is_null($extraData))
		{
			$extraPrice = $extraData['price'];
			$extraPriceType = $extraData['price_type'];

			if($extraPriceType == 0)
			{
				// Price per booking
				$subTotalExtraPrice = $extraPrice;
			} else if($extraPriceType == 1 && $this->priceCalculationType == "1")
			{
				// Extra Price Type = DAYS, Price Calculation Type = DAYS
				$subTotalExtraPrice = $extraPrice * ceil($validPeriod / 86400);
			} else if($extraPriceType == 2 && $this->priceCalculationType == "2")
			{
				// Extra Price Type = HOURS, Price Calculation Type = HOURS
				$subTotalExtraPrice = $extraPrice * ceil($validPeriod / 3600);
			} else if($extraPriceType == 2 && $this->priceCalculationType == "3")
			{
				// Extra Price Type = HOURS, Price Calculation Type = MIXED (HOUR ACCURACY, RESULT IN DAYS)
				$subTotalExtraPrice = $extraPrice * ceil($validPeriod / 3600);



			} else if($extraPriceType == 2 && $this->priceCalculationType == "1")
			{
				// Extra Price Type = HOURS, Price Calculation Type = DAYS
				$subTotalExtraPrice = ($extraPrice * 24) * ceil($validPeriod / 86400);
			} else if($extraPriceType == 1 && $this->priceCalculationType == "2")
			{
				// Extra Price Type = DAYS, Price Calculation Type = HOURS
				$subTotalExtraPrice = ($extraPrice / 24) * ceil($validPeriod / 3600);
			} else if($extraPriceType == 1 && $this->priceCalculationType == "3")
			{
				// Extra Price Type = DAYS, Price Calculation Type = MIXED (HOUR ACCURACY, RESULT IN DAYS)
				$subTotalExtraPrice = ($extraPrice / 24) * ceil($validPeriod / 3600);
			}
		}

        // We must round price amounts with 2 chars after comma, to avoid issues with multiplied amounts print
        $subTotalExtraPrice = round($subTotalExtraPrice, 2);

        if($this->debugMode)
        {
            echo "<br />-&gt; Extra Id: {$validExtraId} , ";
            echo "Local Date: ".date("Y-m-d", $validPickupTimestamp + get_option('gmt_offset') * 3600)." (Unix, GMT: {$validPickupTimestamp}), ";
            echo "Price w/o VAT: ".$subTotalExtraPrice;
        }

		return $subTotalExtraPrice;
	}

	public function getUnitPriceDetailsByInterval($paramPickupTimestamp, $paramReturnTimestamp)
	{
		return $this->getPriceDetails($paramPickupTimestamp, $paramReturnTimestamp, 1, false);
	}

	public function getMultipliedPriceDetailsByInterval($paramPickupTimestamp, $paramReturnTimestamp, $paramMultiplier)
	{
		return $this->getPriceDetails($paramPickupTimestamp, $paramReturnTimestamp, $paramMultiplier, false);
	}

    /**
     * @note1 - FASTEST METHOD - does not include deposit and prepayment details
     * @note2 - For extras Week's Cheapest and Priciest days are the same
     */
    public function getMinimalPriceDetails()
    {
        $localPickupTimestamp = time() + get_option( 'gmt_offset' ) * 3600;
        $localReturnTimestamp = ($this->priceCalculationType == 2 ? time()+3600 : time()+86400) + get_option( 'gmt_offset' ) * 3600;
        $minimalPriceDetails = $this->getPriceDetails($localPickupTimestamp, $localReturnTimestamp, 1, true);

        return $minimalPriceDetails;
    }

    /**
     * @Note - Extras has same prices for each day of week
     * @param $paramDiscountPeriodFrom
     * @param $paramDiscountPeriodTill
     * @return mixed
     */
    public function getPriceDataInWeek($paramDiscountPeriodFrom, $paramDiscountPeriodTill)
    {
        $localPickupTimestamp = time() + get_option( 'gmt_offset' ) * 3600;
        $localReturnTimestamp = ($this->priceCalculationType == "2" ? time()+3600 : time()+86400) + get_option( 'gmt_offset' ) * 3600;

        $priceWithoutTax = $this->getUnitPriceByInterval($localPickupTimestamp, $localReturnTimestamp);
        $objDiscountManager = new ExtraDiscountManager($this->conf, $this->lang, $this->settings, $this->extraId);
        $discountAmountWithMaxAdvance = $priceWithoutTax * ($objDiscountManager->getTotalPercentageWithMinAdvance($paramDiscountPeriodTill) / 100);
        $discountAmountWithMinAdvance = $priceWithoutTax * ($objDiscountManager->getTotalPercentageWithMinAdvance($paramDiscountPeriodFrom) / 100);

        if($this->showPriceWithTaxes == 1)
        {
            $minPrice = ($priceWithoutTax - $discountAmountWithMaxAdvance) * (1 + $this->taxPercentage / 100);
            $maxPrice = ($priceWithoutTax - $discountAmountWithMinAdvance) * (1 + $this->taxPercentage / 100);
            $discountForMinPrice = $discountAmountWithMaxAdvance * (1 + $this->taxPercentage / 100);
            $discountForMaxPrice = $discountAmountWithMinAdvance * (1 + $this->taxPercentage / 100);
        } else
        {
            $minPrice = $priceWithoutTax - $discountAmountWithMaxAdvance;
            $maxPrice = $priceWithoutTax - $discountAmountWithMinAdvance;
            $discountForMinPrice = $discountAmountWithMaxAdvance;
            $discountForMaxPrice = $discountAmountWithMinAdvance;
        }

        // Texts
        $printMinPriceLong = number_format_i18n($minPrice, 2);
        $printMinPrice = number_format_i18n($minPrice, 0);
        $printDiscountForMinPrice = number_format_i18n(round($discountForMinPrice, 1), 1);

        // Only duration discount applied
        $printMaxPriceLong = number_format_i18n($maxPrice, 2);
        $printMaxPrice = number_format_i18n($maxPrice, 0);
        $printDiscountForMaxPrice = number_format_i18n(round($discountForMaxPrice, 1), 1);

        $printPriceDescription = '';
        $printPriceDescription .= ($this->priceCalculationType == 2 ? $this->lang->getText('LANG_PRICE_FOR_HOUR_FROM_TEXT') : $this->lang->getText('LANG_PRICE_FOR_DAY_FROM_TEXT')).' ';
        if($minPrice < $maxPrice)
        {
            $printPriceDescription .= $this->currencySymbol.' '.$printMinPriceLong.', ';
            $printPriceDescription .= $this->lang->getText('LANG_PRICE_WITH_APPLIED_TEXT').' ';
            $printPriceDescription .= $this->currencySymbol.' '.$printDiscountForMinPrice.' '.$this->lang->getText('LANG_WITH_APPLIED_DISCOUNT_TEXT');

            $printPriceDescription .= ' - '.$this->currencySymbol.' '.$printMaxPriceLong.', ';
            $printPriceDescription .= $this->lang->getText('LANG_PRICE_WITH_APPLIED_TEXT').' ';
            $printPriceDescription .= $this->currencySymbol.' '.$printDiscountForMaxPrice.' '.$this->lang->getText('LANG_WITH_APPLIED_DISCOUNT_TEXT');

            $printPrice = $printMinPrice.'-'.$printMaxPrice.' '.$this->currencyCode;
        } else
        {
            $printPriceDescription = $this->currencySymbol.' '.$printMinPriceLong.', ';
            $printPriceDescription .= $this->lang->getText('LANG_PRICE_WITH_APPLIED_TEXT').' '.$this->currencySymbol.' '.$printDiscountForMinPrice.' ';
            $printPriceDescription .= $this->lang->getText('LANG_WITH_APPLIED_DISCOUNT_TEXT');
            $printPrice = $printMinPrice.' '.$this->currencyCode;
        }

        if($this->debugMode)
        {
            echo "-&gt;  Local pickup/return timestamps range: {$localPickupTimestamp} - {$localReturnTimestamp} --&gt; ".($localPickupTimestamp + 86400 * 6)." - ".($localReturnTimestamp + 86400 * 6).",<br />";

            echo "Week&#39;s Cheapest Day Price (w/out VAT): {$priceWithoutTax} - {$discountAmountWithMaxAdvance} = ";
            echo ($priceWithoutTax - $discountAmountWithMaxAdvance).",<br />";

            echo "Week&#39;s Priciest Day Price (w/out VAT): {$priceWithoutTax} - {$discountAmountWithMinAdvance} =  ";
            echo ($priceWithoutTax - $discountAmountWithMinAdvance)."<br /><br />";
        }
        $priceDataInWeek['print_price_description'] = $printPriceDescription;
        $priceDataInWeek['print_price'] = $printPrice;

        return $priceDataInWeek;
    }


    /**
     * Most advanced item pricing function EVER. Final
     * @param int $paramPickupTimestamp
     * @param int $paramReturnTimestamp
     * @param int $paramMultiplier - how many times multiply price for ['multiplied'] stack
     * @param bool $paramMinimalPrice - should be use a minimal price instead of period
     * @return array
     */
	public function getPriceDetails($paramPickupTimestamp, $paramReturnTimestamp, $paramMultiplier = 1, $paramMinimalPrice = false)
	{
		// Single Extra ItemModel

		// 1: Single Extra Subtotal (W/out VAT & w/out Discount)			= I.e. 100 EUR (4x25 EUR Daily Price)      / getPriceByPeriod()
		// --> 1A: Single Extra Subtotal (With VAT)
		// --> 1B: Single Extra Subtotal (Dynamic - with or w/out VAT)
		// 2: Single Extra Discount Percentage      						= I.e. 10%                                 / getTotalDiscountPercentageByPeriod()
		// 3: Single Extra Discount Amount (W/out VAT)	 					= I.e. 10 EUR (100 EUR * 10%)
		// --> 3A: Single Extra Discount Amount (With VAT)
		// --> 3B: Single Extra Discount Amount (Dynamic - with or w/out VAT)
		// 4: Single Extra Discounted Total (W/out VAT)     				= I.e. 90 EUR (100 EUR - 10%)
		// --> 4A: Single Extra Discounted Total (With VAT)					= I.e. 108.90 EUR (90 EUR + 18.90 EUR)
		// --> 4B: Single Extra Discounted Total (Dynamic - with or w/out VAT)
        // 5: Single Extra Fixed Deposit Amount (No VAT Exist)				= I.e. 250 EUR 						       / getFixedDepositByPeriod()
		// 6: Single Extra VAT Percentage (Tax)            					= I.e. 21%                                 / VARIABLE: this->taxPercentage
		// 7: Single Extra Subtotal VAT Amount (Tax)          				= I.e. 21 EUR (100 EUR * TAX_PERCENTAGE)
		// 8: Single Extra Discounted VAT Amount (Tax) 						= I.e. 18.90 EUR (90 EUR * TAX_PERCENTAGE)

        if($this->debugMode == 2)
        {
            echo "<br />---------------------------------------------------------------------------------------------------------";
        }


        // Part 1: Extra Id
		$validExtraId 						= StaticValidator::getValidPositiveInteger($this->extraId);
		$validPickupTimestamp				= StaticValidator::getValidPositiveInteger($paramPickupTimestamp, 0);
		$validReturnTimestamp				= StaticValidator::getValidPositiveInteger($paramReturnTimestamp, 0);
        $validPeriod                        = StaticValidator::getPeriod($validPickupTimestamp, $validReturnTimestamp, false);
		$validMultiplier					= StaticValidator::getValidPositiveInteger($paramMultiplier, 1);


		// Part 3: Extra Prices
		// 1, 1A, 1B - Single Extra Subtotal (W/out VAT), (With VAT),(Dynamic - with or w/out VAT)
        // Note: extras are attached to discounts directly by the id, so we don't need here to have a call by price_plan_id, as extras don't use them
		$unitSubtotalPriceWithoutTax  		= $this->getUnitPriceByInterval($validPickupTimestamp, $validReturnTimestamp);
		$unitSubtotalPriceWithTax 	 		= $unitSubtotalPriceWithoutTax + $unitSubtotalPriceWithoutTax * ($this->taxPercentage / 100);
		$unitSubtotalPriceDynamic 	 		= $this->showPriceWithTaxes == 1 ? $unitSubtotalPriceWithTax : $unitSubtotalPriceWithoutTax;
		$validPeriodUntilPickup			    = StaticValidator::getPeriod(time(), $paramPickupTimestamp, false);

		// 2 - Single Extra Discount Percentage
		$objDiscountManager = new ExtraDiscountManager($this->conf, $this->lang, $this->settings, $validExtraId);

        if($paramMinimalPrice === true)
        {
            $unitDiscountPercentage     = $objDiscountManager->getMaxTotalPercentage();
        } else
        {
            $unitDiscountPercentage	    = $objDiscountManager->getTotalPercentageByPeriod($validPeriodUntilPickup, $validPeriod);
        }
        // 3 - Single Extra Discount Amount (W/out VAT)
        // Note: We must round discount amount here, to avoid issues with multiplied print
        $unitDiscountAmountWithoutTax	= round($unitSubtotalPriceWithoutTax * ($unitDiscountPercentage / 100), 2);

		// 3A, 3B - Single Extra Discount Amount (With VAT), (Dynamic - with or w/out VAT)
		$unitDiscountAmountWithTax			= $unitDiscountAmountWithoutTax + $unitDiscountAmountWithoutTax * ($this->taxPercentage / 100);
		$unitDiscountAmountDynamic			= $this->showPriceWithTaxes == 1 ? $unitDiscountAmountWithTax : $unitDiscountAmountWithoutTax;

		// 4, 4A, 4B - Single Extra Discounted Total (W/out VAT), (With VAT), (Dynamic - with or w/out VAT)
		$unitDiscountedTotalWithoutTax 		=  $unitSubtotalPriceWithoutTax - $unitDiscountAmountWithoutTax;
		$unitDiscountedTotalWithTax    		=  $unitSubtotalPriceWithTax - $unitDiscountAmountWithTax;
		$unitDiscountedTotalDynamic			=  $unitSubtotalPriceDynamic - $unitDiscountAmountDynamic;

        // 5 - Single Extra Fixed Deposit Amount (VAT for deposit do not exist)
        $objDepositManager                  = new ExtraDepositManager($this->conf, $this->lang, $this->settings, $validExtraId);
        $unitFixedDeposit 		    = $objDepositManager->getAmount();

		// 6 - Single Extra Subtotal VAT Amount (Tax)
		$unitSubtotalTaxAmount 	  			= $unitSubtotalPriceWithTax - $unitSubtotalPriceWithoutTax;

		// 7 - Single Extra Discounted VAT Amount (Tax)
		$unitDiscountedTaxAmount 			= $unitDiscountedTotalWithTax - $unitDiscountedTotalWithoutTax;

        // BASIC PRICE DETAILS
        $arrPriceDetails = array();
        $arrPriceDetails['element_id'] = $validExtraId;
        $arrPriceDetails['element_type'] = "extra";
        // We must round price and all discount amounts with 2 chars after comma, to avoid issues with multiplied amounts print
        $arrPriceDetails['unit'] = array(
            "subtotal_price" 			=> round($unitSubtotalPriceWithoutTax, 2),
            "subtotal_price_with_tax" 	=> round($unitSubtotalPriceWithTax, 2),
            "subtotal_price_dynamic" 	=> round($unitSubtotalPriceDynamic, 2),
            "discount_amount"			=> round($unitDiscountAmountWithoutTax, 2),
            "discount_amount_with_tax"	=> round($unitDiscountAmountWithTax, 2),
            "discount_amount_dynamic"	=> round($unitDiscountAmountDynamic, 2),
            "discounted_total"			=> round($unitDiscountedTotalWithoutTax, 2),
            "discounted_total_with_tax" => round($unitDiscountedTotalWithTax, 2),
            "discounted_total_dynamic"	=> round($unitDiscountedTotalDynamic, 2),
            "subtotal_tax_amount" 		=> round($unitSubtotalTaxAmount, 2),
            "discounted_tax_amount" 	=> round($unitDiscountedTaxAmount, 2),
            "fixed_deposit"             => round($unitFixedDeposit, 2),
        );
        $arrPriceDetails['unit_per_period'] = StaticFormatter::getPerPeriodPricesArray($arrPriceDetails['unit'], $validPeriod, $this->priceCalculationType);
        $arrPriceDetails['tax_percentage'] = $this->taxPercentage;

        // Multiplied prices
        $arrPriceDetails['multiplier'] = $validMultiplier;
        $arrPriceDetails['multiplied'] = StaticFormatter::getMultipliedNumberArray($arrPriceDetails['unit'], $validMultiplier);
        $arrPriceDetails['multiplied_per_period'] = StaticFormatter::getMultipliedNumberArray($arrPriceDetails['unit_per_period'], $validMultiplier);

        if($this->debugMode == 3)
        {
            // We want to do a debug here to avoid mess with printed data
            $pickupTMZStamp = $validPickupTimestamp + get_option( 'gmt_offset' ) * 3600;
            $returnTMZStamp = $validReturnTimestamp + get_option( 'gmt_offset' ) * 3600;
            $printPickupDate = date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), $pickupTMZStamp, true);
            $printReturnDate = date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), $returnTMZStamp, true);
            // Deep debug
            echo "<br /><strong>Returned price details for Extra Id={$validExtraId}:</strong>";
            echo "<br />Minimal price only:".var_export($paramMinimalPrice, true);
            echo "<br />Dates: {$printPickupDate} - {$printReturnDate}";
            echo "<br />UNIX, GMT period: {$validPickupTimestamp} - {$validReturnTimestamp}";
            echo "<br />Unit discount amount (without tax): {$unitDiscountAmountWithoutTax}";
            echo "<br />Unit discount amount (with tax): {$unitDiscountAmountWithTax}";
            echo "<br />Unit fixed deposit amount: {$unitFixedDeposit}";
            echo '<div style="font-size:12px;line-height:18px;color:navy;">'.nl2br(print_r($arrPriceDetails, true)).'</div>';
            echo "---------------------------------------------------------------------------------------------------------";
            echo "<br /><br />";
        }

        // Unit prints
		$arrPriceDetails['unit_tiny_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit'], "tiny");
		$arrPriceDetails['unit_tiny_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit'], "tiny_without_fraction");
		$arrPriceDetails['unit_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit'], "regular");
		$arrPriceDetails['unit_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit'], "regular_without_fraction");
		$arrPriceDetails['unit_long_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit'], "long");
		$arrPriceDetails['unit_long_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit'], "long_without_fraction");

        // Unit per period prints
        $arrPriceDetails['unit_per_period_tiny_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit_per_period'], "tiny");
        $arrPriceDetails['unit_per_period_tiny_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit_per_period'], "tiny_without_fraction");
        $arrPriceDetails['unit_per_period_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit_per_period'], "regular");
        $arrPriceDetails['unit_per_period_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit_per_period'], "regular_without_fraction");
        $arrPriceDetails['unit_per_period_long_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit_per_period'], "long");
        $arrPriceDetails['unit_per_period_long_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['unit_per_period'], "long_without_fraction");

		// Multiplied prints
		$arrPriceDetails['multiplied_tiny_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied'], "tiny");
		$arrPriceDetails['multiplied_tiny_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied'], "tiny_without_fraction");
		$arrPriceDetails['multiplied_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied'], "regular");
		$arrPriceDetails['multiplied_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied'], "regular_without_fraction");
		$arrPriceDetails['multiplied_long_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied'], "long");
		$arrPriceDetails['multiplied_long_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied'], "long_without_fraction");

        // Multiplied per period prints
        $arrPriceDetails['multiplied_per_period_tiny_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied_per_period'], "tiny");
        $arrPriceDetails['multiplied_per_period_tiny_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied_per_period'], "tiny_without_fraction");
        $arrPriceDetails['multiplied_per_period_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied_per_period'], "regular");
        $arrPriceDetails['multiplied_per_period_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied_per_period'], "regular_without_fraction");
        $arrPriceDetails['multiplied_per_period_long_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied_per_period'], "long");
        $arrPriceDetails['multiplied_per_period_long_without_fraction_print'] = $this->getFormattedPriceArray($arrPriceDetails['multiplied_per_period'], "long_without_fraction");

		// Word prints
		$arrPriceDetails['time_ext_print'] = $this->getPricePeriodText("SHORT");
		$arrPriceDetails['time_ext_long_print'] = $this->getPricePeriodText("LONG");

		// Percentage (not-escaped)
		$arrPriceDetails['formatted_tax_percentage'] = StaticFormatter::getFormattedPercentage($arrPriceDetails['tax_percentage'], "regular");

		return $arrPriceDetails;
	}

	/**
	 * @param string $paramType - SHORT OR LONG
	 * @return string
	 */
	public function getPricePeriodText($paramType = "LONG")
	{
		$text = "";

		$validExtraId = StaticValidator::getValidPositiveInteger($this->extraId, 0);
		$extraData = $this->conf->getInternalWPDB()->get_row("
			SELECT price_type
			FROM {$this->conf->getPrefix()}extras
			WHERE extra_id='{$validExtraId}'
		", ARRAY_A);
		if(!is_null($extraData))
		{
			if($extraData['price_type'] == 0)
			{
				$text = $paramType == "LONG" ? $this->lang->getText('LANG_PRICING_PER_ORDER_TEXT') : $this->lang->getText('LANG_PRICING_PER_ORDER_SHORT_TEXT');
			} else
			{
				if($this->priceCalculationType == 1)
				{
					$text = $paramType == "LONG" ? $this->lang->getText('LANG_PRICING_PER_DAY_TEXT') : $this->lang->getText('LANG_PRICING_PER_DAY_SHORT_TEXT');
				} else if($this->priceCalculationType == 2)
				{
					$text = $paramType == "LONG" ? $this->lang->getText('LANG_PRICING_PER_HOUR_TEXT') : $this->lang->getText('LANG_PRICING_PER_HOUR_SHORT_TEXT');
				} else if($this->priceCalculationType == 3)
				{
					$text = $paramType == "LONG" ? $this->lang->getText('LANG_PRICING_PER_DAY_TEXT') : $this->lang->getText('LANG_PRICING_PER_DAY_SHORT_TEXT');
				}
			}
		}

		return $text;
	}

    private function getFormattedPriceArray($paramArray, $paramFormatType)
    {
        $retArray = array();
        foreach($paramArray AS $key => $price)
        {
            if($key == "fixed_deposit" && $price == 0.00)
            {
                $showLongText = in_array($paramFormatType, array('long', 'long_without_fraction')) ? true : false;
                $formattedPrice = $this->lang->getText($showLongText ? 'LANG_NOT_REQUIRED_TEXT' : 'LANG_NOT_REQ_TEXT');
            } else
            {
                $formattedPrice = StaticFormatter::getFormattedPrice($price, $paramFormatType, $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
            }
            $retArray[$key] = $formattedPrice;
        }

        return $retArray;
    }
}
