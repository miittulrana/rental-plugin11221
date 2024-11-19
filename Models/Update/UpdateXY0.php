<?php
/**
 * Update class
 * NOTE: This is a boilerplate class, so please replace UpdateXY0 with exact major ("X") and minor ("Y"), i.e. "Database700"
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class UpdateXY0 extends AbstractDatabase implements StackInterface, DatabaseInterface, UpdateInterface
{
    const NEW_MAJOR = 5; // Positive integer [X]
    const NEW_MINOR = 1; // Positive integer [Y]
    // NOTE: No patch here for updates. For updates the patch is always '0'
    const LATEST_RELEASE = ''; // String
    const LATEST_BUILD_METADATA = ''; // String
    const PLUGIN_PREFIX = "fleet_management_";

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param string $paramExtCode - for 6.0.1 and later
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramExtCode, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramExtCode, $paramBlogId);
    }

    /**
     * SQL for early database altering
     * @return bool
     */
    public function alterDatabaseEarlyStructure()
    {
        // NOTHING HERE
        $altered = true;

        // $arrSQL = array();
        // $altered = $this->executeQueries($arrSQL);
        //if($altered === false)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_ALTER_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_ALTERED_TEXT'), $this->blogId);
        //}

        return $altered;
    }

    /**
     * SQL for updating database data
     * @return bool
     */
    public function updateDatabaseData()
    {
        // NOTHING HERE

        // Update main data
        $updated = true;

        // $arrSQL = array();
        // $updated = $this->executeQueries($arrSQL);
        //if($updated === false)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_UPDATE_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_UPDATED_TEXT'), $this->blogId);
        //}

        return $updated;
    }

    /**
     * SQL for late database altering
     * @return bool
     */
    public function alterDatabaseLateStructure()
    {
        // NOTHING HERE
        $altered = true;

        // $arrSQL = array();
        // $altered = $this->executeQueries($arrSQL);
        //if($altered === false)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_LATE_STRUCTURE_ALTER_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_LATE_STRUCTURE_ALTERED_TEXT'), $this->blogId);
        //}

        return $altered;
    }

    public function updateCustomRoles()
    {
        // NOTHING HERE
        $rolesUpdated = true;

        //if($rolesUpdated === false)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_ROLES_UPDATE_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_ROLES_UPDATED_TEXT'), $this->blogId);
        //}

        return $rolesUpdated;
    }

    public function updateCustomCapabilities()
    {
        // NOTHING HERE
        $rolesUpdated = true;

        //if($rolesUpdated === false)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_CAPABILITIES_UPDATE_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_CAPABILITIES_UPDATED_TEXT'), $this->blogId);
        //}

        return $rolesUpdated;
    }

    /**
     * NOTE: This method has to be in update class of specific update, because settings table itself,
     *       and it's columns can change over a time as well
     * @return bool
     */
    public function updateDatabaseSemver()
    {
        $updated = false;
        $validExtCode = StaticValidator::getValidCode($this->extCode, '', true, false, false); // NOTE: This fit's 6.0.0, not just 6.0.1+
        $validBlogId = StaticValidator::getValidPositiveInteger($this->blogId, 0);

        // NOTE: For updates the patch is always 0
        $newSemver = static::NEW_MAJOR.'.'.static::NEW_MINOR.'.0';
        $newSemver .= static::LATEST_RELEASE != "" ? "-".static::LATEST_RELEASE : "";
        $newSemver .= static::LATEST_BUILD_METADATA != "" ? "+".static::LATEST_BUILD_METADATA : "";

        // Update plugin semver till newest
        $semverUpdated = $this->executeQuery("
            UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
            SET `conf_value`='{$newSemver}'
            WHERE `conf_key`='conf_plugin_semver' AND ext_code='{$validExtCode}' AND blog_id='{$validBlogId}'
        ");
        // Reset counter back to 0 to say that the new update can start from the first update class query. That will be used in future updates
        $counterReset = $this->executeQuery("
            UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."}settings`
            SET `conf_value`='0'
            WHERE `conf_key`='conf_updated' AND ext_code='{$validExtCode}' AND blog_id='{$validBlogId}'
        ");
        if($semverUpdated !== false && $counterReset !== false)
        {
            $updated = true;
        }

        if($updated === false)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATE_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_SEMANTIC_VERSION_UPDATED_TEXT'), $this->blogId, $newSemver);
        }

        return $updated;
    }
}