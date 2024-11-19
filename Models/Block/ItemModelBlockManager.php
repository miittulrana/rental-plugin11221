<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
namespace FleetManagement\Models\Block;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class ItemModelBlockManager extends AbstractBlockManager implements ObserverInterface, BlockManagerInterface
{
    protected $locationId   	    = 0;
    protected $itemModelIds            	= array();
    protected $itemModelUnits        	= array();

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
		parent::__construct($paramConf, $paramLang, $paramSettings);
	}

	/**
	 * Step no. 1 - Show reservation box. (optional) + show car
	 * Step no. 2 - (optional) Select car, if no car provided
	 * Step no. 3 - Select car extras
	 * Step no. 4 - Show booking details
	 * Step no. 5 - Process booking
	 * Step no. 6 - PayPal payment
	 */
	public function setVariables()
	{
		// came from step1
		$this->locationId = $this->getValidValueInput(array('POST', 'SESSION'), 'location_id', 0, false, 'positive_integer');
		$inputStartDate = $this->getValidValueInput(array('POST', 'SESSION'), 'start_date', date($this->shortDateFormat), true, $this->shortDateFormat);
		$inputStartTime = $this->getValidValueInput(array('POST', 'SESSION'), 'start_time', date("H:i:s"), true, 'time_validation');
		// For blocking we allow only to select only from date and time options
		$inputEndDate = $this->getValidValueInput(array('POST', 'SESSION'), 'end_date', date($this->shortDateFormat), true, $this->shortDateFormat);
		$inputEndTime = $this->getValidValueInput(array('POST', 'SESSION'), 'end_time', date("H:i:s"), true, 'time_validation');
		$this->startTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($inputStartDate, $inputStartTime);
		$this->endTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($inputEndDate, $inputEndTime);

		if(isset($_POST['block']) || isset($_POST['item_model_ids']))
		{
			// Came from step 2
			$this->itemModelIds = $this->getValidArrayInput(array('POST'), 'item_model_ids', array(), true, 'positive_integer');
		}
        if(isset($_POST['block']) || isset($_POST['item_model_units']))
        {
            // came back to step2 from step3
            // intval allows us here for admins to block all (-1) item units from backend
            $this->itemModelUnits = $this->getValidArrayInput(array('POST'), 'item_model_units', array(), true, 'intval');
        }

		/**********************************************************************/
		if($this->debugMode)
		{
            echo "<br />";
			echo "\$this->locationId: $this->locationId<br />";
			echo "\$inputStartDate: $inputStartDate<br />";
			echo "\$inputStartTime: $inputStartTime<br />";
			echo "\$inputEndDate: $inputEndDate<br />";
			echo "\$inputEndTime: $inputEndTime<br />";
			echo "\$this->startTimestamp: $this->startTimestamp<br />";
			echo "\$this->endTimestamp: $this->endTimestamp<br />";
			echo "\$this->itemModelIds: "; print_r($this->itemModelIds); echo "<br />";
			echo "\$this->itemModelUnits: "; print_r($this->itemModelUnits); echo "<br />";
			echo "POST: ".nl2br(print_r($_POST, true));
		}
		/**********************************************************************/
	}

    public function cacheVariables()
    {
        // Note: we use full path here, because otherwise PhpStorm gives a warning for not used classes, and they can be wrongly deleted
        $class = "\\FleetManagement\\Models\\Cache\\".($this->useSessions ? 'StaticSession' : 'StaticCookie');
        if(method_exists($class, 'cacheValue') && method_exists($class, 'cacheArrayAsJSON'))
        {
            // filled data in step 1, pre-filled in step 2, 3
            $class::cacheValue('location_id', $this->locationId);
            $class::cacheValue('start_date', $this->getShortStartDate());
            $class::cacheValue('start_time', $this->getShortStartTime());
            $class::cacheValue('end_date', $this->getShortEndDate());
            $class::cacheValue('end_time', $this->getShortEndTime());

            // filled in step 2, pre-filled in step 3
            $class::cacheArrayAsJSON('item_model_ids', $this->itemModelIds);
            $class::cacheArrayAsJSON('item_model_units', $this->itemModelUnits);
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
            // filled data in step1
            $class::unsetKey('location_id');
            $class::unsetKey('start_date');
            $class::unsetKey('start_time');
            $class::unsetKey('end_date');
            $class::unsetKey('end_time');

            // filled in step 2
            $class::unsetKey('item_model_ids');
            $class::unsetKey('item_model_units');
        }

        // DEBUG
        if($this->debugMode >= 2)
        {
            echo "[UNSETTING] &#39;unsetKey&#39; METHOD EXISTS IN &#39;{$class}&#39;: ".var_export(method_exists($class, 'cacheValue'), true);
            echo "[UNSETTING] UPDATED {$this->storageType} VARS: ".nl2br(print_r($this->useSessions ? $_SESSION : $_COOKIE, true));
        }
    }

    public function getLocationId()
    {
        return $this->locationId;
    }

    public function getIds()
    {
        return $this->itemModelIds;
    }

    public function getUnits($paramItemModelId)
    {
        return isset($this->itemModelUnits[$paramItemModelId]) ? $this->itemModelUnits[$paramItemModelId] : 0;
    }

    public function getAvailable()
    {
        $addQuery = '';

        $validLocationId = StaticValidator::getValidPositiveInteger($this->locationId, 0);

        if($validLocationId > 0)
        {
            // For items in specific pickup location
            $addQuery .= "
				AND it.item_id IN
				(
					SELECT item_id
					FROM {$this->conf->getPrefix()}item_locations
					WHERE location_id='{$validLocationId}' AND location_type='1'
				)";
        }

        $blockSQL = "
			SELECT it.item_id
			FROM {$this->conf->getPrefix()}items it
			LEFT JOIN {$this->conf->getPrefix()}manufacturers mf ON it.manufacturer_id=mf.manufacturer_id
			WHERE it.units_in_stock > 0 AND it.enabled = '1'
			{$addQuery} AND it.blog_id='{$this->conf->getBlogId()}'
			ORDER BY manufacturer_title ASC, model_name ASC
			";

        //echo "<br />".$blockSQL."<br />"; //die;
        $sqlRows = $this->conf->getInternalWPDB()->get_col($blockSQL);

        if($this->debugMode == 1)
        {
            echo "<br />TOTAL CANDIDATE ITEMS FOUND: " . sizeof($sqlRows);
            echo "<br /><em>(Note: the candidate number is not final, it does not include blocked items ";
            echo "(for all or specific locations) or the situation when all item units are booked)</em>";
        }

        return $sqlRows;
    }

    public function getSelectedWithDetails($paramItemModelIds)
    {
        return $this->getWithDetails($paramItemModelIds, true);
    }

    public function getAvailableWithDetails($paramItemModelIds)
    {
        return $this->getWithDetails($paramItemModelIds, false);
    }

    public function getWithDetails($paramItemModelIds, $paramSelectedOnly = false)
    {
        $retItems = array();
        $itemModelIds = is_array($paramItemModelIds) ? $paramItemModelIds : array();

        // Multi-mode check is not applied for item blocks
        foreach($itemModelIds AS $itemModelId)
        {
            // 1 - Process full item details
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
            $itemModelDetails = $objItemModel->getExtendedDetails();

            // If there is more items in stock than booked, and more items in stock than min quantity for booking
            if($itemModelDetails['units_in_stock'] > 0)
            {
                $selectedQuantity = 0;
                if(isset($this->itemModelUnits[$itemModelId]) && $itemModelDetails['units_in_stock'] > 0)
                {
                    if($this->itemModelUnits[$itemModelId] == -1)
                    {
                        // All units selected
                        $selectedQuantity = -1;
                    } else if($itemModelDetails['units_in_stock'] > $this->itemModelUnits[$itemModelId])
                    {
                        $selectedQuantity = $this->itemModelUnits[$itemModelId];
                    } else
                    {
                        $selectedQuantity = $itemModelDetails['units_in_stock'];
                    }
                }

                // 4 - Extend the $item output with new details
                $itemModelDetails['selected'] = $selectedQuantity > 0 || $selectedQuantity == -1 ? true : false;
                $itemModelDetails['selected_quantity'] = $selectedQuantity;
                $itemModelDetails['quantity_dropdown_options'] = StaticFormatter::generateDropdownOptions(
                    0, $itemModelDetails['units_in_stock'], $selectedQuantity, -1, $this->lang->getText('LANG_ALL_TEXT'), false, ""
                );
                $itemModelDetails['print_checked'] = $selectedQuantity > 0 || $selectedQuantity == -1 ? ' checked="checked"' : '';
                $itemModelDetails['print_selected'] = $selectedQuantity > 0 || $selectedQuantity == -1 ? 'selected' : '';

                if($selectedQuantity > 0 || $selectedQuantity == -1 || $paramSelectedOnly === false)
                {
                    // Add to stack only if element is selected or if we return all elements
                    $retItems[] = $itemModelDetails;
                }

                if($this->debugMode == 1)
                {
                    echo "<br /><br />ItemModel with ID={$itemModelId} is <span style='color:green;font-weight:bold;'>AVAILABLE</span> for booking ";
                    echo "has {$selectedQuantity} units selected, with total {$itemModelDetails['units_in_stock']} units in stock";
                }
            } else
            {
                if($this->debugMode == 1)
                {
                    echo "<br /><br />ItemModel with ID={$itemModelId} is <span style='color:red;font-weight:bold;'>NOT AVAILABLE</span> for booking ";
                    echo "and has {$itemModelDetails['units_in_stock']} units in stock";
                }
            }
        }

        // DEBUG
        //echo "<br />ITEMS: ".nl2br(esc_textarea(print_r($retItems, true)));

        return $retItems;
    }


}