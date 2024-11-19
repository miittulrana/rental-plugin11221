<?php
/**
 * Extras Observer (no setup for single extra)

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\ExtraDiscount;
use FleetManagement\Models\Extra\ExtraDiscountsObserver;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Extra\ExtraOption;
use FleetManagement\Models\Extra\ExtraOptionsObserver;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtraPriceManager;

final class ExtrasObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings		            = array();
    protected $debugMode 	            = 0;
    protected $depositsEnabled          = false;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        if(isset($paramSettings['conf_deposit_enabled']))
        {
            // Set deposit status
            $this->depositsEnabled = StaticValidator::getValidPositiveInteger($paramSettings['conf_deposit_enabled'], 1) == 1 ? true : false;
        }
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getIdBySKU($paramExtraSKU)
    {
        $retExtraId = 0;
        $validExtraSKU = esc_sql(sanitize_text_field($paramExtraSKU)); // For sql query only

        $extraData = $this->conf->getInternalWPDB()->get_row("
                SELECT extra_id
                FROM {$this->conf->getPrefix()}extras
                WHERE extra_sku='{$validExtraSKU}' AND blog_id='{$this->conf->getBlogId()}'
            ", ARRAY_A);
        if(!is_null($extraData))
        {
            $retExtraId = $extraData['extra_id'];
        }

        return $retExtraId;
    }

    /**
     * Get an extra which has amount > 0 of units in stock
     * @param int $paramPartnerId
     * @param int $paramExtraId
     * @param int $paramItemModelId
     * @return mixed
     */
    public function getAvailableIds($paramPartnerId = -1, $paramExtraId = -1, $paramItemModelId = -1)
    {
        return $this->getIds("AVAILABLE", $paramPartnerId, $paramExtraId, $paramItemModelId);
    }

    public function getAllIds($paramPartnerId = -1, $paramExtraId = -1, $paramItemModelId = -1)
    {
        return $this->getIds("ALL", $paramPartnerId, $paramExtraId, $paramItemModelId);
    }

    /**
     * @param string $displayMode - "ALL" or "AVAILABLE"
     * @param int $paramPartnerId
     * @param int $paramExtraId
     * @param int $paramItemModelId
     * @return array
     */
    private function getIds($displayMode = "ALL", $paramPartnerId = -1, $paramExtraId = -1, $paramItemModelId = -1)
    {
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1);
        $validExtraId = StaticValidator::getValidInteger($paramExtraId, -1);
        $validItemModelId = StaticValidator::getValidInteger($paramItemModelId, -1);

        $sqlAdd = "";
        if($displayMode == "AVAILABLE")
        {
            $sqlAdd .= " AND units_in_stock > 0";
        }

        // Partner field
        if($validPartnerId >= 0)
        {
            $sqlAdd .= " AND partner_id='{$validPartnerId}'";
        }

        // Extra field
        if($validExtraId > 0)
        {
            $sqlAdd .= " AND extra_id='{$validExtraId}'";
        }

        // Item Model field
        if($validItemModelId >= 0)
        {
            $sqlAdd .= " AND item_id='{$validItemModelId}'";
        }

        $searchSQL = "
            SELECT extra_id
            FROM {$this->conf->getPrefix()}extras
            WHERE blog_id='{$this->conf->getBlogId()}' {$sqlAdd}
            ORDER BY extra_name ASC
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResult;
    }

    /**
     * Delete corresponding extras by item id
     * @param $paramItemModelId
     * @return bool
     */
    public function explicitDeleteByItemModelId($paramItemModelId)
    {
        $retAllDeleted = true;
        // Delete corresponding extras by item id
        $extraIds = $this->getAllIds(-1, -1, $paramItemModelId);
        foreach ($extraIds AS $extraId)
        {
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
            $deleted = $objExtra->delete();
            if($deleted === false || $deleted === 0)
            {
                $retAllDeleted = false;
            } else
            {
                // Delete corresponding discounts
                $objDiscountsObserver = new ExtraDiscountsObserver($this->conf, $this->lang, $this->settings);
                $discountIds = $objDiscountsObserver->getAllIds("", $extraId);
                foreach ($discountIds AS $discountId)
                {
                    $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->settings, $discountId);
                    $objDiscount->delete();
                }

                // Delete corresponding extra options
                $objOptionsObserver = new ExtraOptionsObserver($this->conf, $this->lang, $this->settings);
                $optionIds = $objOptionsObserver->getAllIds($extraId);
                foreach ($optionIds AS $optionId)
                {
                    $objOption = new ExtraOption($this->conf, $this->lang, $this->settings, $optionId);
                    $objOption->delete();
                }
            }
        }

        return $retAllDeleted;
    }

    public function canShowOnlyPartnerOwned()
    {
        $canEditOwnExtras = current_user_can('manage_'.$this->conf->getExtPrefix().'own_extras');
        $canEditAllExtras = current_user_can('manage_'.$this->conf->getExtPrefix().'all_extras');
        $onlyPartnerOwned = $canEditOwnExtras == true && $canEditAllExtras == false;

        return $onlyPartnerOwned;
    }

    public function getTrustedTranslatedExtrasDropdownOptionsHTML_ByPartnerId($paramPartnerId = -1, $paramSelectedExtraId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowExtraId = true)
    {
        return $this->getTrustedExtrasDropdownOptionsHTML($paramSelectedExtraId, $paramDefaultValue, $paramDefaultLabel, $paramShowExtraId, true, $paramPartnerId);
    }

    public function getTrustedExtrasDropdownOptionsHTML_ByPartnerId($paramPartnerId = -1, $paramSelectedExtraId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowExtraId = true)
    {
        return $this->getTrustedExtrasDropdownOptionsHTML($paramSelectedExtraId, $paramDefaultValue, $paramDefaultLabel, $paramShowExtraId, false, $paramPartnerId);
    }

    public function getTrustedTranslatedExtrasDropdownOptionsHTML($paramSelectedExtraId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowExtraId = true)
    {
        return $this->getTrustedExtrasDropdownOptionsHTML($paramSelectedExtraId, $paramDefaultValue, $paramDefaultLabel, $paramShowExtraId, true, -1);
    }

    /**
     * @param int $paramSelectedExtraId
     * @param string $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramShowExtraId
     * @param bool $paramTranslated
     * @param int $paramPartnerId
     * @return string
     */
    public function getTrustedExtrasDropdownOptionsHTML($paramSelectedExtraId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowExtraId = true, $paramTranslated = false, $paramPartnerId = -1)
    {
        $printDefaultValue = esc_html(sanitize_text_field($paramDefaultValue));
        $printDefaultLabel = esc_html(sanitize_text_field($paramDefaultLabel));
        $extraHTML = '';
        if($paramDefaultValue != "" || $paramDefaultLabel != "")
        {
            $defaultSelected = $paramSelectedExtraId == $paramDefaultValue ? ' selected="selected"' : '';
            $extraHTML .= '<option value="'.$printDefaultValue.'"'.$defaultSelected.'>'.$printDefaultLabel.'</option>';
        }

        $extraIds = $this->getAllIds($paramPartnerId);
        foreach ($extraIds AS $extraId)
        {
            // Process extra details
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
            $extraDetails = $objExtra->getDetailsWithItemAndPartner();

            if($paramTranslated)
            {
                $printTitle = esc_html($extraDetails['translated_extra_name_with_dependant_item_model']);
            } else
            {
                $printTitle = esc_html($extraDetails['translated_extra_name_with_dependant_item_model']);
            }

            if($paramShowExtraId)
            {
                $printTitle .= " (ID=".$extraDetails['extra_id'].")";
            }
            if($paramSelectedExtraId == $extraDetails['extra_id'])
            {
                $extraHTML .= '<option value='.$extraDetails['extra_id'].' selected="selected">'.$printTitle.'</option>';
            } else
            {
                $extraHTML .= '<option value='.$extraDetails['extra_id'].'>'.$printTitle.'</option>';
            }
        }

        return $extraHTML;
    }


    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    public function getTrustedAdminListHTML()
    {
        $extraList = '';

        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->settings);
        $taxPercentage = $objTaxManager->getTaxPercentage(0, 0);

        $extraIds = $this->getAllIds($this->canShowOnlyPartnerOwned() ? get_current_user_id() : -1);
        foreach($extraIds AS $extraId)
        {
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
            $canEdit = $objExtra->canEdit();
            if($canEdit || current_user_can('view_'.$this->conf->getExtPrefix().'all_extras'))
            {
                $objPriceManager = new ExtraPriceManager($this->conf, $this->lang, $this->settings, $extraId, $taxPercentage);
                $extraDetails = $objExtra->getDetailsWithItemAndPartner();
                $extraPriceDetails = $objPriceManager->getMinimalPriceDetails();
                $extra = array_merge($extraDetails, $extraPriceDetails);

                $printTranslatedExtraName = esc_html($extraDetails['translated_extra_name_with_dependant_item_model']).' '.esc_html($extraDetails['via_partner']);
                if($this->lang->canTranslateSQL())
                {
                    $printTranslatedExtraName .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$extraDetails['print_extra_name'].')</span>';
                }

                $printUnitsRange  = '<span style="cursor:pointer;" title="'.$this->lang->getText('LANG_MAX_EXTRA_UNITS_PER_ORDER_TEXT').'">'.$extra['max_units_per_booking'].'</span> / ';
                $printUnitsRange .= '<span style="cursor:pointer;font-weight:bold" title="'.$this->lang->getText('LANG_TOTAL_EXTRA_UNITS_IN_STOCK_TEXT').'">'.$extra['units_in_stock'].'</span> ';
                $extraList .= '<tr>';
                $extraList .= '<td>'.$extra['extra_id'].'</td>';
                $extraList .= '<td>'.$extra['print_extra_sku'].'</td>';
                $extraList .= '<td>'.$printTranslatedExtraName.'</td>';
                $extraList .= '<td>'.$printUnitsRange.'</td>';
                $extraList .= '<td>'.$extra['unit_print']['subtotal_price']." / ".$extra['time_ext_long_print'].'</td>';
                if($this->depositsEnabled)
                {
                    $extraList .= '<td>'.$extra['unit_print']['fixed_deposit'].'</td>';
                }
                $extraList .= '<td align="right">';
                if($canEdit)
                {
                    $extraList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra&amp;extra_id='.$extraId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                    $extraList .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteExtra(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($extraId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
                } else
                {
                    $extraList .= '--';
                }
                $extraList .= '</td>';
                $extraList .= '</tr>';
            }
        }

        return  $extraList;
    }
}