<?php
/**
 * Items Observer (array)

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Search;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Feature\FeaturesObserver;
use FleetManagement\Models\ItemModel\ItemModelOption;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelOptionManager;
use FleetManagement\Models\ItemModel\ItemModelPriceManager;
use FleetManagement\Models\Unit\ItemModelUnitManager;

final class SearchItemModelsManager
{
    private $conf 	            = null;
    private $lang 		        = null;
    private $debugMode 	        = 0;
    private $settings             = array();

    private $orderId			    = 0;
    private $couponCode			= "";
    private $multimode            = false;
    // Additional
    private $taxPercentage          = 0.00;
    private $locationUniqueIdentifier = "";

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param $paramTaxPercentage
     * @param $paramLocationUniqueIdentifier
     * @param int $paramOrderId - used because we need to support booking edits
     * @param string $paramCouponCode
     */
    public function __construct(
        ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramTaxPercentage, $paramLocationUniqueIdentifier,
        $paramOrderId, $paramCouponCode
    ) {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        // Dynamic tax percentage
        $this->taxPercentage = floatval($paramTaxPercentage);
        // Location code
        $this->locationUniqueIdentifier = sanitize_text_field($paramLocationUniqueIdentifier);

        $this->multimode = isset($paramSettings['conf_booking_model']) && $paramSettings['conf_booking_model'] == 2 ? true : false;
        $this->orderId = StaticValidator::getValidPositiveInteger($paramOrderId, 0);
        $this->couponCode = sanitize_text_field($paramCouponCode);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Verify if those selected item ids really exists
     * @param array $paramSelectedItemModelIds
     * @param array $paramAvailableItemModelIds
     * @return array
     */
    public function getExistingSelectedItemModelIds(array $paramSelectedItemModelIds, array $paramAvailableItemModelIds)
    {
        $retItemModelIds = array();
        $counter = 0;
        foreach($paramSelectedItemModelIds AS $itemModelId)
        {
            if(in_array($itemModelId, $paramAvailableItemModelIds))
            {
                $counter++;
                if($counter > 1 && $this->multimode == false)
                {
                    // Do not allow to have more than 1 different SELECTED item if it is not multi-mode
                    break;
                }
                $retItemModelIds[] = $itemModelId;
            }
        }

        if($this->debugMode >= 1)
        {
            echo "<br />SELECTED ITEM IDs BEFORE: ".print_r($paramSelectedItemModelIds, true);
            echo "<br />SELECTED ITEM IDs AFTER: ".print_r($retItemModelIds, true);
        }

        return $retItemModelIds;
    }

    /**
     * @param $paramPickupLocationId
     * @param $paramReturnLocationId
     * @param $paramPartnerId
     * @param $paramManufacturerId
     * @param $paramClassId
     * @param $paramAttributeId2
     * @param $paramAttributeId1
     * @return array
     */
    public function getAvailableItemModelIds(
        $paramPickupLocationId, $paramReturnLocationId, $paramPartnerId, $paramManufacturerId,
        $paramClassId, $paramAttributeId1, $paramAttributeId2
    ) {
        $addQuery = '';

        $validPickupLocationId = StaticValidator::getValidPositiveInteger($paramPickupLocationId, 0);
        $validReturnLocationId = StaticValidator::getValidPositiveInteger($paramReturnLocationId, 0);
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1);
        $validManufacturerId = StaticValidator::getValidInteger($paramManufacturerId, -1);
        $validClassId = StaticValidator::getValidInteger($paramClassId, -1);
        $validAttributeId1 = StaticValidator::getValidInteger($paramAttributeId1, -1);
        $validAttributeId2 = StaticValidator::getValidInteger($paramAttributeId2, -1);

        if($validPartnerId >= 0)
        {
            $addQuery .= " AND it.partner_id='{$validPartnerId}' ";
        }

        if($validManufacturerId >= 0)
        {
            $addQuery .= " AND it.manufacturer_id='{$validManufacturerId}' ";
        }

        if($validClassId >= 0)
        {
            $addQuery .= " AND it.body_type_id='{$validClassId}' ";
        }

        if($validAttributeId1 >= 0)
        {
            $addQuery .= " AND it.fuel_type_id='{$validAttributeId1}' ";
        }

        if($validAttributeId2 >= 0)
        {
            $addQuery .= " AND it.transmission_type_id='{$validAttributeId2}' ";
        }

        if($validPickupLocationId > 0)
        {
            $addQuery .= "
				AND it.item_id IN
				(
					SELECT item_id
					FROM {$this->conf->getPrefix()}item_locations
					WHERE location_id='{$validPickupLocationId}' AND location_type='1'
				)";
        }

        if($validReturnLocationId > 0)
        {
            $addQuery .= "AND it.item_id IN
			(
				SELECT item_id
				FROM {$this->conf->getPrefix()}item_locations
				WHERE location_id='{$validReturnLocationId}' AND location_type='2'
			)";
        }

        $searchSQL = "
			SELECT it.item_id
			FROM {$this->conf->getPrefix()}items it
			LEFT JOIN {$this->conf->getPrefix()}manufacturers mf ON it.manufacturer_id=mf.manufacturer_id
			WHERE it.units_in_stock > 0 AND it.enabled = '1'
			{$addQuery} AND it.blog_id='{$this->conf->getBlogId()}'
			ORDER BY manufacturer_title ASC, model_name ASC
			";

        //echo "<br />".nl2br($searchSQL)."<br />"; //die;

        $sqlRows = $this->conf->getInternalWPDB()->get_col($searchSQL);

        if($this->debugMode >= 1)
        {
            echo "<br /><br />TOTAL AVAILABLE ITEMS FOUND: " . sizeof($sqlRows).", ";
            echo "BODY TYPE ID: ".($validClassId >= 0 ? $validClassId : "ANY");
            echo "<br />AVAILABLE ITEM IDs: ".print_r($sqlRows, true);
            echo "<br /><em>(Note: the candidate number is not final, it does not check for booked or blocked items)</em>";
        }

        return $sqlRows;
    }

    /**
     * @param array $paramItemModelIds
     * @param array $paramItemModelUnits
     * @param array $paramItemModelOptions
     * @param int $paramPickupTimestamp
     * @param int $paramReturnTimestamp
     * @param bool $paramValidateQuantity
     * @return array
     */
	public function getItemModelsWithPricesAndOptions(
        array $paramItemModelIds, array $paramItemModelUnits, array $paramItemModelOptions, $paramPickupTimestamp, $paramReturnTimestamp, $paramValidateQuantity = false
    ) {
        $validItemModelIds = StaticValidator::getValidArray($paramItemModelIds, 'positive_integer', 0);
        $validItemModelUnits = StaticValidator::getValidArray($paramItemModelUnits, 'positive_integer', 0);
        $validItemModelOptions = StaticValidator::getValidArray($paramItemModelOptions, 'positive_integer', 0);
		$retItems = array();


        foreach($validItemModelIds AS $itemModelId)
        {
            // 1 - Process full item details
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
            $itemModelDetails = $objItemModel->getExtendedDetails();
			$objUnitsManager = new ItemModelUnitManager(
                $this->conf, $this->lang, $this->settings, $itemModelDetails['item_model_sku'], $paramPickupTimestamp, $paramReturnTimestamp
			);

			$availableUnits = $objUnitsManager->getTotalUnitsAvailable($this->locationUniqueIdentifier, $this->orderId);

			// If there is more items in stock than booked, and more items in stock than min quantity for booking
			if($availableUnits > 0)
			{
				$objOptionsManager = new ItemModelOptionManager($this->conf, $this->lang, $this->settings, $itemModelId);
				$objPriceManager = new ItemModelPriceManager(
                    $this->conf, $this->lang, $this->settings, $itemModelId, $itemModelDetails['price_group_id'], $this->couponCode, $this->taxPercentage
				);

				// 2 - Process item prices
				$maxAllowedUnits = $objUnitsManager->getMaxAllowedUnitsForOrder($this->locationUniqueIdentifier, $this->orderId);
				$unitsSelected = 0;
                if(isset($validItemModelUnits[$itemModelId]) && $validItemModelUnits[$itemModelId] > 0)
                {
                    $unitsSelected = ($maxAllowedUnits > $validItemModelUnits[$itemModelId]) ? $validItemModelUnits[$itemModelId] : $maxAllowedUnits;
                }

			    $itemPriceDetails = $objPriceManager->getMultipliedPriceDetailsByInterval($paramPickupTimestamp, $paramReturnTimestamp, $unitsSelected);

				// 3 - Process item options
				$totalOptions = $objOptionsManager->getTotalOptions();
				if($totalOptions == 1)
				{
					// Auto-select first option
                    $selectedOptionId = $objOptionsManager->getFirstIds();
				} else
				{
					$selectedOptionId = 0;
                    if(isset($validItemModelOptions[$itemModelId]) && $validItemModelOptions[$itemModelId] > 0)
                    {
                        $selectedOptionId = $validItemModelOptions[$itemModelId];
                    }
				}
				$objSelectedOption = new ItemModelOption($this->conf, $this->lang, $this->settings, $selectedOptionId);
				$selectedOptionDetails = $objSelectedOption->getDetails();
				$selectedOptionName = "";
				$translatedSelectedOptionName = "";
				$optionsHTML = "";
				if($totalOptions > 1)
				{
					$selectedOptionName = $selectedOptionDetails['option_name'];
					$translatedSelectedOptionName = $selectedOptionDetails['translated_option_name'];
					$optionsHTML = $objOptionsManager->getTranslatedDropdown($selectedOptionId);
				} else if($totalOptions == 1)
				{
					$selectedOptionName = $selectedOptionDetails['option_name'];
					$translatedSelectedOptionName = $selectedOptionDetails['translated_option_name'];
					$optionsHTML = $selectedOptionDetails['option_name'];
				}

				$itemModelWithOption = $itemModelDetails['manufacturer_name'].' '.$itemModelDetails['item_model_name'];
				$itemModelWithOption .= $itemModelDetails['class_name'] ? ', '.$itemModelDetails['class_name'] : '';
				$itemModelWithOption .= $selectedOptionName ? ', '.$selectedOptionName : '';
                $itemModelWithOption .= ' '.$itemModelDetails['via_partner'];

                $translatedItemModelWithOption = $itemModelDetails['translated_manufacturer_name'].' '.$itemModelDetails['translated_item_model_name'];
                $translatedItemModelWithOption .= $itemModelDetails['translated_class_name'] ? ', '.$itemModelDetails['translated_class_name'] : '';
                $translatedItemModelWithOption .= $translatedSelectedOptionName ? ', '.$translatedSelectedOptionName : '';
                $translatedItemModelWithOption .= ' '.$itemModelDetails['via_partner'];
                ///////////////////////////////////////////////////////////////////////////////
                // FEATURES: START
                $objFeaturesObserver = new FeaturesObserver($this->conf, $this->lang, $this->settings);
                $features = $objFeaturesObserver->getTranslatedSelectedFeaturesByItemModelId($itemModelId, false);
                $itemModelDetails['show_features'] = sizeof($features) > 0 ? true : false;
                $itemModelDetails['features'] = $features;
                // FEATURES: END
                ///////////////////////////////////////////////////////////////////////////////

				// 4 - Extend the $item output with new details
				$itemModelDetails['selected'] = $unitsSelected > 0 ? true : false;
				$itemModelDetails['selected_quantity'] = $unitsSelected;
                $itemModelDetails['quantity_dropdown_options'] = StaticFormatter::generateDropdownOptions(1, $maxAllowedUnits, $unitsSelected, "", "", false, "");
                $itemModelDetails['max_allowed_units'] = $maxAllowedUnits;
				$itemModelDetails['selected_option_id'] = $selectedOptionId;
				$itemModelDetails['selected_option_name'] = $selectedOptionName;
				$itemModelDetails['translated_selected_option_name'] = $translatedSelectedOptionName;
				$itemModelDetails['options_html'] = $optionsHTML;
				$itemModelDetails['total_options'] = $totalOptions;
                $itemModelDetails['item_model_with_option'] = $itemModelWithOption;
                $itemModelDetails['translated_item_model_with_option'] = $translatedItemModelWithOption;
				$itemModelDetails['print_checked'] = $unitsSelected > 0 ? ' checked="checked"' : '';
				$itemModelDetails['print_selected'] = $unitsSelected > 0 ? 'selected="selected"' : '';


                if(($paramValidateQuantity && $unitsSelected > 0) || $paramValidateQuantity === false)
                {
                    // Add to stack only if item is selected or if we return all items
                    $retItems[] = array_merge($itemModelDetails, $itemPriceDetails);
                }

				if($this->debugMode == 1)
				{
					echo "<br /><br />ItemModel with ID={$itemModelId} is <span style='color:green;font-weight:bold;'>AVAILABLE</span> for booking ";
					echo "has {$unitsSelected} units selected of {$availableUnits} units available, with total {$itemModelDetails['units_in_stock']} units in stock, ";
					echo "<br />with maximum {$maxAllowedUnits} units allowed per booking, ";
					echo "and booking min/max unit limits set to: 0/{$itemModelDetails['max_units_per_booking']}, ";
					echo "including current booking #{$this->orderId}";
				}
			} else
			{
				if($this->debugMode == 1)
				{
					echo "<br /><br />ItemModel with ID={$itemModelId} is <span style='color:red;font-weight:bold;'>NOT AVAILABLE</span> for booking ";
					echo "and currently has {$availableUnits} units available, ";
					echo "including current booking #{$this->orderId}";
				}
			}
		}

		return $retItems;
	}
}
