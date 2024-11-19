<?php
/**
 * Feature Element
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Feature;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Feature extends AbstractStack implements StackInterface, ElementInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $featureId              = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramFeatureId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->featureId = StaticValidator::getValidValue($paramFeatureId, 'positive_integer', 0);
    }

    private function getDataFromDatabaseById($paramFeatureId, $paramColumns = array('*'))
    {
        $validFeatureId = StaticValidator::getValidPositiveInteger($paramFeatureId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);

        $sqlQuery = "
            SELECT {$validSelect}, display_in_item_list AS key_feature
            FROM {$this->conf->getPrefix()}features
            WHERE feature_id='{$validFeatureId}'
        ";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->featureId;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @note Do not translate title here - it is used for editing
     * @param bool $paramPrefillWhenNull
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->featureId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['feature_title'] = stripslashes($ret['feature_title']);

            // Retrieve translation
            $ret['translated_feature_title'] = $this->lang->getTranslated("fe{$ret['feature_id']}_feature_title", $ret['feature_title']);

            // Prepare output for print
            $ret['print_feature_title'] = esc_html($ret['feature_title']);
            $ret['print_translated_feature_title'] = esc_html($ret['translated_feature_title']);

            // Prepare output for edit
            $ret['edit_feature_title'] = esc_attr($ret['feature_title']); // for input field
        }

        return $ret;
    }

    /**
     * NOTE: If add_to_all_item_models is checked - this function will also add this feature to all item models
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validFeatureId = StaticValidator::getValidPositiveInteger($this->featureId, 0);
        $sanitizedFeatureTitle = isset($params['feature_title']) ? sanitize_text_field($params['feature_title']) : '';
        $validFeatureTitle = esc_sql($sanitizedFeatureTitle); // for sql query only
        $validKeyFeature = isset($params['key_feature']) ? 1 : 0;
        if($validFeatureId == 0)
        {
            $addToAllItemModels = isset($params['add_to_all_item_models']) ? true : false;
        } else
        {
            $addToAllItemModels = false;
        }

        $titleExistsQuery = "
            SELECT feature_id
            FROM {$this->conf->getPrefix()}features
            WHERE feature_title='{$validFeatureTitle}' AND feature_id!='{$validFeatureId}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $titleExists = $this->conf->getInternalWPDB()->get_row($titleExistsQuery, ARRAY_A);

        if(!is_null($titleExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_FEATURE_TITLE_EXISTS_ERROR_TEXT');
        }

        if($validFeatureId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}features SET
                feature_title='{$validFeatureTitle}', display_in_item_list='{$validKeyFeature}'
                WHERE feature_id='{$validFeatureId}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_FEATURE_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_FEATURE_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}features
                (
                    feature_title, display_in_item_list, blog_id
                ) VALUES
                (
                    '{$validFeatureTitle}', '{$validKeyFeature}', '{$this->conf->getBlogId()}'
                )
            ");

            if($saved)
            {
                // Get newly inserted feature id
                $validInsertedNewFeatureId = $this->conf->getInternalWPDB()->insert_id;

                // Update class tax id with newly inserted feature id for future work
                $this->featureId = $validInsertedNewFeatureId;

                $itemModels = $this->conf->getInternalWPDB()->get_results("
                    SELECT item_id
                    FROM {$this->conf->getPrefix()}items
                    WHERE blog_id='{$this->conf->getBlogId()}'
                ", ARRAY_A);

                if(!is_null($itemModels) && $addToAllItemModels == true)
                {
                    foreach($itemModels as $itemModel)
                    {
                        $this->conf->getInternalWPDB()->query("
                            INSERT INTO {$this->conf->getPrefix()}item_features
                            (
                                item_id, feature_id, blog_id
                            ) VALUES
                            (
                                '{$itemModel['item_model_id']}', '{$validInsertedNewFeatureId}', '{$this->conf->getBlogId()}'
                            )
                        ");
                    }
                }
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_FEATURE_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_FEATURE_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $featureDetails = $this->getDetails();
        if(!is_null($featureDetails))
        {
            $this->lang->register("fe{$this->featureId}_feature_title", $featureDetails['feature_title']);
            $this->okayMessages[] = $this->lang->getText('LANG_FEATURE_REGISTERED_TEXT');
        }
    }

    public function delete()
    {
        $validFeatureId = StaticValidator::getValidPositiveInteger($this->featureId, 0);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}features
            WHERE feature_id='{$validFeatureId}' AND blog_id='{$this->conf->getBlogId()}'
        ");
        if($deleted)
        {
            // Delete corresponding item features
            $this->conf->getInternalWPDB()->query("
                DELETE FROM {$this->conf->getPrefix()}item_features
                WHERE feature_id='{$validFeatureId}' AND blog_id='{$this->conf->getBlogId()}'
            ");
        }

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_FEATURE_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_FEATURE_DELETED_TEXT');
        }
    }
}