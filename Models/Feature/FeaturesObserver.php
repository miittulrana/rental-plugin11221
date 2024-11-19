<?php
/**
 * Features Observer (no setup for single feature)
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Feature;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class FeaturesObserver implements ObserverInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings		        = array();
    private $debugMode 	            = 0;

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

    public function getAllIds($paramKeyFeature = -1, $paramAllowedFeatureIds = array())
    {
        $validKeyFeature = StaticValidator::getValidInteger($paramKeyFeature, -1); // Supports '-1' (IGNORE)
        $validAllowedFeatureIds = StaticValidator::getValidArray($paramAllowedFeatureIds, 'positive_integer', 0);

        $sqlAdd = "";
        if(in_array($validKeyFeature, array(0, 1)))
        {
            $sqlAdd =" AND display_in_item_list='{$validKeyFeature}'";
        }
        if(sizeof($validAllowedFeatureIds) > 0)
        {
            $sqlAdd = " AND feature_id IN (".implode(", ", $validAllowedFeatureIds).")";
        }

        $searchSQL = "
            SELECT feature_id
            FROM {$this->conf->getPrefix()}features
            WHERE blog_id='{$this->conf->getBlogId()}'
            {$sqlAdd}
            ORDER BY display_in_item_list ASC, feature_title ASC
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResult;
    }

    public function getCheckboxes($paramItemModelId)
    {
        $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
        $i = 0;
        $validFeaturesIds = $this->getAllIds();

        $html = '<table width="100%"><tr>';
        foreach ($validFeaturesIds AS $validFeaturesId)
        {
            $objFeature = new Feature($this->conf, $this->lang, $this->settings, $validFeaturesId);
            $featureDetails = $objFeature->getDetails();
            $checked = '';
            if($validItemModelId > 0)
            {
                $itemFeatureData = $this->conf->getInternalWPDB()->get_row("
                    SELECT feature_id
                    FROM {$this->conf->getPrefix()}item_features
                    WHERE item_id='{$validItemModelId}' AND feature_id='{$validFeaturesId}' AND blog_id='{$this->conf->getBlogId()}'
                ", ARRAY_A);
                if(!is_null($itemFeatureData) && $itemFeatureData['feature_id'] == $validFeaturesId)
                {
                    $checked = 'checked="checked"';
                }
            }
            $html .= '<td><input type="checkbox" name="features[]" value="'.$validFeaturesId.'" '.$checked.' /><span> '.$featureDetails['print_feature_title'].'</span></td>';
            $i++;

            if($i == 2)
            {
                $html .= '</tr><tr>';
                $i = 0;
            }
        }
        $html .= '</tr></table>';

        return $html;
    }

    public function getTranslatedSelectedFeaturesByItemModelId($paramItemModelId, $showOnlyItemModelListFeatures = false)
    {
        return $this->getSelectedFeaturesByItemModelId($paramItemModelId, $showOnlyItemModelListFeatures, 1);
    }

    /**
     * @param $paramItemModelId
     * @param bool $showOnlyKeyFeatures
     * @param bool $paramTranslated
     * @return string|array
     */
    private function getSelectedFeaturesByItemModelId($paramItemModelId, $showOnlyKeyFeatures = false, $paramTranslated = false)
    {
        $validItemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
        $sqlAddForKeyFeatureOnly = $showOnlyKeyFeatures ? " AND f.display_in_item_list='1'" : "";
        $sqlQuery = "
			SELECT f.feature_id
			FROM {$this->conf->getPrefix()}item_features AS itf, {$this->conf->getPrefix()}features AS f
			WHERE itf.item_id = '{$validItemModelId}' AND f.feature_id = itf.feature_id AND itf.blog_id='{$this->conf->getBlogId()}'
			{$sqlAddForKeyFeatureOnly}
		";
        $featureIds = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        $features = array();
        foreach ($featureIds AS $featureId)
        {
            $objFeature = new Feature($this->conf, $this->lang, $this->settings, $featureId);
            $featureDetails = $objFeature->getDetails();
            $features[] = $featureDetails[$paramTranslated ? 'print_translated_feature_title' : 'print_feature_title'];
        }

        if($this->debugMode)
        {
            echo "<br />Item model id: {$validItemModelId}, Feature Ids: ".print_r($featureIds, true);
        }

        return $features;
    }



    /* --------------------------------------------------------------------------- */
    /* ----------------------- METHODS FOR ADMIN ACCESS ONLY --------------------- */
    /* --------------------------------------------------------------------------- */

    public function getTrustedAdminListHTML()
    {
        $retHTML = '';
        $featureIds = $this->getAllIds(-1, array());
        foreach ($featureIds AS $featureId)
        {
            $objFeature = new Feature($this->conf, $this->lang, $this->settings, $featureId);
            $featureDetails = $objFeature->getDetails();

            $printFeatureTitle = $featureDetails['print_translated_feature_title'];
            if($this->lang->canTranslateSQL())
            {
                $printFeatureTitle .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$featureDetails['print_feature_title'].')</span>';
            }

            $keyFeature = $this->lang->getText($featureDetails['key_feature'] == 1 ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT');
            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$printFeatureTitle.'</td>';
            $retHTML .= '<td>'.esc_html($keyFeature).'</td>';
            $retHTML .= '<td align="right">';
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_inventory'))
            {
                $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-feature&amp;feature_id='.$featureId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $retHTML .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteFeature(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($featureId).'\')">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $retHTML .= '--';
            }
            $retHTML .= '</td>';
            $retHTML .= '</tr>';
        }
        return  $retHTML;
    }
}