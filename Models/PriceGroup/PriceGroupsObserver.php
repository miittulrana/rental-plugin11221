<?php
/**
 * Price Groups Observer (no setup for single price group)
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PriceGroup;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class PriceGroupsObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings 	            = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getAllIds($paramPartnerId = -1)
    {
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1);

        $sqlAdd = "";
        if($validPartnerId >= 0)
        {
            $sqlAdd = "AND partner_id='{$validPartnerId}'";
        }

        $sqlQuery = "
            SELECT price_group_id
            FROM {$this->conf->getPrefix()}price_groups
            WHERE blog_id='{$this->conf->getBlogId()}' {$sqlAdd}
            ORDER BY price_group_name ASC
        ";

        $ids = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        return $ids;
    }

    public function canShowOnlyPartnerOwned()
    {
        $canEditOwnItems = current_user_can('manage_'.$this->conf->getExtPrefix().'own_items');
        $canEditAllItems = current_user_can('manage_'.$this->conf->getExtPrefix().'all_items');
        $onlyPartnerOwned = $canEditOwnItems == true && $canEditAllItems == false;

        return $onlyPartnerOwned;
    }

    public function getTrustedTranslatedDropdownOptionsHTML_ByPartnerId($paramPartnerId, $paramSelectedPriceGroupId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowPriceGroupId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedPriceGroupId, $paramDefaultValue, $paramDefaultLabel, $paramShowPriceGroupId, true, $paramPartnerId);
    }

    public function getTrustedDropdownOptionsHTML_ByPartnerId($paramPartnerId, $paramSelectedPriceGroupId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowPriceGroupId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedPriceGroupId, $paramDefaultValue, $paramDefaultLabel, $paramShowPriceGroupId, false, $paramPartnerId);
    }

    public function getTrustedTranslatedDropdownOptionsHTML($paramSelectedPriceGroupId = 0, $paramDefaultValue = "", $paramDefaultLabel = "", $paramShowPriceGroupId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedPriceGroupId, $paramDefaultValue, $paramDefaultLabel, $paramShowPriceGroupId, true, -1);
    }

    /**
     * @param int $paramSelectedPriceGroupId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramShowPriceGroupId
     * @param bool $paramTranslated
     * @param int $paramPartnerId
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedPriceGroupId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramShowPriceGroupId = true, $paramTranslated = false, $paramPartnerId = -1)
    {
        $validDefaultValue = StaticValidator::getValidPositiveInteger($paramDefaultValue, 0);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $defaultSelected = $paramSelectedPriceGroupId == $validDefaultValue ? ' selected="selected"' : '';
        $priceGroupHTML = '<option value="'.$validDefaultValue.'"'.$defaultSelected.'>'.$sanitizedDefaultLabel.'</option>';

        $priceGroupIds = $this->getAllIds($paramPartnerId);
        foreach ($priceGroupIds AS $priceGroupId)
        {
            // Process full item details
            $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->settings, $priceGroupId);
            $priceGroupDetails = $objPriceGroup->getDetailsWithPartner();

            $printTitle = $priceGroupDetails[$paramTranslated ? 'print_translated_price_group_name' : 'print_price_group_name'];
            $printTitle .= ' '.esc_html($priceGroupDetails['via_partner']);
            if($paramShowPriceGroupId)
            {
                $printTitle .= " (ID=".$priceGroupDetails['price_group_id'].")";
            }
            $selected = $paramSelectedPriceGroupId == $priceGroupDetails['price_group_id'] ? ' selected="selected"' : '';

            $priceGroupHTML .= '<option value="'.$priceGroupDetails['price_group_id'].'"'.$selected.'>'.$printTitle.'</option>';
        }

        return $priceGroupHTML;
    }


    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

	public function getTrustedAdminListHTML()
	{
        $getHtml = '';

        $priceGroupIds = $this->getAllIds($this->canShowOnlyPartnerOwned() ? get_current_user_id() : -1);
		foreach($priceGroupIds AS $priceGroupId)
		{
            $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->settings, $priceGroupId);
            $priceGroupDetails = $objPriceGroup->getDetailsWithPartner();
            $printTranslatedPriceGroupName = $priceGroupDetails['print_translated_price_group_name'].' '.esc_html($priceGroupDetails['via_partner']);
            if($this->lang->canTranslateSQL())
            {
                $printTranslatedPriceGroupName .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$priceGroupDetails['print_price_group_name'].')</span>';
            }

			// HTML OUTPUT
			$getHtml .= '<tr>';
			$getHtml .= '<td>'.$priceGroupId.'</td>';
			$getHtml .= '<td>'.$printTranslatedPriceGroupName.'</td>';
            $getHtml .= '<td align="right">';
            if($objPriceGroup->canEdit())
            {
                $getHtml .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-group&amp;price_group_id='.$priceGroupId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $getHtml .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deletePriceGroup(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($priceGroupId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $getHtml .= '--';
            }
            $getHtml .= '</td>';
            $getHtml .= '</tr>';

		}
		return  $getHtml;
	}
}