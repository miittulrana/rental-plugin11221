<?php
/**
 * Extra Price Table

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\ExtraDiscount;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtraDepositManager;
use FleetManagement\Models\Extra\ExtraDiscountsObserver;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Extra\ExtraPriceManager;
use FleetManagement\Models\Validation\StaticValidator;

final class ExtrasPriceTable
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings                 = array();
    // Price calculation type: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
    protected $priceCalculationType 	= 1;
    protected $currencySymbol		    = '$';
    protected $currencyCode			    = 'USD';

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        $this->priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1, array(1, 2, 3));
        $this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Get the price table
     * @param int $paramItemModelId
     * @param int $paramExtraId
     * @param int $paramPickupLocationId (used for tax percentage calculation)
     * @param int $paramReturnLocationId (used for tax percentage calculation)
     * @param int $paramPartnerId
     * @return array
     */
	public function getPriceTable(
        $paramItemModelId = -1, $paramExtraId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1, $paramPartnerId = -1
    ) {
        $objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, $this->settings);
		$objDiscountsObserver = new ExtraDiscountsObserver($this->conf, $this->lang, $this->settings);
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->settings);
        $taxPercentage = $objTaxManager->getTaxPercentage($paramPickupLocationId, $paramReturnLocationId);

		// Get all discount periods: START
        // @note - In case if discounts are disabled, for price table and maybe somewhere else, we will need a default discount
        $discountIds = $objDiscountsObserver->getAllIds("DURATION", -1); // We return only admin's discount periods
        $allDiscountPeriods = array();
        foreach ($discountIds AS $discountId)
        {
            $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->settings, $discountId);
            $allDiscountPeriods[] = $objDiscount->getDetails(true);
        }
        if(sizeof($allDiscountPeriods) == 0)
        {
            $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->settings, 0);
            // Include default discount
            $allDiscountPeriods[] = $objDiscount->getDetails(true);
        }
        // Get all discount periods: END

		$extraIds = $objExtrasObserver->getAvailableIds($paramPartnerId, $paramExtraId, $paramItemModelId);
		$gotSearchResult = false;
		$extras = array();
		foreach ($extraIds AS $extraId)
		{
			$objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
			$objDepositManager = new ExtraDepositManager($this->conf, $this->lang, $this->settings, $extraId);
			$extra = array_merge($objExtra->getDetailsWithItemAndPartner(), $objDepositManager->getDetails());

            // Add periods data to extra row
            $extra['period_list'] = array();
            foreach($allDiscountPeriods as $discountPeriod)
            {
                $objPriceManager = new ExtraPriceManager($this->conf, $this->lang, $this->settings, $extraId, $taxPercentage);
                $extra['period_list'][] = $objPriceManager->getPriceDataInWeek($discountPeriod['period_from'], $discountPeriod['period_till']);
            }

			$extras[] = $extra;
			$gotSearchResult = true;
		}

        $printDynamicPeriodLabel = $this->lang->getText($this->priceCalculationType == 2 ? 'LANG_HOUR_PRICE_TEXT' : 'LANG_DAY_PRICE_TEXT');

        $priceTable = array(
			"print_periods" => $allDiscountPeriods,
			"total_periods" => sizeof($allDiscountPeriods),
            "print_dynamic_period_label" => $printDynamicPeriodLabel,
			"extras" => $extras,
			"got_search_result" => $gotSearchResult,
		);

        if($this->debugMode)
        {
            echo "<br />[Extras Price Table] Discount periods: ".nl2br(print_r($allDiscountPeriods, true));
            echo "<br />[Extras Price Table] Total periods: ".sizeof($allDiscountPeriods);
            echo "<br />[Extras Price Table] Got search results: ".($gotSearchResult ? "Yes" : "No");
        }

		return $priceTable;
	}
}