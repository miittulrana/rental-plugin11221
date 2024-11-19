<?php
/**
 * Patch class
 * NOTE: This is a boilerplate class, so please replace PatchXYZ with exact major ("X") and minor ("Y"), i.e. "Patches70Z"
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

final class PatchesXYZ extends AbstractDatabase implements StackInterface, DatabaseInterface, PatchInterface
{
    const CURRENT_MAJOR = 5; // Positive integer [X]
    const CURRENT_MINOR = 1; // Positive integer [Y]
    const LATEST_PATCH = 1; // Positive integer [Z]
    const LATEST_RELEASE = ''; // String
    const LATEST_BUILD_METADATA = ''; // String
    const PLUGIN_PREFIX = "fleet_management_";

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param string $paramExtCode - for 5.1.1 and later
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramExtCode, $paramBlogId)
    {
        parent::__construct($paramConf, $paramLang, $paramExtCode, $paramBlogId);
    }

    /**
     * SQL for early database altering
     * NOTE: Returns true even if 0 lines altered
     * @return bool
     */
    public function patchDatabaseEarlyStructure()
    {
        // NOTHING HERE
        $patched = true;

        //$arrSQL = array();
        //$patched = $this->executeQueries($arrSQL);
        //if($patched === false)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_PATCH_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_PATCHED_TEXT'), $this->blogId);
        //}

        return $patched;
    }

    /**
     * @return bool
     */
    public function patchData()
    {
        // NOTHING HERE
        $patched = true;

        //$arrSQL = array();
        //$patched = $this->executeQueries($arrSQL);
        //if($patched === false)
        //{
        //    $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCH_ERROR_TEXT'), $this->blogId);
        //} else
        //{
        //    $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCHED_TEXT'), $this->blogId);
        //}

        return $patched;
    }

    // NOTE: For patches late struct patching is not possible at all, due to fact that we can update struct on the same first site that has data,
    //          but we cannot do the same for late struct, as we the would not have clear data patching

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

        $newSemver = static::CURRENT_MAJOR.'.'.static::CURRENT_MINOR.'.'.static::LATEST_PATCH;
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
            UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
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