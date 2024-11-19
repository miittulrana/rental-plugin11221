<?php
/**
 * Classes Observer (no setup for single body type)
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Class_;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class ClassesObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings		            = array();
    protected $debugMode 	            = 0;

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

    public function getAllIds($includeUnclassified = false)
    {
        $searchSQL = "
            SELECT body_type_id AS class_id
            FROM {$this->conf->getPrefix()}body_types
            WHERE blog_id='{$this->conf->getBlogId()}'
            ORDER BY body_type_order ASC, body_type_title ASC
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        // Add unclassified types results
        if($includeUnclassified)
        {
            $searchResult[] = 0;
        }

        return $searchResult;
    }

    public function getTrustedTranslatedDropdownOptionsHTML($paramSelectedClassId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "")
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedClassId, $paramDefaultValue, $paramDefaultLabel, true);
    }

    /**
     * Get car body type - sedan, compact, jeep etc.
     * @param int $paramSelectedClassId - for edit mode
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramTranslated
     * @return string type drop-down html
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedClassId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "", $paramTranslated = false)
    {

        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $defaultSelected = $paramSelectedClassId == $validDefaultValue ? ' selected="selected"' : '';

        $classesHTML = '';
        $classesHTML .= '<option value="'.$validDefaultValue.'"'.$defaultSelected.'>'.$sanitizedDefaultLabel.'</option>';
        $classIds = $this->getAllIds();
        foreach($classIds AS $classId)
        {
            $objClass = new Class_($this->conf, $this->lang, $this->settings, $classId);
            $classDetails = $objClass->getDetails();
            $className = $paramTranslated ? $classDetails['translated_class_name'] : $classDetails['class_name'];
            $selected = $classDetails['class_id'] == $paramSelectedClassId ? ' selected="selected"' : '';

            $classesHTML .= '<option value="'.$classDetails['class_id'].'"'.$selected.'>'.$className.'</option>';
        }

        return $classesHTML;
    }

    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    public function getTrustedAdminListHTML()
    {
        $classesHTML = '';
        $classIds = $this->getAllIds();
        foreach ($classIds AS $classId)
        {
            $objClass = new Class_($this->conf, $this->lang, $this->settings, $classId);
            $classDetails = $objClass->getDetails();

            $printTranslatedClassTitle = $classDetails['print_translated_class_name'];
            if($this->lang->canTranslateSQL())
            {
                $printTranslatedClassTitle .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$classDetails['print_class_name'].')</span>';
            }

            $classesHTML .= '<tr>';
            $classesHTML .= '<td>'.$classId.'</td>';
            $classesHTML .= '<td>'.$printTranslatedClassTitle.'</td>';
            $classesHTML .= '<td style="text-align: center">'.$classDetails['class_order'].'</td>';
            $classesHTML .= '<td align="right">';
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_inventory'))
            {
                $classesHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-class&amp;class_id='.$classId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $classesHTML .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteClass(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($classId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $classesHTML .= '--';
            }
            $classesHTML .= '</td>';
            $classesHTML .= '</tr>';
        }

        return $classesHTML;
    }
}