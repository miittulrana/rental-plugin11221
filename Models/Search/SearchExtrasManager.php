<?php
/**
 * Extras Observer (array)

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Search;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtraOption;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Extra\ExtraOptionManager;
use FleetManagement\Models\Extra\ExtraPriceManager;
use FleetManagement\Models\Unit\ExtraUnitManager;

final class SearchExtrasManager
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $settings               = array();

    private $orderId			    = 0;
    private $multimode              = false;
    // Extras may be dependant on item models
    private $itemModelIds           = false;
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
     * @param array $paramItemModelIds
     */
    public function __construct(
        ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramTaxPercentage, $paramLocationUniqueIdentifier,
        $paramOrderId, array $paramItemModelIds
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
        $this->itemModelIds = StaticValidator::getValidArray($paramItemModelIds, 'positive_integer', 0);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Verify if those selected extra ids really exists
     * @param array $paramSelectedExtraIds
     * @param array $paramAvailableExtraIds
     * @return array
     */
    public function getExistingSelectedExtraIds(array $paramSelectedExtraIds, array $paramAvailableExtraIds)
    {
        $retExtraIds = array();
        foreach($paramSelectedExtraIds AS $extraId)
        {
            if(in_array($extraId, $paramAvailableExtraIds))
            {
                $retExtraIds[] = $extraId;
            }
        }

        if($this->debugMode == 1)
        {
            echo "<br />SELECTED EXTRA IDs BEFORE: ".print_r($paramSelectedExtraIds, true);
            echo "<br />SELECTED EXTRA IDs AFTER: ".print_r($retExtraIds, true);
        }

        return $retExtraIds;
    }

    public function getAvailableExtraIds()
    {
        $addQuery = "";

        $searchSQL = "
            SELECT extra_id
            FROM {$this->conf->getPrefix()}extras
            WHERE units_in_stock > 0 AND max_units_per_booking > 0
            {$addQuery} AND blog_id='{$this->conf->getBlogId()}'
			ORDER BY extra_name ASC
		";
        //echo "<br />".$searchSQL."<br />"; //die;

        $sqlRows = $this->conf->getInternalWPDB()->get_col($searchSQL);

        if($this->debugMode == 2)
        {
            echo "<br />TOTAL AVAILABLE EXTRAS FOUND: " . sizeof($sqlRows);
            echo "<br />AVAILABLE EXTRA IDs: ".print_r($sqlRows, true);
            echo "<br /><em>(Note: the number is not final, it does not check for booked or blocked extras)</em>";
        }

        return $sqlRows;
    }

    /**
     * @param array $paramExtraIds
     * @param array $paramExtraUnits
     * @param array $paramExtraOptions
     * @param int $paramPickupTimestamp
     * @param int $paramReturnTimestamp
     * @param bool $paramValidateQuantity
     * @return array
     */
	public function getExtrasWithPricesAndOptions(
        array $paramExtraIds, array $paramExtraUnits, array $paramExtraOptions, $paramPickupTimestamp, $paramReturnTimestamp, $paramValidateQuantity = false
    ) {
        $validExtraIds = StaticValidator::getValidArray($paramExtraIds, 'positive_integer', 0);
        $validExtraUnits = StaticValidator::getValidArray($paramExtraUnits, 'positive_integer', 0);
        $validExtraOptions = StaticValidator::getValidArray($paramExtraOptions, 'positive_integer', 0);

		$retExtras = array();

        foreach($validExtraIds AS $extraId)
        {
            // 1 - Process extra details
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
            $extraDetails = $objExtra->getDetailsWithItemAndPartner();
			$objUnitsManager = new ExtraUnitManager(
                $this->conf, $this->lang, $this->settings, $extraDetails['extra_sku'], $paramPickupTimestamp, $paramReturnTimestamp
			);

			$availableUnits = $objUnitsManager->getTotalUnitsAvailable($this->locationUniqueIdentifier, $this->orderId);
			// If there is more items in stock than booked, and more items in stock than min quantity for booking
			if($availableUnits > 0)
			{
			    if($this->multimode)
                {
                    // Multi-mode (more than 1 different item model allowed to select)
                    $fitsToItemModel = sizeof($this->itemModelIds) > 0 && in_array($extraDetails['item_model_id'], $this->itemModelIds) ? true : false;
                } else
                {
                    // Single mode (only one different item model allowed to select)
                    $fitsToItemModel = isset($this->itemModelIds[0]) ? $extraDetails['item_model_id'] == $this->itemModelIds[0] : false;
                }

                if($extraDetails['item_model_id'] == 0 || $extraDetails['item_model_id'] > 0 && $fitsToItemModel)
                {
                    $objOptionsManager = new ExtraOptionManager($this->conf, $this->lang, $this->settings, $extraId);
                    $objPriceManager = new ExtraPriceManager($this->conf, $this->lang, $this->settings, $extraId, $this->taxPercentage);

                    // 1 - Get selected units
                    $maxAllowedUnits = $objUnitsManager->getMaxAllowedUnitsForOrder($this->locationUniqueIdentifier, $this->orderId);
                    $unitsSelected = 0;
                    if(isset($validExtraUnits[$extraId]) && $validExtraUnits[$extraId] > 0)
                    {
                        $unitsSelected = ($maxAllowedUnits > $validExtraUnits[$extraId]) ? $validExtraUnits[$extraId] : $maxAllowedUnits;
                    }

                    // 3 - Process extra prices
                    $extraPriceDetails = $objPriceManager->getMultipliedPriceDetailsByInterval($paramPickupTimestamp, $paramReturnTimestamp, $unitsSelected);

                    // 4 - Process extra options
                    $totalOptions = $objOptionsManager->getTotalOptions();
                    if($totalOptions == 1)
                    {
                        // Auto-select first option
                        $selectedOptionId = $objOptionsManager->getFirstIds();
                    } else
                    {
                        $selectedOptionId = 0;
                        if(isset($validExtraOptions[$extraId]) && $validExtraOptions[$extraId] > 0)
                        {
                            $selectedOptionId = $validExtraOptions[$extraId];
                        }
                    }
                    $objSelectedOption = new ExtraOption($this->conf, $this->lang, $this->settings, $selectedOptionId);
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

                    if($this->multimode)
                    {
                        // Only if items (!) are in multi mode, we need to display dependant item title and partner name (if exist)
                        $extraText = $extraDetails['translated_extra_name_with_dependant_item_model'].' '.$extraDetails['via_partner'];
                        $translatedExtra = $extraDetails['translated_extra_name_with_dependant_item_model'].' '.$extraDetails['via_partner'];

                        $extraWithOption  = $extraDetails['translated_extra_name_with_dependant_item_model'];
                        $extraWithOption .= $selectedOptionName ? ', '.$selectedOptionName : '';
                        $extraWithOption .= ' '.$extraDetails['via_partner'];

                        $translatedExtraWithOption  = $extraDetails['translated_extra_name_with_dependant_item_model'];
                        $translatedExtraWithOption .= $translatedSelectedOptionName ? ', '.$translatedSelectedOptionName : '';
                        $translatedExtraWithOption .= ' '.$extraDetails['via_partner'];
                    } else
                    {
                        $extraText  = $extraDetails['print_extra_name'];
                        $translatedExtra  = $extraDetails['print_translated_extra_name'];
                        $extraWithOption  = $extraDetails['print_extra_name'];
                        $extraWithOption .= $selectedOptionName ? ', '.$selectedOptionName : '';
                        $translatedExtraWithOption  = $extraDetails['print_extra_name'];
                        $translatedExtraWithOption .= $selectedOptionName ? ', '.$selectedOptionName : '';
                    }

                    // 5 - Extend the $extra output with new details
                    $extraDetails['selected'] = $unitsSelected > 0 ? true : false;
                    $extraDetails['selected_quantity'] = $unitsSelected;
                    $extraDetails['quantity_dropdown_options'] = StaticFormatter::generateDropdownOptions(0, $maxAllowedUnits, $unitsSelected, "", "", false, "");
                    $extraDetails['max_allowed_units'] = $maxAllowedUnits;
                    $extraDetails['selected_option_id'] = $selectedOptionId;
                    $extraDetails['selected_option_name'] = $selectedOptionName;
                    $extraDetails['translated_selected_option_name'] = $translatedSelectedOptionName;
                    $extraDetails['options_html'] = $optionsHTML;
                    $extraDetails['total_options'] = $totalOptions;
                    $extraDetails['print_checked'] = $unitsSelected > 0 ? ' checked="checked"' : '';
                    $extraDetails['print_selected'] = $unitsSelected > 0 ? 'selected' : '';
                    $extraDetails['extra'] = $extraText;
                    $extraDetails['translated_extra'] = $translatedExtra;
                    $extraDetails['extra_with_option'] = $extraWithOption;
                    $extraDetails['translated_extra_with_option'] = $translatedExtraWithOption;

                    if(($paramValidateQuantity && $unitsSelected > 0) || $paramValidateQuantity === false)
                    {
                        // 6 - Add to stack only if extra is selected or if we return all extras
                        $retExtras[] = array_merge($extraDetails, $extraPriceDetails);
                    }

                    if($this->debugMode == 1)
                    {
                        echo "<br /><br />Extra with ID={$extraId} is <span style='color:green;font-weight:bold;'>AVAILABLE</span> for ordering ";
                        if($extraDetails['item_model_id'] > 0)
                        {
                            echo "with dependency on item model ID={$extraDetails['item_model_id']}, which is in the list of selected item models: ";
                            print_r($this->itemModelIds);
                        } else
                        {
                            echo ", is not dependant on any item (Item Model ID=0) from the list of selected item models: ";
                            print_r($this->itemModelIds);
                        }
                        echo "<br />and has {$unitsSelected} units selected of {$availableUnits} units available, with total {$extraDetails['units_in_stock']} units in stock, ";
                        echo "<br />with maximum {$maxAllowedUnits} units allowed per booking, ";
                        echo "and booking max unit limits set to: {$extraDetails['max_units_per_booking']}, ";
                        echo "including current booking #{$this->orderId}";
                    }
                } else
                {
                    if($this->debugMode == 1)
                    {
                        echo "<br /><br />Extra with ID={$extraId} is <span style='color:blue;font-weight:bold;'>AVAILABLE</span> for booking, ";
                        echo "but is dependant on item model ID={$extraDetails['item_model_id']}, which is not in the list of selected items: ";
                        print_r($this->itemModelIds);
                        echo "<br / >and currently has {$availableUnits} units available, ";
                        echo "including current booking #{$this->orderId}";
                    }
                }
			} else
			{
				if($this->debugMode == 1)
				{
					echo "<br /><br />Extra with ID={$extraId} is <span style='color:red;font-weight:bold;'>NOT AVAILABLE</span> for booking ";
					echo "and currently has {$availableUnits} units available, ";
					echo "including current booking #{$this->orderId}";
				}
			}
		}

		return $retExtras;
	}
}
