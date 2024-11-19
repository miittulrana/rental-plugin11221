<?php
/**
 * Extra Options Observer

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class ExtraOptionsObserver implements ObserverInterface
{
    private $conf 	            = null;
    private $lang 		        = null;
    private $debugMode 	        = 0;
    private $settings           = array();

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

    public function getAllIds($paramExtraId = -1)
    {
        $validExtraId = StaticValidator::getValidPositiveInteger($paramExtraId, 0);
        $sqlWhere = $validExtraId == -1 ? "extra_id>'0'" : "extra_id='{$validExtraId}'";
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
        $extraList = '';

        $objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, $this->settings);
        $extraIds = $objExtrasObserver->getAllIds($objExtrasObserver->canShowOnlyPartnerOwned() ? get_current_user_id() : -1);

        $i = 0;
        foreach($extraIds AS $extraId)
        {
            $i++;
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
            $optionsList = $this->getTrustedAdminOptionsListByExtraIdHTML($extraId, sprintf('%02d', $i) . ".");
            $extraDetails = $objExtra->getDetailsWithItemAndPartner();

            if($optionsList != "")
            {
                // HTML OUTPUT
                $extraList .= '<tr>';
                $extraList .= '<td>'.sprintf('%02d', $i).'</td>';
                $extraList .= '<td>'.esc_html($extraDetails['translated_extra_name_with_dependant_item_model']).' '.esc_html($extraDetails['via_partner']).'</td>';
                $extraList .= '<td>ID: '.$extraId.'</td>';
                $extraList .= '<td>&nbsp;</td>';
                $extraList .= '</tr>';
                $extraList .= $optionsList;
            }
        }

        return  $extraList;
    }

    /**
     * @param $paramExtraId
     * @param string $paramRowNumbersPrefix
     * @return string
     * @internal param bool $paramShowRowNumbers
     */
    private function getTrustedAdminOptionsListByExtraIdHTML($paramExtraId, $paramRowNumbersPrefix = "0.")
    {
        $optionList = '';
        $validRowNumberPrefix = esc_html(sanitize_text_field($paramRowNumbersPrefix));
        $optionIds = $this->getAllIds($paramExtraId);

        $i = 0;
        foreach($optionIds AS $optionId)
        {
            $i++;
            $objOption = new ExtraOption($this->conf, $this->lang, $this->settings, $optionId);
            $optionDetails = $objOption->getDetails();
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $optionDetails['extra_id']);
            $extraDetails = $objExtra->getDetails();
            if(!is_null($extraDetails))
            {
                $partnerId = $extraDetails['partner_id'];
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
                $optionList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-option&amp;option_id='.$optionId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $optionList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-option&amp;delete_option='.$optionId.'&amp;noheader=true')).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
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