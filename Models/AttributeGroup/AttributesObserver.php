<?php
/**
 * Attributes Observer (no setup for single attribute)
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\AttributeGroup;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class AttributesObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings 		        = array();
    protected $debugMode 	            = 0;

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
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @param int (required) $paramAttributeGroupId
     * @return array
     */
    public function getAllIds($paramAttributeGroupId)
    {
        $searchSQL = "";
        switch($paramAttributeGroupId)
        {
            case 1:
                $searchSQL = "
                    SELECT fuel_type_id
                    FROM {$this->conf->getPrefix()}fuel_types
                    WHERE blog_id='{$this->conf->getBlogId()}'
                    ORDER BY fuel_type_title ASC
                ";
                break;
            case 2:
                $searchSQL = "
                    SELECT transmission_type_id
                    FROM {$this->conf->getPrefix()}transmission_types
                    WHERE blog_id='{$this->conf->getBlogId()}'
                    ORDER BY transmission_type_title ASC
                ";
                break;
        }

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = array();
        if($searchSQL != "")
        {
            $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);
        }

        return $searchResult;
    }

    public function getTrustedTranslatedDropdownOptionsHTML($paramAttributeGroupId, $paramSelectedAttributeId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "")
    {
        return $this->getTrustedDropdownOptionsHTML($paramAttributeGroupId, $paramSelectedAttributeId, $paramDefaultValue, $paramDefaultLabel, true);
    }

    /**
     * Get attributes dropdown options
     * @param $paramAttributeGroupId
     * @param int $paramSelectedAttributeId - for edit mode
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramTranslated
     * @return string dropdown html
     */
    public function getTrustedDropdownOptionsHTML($paramAttributeGroupId, $paramSelectedAttributeId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "", $paramTranslated = false)
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $defaultSelected = $paramSelectedAttributeId == $validDefaultValue ? ' selected="selected"' : '';
        $attributeIds = $this->getAllIds($paramAttributeGroupId);

        $attributesHTML = '';
        $attributesHTML .= '<option value="'.$validDefaultValue.'"'.$defaultSelected.'>'.$sanitizedDefaultLabel.'</option>';
        foreach($attributeIds AS $attributeId)
        {
            $objAttribute = null;
            switch($paramAttributeGroupId)
            {
                case 1:
                    $objAttribute = new Attribute1($this->conf, $this->lang, $this->settings, $attributeId);
                    break;
                case 2:
                    $objAttribute = new Attribute2($this->conf, $this->lang, $this->settings, $attributeId);
                    break;
            }
            if(!is_null($objAttribute))
            {
                $attributeDetails = $objAttribute->getDetails();
                $attributeTitle = $paramTranslated ? $attributeDetails['translated_attribute_title'] : $attributeDetails['attribute_title'];
                $selected = $attributeDetails['attribute_id'] == $paramSelectedAttributeId ? ' selected="selected"' : '';

                $attributesHTML .= '<option value="'.$attributeDetails['attribute_id'].'"'.$selected.'>'.$attributeTitle.'</option>';
            }
        }

        return $attributesHTML;
    }

    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    /**
     * @param $paramAttributeGroupId
     * @return string
     */
    public function getTrustedAdminListByAttributeGroupIdHTML($paramAttributeGroupId)
    {
        $validAttributeGroupId = StaticValidator::getValidInteger($paramAttributeGroupId, -1);
        $attributesHTML = '';
        $attributeIds = $this->getAllIds($paramAttributeGroupId);
        foreach ($attributeIds AS $attributeId)
        {
            $objAttribute = null;
            switch($validAttributeGroupId)
            {
                case 1:
                    $objAttribute = new Attribute1($this->conf, $this->lang, $this->settings, $attributeId);
                    break;
                case 2:
                    $objAttribute = new Attribute2($this->conf, $this->lang, $this->settings, $attributeId);
                    break;
            }
            if(!is_null($objAttribute))
            {
                $attributeDetails = $objAttribute->getDetails();

                $printTranslatedAttributeTitle = $attributeDetails['print_translated_attribute_title'];
                if($this->lang->canTranslateSQL())
                {
                    $printTranslatedAttributeTitle .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$attributeDetails['print_attribute_title'].')</span>';
                }

                $attributesHTML .= '<tr>';
                $attributesHTML .= '<td style="width: 1%">'.$attributeId.'</td>';
                $attributesHTML .= '<td>'.$printTranslatedAttributeTitle.'</td>';
                $attributesHTML .= '<td align="right">';
                if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_inventory'))
                {
                    $attributesHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-attribute&amp;attribute_id='.$attributeId.'&attribute_group_id='.$validAttributeGroupId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                    $attributesHTML .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteAttribute(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($attributeId).'\', \''.esc_js($validAttributeGroupId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
                } else
                {
                    $attributesHTML .= '--';
                }
                $attributesHTML .= '</td>';
                $attributesHTML .= '</tr>';
            }
        }

        return $attributesHTML;
    }
}