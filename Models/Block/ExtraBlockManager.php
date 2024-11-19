<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
namespace FleetManagement\Models\Block;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class ExtraBlockManager extends AbstractBlockManager implements ObserverInterface, BlockManagerInterface
{
    protected $extraIds		   		= array();
    protected $extraUnits      		= array();

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
		$inputStartDate = $this->getValidValueInput(array('POST', 'SESSION'), 'start_date', date($this->shortDateFormat), true, $this->shortDateFormat);
		$inputStartTime = $this->getValidValueInput(array('POST', 'SESSION'), 'start_time', date("H:i:s"), true, 'time_validation');
		// For blocking we allow only to select only from date and time options
		$inputEndDate = $this->getValidValueInput(array('POST', 'SESSION'), 'end_date', date($this->shortDateFormat), true, $this->shortDateFormat);
		$inputEndTime = $this->getValidValueInput(array('POST', 'SESSION'), 'end_time', date("H:i:s"), true, 'time_validation');
		$this->startTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($inputStartDate, $inputStartTime);
		$this->endTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($inputEndDate, $inputEndTime);

		if(isset($_POST['block']) || isset($_POST['extra_ids']))
		{
			// Came from step 2
			$this->extraIds = $this->getValidArrayInput(array('POST'), 'extra_ids', array(), true, 'positive_integer');
		}
        // 17 - Extra Units
        if(isset($_POST['block']) || isset($_POST['extra_units']))
        {
            // intval allows us here for admins to block all (-1) extra units from backend
            $this->extraUnits = $this->getValidArrayInput(array('POST'), 'extra_units', array(), true, 'intval');
        }

		/**********************************************************************/
		if($this->debugMode)
		{
		    echo "<br />";
			echo "\$inputStartDate: $inputStartDate<br />";
			echo "\$inputStartTime: $inputStartTime<br />";
			echo "\$inputEndDate: $inputEndDate<br />";
			echo "\$inputEndTime: $inputEndTime<br />";
			echo "\$this->startTimestamp: $this->startTimestamp<br />";
			echo "\$this->endTimestamp: $this->endTimestamp<br />";
			echo "\$this->extraIds: "; print_r($this->extraIds); echo "<br />";
            echo "\$this->extraUnits: "; print_r($this->extraUnits); echo "<br />";
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
            $class::cacheValue('start_date', $this->getShortStartDate());
            $class::cacheValue('start_time', $this->getShortStartTime());
            $class::cacheValue('end_date', $this->getShortEndDate());
            $class::cacheValue('end_time', $this->getShortEndTime());

            // filled in step 2, pre-filled in step 3
            $class::cacheArrayAsJSON('extra_ids', $this->extraIds);
            $class::cacheArrayAsJSON('extra_units', $this->extraUnits);
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
            $class::unsetKey('start_date');
            $class::unsetKey('start_time');
            $class::unsetKey('end_date');
            $class::unsetKey('end_time');

            // filled in step 2
            $class::unsetKey('extra_ids');
            $class::unsetKey('extra_units');
        }

