<?php
/**
 * Class Element
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Class_;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Class_ extends AbstractStack implements ElementInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $classId                  = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramClassId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->classId = StaticValidator::getValidValue($paramClassId, 'positive_integer', 0);
    }

    /**
     * @param $paramClassId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramClassId)
    {
        $validClassId = StaticValidator::getValidPositiveInteger($paramClassId, 0);

        $retData = $this->conf->getInternalWPDB()->get_row("
            SELECT body_type_id AS class_id, body_type_title AS class_name, body_type_order AS class_order, blog_id
            FROM {$this->conf->getPrefix()}body_types
            WHERE body_type_id='{$validClassId}'
        ", ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->classId;
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
        $ret = $this->getDataFromDatabaseById($this->classId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['class_name'] = stripslashes($ret['class_name']);

            // Retrieve translation
            $ret['translated_class_name'] = $this->lang->getTranslated("cl{$ret['class_id']}_class_name", $ret['class_name']);

            // Prepare output for print
            $ret['print_class_name'] = esc_html($ret['class_name']);
            $ret['print_translated_class_name'] = esc_html($ret['translated_class_name']);

            // Prepare output for edit
            $ret['edit_class_name'] = esc_attr($ret['class_name']); // for input field
        }

        // Add unclassified type result
        if($paramPrefillWhenNull && $this->classId == 0)
        {
            $ret = array(
                "class_id" => 0,
                "class_name" => $this->lang->getText('LANG_OTHER_TEXT'),
                "translated_class_name" => $this->lang->getText('LANG_OTHER_TEXT'),
                "print_class_name" => esc_html($this->lang->getText('LANG_OTHER_TEXT')),
                "print_translated_class_name" => esc_html($this->lang->getText('LANG_OTHER_TEXT')),
                "edit_class_name" => "",
            );
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
        $validClassId = StaticValidator::getValidPositiveInteger($this->classId, 0);
        $sanitizedClassName = sanitize_text_field($params['class_name']);
        $validClassName = esc_sql($sanitizedClassName); // for sql query only
        if(isset($params['class_order']) && StaticValidator::isPositiveInteger($params['class_order']))
        {
            $validClassOrder = StaticValidator::getValidPositiveInteger($params['class_order'], 1);
        } else
        {
            // SELECT MAX
            $sqlQuery = "
                SELECT MAX(body_type_order) AS max_order
                FROM {$this->conf->getPrefix()}body_types
                WHERE 1
            ";
            $maxOrderResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            $validClassOrder = !is_null($maxOrderResult) ? intval($maxOrderResult)+1 : 1;
        }

        $titleExistsQuery = "
            SELECT body_type_id
            FROM {$this->conf->getPrefix()}body_types
            WHERE body_type_title='{$validClassName}' AND body_type_id!='{$validClassId}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $titleExists = $this->conf->getInternalWPDB()->get_row($titleExistsQuery, ARRAY_A);

        if(!is_null($titleExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_CLASS_TITLE_EXISTS_ERROR_TEXT');
        }

        if($validClassId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}body_types SET
                  body_type_title='{$validClassName}', body_type_order='{$validClassOrder}'
                  WHERE body_type_id='{$validClassId}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_CLASS_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_CLASS_UPDATED_TEXT');
            }

        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}body_types
                (body_type_title, body_type_order, blog_id)
                VALUES
                ('{$validClassName}', '{$validClassOrder}', '{$this->conf->getBlogId()}')
            ");

            if($saved)
            {
                // Get newly inserted class id
                $validInsertedNewClassId = $this->conf->getInternalWPDB()->insert_id;

                // Update the core body type id for future use
                $this->classId = $validInsertedNewClassId;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_CLASS_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_CLASS_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $classDetails = $this->getDetails();
        if(!is_null($classDetails))
        {
            $this->lang->register("cl{$this->classId}_class_name", $classDetails['class_name']);
            $this->okayMessages[] = $this->lang->getText('LANG_CLASS_REGISTERED_TEXT');
        }
    }

    /**
     * @return false|int
     */
    public function delete()
    {
        $validClassId = StaticValidator::getValidPositiveInteger($this->classId, 0);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}body_types
            WHERE body_type_id='{$validClassId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_CLASS_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_CLASS_DELETED_TEXT');
        }

        return $deleted;
    }
}