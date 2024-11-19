<?php
/**
 * Item Models Observer (no setup for single item model)
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\ItemModel;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PriceGroup\PriceGroup;

final class ItemModelsObserver implements ObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $settings                   = array();
    private $debugMode 	                = 0;
    private $depositsEnabled            = false;
    private $classifyItemModels         = false;
    private $savedMessages              = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        if(isset($paramSettings['conf_deposit_enabled']))
        {
            // Set deposit status
            $this->depositsEnabled = StaticValidator::getValidPositiveInteger($paramSettings['conf_deposit_enabled'], 1) == 1 ? true : false;
        }
        if(isset($paramSettings['conf_classify_items']))
        {
            // Set classified status
            $this->classifyItemModels = $paramSettings['conf_classify_items'] == 1 ? true : false;
        }
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getSavedDebugMessages()
    {
        return isset($this->savedMessages['debug']) ? $this->savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset($this->savedMessages['okay']) ? $this->savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset($this->savedMessages['error']) ? $this->savedMessages['error'] : array();
    }

    private function saveAllMessages($paramArrMessages)
    {
        if(isset($paramArrMessages['debug']))
        {
            $this->savedMessages['debug'] = array_merge($this->getSavedDebugMessages(), $paramArrMessages['debug']);
        }
        if(isset($paramArrMessages['okay']))
        {
            $this->savedMessages['okay'] = array_merge($this->getSavedOkayMessages(), $paramArrMessages['okay']);
        }
        if(isset($paramArrMessages['error']))
        {
            $this->savedMessages['error'] = array_merge($this->getSavedErrorMessages(), $paramArrMessages['error']);
        }
    }

    public function canShowOnlyPartnerOwned()
    {
        $canEditOwnItemModels = current_user_can('manage_'.$this->conf->getExtPrefix().'own_items');
        $canEditAllItemModels = current_user_can('manage_'.$this->conf->getExtPrefix().'all_items');
        $onlyPartnerOwned = $canEditOwnItemModels == true && $canEditAllItemModels == false;

        return $onlyPartnerOwned;
    }

    public function checkExists($paramItemModelId = 0)
    {
        $itemModelExists = false;
        $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
        $itemModelExistsResult = $this->conf->getInternalWPDB()->get_var("
            SELECT item_id AS item_model_id
            FROM {$this->conf->getPrefix()}items
            WHERE item_id='{$validItemModelId}'
            AND blog_id='{$this->conf->getBlogId()}'
        ");

        if(!is_null($itemModelExistsResult))
        {
            $itemModelExists = true;
        }

        return $itemModelExists;
    }

    public function getIdBySKU($paramItemModelSKU)
    {
        $retItemModelId = 0;
        $validItemModelSKU = StaticValidator::getValidCode($paramItemModelSKU, '', true, true, false);

        $itemModelData = $this->conf->getInternalWPDB()->get_row("
                SELECT item_id AS item_model_id
                FROM {$this->conf->getPrefix()}items
                WHERE item_sku='{$validItemModelSKU}' AND blog_id='{$this->conf->getBlogId()}'
            ", ARRAY_A);
        if(!is_null($itemModelData))
        {
            $retItemModelId = $itemModelData['item_model_id'];
        }

        return $retItemModelId;
    }

    /**
     * @return int
     */
    public function getTotalItemModels()
    {
        $sqlQuery = "
            SELECT item_id AS item_model_id
            FROM {$this->conf->getPrefix()}items
            WHERE blog_id='{$this->conf->getBlogId()}'
        ";

        //DEBUG
        //echo nl2br($sqlQuery)."<br /><br />";

        $totalItemModels = sizeof($this->conf->getInternalWPDB()->get_col($sqlQuery));

        return $totalItemModels;
    }

    public function getAvailableIdsByLayout(
        $paramLayout, $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    )
    {
        switch(strtolower($paramLayout))
        {
            case "form":
                $displayMode = "AVAILABLE";
                break;

            case "slider":
                $displayMode = "IN_SLIDER";
                break;

            case "list":
                $displayMode = "IN_ITEM_LIST";
                break;

            case "grid":
                $displayMode = "IN_ITEM_LIST";
                break;

            case "table":
                $displayMode = "IN_PRICE_TABLE";
                break;

            case "calendar":
                $displayMode = "IN_AVAILABILITY_CALENDAR";
                break;

            default:
                $displayMode = "AVAILABLE";
        }

        return $this->getIds(
            $displayMode, $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
            $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
        );
    }

    public function getAvailableIdsForSlider(
        $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    ) {
        return $this->getIds(
            "IN_SLIDER", $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
            $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
        );
    }

    public function getAvailableIdsForListOrGrid(
        $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    ) {
        return $this->getIds(
            "IN_ITEM_LIST", $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
            $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
        );
    }

    public function getAvailableIdsForPriceTable(
        $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    ) {
        return $this->getIds(
            "IN_PRICE_TABLE", $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
            $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
        );
    }

    public function getAvailableIdsForCalendar(
        $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    ) {
        return $this->getIds(
            "IN_AVAILABILITY_CALENDAR", $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
            $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
        );
    }

    /**
     * Get an item which has amount > 0 of units and status != hidden
     * @param int $paramPartnerId
     * @param int $paramManufacturerId
     * @param int $paramClassId
     * @param int $paramAttributeId1
     * @param int $paramAttributeId2
     * @param int $paramItemModelId
     * @param int $paramPickupLocationId
     * @param int $paramReturnLocationId
     * @return array
     */
    public function getAvailableIds(
        $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    ) {
        return $this->getIds(
            "AVAILABLE", $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
            $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
        );
    }

    public function getAllIds(
        $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    ) {
        return $this->getIds(
            "ALL", $paramPartnerId, $paramManufacturerId, $paramClassId, $paramAttributeId1,
            $paramAttributeId2, $paramItemModelId, $paramPickupLocationId, $paramReturnLocationId
        );
    }

    /**
     * @param string $paramDisplayMode - one of display modes: "ALL", "AVAILABLE", "IN_SLIDER", "IN_ITEM_LIST", "IN_PRICE_TABLE", "IN_AVAILABILITY_CALENDAR"
     * @param int $paramPartnerId
     * @param int $paramManufacturerId
     * @param int $paramClassId
     * @param int $paramAttributeId1
     * @param int $paramAttributeId2
     * @param int $paramItemModelId
     * @param int $paramPickupLocationId
     * @param int $paramReturnLocationId
     * @return array
     */
    private function getIds(
        $paramDisplayMode = "ALL", $paramPartnerId = -1, $paramManufacturerId = -1, $paramClassId = -1, $paramAttributeId1 = -1,
        $paramAttributeId2 = -1, $paramItemModelId = -1, $paramPickupLocationId = -1, $paramReturnLocationId = -1
    ) {
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1); // -1 means 'skip'
        $validManufacturerId = StaticValidator::getValidInteger($paramManufacturerId, -1); // -1 means 'skip'
        $validClassId = StaticValidator::getValidInteger($paramClassId, -1); // -1 means 'skip'
        $validAttributeId1 = StaticValidator::getValidInteger($paramAttributeId1, -1); // -1 means 'skip'
        $validAttributeId2 = StaticValidator::getValidInteger($paramAttributeId2, -1); // -1 means 'skip'
        $validItemModelId = StaticValidator::getValidInteger($paramItemModelId, -1); // -1 means 'skip'
        $validPickupLocationId = StaticValidator::getValidInteger($paramPickupLocationId, -1); // -1 means 'skip'
        $validReturnLocationId = StaticValidator::getValidInteger($paramReturnLocationId, -1); // -1 means 'skip'

        switch($paramDisplayMode)
        {
            case "IN_SLIDER":
                $sqlAdd = "AND it.units_in_stock > 0 AND enabled = '1' AND it.display_in_slider='1'";
                break;

            case "IN_ITEM_LIST":
                $sqlAdd = "AND it.units_in_stock > 0 AND enabled = '1' AND it.display_in_item_list='1'";
                break;

            case "IN_PRICE_TABLE":
                $sqlAdd = "AND it.units_in_stock > 0 AND enabled = '1' AND it.display_in_price_table='1'";
                break;

            case "IN_AVAILABILITY_CALENDAR":
                $sqlAdd = "AND it.units_in_stock > 0 AND enabled = '1' AND it.display_in_calendar='1'";
                break;

            case "AVAILABLE":
                $sqlAdd = "AND it.units_in_stock > 0 AND enabled = '1'";
                break;

            default:
                $sqlAdd = "";
        }

        // Partner field
        if($validPartnerId >= 0)
        {
            $sqlAdd .= " AND it.partner_id='{$validPartnerId}'";
        }

        // Manufacturer field
        if($validManufacturerId >= 0)
        {
            $sqlAdd .= " AND it.manufacturer_id='{$validManufacturerId}'";
        }

        // Class field
        if($validClassId >= 0)
        {
            $sqlAdd .= " AND it.body_type_id='{$validClassId}'";
        }

        // Attribute 1 field
        if($validAttributeId1 >= 0)
        {
            $sqlAdd .= " AND it.transmission_type_id='{$validAttributeId1}'";
        }

        // Attribute 2 field
        if($validAttributeId2 >= 0)
        {
            $sqlAdd .= " AND it.fuel_type_id='{$validAttributeId2}'";
        }

        // ItemModel field
        if($validItemModelId > 0)
        {
            $sqlAdd .= " AND it.item_id='{$validItemModelId}'";
        }

        if($validPickupLocationId > 0)
        {
            $sqlAdd .= "
				AND it.item_id IN
				(
					SELECT item_id
					FROM {$this->conf->getPrefix()}item_locations
					WHERE location_id='{$validPickupLocationId}' AND location_type='1'
				)";
        }

        if($validReturnLocationId > 0)
        {
            $sqlAdd .= "AND it.item_id IN
			(
				SELECT item_id
				FROM {$this->conf->getPrefix()}item_locations
				WHERE location_id='{$validReturnLocationId}' AND location_type='2'
			)";
        }

        $searchSQL = "
            SELECT it.item_id AS item_model_id
            FROM {$this->conf->getPrefix()}items it
            LEFT JOIN {$this->conf->getPrefix()}manufacturers mf ON it.manufacturer_id=mf.manufacturer_id
            WHERE it.blog_id='{$this->conf->getBlogId()}' {$sqlAdd}
            ORDER BY manufacturer_title ASC, model_name ASC
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResult;
    }

    /**
     * Do items are classified?
     * @return bool
     */
    public function areItemModelsClassified()
    {
        return $this->classifyItemModels;
    }

    public function getTrustedTranslatedDropdownOptionsHTML_ByPartnerId($paramPartnerId = -1, $paramSelectedItemModelId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowItemModelId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedItemModelId, $paramDefaultValue, $paramDefaultLabel, $paramShowItemModelId, true, $paramPartnerId);
    }

    public function getTrustedDropdownOptionsHTML_ByPartnerId($paramPartnerId = -1, $paramSelectedItemModelId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowItemModelId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedItemModelId, $paramDefaultValue, $paramDefaultLabel, $paramShowItemModelId, false, $paramPartnerId);
    }

    public function getTrustedTranslatedDropdownOptionsHTML($paramSelectedItemModelId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowItemModelId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedItemModelId, $paramDefaultValue, $paramDefaultLabel, $paramShowItemModelId, true, -1);
    }

    /**
     * @param int $paramSelectedItemModelId
     * @param string $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramShowItemModelId
     * @param bool $paramTranslated
     * @param int $paramPartnerId
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedItemModelId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowItemModelId = true, $paramTranslated = false, $paramPartnerId = -1)
    {
        $sanitizedDefaultValue = sanitize_text_field($paramDefaultValue);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $retHTML = '';
        if($paramDefaultValue != "" || $paramDefaultLabel != "")
        {
            if($paramSelectedItemModelId == $paramDefaultValue)
            {
                $retHTML .= '<option value="'.esc_attr($sanitizedDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($sanitizedDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
            }
        }

        $itemModelIds = $this->getIds("ALL", $paramPartnerId);
        foreach ($itemModelIds AS $itemModelId)
        {
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
            $itemModelDetails = $objItemModel->getExtendedDetails();

            if($paramTranslated)
            {
                $printTitle = $itemModelDetails['print_translated_manufacturer_name'].' '.$itemModelDetails['print_translated_item_model_name'].' '.esc_html($itemModelDetails['via_partner']);
            } else
            {
                $printTitle = $itemModelDetails['print_manufacturer_name'].' '.$itemModelDetails['print_item_model_name'].' '.esc_html($itemModelDetails['via_partner']);
            }
            if($paramShowItemModelId)
            {
                $printTitle .= " (ID=".$itemModelDetails['item_model_id'].")";
            }
            $selected = $paramSelectedItemModelId == $itemModelDetails['item_model_id'] ? ' selected="selected"' : '';

            $retHTML .= '<option value="'.esc_attr($itemModelDetails['item_model_id']).'"'.$selected.'>'.$printTitle.'</option>';
        }

        return $retHTML;
    }

    /**
     * @param int $paramSelectPageId
     * @param string $name
     * @param null $id
     * @return string
     */
    public function getPagesDropdown($paramSelectPageId = 0, $name = "item_page_id", $id = null)
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
            'post_type' => $this->conf->getPostTypePrefix().'item',
        );
        $dropDownHtml = wp_dropdown_pages($pageArgs);

        // DEBUG
        //echo "RESULT: $dropDownHtml";

        return $dropDownHtml;
    }

    public function validateAgeForItemModels(array $paramItemModelIds, $paramCustomerAge)
    {
        $retIsValidAge = true;
        foreach($paramItemModelIds AS $paramItemModelId)
        {
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $paramItemModelId);
            if($objItemModel->isAllowedAge($paramCustomerAge) === false)
            {
                $retIsValidAge = false;
                $itemModelDetails = $objItemModel->getExtendedDetails();
                if(!is_null($itemModelDetails))
                {
                    $paramItemModelTitle = $itemModelDetails['print_translated_manufacturer_name'].' '.$itemModelDetails['print_translated_item_model_name'];
                    $paramItemModelTitle .= ' '.esc_html($itemModelDetails['via_partner']);
                    $errorMessage = sprintf($this->lang->getText('LANG_ITEM_MODEL_AGE_S_ERROR_TEXT'), $paramItemModelTitle);
                    $this->saveAllMessages(array('error' => array($errorMessage)));
                } else
                {
                    $errorMessage = sprintf($this->lang->getText('LANG_ITEM_MODEL_WITH_ID_D_DOES_NOT_EXIST_ERROR_TEXT'), intval($paramItemModelId));
                    $this->saveAllMessages(array('error' => array($errorMessage)));
                }
            }
        }

        return $retIsValidAge;
    }


    /* --------------------------------------------------------------------------- */
    /* ----------------------- METHODS FOR ADMIN ACCESS ONLY --------------------- */
    /* --------------------------------------------------------------------------- */

    public function getTrustedAdminListHTML($paramPartnerId = -1)
    {
        $retHTML = '';
        $itemModelIds = $this->getIds("ALL", $paramPartnerId);
        foreach($itemModelIds AS $itemModelId)
        {
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
            $itemModelDetails = $objItemModel->getExtendedDetails();
            $objDepositManager = new ItemModelDepositManager($this->conf, $this->lang, $this->settings, $itemModelId);
            $itemDepositDetails = $objDepositManager->getDetails();
            $itemModel = array_merge($itemModelDetails, $itemDepositDetails);
            $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->settings, $itemModel['price_group_id']);
            $priceGroupDetails = $objPriceGroup->getDetailsWithPartner();

            if(!is_null($priceGroupDetails))
            {
                $printTranslatedPriceGroupName = $priceGroupDetails['print_translated_price_group_name'].' '.esc_html($priceGroupDetails['via_partner']);
            } else
            {
                $printTranslatedPriceGroupName = '<span style="color: darkred;">'.$this->lang->escHTML('LANG_NOT_SET_TEXT').'</span>';
            }

            if($itemModel['item_page_id'] != 0 && $itemModel['item_model_page_url'] != '')
            {
                $itemModelPageTitle = get_the_title($itemModel['item_page_id']);
                $linkTitle = sprintf($this->lang->getText('LANG_VIEW_PAGE_IN_NEW_WINDOW_TEXT'), $itemModelPageTitle);
                $trustedTranslatedManufacturerAndItemModelWithLinkHTML = '<a href="'.esc_url($itemModel['item_model_page_url']).'" target="_blank" title="'.esc_attr($linkTitle).'">';
                $trustedTranslatedManufacturerAndItemModelWithLinkHTML .= $itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_html($itemModel['via_partner']);
                $trustedTranslatedManufacturerAndItemModelWithLinkHTML .= '</a>';
            } else
            {
                $trustedTranslatedManufacturerAndItemModelWithLinkHTML = $itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_html($itemModel['via_partner']);
            }

            if($this->lang->canTranslateSQL())
            {
                $trustedTranslatedManufacturerAndItemModelWithLinkHTML .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$itemModel['print_item_model_name'].')</span>';
            }
            $displayInSlider = $this->lang->getText($itemModel['display_in_slider'] == 1 ? 'LANG_DISPLAYED_TEXT' : 'LANG_HIDDEN_TEXT');

            // HTML Output
            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$itemModelId.'</td>';
            $retHTML .= '<td>'.$itemModel['print_item_model_sku'].'</td>';
            $retHTML .= '<td>'.$itemModel['print_translated_class_name'].'</td>';
            $retHTML .= '<td>'.$itemModel['print_translated_attribute2_title'].'</td>';
            $retHTML .= '<td>'.$trustedTranslatedManufacturerAndItemModelWithLinkHTML.'</td>';
            $retHTML .= '<td style="white-space: nowrap">';
            $retHTML .= '<span style="cursor:pointer;" title="'.$this->lang->escAttr('LANG_MAX_ITEM_UNITS_PER_ORDER_TEXT').'">'.esc_html($itemModel['max_units_per_booking']).'</span> / ';
            $retHTML .= '<span style="cursor:pointer;font-weight:bold" title="'.$this->lang->escAttr('LANG_TOTAL_ITEM_UNITS_IN_STOCK_TEXT').'">'.esc_html($itemModel['units_in_stock']).'</span> ';
            $retHTML .= '</td>';
            $retHTML .= '<td>'.$itemModel['print_translated_attribute1_title'].'</td>';
            $retHTML .= '<td>'.$printTranslatedPriceGroupName.'</td>';
            if($this->depositsEnabled)
            {
                $retHTML .= '<td>'.$itemModel['unit_print']['fixed_deposit'].'</td>';
            }
            $retHTML .= '<td>'.$itemModel['print_min_driver_age'].'</td>';
            $retHTML .= '<td>'.esc_html($displayInSlider).'</td>';
            $retHTML .= '<td align="right">';
            if($objItemModel->canEdit())
            {
                $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-item-model&item_model_id='.$itemModelId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $retHTML .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteItemModel(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($itemModelId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $retHTML .= '--';
            }
            $retHTML .= '</td>';
            $retHTML .= '</tr>';
        }

        return $retHTML;
    }
}