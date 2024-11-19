<?php
/**
 * Manufacturer Element
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Manufacturer;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Manufacturer extends AbstractStack implements StackInterface, ElementInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $manufacturerId         = 0;
    private $thumbWidth	            = 205;
    private $thumbHeight		    = 205;

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramManufacturerId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramManufacturerId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->manufacturerId = StaticValidator::getValidValue($paramManufacturerId, 'positive_integer', 0);

        if(isset($paramSettings['conf_manufacturer_thumb_w'], $paramSettings['conf_manufacturer_thumb_h']))
        {
            // Set image dimensions
            $this->thumbWidth = StaticValidator::getValidPositiveInteger($paramSettings['conf_manufacturer_thumb_w'], 205);
            $this->thumbHeight = StaticValidator::getValidPositiveInteger($paramSettings['conf_manufacturer_thumb_h'], 205);
        }
    }

    private function getDataFromDatabaseById($paramManufacturerId, $paramColumns = array('*'))
    {
        $validManufacturerId = StaticValidator::getValidPositiveInteger($paramManufacturerId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);

        $sqlQuery = "
            SELECT {$validSelect}, manufacturer_title AS manufacturer_name
            FROM {$this->conf->getPrefix()}manufacturers
            WHERE manufacturer_id='{$validManufacturerId}'
        ";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->manufacturerId;
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
        $ret = $this->getDataFromDatabaseById($this->manufacturerId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['manufacturer_name'] = stripslashes($ret['manufacturer_name']);
            $ret['manufacturer_logo'] = stripslashes($ret['manufacturer_logo']);

            // Note: providing exact file name is important here, because then the system will correctly decide
            //       from which exact folder to load that file, as some demo images can be cross-extensional
            if($ret['demo_manufacturer_logo'] == 1)
            {
                $logoFolder = $this->conf->getRouting()->getDemoGalleryURL($ret['manufacturer_logo'], false);
            } else
            {
                $logoFolder = $this->conf->getGlobalGalleryURL();
            }

            // Retrieve translation
            $ret['translated_manufacturer_name'] = $this->lang->getTranslated("ma{$ret['manufacturer_id']}_manufacturer_name", $ret['manufacturer_name']);

            // Extend with additional rows
            $ret['manufacturer_thumb_url'] = $ret['manufacturer_logo'] != "" ? $logoFolder."thumb_".$ret['manufacturer_logo'] : "";
            $ret['manufacturer_logo_url'] = $ret['manufacturer_logo'] != "" ? $logoFolder.$ret['manufacturer_logo'] : "";

            // Prepare output for print
            $ret['print_manufacturer_name'] = esc_html($ret['manufacturer_name']);
            $ret['print_translated_manufacturer_name'] = esc_html($ret['translated_manufacturer_name']);

            // Prepare output for edit
            $ret['edit_manufacturer_name'] = esc_attr($ret['manufacturer_name']); // for input field
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
        $validManufacturerId = StaticValidator::getValidPositiveInteger($this->manufacturerId, 0);
        $sanitizedManufacturerName = sanitize_text_field($params['manufacturer_name']);
        $validManufacturerName = esc_sql($sanitizedManufacturerName); // for sql query only

        $titleExistsQuery = "
            SELECT manufacturer_id
            FROM {$this->conf->getPrefix()}manufacturers
            WHERE manufacturer_title='{$validManufacturerName}' AND manufacturer_id!='{$validManufacturerId}'
            AND blog_id='{$this->conf->getBlogId()}'
        ";
        $titleExists = $this->conf->getInternalWPDB()->get_row($titleExistsQuery, ARRAY_A);

        if(!is_null($titleExists))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_MANUFACTURER_TITLE_EXISTS_ERROR_TEXT');
        }

        if($validManufacturerId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}manufacturers SET
                manufacturer_title='{$validManufacturerName}'
                WHERE manufacturer_id='{$validManufacturerId}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            // Only if there is error in query we will skip that, if no changes were made (and 0 was returned) we will still process
            if($saved !== false)
            {
                $manufacturerEditData = $this->conf->getInternalWPDB()->get_row("
                    SELECT *
                    FROM {$this->conf->getPrefix()}manufacturers
                    WHERE manufacturer_id='{$validManufacturerId}' AND blog_id='{$this->conf->getBlogId()}'
                ", ARRAY_A);

                // Upload logo
                if(
                    isset($params['delete_manufacturer_logo']) && $manufacturerEditData['manufacturer_logo'] != "" &&
                    $manufacturerEditData['demo_manufacturer_logo'] == 0
                ) {
                    // Unlink files only if it's not a demo image
                    unlink($this->conf->getGlobalGalleryPath().$manufacturerEditData['manufacturer_logo']);
                    unlink($this->conf->getGlobalGalleryPath()."thumb_".$manufacturerEditData['manufacturer_logo']);
                }

                $validUploadedLogoFileName = '';
                if($_FILES['manufacturer_logo']['tmp_name'] != '')
                {
                    $uploadedLogoFileName = StaticFile::uploadImageFile($_FILES['manufacturer_logo'], $this->conf->getGlobalGalleryPathWithoutEndSlash(), "manufacturer_");
                    StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedLogoFileName, $this->thumbWidth, $this->thumbHeight, "thumb_");
                    $validUploadedLogoFileName = esc_sql(sanitize_file_name($uploadedLogoFileName)); // for sql query only
                }

                if($validUploadedLogoFileName != '' || isset($params['delete_manufacturer_logo']))
                {
                    // Update the sql
                    $this->conf->getInternalWPDB()->query("
                        UPDATE {$this->conf->getPrefix()}manufacturers SET
                        manufacturer_logo='{$validUploadedLogoFileName}', demo_manufacturer_logo='0'
                        WHERE manufacturer_id='{$validManufacturerId}' AND blog_id='{$this->conf->getBlogId()}'
                    ");
                }
            }

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_MANUFACTURER_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_MANUFACTURER_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}manufacturers
                (manufacturer_title, blog_id)
                VALUES
                ('{$validManufacturerName}', '{$this->conf->getBlogId()}')
            ");

            // We will process only if there one line was added to sql
            if($saved)
            {
                // Get newly inserted manufacturer id
                $validInsertedNewManufacturerId = $this->conf->getInternalWPDB()->insert_id;

                // Update the core element id for future use
                $this->manufacturerId = $validInsertedNewManufacturerId;

                $validUploadedLogoFileName = '';
                if($_FILES['manufacturer_logo']['tmp_name'] != '')
                {
                    $uploadedLogoFileName = StaticFile::uploadImageFile($_FILES['manufacturer_logo'], $this->conf->getGlobalGalleryPathWithoutEndSlash(), "manufacturer_");
                    StaticFile::makeThumbnail($this->conf->getGlobalGalleryPath(), $uploadedLogoFileName, $this->thumbWidth, $this->thumbHeight, "thumb_");
                    $validUploadedLogoFileName = esc_sql(sanitize_file_name($uploadedLogoFileName)); // for sql query only
                }

                if($validUploadedLogoFileName != '')
                {
                    // Update the sql
                    $this->conf->getInternalWPDB()->query("
                        UPDATE {$this->conf->getPrefix()}manufacturers SET
                        manufacturer_logo='{$validUploadedLogoFileName}', demo_manufacturer_logo='0'
                        WHERE manufacturer_id='{$validInsertedNewManufacturerId}' AND blog_id='{$this->conf->getBlogId()}'
                    ");
                }
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_MANUFACTURER_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_MANUFACTURER_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $manufacturerDetails = $this->getDetails();
        if(!is_null($manufacturerDetails))
        {
            $this->lang->register("ma{$this->manufacturerId}_manufacturer_name", $manufacturerDetails['manufacturer_name']);
            $this->okayMessages[] = $this->lang->getText('LANG_MANUFACTURER_REGISTERED_TEXT');
        }
    }

    /**
     * @return false|int
     */
    public function delete()
    {
        $deleted = false;
        $manufacturerDetails = $this->getDetails();
        if(!is_null($manufacturerDetails))
        {
            $deleted = $this->conf->getInternalWPDB()->query("
                DELETE FROM {$this->conf->getPrefix()}manufacturers
                WHERE manufacturer_id='{$manufacturerDetails['manufacturer_id']}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($deleted)
            {
                // Unlink logo file
                if($manufacturerDetails['demo_manufacturer_logo'] == 0 && $manufacturerDetails['manufacturer_logo'] != "")
                {
                    unlink($this->conf->getGlobalGalleryPath().$manufacturerDetails['manufacturer_logo']);
                    unlink($this->conf->getGlobalGalleryPath()."thumb_".$manufacturerDetails['manufacturer_logo']);
                }
            }
        }

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_MANUFACTURER_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_MANUFACTURER_DELETED_TEXT');
        }

        return $deleted;
    }
}