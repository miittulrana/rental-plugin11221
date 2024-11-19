<?php
/**
 * Manufacturers Observer (no setup for single manufacturer)
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Manufacturer;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class ManufacturersObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings		            = array();
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

    public function getAllIds()
    {
        $searchSQL = "
            SELECT manufacturer_id
            FROM {$this->conf->getPrefix()}manufacturers
            WHERE blog_id='{$this->conf->getBlogId()}'
            ORDER BY manufacturer_title ASC
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResult;
    }

    public function getTrustedTranslatedDropdownOptionsHTML($paramSelectedManufacturerId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "")
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedManufacturerId, $paramDefaultValue, $paramDefaultLabel, true);
    }

    /**
     * @param int $paramSelectedManufacturerId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramTranslated
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedManufacturerId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "", $paramTranslated = false)
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $defaultSelected = $paramSelectedManufacturerId == $validDefaultValue ? ' selected="selected"' : '';
        $manufacturerIds = $this->getAllIds();

        $manufacturersHTML = '';
        $manufacturersHTML .= '<option value="'.$validDefaultValue.'"'.$defaultSelected.'>'.$sanitizedDefaultLabel.'</option>';
        foreach($manufacturerIds AS $manufacturerId)
        {
            $objManufacturer = new Manufacturer($this->conf, $this->lang, $this->settings, $manufacturerId);
            $manufacturerDetails = $objManufacturer->getDetails();
            $printManufacturerName = $paramTranslated ? $manufacturerDetails['print_translated_manufacturer_name'] : $manufacturerDetails['print_manufacturer_name'];
            $selected = $manufacturerDetails['manufacturer_id'] == $paramSelectedManufacturerId ? ' selected="selected"' : '';

            $manufacturersHTML .= '<option value="'.$manufacturerDetails['manufacturer_id'].'"'.$selected.'>'.$printManufacturerName.'</option>';
        }

        return $manufacturersHTML;
    }

    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    public function getTrustedAdminListHTML()
    {
        $manufacturersHTML = '';
        $manufacturerIds = $this->getAllIds();
        foreach ($manufacturerIds AS $manufacturerId)
        {
            $objManufacturer = new Manufacturer($this->conf, $this->lang, $this->settings, $manufacturerId);
            $manufacturerDetails = $objManufacturer->getDetails();

            $printTranslatedManufacturerName = $manufacturerDetails['print_translated_manufacturer_name'];
            if($this->lang->canTranslateSQL())
            {
                $printTranslatedManufacturerName .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$manufacturerDetails['print_manufacturer_name'].')</span>';
            }

            $manufacturersHTML .= '<tr>';
            $manufacturersHTML .= '<td style="width: 1%">'.$manufacturerId.'</td>';
            $manufacturersHTML .= '<td>'.$printTranslatedManufacturerName.'</td>';
            $manufacturersHTML .= '<td align="right">';
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_inventory'))
            {
                $manufacturersHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-manufacturer&amp;manufacturer_id='.$manufacturerId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $manufacturersHTML .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteManufacturer(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($manufacturerId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $manufacturersHTML .= '--';
            }
            $manufacturersHTML .= '</td>';
            $manufacturersHTML .= '</tr>';
        }

        return  $manufacturersHTML;
    }
}