<?php
/**
 * ItemModel Options Observer

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

final class ItemModelOptionsObserver implements ObserverInterface
{
    private $conf 	            = null;
    private $lang 		        = null;
    private $debugMode 	        = 0;
    private $settings      = array();

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

    public function getAllIds($paramItemModelId = -1)
    {
        $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
        $sqlWhere = $validItemModelId == -1 ? "item_id>'0'" : "item_id='{$validItemModelId}'";
        $optionIds = $this->conf->getInternalWPDB()->get_col("
            SELECT option_id
            FROM {$this->conf->getPrefix()}options
            WHERE {$sqlWhere} AND blog_id='{$this->conf->getBlogId()}'
            ORDER BY option_name ASC
        ");

        return $optionIds;
    }


    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY *************************
    /*******************************************************************************/

    public function getTrustedAdminListHTML()
    {
        $itemModelList = '';

        $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->settings);
        $itemModelIds = $objItemModelsObserver->getAllIds($objItemModelsObserver->canShowOnlyPartnerOwned() ? get_current_user_id() : -1);

        $i = 0;
        foreach($itemModelIds AS $itemModelId)
        {
            $i++;
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
            $optionsList = $this->getTrustedAdminOptionsListByItemModelIdHTML($itemModelId, sprintf('%02d', $i) . ".");
            $itemModelDetails = $objItemModel->getExtendedDetails();

            if($optionsList != "")
            {
                // HTML OUTPUT
                $itemModelList .= '<tr>';
                $itemModelList .= '<td>'.sprintf('%02d', $i).'</td>';
                $itemModelList .= '<td>'.$itemModelDetails['print_translated_manufacturer_name'].' '.$itemModelDetails['print_translated_item_model_name'].' '.esc_html($itemModelDetails['via_partner']).'</td>';
                $itemModelList .= '<td>ID: '.$itemModelId.', '.$itemModelDetails['print_translated_class_name'].', '.$itemModelDetails['print_translated_attribute2_title'].'</td>';
                $itemModelList .= '<td>&nbsp;</td>';
                $itemModelList .= '</tr>';
                $itemModelList .= $optionsList;
            }
        }

        return  $itemModelList;
    }

    /**
     * @param $paramItemModelId
     * @param string $paramRowNumbersPrefix
     * @return string
     */
    private function getTrustedAdminOptionsListByItemModelIdHTML($paramItemModelId, $paramRowNumbersPrefix = "0.")
    {
        $optionList = '';
        $validRowNumberPrefix = esc_html(sanitize_text_field($paramRowNumbersPrefix));
        $optionIds = $this->getAllIds($paramItemModelId);

        $i = 0;
        foreach($optionIds AS $optionId)
        {
            $i++;
            $objOption = new ItemModelOption($this->conf, $this->lang, $this->settings, $optionId);
            $optionDetails = $objOption->getDetails();
            $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $optionDetails['item_model_id']);
            $itemModelDetails = $objItemModel->getDetails();
            if(!is_null($itemModelDetails))
            {
                $partnerId = $itemModelDetails['partner_id'];
            } else
            {
                $partnerId = 0;
            }

            $printTranslatedOptionName = $optionDetails['print_translated_option_name'];
            if($this->lang->canTranslateSQL())
            {
                $printTranslatedOptionName .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$optionDetails['print_option_name'].')</span>';
            }

            // HTML OUTPUT
            $optionList .= '<tr>';
            $optionList .= '<td>'.$validRowNumberPrefix.sprintf('%02d', $i).'</td>';
            $optionList .= '<td><strong>'.$printTranslatedOptionName.'</strong></td>';
            $optionList .= '<td align="right">';
            if($objOption->canEdit($partnerId))
            {
                $optionList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-item-model-option&amp;option_id='.$optionId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $optionList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-item-model-option&amp;delete_option='.$optionId.'&amp;noheader=true')).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $optionList .= '--';
            }
            $optionList .= '</td>';
            $optionList .= '</tr>';
        }

        return  $optionList;
    }
}