        // DEBUG
        if($this->debugMode >= 2)
        {
            echo "[UNSETTING] &#39;unsetKey&#39; METHOD EXISTS IN &#39;{$class}&#39;: ".var_export(method_exists($class, 'cacheValue'), true);
            echo "[UNSETTING] UPDATED {$this->storageType} VARS: ".nl2br(print_r($this->useSessions ? $_SESSION : $_COOKIE, true));
        }
    }

    public function getIds()
    {
        return $this->extraIds;
    }

    public function getUnits($paramExtraId)
    {
        return isset($this->extraUnits[$paramExtraId]) ? $this->extraUnits[$paramExtraId] : 0;
    }

    public function getAvailable()
    {
        $addQuery = "";

        $searchSQL = "
            SELECT extra_id
            FROM {$this->conf->getPrefix()}extras
            WHERE units_in_stock > 0
            {$addQuery} AND blog_id='{$this->conf->getBlogId()}'
			ORDER BY extra_name ASC
		";
        //echo "<br />".$searchSQL."<br />"; //die;

        $sqlRows = $this->conf->getInternalWPDB()->get_col($searchSQL);

        if($this->debugMode == 1)
        {
            echo "<br />TOTAL CANDIDATE EXTRAS FOUND: " . sizeof($sqlRows);
            echo "<br /><em>(Note: the candidate number is not final, it does not include blocked extras ";
            echo "or the situation when all extra units are booked)</em>";
        }

        return $sqlRows;
    }

    public function getSelectedWithDetails($paramExtraIds)
    {
        return $this->getWithDetails($paramExtraIds, true);
    }

    public function getAvailableWithDetails($paramExtraIds)
    {
        return $this->getWithDetails($paramExtraIds, false);
    }

    public function getWithDetails($paramExtraIds, $paramSelectedOnly = false)
    {
        $retExtras = array();
        $extraIds = is_array($paramExtraIds) ? $paramExtraIds : array();

        // Multi-mode check is not applied for extra blocks
        foreach($extraIds AS $extraId)
        {
            // 1 - Process extra details
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
            $extraDetails = $objExtra->getDetailsWithItemAndPartner();

            if($extraDetails['units_in_stock'] > 0 && $extraDetails['item_model_id'] == 0)
            {
                $selectedQuantity = 0;
                if(isset($this->extraUnits[$extraId]) && $extraDetails['units_in_stock'] > 0)
                {
                    if($this->extraUnits[$extraId] == -1)
                    {
                        // All units selected
                        $selectedQuantity = -1;
                    } else if($extraDetails['units_in_stock'] > $this->extraUnits[$extraId])
                    {
                        $selectedQuantity = $this->extraUnits[$extraId];
                    } else
                    {
                        $selectedQuantity = $extraDetails['units_in_stock'];
                    }
                }

                // For blocks we always need to display dependant item title and partner name (if exist)
                $extraText = $extraDetails['translated_extra_name_with_dependant_item_model'].' '.$extraDetails['via_partner'];
                $translatedExtra = $extraDetails['translated_extra_name_with_dependant_item_model'].' '.$extraDetails['via_partner'];

                // 4 - Extend the $extra output with new details
                $extraDetails['selected'] = $selectedQuantity > 0 || $selectedQuantity == -1 ? true : false;
                $extraDetails['selected_quantity'] = $selectedQuantity;
                $extraDetails['quantity_dropdown_options'] = StaticFormatter::generateDropdownOptions(
                    0, $extraDetails['units_in_stock'], $selectedQuantity, -1, $this->lang->getText('LANG_ALL_TEXT'), false, ""
                );
                $extraDetails['print_checked'] = $selectedQuantity > 0 || $selectedQuantity == -1 ? ' checked="checked"' : '';
                $extraDetails['print_selected'] = $selectedQuantity > 0 || $selectedQuantity == -1 ? 'selected' : '';
                $extraDetails['extra'] = $extraText;
                $extraDetails['translated_extra'] = $translatedExtra;

                if($selectedQuantity > 0 || $selectedQuantity == -1 || $paramSelectedOnly === false)
                {
                    // Add to stack only if element is selected or if we return all elements
                    $retExtras[] = $extraDetails;
                }

                if($this->debugMode == 1)
                {
                    echo "<br /><br />Extra with ID={$extraId} is <span style='color:green;font-weight:bold;'>AVAILABLE</span> for ordering ";
                    echo ", is not dependant on any item (Item Model ID=0) from the list of selected items: ";
                    echo "<br />and has {$selectedQuantity} units selected, with total {$extraDetails['units_in_stock']} units in stock";
                }
            } else
            {
                if($this->debugMode == 1)
                {
                    echo "<br /><br />Extra with ID={$extraId} is <span style='color:red;font-weight:bold;'>NOT AVAILABLE</span> for ordering ";
                    echo "is dependant on item model ID={$extraDetails['item_model_id']}, with {$extraDetails['units_in_stock']}";
                }
            }
        }

        // DEBUG
        //echo "<br />EXTRAS: ".nl2br(esc_textarea(print_r($retExtras, true)));

        return $retExtras;
    }
}