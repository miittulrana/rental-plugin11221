<?php
/**
 * Item Model Price Table
 *
 * NOTE: It should not process coupons, as they are per order, not per extra or item model
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\ItemModel;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\PriceGroup\PricePlanDiscount;
use FleetManagement\Models\Class_\Class_;
use FleetManagement\Models\Class_\ClassesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModelDepositManager;
use FleetManagement\Models\PriceGroup\PricePlanDiscountsObserver;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\ItemModel\ItemModelPriceManager;
use FleetManagement\Models\Validation\StaticValidator;

final class ItemModelsPriceTable
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
     * @param int $paramPickupLocationId
     * @param int $paramReturnLocationId
     * @param int $paramPartnerId
     * @param int $paramManufacturerId - manufacturer id
     * @param int $paramClassId - class id
     * @param int $paramAttributeId1 - attribute id
     * @param int $paramAttributeId2 - attribute id
     * Return example: priceTable = array("got_search_result" => true, "classes" => array());
     * Return example: priceTable['classes'][0] = array("item_models" => array());
     * Return example: priceTable['classes'][0]['item_models'][0]['attribute1_title'] = "Manual";
     * Return example: priceTable['classes'][0]['item_models'][0]['period_list'][0]['period_from'] = "1234567890";
     * Return example: priceTable['classes'][0]['item_models'][0]['period_list'][0]['print_dynamic_period_label'] = "10-20 Hours";
     * @return array
     */
	public function getPriceTable(
        $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1, $paramPartnerId = -1, $paramManufacturerId = -1,
        $paramClassId = -1, $paramAttributeId1 = -1, $paramAttributeId2 = -1
    ) {
		$objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->settings);
		$objDiscountsObserver = new PricePlanDiscountsObserver($this->conf, $this->lang, $this->settings);
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->settings);
        $taxPercentage = $objTaxManager->getTaxPercentage($paramPickupLocationId, $paramReturnLocationId);

        // Get all discount periods: START
        // @note - In case if discounts are disabled, for price table and maybe somewhere else, we will need a default discount
        $discountIds = $objDiscountsObserver->getGroupedIds("DURATION", true, -1, 0); // We return only admin's discount periods
        $allDiscountPeriods = array();
        foreach ($discountIds AS $discountId)
        {
            $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->settings, $discountId);
            $allDiscountPeriods[] = $objDiscount->getDetails(false);
        }
        if(sizeof($allDiscountPeriods) == 0)
        {
            $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->settings, 0);
            // Include default discount
            $allDiscountPeriods[] = $objDiscount->getDetails(true);
        }
        // Get all discount periods: END

		$gotSearchResult = false;
		$classesWithItemModels = array();
		// Includes items with no type
		if($objItemModelsObserver->areItemModelsClassified())
		{
            $objClasses = new ClassesObserver($this->conf, $this->lang, $this->settings);
            $classIds = $objClasses->getAllIds(true);
			foreach($classIds AS $classId)
			{
                if($paramClassId == -1 || ($paramClassId >= 0 && $paramClassId == $classId))
                {
                    $objClass = new Class_($this->conf, $this->lang, $this->settings, $classId);
                    $type = $objClass->getDetails(true);
                    $itemModelIds = $objItemModelsObserver->getAvailableIdsForPriceTable(
                        $paramPartnerId, $paramManufacturerId, $classId, $paramAttributeId1,
                        $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
                    );

                    $type['item_models'] = array();
                    $type['got_search_result'] = false;
                    foreach($itemModelIds AS $itemModelId)
                    {
                        $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
                        $objDepositManager = new ItemModelDepositManager($this->conf, $this->lang, $this->settings, $itemModelId);
                        $itemModel = array_merge($objItemModel->getExtendedDetails(), $objDepositManager->getDetails());

                        // Add periods data to item row
                        $itemModel['period_list'] = array();
                        foreach($allDiscountPeriods as $discountPeriod)
                        {
                            $objPriceManager = new ItemModelPriceManager(
                                $this->conf, $this->lang, $this->settings, $paramItemModelId, $itemModel['price_group_id'], '', $taxPercentage
                            );
                            $itemModel['period_list'][] = $objPriceManager->getPriceWithoutCouponDataInWeek($discountPeriod['period_from'], $discountPeriod['period_till']);
                        }

                        $type['item_models'][] = $itemModel;
                        $type['got_search_result'] = true;
                        $gotSearchResult = true;
                    }
                    // Add to stack
                    $classesWithItemModels[] = $type;
                }
			}
		} else
		{
			// Same, just everything added to param zero
			$itemModelIds = $objItemModelsObserver->getAvailableIdsForPriceTable(
			    $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
                $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
            );
			$type['item_models'] = array();
			$type['got_search_result'] = false;
			foreach($itemModelIds AS $itemModelId)
			{
				$objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
                $objDepositManager = new ItemModelDepositManager($this->conf, $this->lang, $this->settings, $itemModelId);
                $itemModel = array_merge($objItemModel->getExtendedDetails(), $objDepositManager->getDetails());

                // Add periods data to item row
                $itemModel['period_list'] = array();
                foreach($allDiscountPeriods as $discountPeriod)
                {
                    $objPriceManager = new ItemModelPriceManager(
                        $this->conf, $this->lang, $this->settings, $paramItemModelId, $itemModel['price_group_id'], "", $taxPercentage
                    );
                    $itemModel['period_list'][] = $objPriceManager->getPriceWithoutCouponDataInWeek($discountPeriod['period_from'], $discountPeriod['period_till']);
                }

				$type['item_models'][] = $itemModel;
				$type['got_search_result'] = true;
				$gotSearchResult = true;
			}
			// Add to stack
			$classesWithItemModels[0] = $type;
		}

        $printDynamicPeriodLabel = $this->lang->getText($this->priceCalculationType == 2 ? 'LANG_HOUR_PRICE_TEXT' : 'LANG_DAY_PRICE_TEXT');

		$priceTable = array(
			"print_periods" => $allDiscountPeriods,
			"total_periods" => sizeof($allDiscountPeriods),
            "print_dynamic_period_label" => $printDynamicPeriodLabel,
			"classes" => $classesWithItemModels,
			"got_search_result" => $gotSearchResult,
		);

		if($this->debugMode)
        {
            echo "<br />[Items Price Table] Discount periods: ".nl2br(print_r($allDiscountPeriods, true));
            echo "<br />[Items Price Table] Total periods: ".sizeof($allDiscountPeriods);
            echo "<br />[Items Price Table] Got search results: ".($gotSearchResult ? "Yes" : "No");
        }

		return $priceTable;
	}
}