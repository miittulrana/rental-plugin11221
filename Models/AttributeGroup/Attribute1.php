<?php
/**
 * Attribute Element
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\AttributeGroup;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Attribute1 extends AbstractStack implements ElementInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $attributeId              = 0;

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramAttributeId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramAttributeId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->attributeId = StaticValidator::getValidValue($paramAttributeId, 'positive_integer', 0);
    }

    /**
     * @param $paramAttributeId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramAttributeId)
    {
        $validAttributeId = StaticValidator::getValidPositiveInteger($paramAttributeId, 0);

        $retData = $this->conf->getInternalWPDB()->get_row("
            SELECT fuel_type_id AS attribute_id, fuel_type_title AS attribute_title, blog_id
            FROM {$this->conf->getPrefix()}fuel_types
            WHERE fuel_type_id='{$validAttributeId}'
        ", ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->attributeId;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @note Do not translate title here - it is used for editing
     * @param bool $paramPrefillWhenNull - not used
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->attributeId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['attribute_title'] = stripslashes($ret['attribute_title']);

            // Retrieve translation
            $ret['translated_attribute_title'] = $this->lang->getTranslated("at1_{$ret['attribute_id']}_attribute_title", $ret['attribute_title']);

            // Prepare output for print
            $ret['print_attribute_title'] = esc_html($ret['attribute_title']);
            $ret['print_translated_attribute_title'] = esc_html($ret['translated_attribute_title']);

            // Prepare output for edit
            $ret['edit_attribute_title'] = esc_attr($ret['attribute_title']); // for input field
        }

        return $ret;
    }

    /**
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validAttributeId = StaticValidator::getValidPositiveInteger($this->attributeId, 0);
        $sanitizedAttributeTitle = isset($params['attribute_title']) ? sanitize_text_field($params['attribute_title']) : '';
        $validAttributeTitle = esc_sql($sanitizedAttributeTitle); // for sql query only

        $titleExistsQuery = "
            SELECT fuel_type_id
            FROM {$this->conf->getPrefix()}fuel_types
            WHERE fuel_type_title='{$validAttributeTitle}' AND fuel_type_id!='{$validAttributeId}'
            AND blog_id='{$this->conf->getBlogId()}'
        ";
        $titleExists = $this->conf->getInternalWPDB()->get_row($titleExistsQuery, ARRAY_A);

        if(!is_null($titleExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_ATTRIBUTE_TITLE_EXISTS_ERROR_TEXT');
        }

        if($validAttributeId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}fuel_types SET
                  fuel_type_title='{$validAttributeTitle}'
                  WHERE fuel_type_id='{$validAttributeId}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_ATTRIBUTE_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_ATTRIBUTE_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}fuel_types
                (fuel_type_title, blog_id)
                VALUES
                ('{$validAttributeTitle}', '{$this->conf->getBlogId()}')
            ");

            if($saved)
            {
                // Get newly inserted attribute id
                $validInsertedNewAttributeId = $this->conf->getInternalWPDB()->insert_id;

                // Update attribute id with newly inserted id for future work
                $this->attributeId = $validInsertedNewAttributeId;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_ATTRIBUTE_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_ATTRIBUTE_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $attributeDetails = $this->getDetails();
        if(!is_null($attributeDetails))
        {
            $this->lang->register("at1_{$this->attributeId}_attribute_title", $attributeDetails['attribute_title']);
            $this->okayMessages[] = $this->lang->getText('LANG_ATTRIBUTE_REGISTERED_TEXT');
        }
    }

    /**
     * @return false|int
     */
    public function delete()
    {
        $validAttributeId = StaticValidator::getValidPositiveInteger($this->attributeId, 0);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}fuel_types
            WHERE fuel_type_id='{$validAttributeId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ATTRIBUTE_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ATTRIBUTE_DELETED_TEXT');
        }

        return $deleted;
    }
}