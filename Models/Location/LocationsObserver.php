<?php
/**
 * Locations Observer (no setup for single location)
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Location;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Validation\StaticValidator;

final class LocationsObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings 	            = array();

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
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getIdByCode($paramLocationUniqueIdentifier)
    {
        $retLocationId = 0;
        $validLocationUniqueIdentifier = esc_sql(sanitize_text_field($paramLocationUniqueIdentifier)); // For sql query only

        $locationData = $this->conf->getInternalWPDB()->get_row("
                SELECT location_id
                FROM {$this->conf->getPrefix()}locations
                WHERE location_code='{$validLocationUniqueIdentifier}' AND blog_id='{$this->conf->getBlogId()}'
            ", ARRAY_A);
        if(!is_null($locationData))
        {
            $retLocationId = $locationData['location_id'];
        }

        return $retLocationId;
    }

    public function getItemPickupIds($paramItemModelId)
    {
        return $this->getAllIds("PICKUP", $paramItemModelId);
    }

    public function getItemReturnIds($paramItemModelId)
    {
        return $this->getAllIds("RETURN", $paramItemModelId);
    }

    public function getItemBothIds($paramItemModelId)
    {
        return $this->getAllIds("BOTH", $paramItemModelId);
    }

    /**
     * Get all location ids (for selected item or all)
     * @param string $paramLocationType - "BOTH", "PICKUP" or "RETURN"
     * @param int $paramItemModelId - optional car id
     * @return array
     */
    public function getAllIds($paramLocationType = "ANY", $paramItemModelId = 0)
    {
        if($paramLocationType == "PICKUP" || $paramLocationType == "RETURN")
        {
            $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
            $validLocationType = $paramLocationType == "PICKUP" ? 1 : 2;
            $sqlWHERE = $paramItemModelId > 0 ? "item_id='{$validItemModelId}' AND location_type='{$validLocationType}'" : "location_type='{$validLocationType}'";
            // For single item reservation
            $sqlQuery = "
                SELECT location_id
                FROM {$this->conf->getPrefix()}locations
                WHERE location_id in
                (
                    SELECT location_id
                    FROM {$this->conf->getPrefix()}item_locations
                    WHERE {$sqlWHERE}
                ) AND blog_id='{$this->conf->getBlogId()}'
                ORDER BY location_order ASC, location_name ASC
            ";
        } else if($paramLocationType == "BOTH")
        {
            $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
            $sqlWHERE1 = $paramItemModelId > 0 ? "item_id='{$validItemModelId}' AND location_type='1'" : "location_type='1'";
            $sqlWHERE2 = $paramItemModelId > 0 ? "item_id='{$validItemModelId}' AND location_type='2'" : "location_type='2'";
            // For single item model order
            $sqlQuery = "
                SELECT location_id
                FROM {$this->conf->getPrefix()}locations
                WHERE location_id IN
                (
                    SELECT location_id
                    FROM {$this->conf->getPrefix()}item_locations
                    WHERE {$sqlWHERE1}
                ) AND location_id IN
                (
                    SELECT location_id
                    FROM {$this->conf->getPrefix()}item_locations
                    WHERE {$sqlWHERE2}
                ) AND blog_id='{$this->conf->getBlogId()}'
                ORDER BY location_order ASC, location_name ASC
            ";
        } else
        {
            // "ANY" location type or whatever else
            $sqlQuery = "
                SELECT location_id
                FROM {$this->conf->getPrefix()}locations
                WHERE blog_id='{$this->conf->getBlogId()}'
                ORDER BY location_order ASC, location_name ASC
            ";
        }

        $locationIds = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        return $locationIds;
    }

    public function getTranslatedPickupSelectOptions($paramItemModelId = 0)
    {
        return $this->getSelectOptions($paramItemModelId, "PICKUP", true);
    }

    public function getPickupSelectOptions($paramItemModelId = 0)
    {
        return $this->getSelectOptions($paramItemModelId, "PICKUP", false);
    }

    public function getTranslatedReturnSelectOptions($paramItemModelId = 0)
    {
        return $this->getSelectOptions($paramItemModelId, "RETURN", true);
    }

    public function getReturnSelectOptions($paramItemModelId = 0)
    {
        return $this->getSelectOptions($paramItemModelId, "RETURN", false);
    }

    private function getSelectOptions($paramItemModelId = 0, $paramMode = "PICKUP", $paramTranslated = false)
    {
        $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
        $validLocationType = $paramMode == "RETURN" ? 2 : 1;

        // List all locations - we don't know what kind of type they are as we only set the type after this - by add an item to it.
        $validLocationIds = $this->getAllIds("ANY", -1);
        $locationsHTML = "";
        foreach ($validLocationIds AS $validLocationId)
        {
            $objLocation = new Location($this->conf, $this->lang, $this->settings, $validLocationId);
            $printLocation = $paramTranslated ? $objLocation->getPrintTranslatedLocationName() : $objLocation->getPrintLocationName();
            $selected = "";
            if($validItemModelId > 0)
            {
                $itemLocationExists = $this->conf->getInternalWPDB()->get_row("
                    SELECT location_id
                    FROM {$this->conf->getPrefix()}item_locations
                    WHERE item_id='{$validItemModelId}' AND location_type='{$validLocationType}' AND location_id='{$validLocationId}'
                ", ARRAY_A);
                if(!is_null($itemLocationExists))
                {
                    $selected .= 'selected="selected"';
                }
            }

            $locationsHTML .= '<option value="'.$validLocationId.'"'.$selected.'>'.$printLocation.'</option>';
        }

        return $locationsHTML;
    }

    public function getTrustedTranslatedPickupDropdownOptionsHTML($paramItemModelId = 0, $paramSelectedLocationId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramSkipLocationId = 0)
    {
        return $this->getTrustedDropdownOptionsHTML("PICKUP", $paramItemModelId, $paramSelectedLocationId, $paramDefaultValue, $paramDefaultLabel, $paramSkipLocationId, true);
    }

    public function getTrustedPickupDropdownOptionsHTML($paramItemModelId = 0, $paramSelectedLocationId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramSkipLocationId = 0)
    {
        return $this->getTrustedDropdownOptionsHTML("PICKUP", $paramItemModelId, $paramSelectedLocationId, $paramDefaultValue, $paramDefaultLabel, $paramSkipLocationId, false);
    }

    public function getTrustedTranslatedReturnDropdownOptionsHTML($paramItemModelId = 0, $paramSelectedLocationId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramSkipLocationId = 0)
    {
        return $this->getTrustedDropdownOptionsHTML("RETURN", $paramItemModelId, $paramSelectedLocationId, $paramDefaultValue, $paramDefaultLabel, $paramSkipLocationId, true);
    }

    public function getTrustedReturnDropdownOptionsHTML($paramItemModelId = 0, $paramSelectedLocationId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramSkipLocationId = 0)
    {
        return $this->getTrustedDropdownOptionsHTML("RETURN", $paramItemModelId, $paramSelectedLocationId, $paramDefaultValue, $paramDefaultLabel, $paramSkipLocationId, false);
    }

    public function getTrustedTranslatedLocationsDropdownOptionsHTML($paramLocationType = "BOTH", $paramItemModelId = 0, $paramSelectedLocationId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramSkipLocationId = 0)
    {
        return $this->getTrustedDropdownOptionsHTML($paramLocationType, $paramItemModelId, $paramSelectedLocationId, $paramDefaultValue, $paramDefaultLabel, $paramSkipLocationId, true);
    }

    /**
     * @param string $paramLocationType - "BOTH", "PICKUP" or "RETURN"
     * @param int $paramItemModelId - optional item id
     * @param int $paramSelectedLocationId - Selected location ID or 0 as default
     * @param mixed $paramDefaultValue - empty item value, 0 or ""
     * @param string $paramDefaultLabel - i.e. select return
     * @param int $paramSkipLocationId
     * @param bool $paramTranslated
     * @return string
     */
    public function getTrustedDropdownOptionsHTML(
        $paramLocationType = "BOTH", $paramItemModelId = 0, $paramSelectedLocationId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "",
        $paramSkipLocationId = 0, $paramTranslated = false
    ) {
        $locationsHTML = '';
        $validDefaultValue = StaticValidator::getValidPositiveInteger($paramDefaultValue, 0);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $defaultSelected = $paramSelectedLocationId == $validDefaultValue ? ' selected="selected"' : '';
        $locationsHTML .= '<option value="'.$validDefaultValue.'"'.$defaultSelected.'>'.$sanitizedDefaultLabel.'</option>';
        $locationIds = $this->getAllIds($paramLocationType, $paramItemModelId);

        $i = 0;
        foreach ($locationIds AS $locationId)
        {
            $i++;
            if($locationId != $paramSkipLocationId)
            {
                $objLocation = new Location($this->conf, $this->lang, $this->settings, $locationId);
                $locationDetails = $objLocation->getDetails();
                $printLocationName = $paramTranslated ? $locationDetails['print_translated_location_name'] : $locationDetails['print_location_name'];
                $printLocationName .= $locationDetails['street_address'] != '' ? ' - '.$locationDetails['street_address'] : '';
                $selected = $locationDetails['location_id'] == $paramSelectedLocationId ? ' selected="selected"' : '';

                $locationsHTML .= '<option value="'.$locationDetails['location_id'].'"'.$selected.'>'.$i.'. '.$printLocationName.'</option>';
            }
        }
        return $locationsHTML;
    }

    /**
     * @param int $paramSelectPageId
     * @param string $name
     * @param null $id
     * @return string
     */
    public function getPagesDropdown($paramSelectPageId = 0, $name = "location_page_id", $id = null)
    {
        $pageArgs = array(
            'depth' => 1,
            'child_of' => 0,
            'selected' => $paramSelectPageId,
            'echo' => 0,
            'name' => $name,
            'id' => $id, // string
            'show_option_none' => $this->lang->getText('LANG_PAGE_SELECT_TEXT'), // string
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'post_type' => $this->conf->getPostTypePrefix().'location',
        );
        $dropDownHtml = wp_dropdown_pages($pageArgs);

        // DEBUG
        //echo "RESULT: $dropDownHtml";

        return $dropDownHtml;
    }

    /**
     * @param $paramLocationId
     * @param string $dayOfWeek
     * @param bool $paramFullDetails - is true, when we want to show LATE/EARLY/REGULAR day time split even for the same location
     * @return string
     */
    public function getPrintPickups($paramLocationId, $dayOfWeek = "mon", $paramFullDetails = false)
    {
        $objLocation = new Location($this->conf, $this->lang, $this->settings, $paramLocationId);
        $locationDetails = $objLocation->getDetailsByDayOfWeek($dayOfWeek);
        $printLocationsHTML = "";
        if(!is_null($locationDetails) && $locationDetails['afterhours_pickup_allowed'] == 1 && $locationDetails['afterhours_pickup_location_id'] > 0)
        {
            // Afterhours pickup from different location
            // Get afterhours location data
            $objAfterHoursLocation = new Location($this->conf, $this->lang, $this->settings, $locationDetails['afterhours_pickup_location_id']);

            if($objAfterHoursLocation->isValidForAfterHoursPickup($dayOfWeek, $locationDetails['open_time'], $locationDetails['close_time']) == true)
            {
                $afterHoursPickupDetails = $objAfterHoursLocation->getAfterHoursDetails($locationDetails['open_time'], $locationDetails['close_time'], $dayOfWeek);
                if($afterHoursPickupDetails['works_early'])
                {
                    $printLocationsHTML .= '('.$this->lang->getText('LANG_ORDER_EARLY_TEXT').' - '.$afterHoursPickupDetails['print_early_hours'].') '.$afterHoursPickupDetails['print_translated_location_name'].'<br />';
                    $printLocationsHTML .= $afterHoursPickupDetails['print_full_address'] != '' ? $afterHoursPickupDetails['print_full_address'].'<br /><br />' : '';
                }
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') '.$locationDetails['print_translated_location_name'];
                $printLocationsHTML .= $locationDetails['print_full_address'] != '' ? '<br />'.$locationDetails['print_full_address'] : '';
                if($afterHoursPickupDetails['works_late'])
                {
                    $printLocationsHTML .= $afterHoursPickupDetails['print_full_address'] != '' ? '<br />' : '';
                    $printLocationsHTML .= '<br />('.$this->lang->getText('LANG_ORDER_LATE_TEXT').': '.$afterHoursPickupDetails['print_late_hours'].') '.$afterHoursPickupDetails['print_translated_location_name'];
                    $printLocationsHTML .= $afterHoursPickupDetails['print_full_address'] != '' ? '<br />'.$afterHoursPickupDetails['print_full_address'] : '';
                }
                if($locationDetails['lunch_enabled'])
                {
                    $printLocationsHTML .= '<br /><br />('.$locationDetails['print_lunch_hours'].') '.$this->lang->getText('LANG_LOCATION_LUNCH_TIME_TEXT');
                }
            } else
            {
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') '.$locationDetails['print_translated_location_name'];
                $printLocationsHTML .= $locationDetails['print_full_address'] != '' ? ' - '.$locationDetails['print_full_address'] : '';
            }
        } else if(!is_null($locationDetails) && $paramFullDetails === true && $locationDetails['afterhours_pickup_allowed'] == 1 && $locationDetails['afterhours_pickup_location_id'] == 0)
        {
            // Afterhours pickup from same location
            $printLocationTitle = $locationDetails['print_translated_location_name'];
            $printLocationTitle .= $locationDetails['print_full_address'] != '' ? '<br />'.$locationDetails['print_full_address'].'<br />' : '';
            $printLocationsHTML .= $locationDetails['works_early'] ? '('.$this->lang->getText('LANG_ORDER_EARLY_TEXT').': '.$locationDetails['print_early_hours'].') '.$printLocationTitle.'<br />' : '';
            if($locationDetails['works_early'] || $locationDetails['works_late'])
            {
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') '.$printLocationTitle;
            } else
            {
                // When it's regular hours in 24/7
                $printLocationsHTML .= $printLocationTitle;
            }
            $printLocationsHTML .= $locationDetails['works_late'] ? '<br />('.$this->lang->getText('LANG_ORDER_LATE_TEXT').': '.$locationDetails['print_late_hours'].') '.$printLocationTitle : '';
            if($locationDetails['lunch_enabled'])
            {
                $printLocationsHTML .= '<br /><br />('.$locationDetails['print_lunch_hours'].') '.$this->lang->getText('LANG_LOCATION_LUNCH_TIME_TEXT');
            }
        } else if(!is_null($locationDetails))
        {
            if($locationDetails['afterhours_pickup_allowed'] == 0 &&
                ($locationDetails['open_time'] != "00:00:00" || $locationDetails['close_time'] != "23:59:59")
            ) {
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') ';
            }
            // When after-hours pickup is not allowed
            $printLocationsHTML .= $locationDetails['print_translated_location_name'];
            $printLocationsHTML .= $locationDetails['print_full_address'] != '' ? '<br />'.$locationDetails['print_full_address'] : '';
            if($locationDetails['lunch_enabled'])
            {
                $printLocationsHTML .= '<br /><br />('.$locationDetails['print_lunch_hours'].') '.$this->lang->getText('LANG_LOCATION_LUNCH_TIME_TEXT');
            }
        }
        return $printLocationsHTML;
    }

    /**
     * @param $paramLocationId
     * @param string $dayOfWeek
     * @param bool $paramFullDetails - is true, when we want to show LATE/EARLY/REGULAR day time split even for the same location
     * @return string
     */
    public function getPrintReturns($paramLocationId, $dayOfWeek = "mon", $paramFullDetails = false)
    {
        $objLocation = new Location($this->conf, $this->lang, $this->settings, $paramLocationId);
        $locationDetails = $objLocation->getDetailsByDayOfWeek($dayOfWeek);
        $printLocationsHTML = "";
        if(!is_null($locationDetails) && $locationDetails['afterhours_return_allowed'] == 1 && $locationDetails['afterhours_return_location_id'] > 0)
        {
            // Get afterhours location data
            $objAfterHoursLocation = new Location($this->conf, $this->lang, $this->settings, $locationDetails['afterhours_return_location_id']);

            if($objAfterHoursLocation->isValidForAfterHoursReturn($dayOfWeek, $locationDetails['open_time'], $locationDetails['close_time']) == true)
            {
                $afterHoursReturnDetails = $objAfterHoursLocation->getAfterHoursDetails($locationDetails['open_time'], $locationDetails['close_time'], $dayOfWeek);
                if($afterHoursReturnDetails['works_early'])
                {
                    $printLocationsHTML .= '('.$this->lang->getText('LANG_ORDER_EARLY_TEXT').': '.$afterHoursReturnDetails['print_early_hours'].') '.$afterHoursReturnDetails['print_translated_location_name'].'<br />';
                    $printLocationsHTML .= $afterHoursReturnDetails['print_full_address'] != '' ? $afterHoursReturnDetails['print_full_address'].'<br /><br />' : '';
                }
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') '.$locationDetails['print_translated_location_name'];
                $printLocationsHTML .= $locationDetails['print_full_address'] != '' ? ' - '.$locationDetails['print_full_address'] : '';
                if($afterHoursReturnDetails['works_late'])
                {
                    $printLocationsHTML .= $afterHoursReturnDetails['print_full_address'] != '' ? '<br />' : '';
                    $printLocationsHTML .= '<br />('.$this->lang->getText('LANG_ORDER_LATE_TEXT').': '.$afterHoursReturnDetails['print_late_hours'].') '.$afterHoursReturnDetails['print_translated_location_name'];
                    $printLocationsHTML .= $afterHoursReturnDetails['print_full_address'] != '' ? $afterHoursReturnDetails['print_full_address'] : '';
                }
                if($locationDetails['lunch_enabled'])
                {
                    $printLocationsHTML .= '<br /><br />('.$locationDetails['print_lunch_hours'].') '.$this->lang->getText('LANG_LOCATION_LUNCH_TIME_TEXT');
                }
            } else
            {
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') '.$locationDetails['print_translated_location_name'];
                $printLocationsHTML .= $locationDetails['print_full_address'] != '' ? ' - '.$locationDetails['print_full_address'] : '';
            }
        } else if(!is_null($locationDetails) && $paramFullDetails === true && $locationDetails['afterhours_return_allowed'] == 1 && $locationDetails['afterhours_return_location_id'] == 0)
        {
            // Afterhours return to same location
            $printLocationTitle = $locationDetails['print_translated_location_name'];
            $printLocationTitle .= $locationDetails['print_full_address'] != '' ? '<br />'.$locationDetails['print_full_address'].'<br />' : '';
            $printLocationsHTML .= $locationDetails['works_early'] ? '('.$this->lang->getText('LANG_ORDER_EARLY_TEXT').': '.$locationDetails['print_early_hours'].') '.$printLocationTitle.'<br />' : '';
            if($locationDetails['works_early'] || $locationDetails['works_late'])
            {
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') '.$printLocationTitle;
            } else
            {
                // When it's regular hours in 24/7
                $printLocationsHTML .= $printLocationTitle;
            }
            $printLocationsHTML .= $locationDetails['works_late'] ? '<br />('.$this->lang->getText('LANG_ORDER_LATE_TEXT').': '.$locationDetails['print_late_hours'].') '.$printLocationTitle : '';
            if($locationDetails['lunch_enabled'])
            {
                $printLocationsHTML .= '<br /><br />('.$locationDetails['print_lunch_hours'].') '.$this->lang->getText('LANG_LOCATION_LUNCH_TIME_TEXT');
            }
        }else if(!is_null($locationDetails))
        {
            if($locationDetails['afterhours_return_allowed'] == 0 &&
               ($locationDetails['open_time'] != "00:00:00" || $locationDetails['close_time'] != "23:59:59")
            ) {
                $printLocationsHTML .= '('.$locationDetails['print_open_hours'].') ';
            }
            // When after-hours are in same location or when after-hours are not allowed
            $printLocationsHTML .= $locationDetails['print_translated_location_name'];
            $printLocationsHTML .= $locationDetails['print_full_address'] != '' ? '<br />'.$locationDetails['print_full_address'] : '';
            if($locationDetails['lunch_enabled'])
            {
                $printLocationsHTML .= '<br /><br />('.$locationDetails['print_lunch_hours'].') '.$this->lang->getText('LANG_LOCATION_LUNCH_TIME_TEXT');
            }
        }

        return $printLocationsHTML;
    }

    /*****************************************************************************/
    /****************************** ADMIN SECTION ********************************/
    /*****************************************************************************/
    public function getTrustedAdminListHTML()
    {
        $locationsHTML = '';
        $locationIds = $this->getAllIds("ANY", -1);

        foreach ($locationIds AS $locationId)
        {
            $objTaxManager = new TaxManager($this->conf, $this->lang, $this->settings);
            $taxPercentage = $objTaxManager->getTaxPercentage($locationId, $locationId);
            $objLocation = new Location($this->conf, $this->lang, $this->settings, $locationId);
            $objLocationFeeManager = new LocationFeeManager($this->conf, $this->lang, $this->settings, $locationId, $taxPercentage);
            $location = array_merge($objLocation->getDetails(), $objLocationFeeManager->getDetails());

            if($location['location_page_id'] != 0 && $location['location_page_url'] != '')
            {
                $locationPageTitle = get_the_title($location['location_page_id']);
                $linkTitle = sprintf($this->lang->getText('LANG_VIEW_PAGE_IN_NEW_WINDOW_TEXT'), $locationPageTitle);
                $trustedTranslatedLocationNameWithLinkHTML = '<a href="'.esc_url($location['location_page_url']).'" target="_blank" title="'.esc_attr($linkTitle).'">';
                $trustedTranslatedLocationNameWithLinkHTML .= $location['print_translated_location_name'];
                $trustedTranslatedLocationNameWithLinkHTML .= '</a>';
            } else
            {
                $trustedTranslatedLocationNameWithLinkHTML = $location['print_translated_location_name'];
            }

            $printOriginalLocationName = '';
            if($this->lang->canTranslateSQL())
            {
                $printOriginalLocationName .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$location['print_location_name'].')</span>';
            }
            $printFullAddress = $location['print_full_address'] != "" ? '<br />'.$location['print_full_address'] : '';


            if($location['afterhours_pickup_allowed'] == 1 && $location['afterhours_pickup_location_id'] > 0)
            {
                $objAfterHoursPickup = new Location($this->conf, $this->lang, $this->settings, $location['afterhours_pickup_location_id']);
                $printAfterHoursPickupWithFullAddress = $objAfterHoursPickup->getPrintTranslatedLocationNameWithFullAddress();
            } else if($location['afterhours_pickup_allowed'] == 1 && $location['afterhours_pickup_location_id'] == 0)
            {
                $printAfterHoursPickupWithFullAddress = $this->lang->getText('LANG_IN_THIS_LOCATION_TEXT');
            } else
            {
                $printAfterHoursPickupWithFullAddress = $this->lang->getText('LANG_AFTERHOURS_PICKUP_IS_NOT_ALLOWED_TEXT');
            }

            if($location['afterhours_return_allowed'] == 1 && $location['afterhours_return_location_id'] > 0)
            {
                $objAfterHoursReturn = new Location($this->conf, $this->lang, $this->settings, $location['afterhours_return_location_id']);
                $printAfterHoursReturnWithFullAddress = $objAfterHoursReturn->getPrintTranslatedLocationNameWithFullAddress();
            } else if($location['afterhours_return_allowed'] == 1 && $location['afterhours_return_location_id'] == 0)
            {
                $printAfterHoursReturnWithFullAddress = $this->lang->getText('LANG_IN_THIS_LOCATION_TEXT');
            } else
            {
                $printAfterHoursReturnWithFullAddress = $this->lang->getText('LANG_AFTERHOURS_RETURN_IS_NOT_ALLOWED_TEXT');
            }

            if($location['afterhours_pickup_allowed'] == 1)
            {
                $printAfterHoursPickupFee = $location['unit_print']['afterhours_pickup_fee'].' ('.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').')<br />';
                $printAfterHoursPickupFee .= $location['unit_print']['afterhours_pickup_fee_with_tax'];
            } else
            {
                $printAfterHoursPickupFee = $this->lang->getText('LANG_AFTERHOURS_PICKUP_IS_NOT_ALLOWED_TEXT');
            }
            if($location['afterhours_return_allowed'] == 1)
            {
                $printAfterHoursReturnFee = $location['unit_print']['afterhours_return_fee'].' ('.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').')<br />';
                $printAfterHoursReturnFee .= $location['unit_print']['afterhours_return_fee_with_tax'];
            } else
            {
                $printAfterHoursReturnFee = $this->lang->getText('LANG_AFTERHOURS_RETURN_IS_NOT_ALLOWED_TEXT');
            }

            $businessHoursText = $objLocation->getBusinessHoursWithShortDayNameText();
            $lunchHoursText = $objLocation->getShortLunchHoursText();

            $locationsHTML .= '<tr>';
            $locationsHTML .= '<td>'.$locationId.'<br />'.$location['print_location_code'].'</td>';
            $locationsHTML .= '<td>'.$trustedTranslatedLocationNameWithLinkHTML.$printOriginalLocationName.$printFullAddress;
            $locationsHTML .= $location['print_contacts'] != '' ? '<br /><br />'.$this->lang->escHTML('LANG_CONTACTS_TEXT').':<br />'.$location['print_contacts'] : '';
            $locationsHTML .= '</td>';
            $locationsHTML .= '<td>';
            $locationsHTML .= $this->lang->getText('LANG_ORDER_PICKUP_TEXT').':<br />'.$location['unit_print']['pickup_fee'].' ('.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').')<br />';
            $locationsHTML .= $location['unit_print']['pickup_fee_with_tax'].'<br /><br />';
            $locationsHTML .= $this->lang->getText('LANG_ORDER_RETURN_TEXT').':<br />'.$location['unit_print']['return_fee'].' ('.$this->lang->getText('LANG_TAX_WITHOUT_TEXT').')<br />';
            $locationsHTML .= $location['unit_print']['return_fee_with_tax'];
            $locationsHTML .= '</td>';
            $locationsHTML .= '<td style="white-space: nowrap">'.esc_br_html($businessHoursText);
            $locationsHTML .= $lunchHoursText != '' ? '</br />-----------------------------<br />'.esc_html($lunchHoursText) : '';
            $locationsHTML .= '</td>';
            $locationsHTML .= '<td>'.$this->lang->escHTML('LANG_ORDER_PICKUP_TEXT').':<br />'.$printAfterHoursPickupWithFullAddress.'<br /><br />';
            $locationsHTML .= $this->lang->getText('LANG_ORDER_RETURN_TEXT').':<br />'.$printAfterHoursReturnWithFullAddress.'</td>';
            $locationsHTML .= '<td>'.$this->lang->escHTML('LANG_ORDER_PICKUP_TEXT').':<br />'.$printAfterHoursPickupFee.'<br /><br />';
            $locationsHTML .= $this->lang->getText('LANG_ORDER_RETURN_TEXT').':<br />'.$printAfterHoursReturnFee.'</td>';
            $locationsHTML .= '<td style="text-align: center">'.$location['location_order'].'</td>';
            $locationsHTML .= '<td align="right" style="white-space: nowrap">';
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_locations'))
            {
                $locationsHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-location&amp;location_id='.$locationId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $locationsHTML .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteLocation(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($locationId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $locationsHTML .= '--';
            }
            $locationsHTML .= '</td>';
            $locationsHTML .= '</tr>';
        }

        return  $locationsHTML;
    }
}