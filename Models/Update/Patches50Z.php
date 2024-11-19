<?php
/**
 * Patch class
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Semver\Semver;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class Patches50Z extends AbstractDatabase implements StackInterface, DatabaseInterface, PatchInterface
{
    const CURRENT_MAJOR = 5; // Positive integer [X]
    const CURRENT_MINOR = 0; // Positive integer [Y]
    const LATEST_PATCH = 6; // Positive integer [Z]
    const LATEST_RELEASE = ''; // String
    const LATEST_BUILD_METADATA = ''; // String
    const PLUGIN_PREFIX = "car_rental_";

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
     * NOTE: Returns true even if 0 lines altered
     * @return bool
     */
    public function patchDatabaseEarlyStructure()
    {
        $arrSQL = array();
        $objSemver = new Semver($this->extSemverInDatabase, false);
        $currentPatch = $objSemver->getPatch();

        if($currentPatch < 1)
        {
            // [SETTINGS] Add indexes for logic scope and for the fact that both - ext_code and blog_id is used in WHERE statement,
            //  when we pull to cache all DB settings, or when we want to drop data from specific table
            $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
                ADD INDEX ( `blog_id` );";
        }

        if($currentPatch < 2)
        {
            // [INVOICES] To solve duplicate key issue
            $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."invoices` DROP PRIMARY KEY;";
            $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."invoices` ADD INDEX (`booking_id`);";
            $arrSQL[] = "ALTER TABLE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."invoices`
                ADD `invoice_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
                ADD PRIMARY KEY (`invoice_id`);";
        }

        $patched = $this->executeQueries($arrSQL);
        if($patched === false)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_PATCH_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_EARLY_STRUCTURE_PATCHED_TEXT'), $this->blogId);
        }

        return $patched;
    }

    /**
     * @return bool
     */
    public function patchData()
    {
        $arrSQL = array();
        $validExtCode = StaticValidator::getValidCode($this->extCode, '', true, false, false); // NOTE: This will fit 6.0.0 as well
        $validBlogId = StaticValidator::getValidPositiveInteger($this->blogId, 0);

        $objSemver = new Semver($this->extSemverInDatabase, false);
        $currentPatch = $objSemver->getPatch();

        if($currentPatch < 1)
        {
            // [SETTINGS] Rename settings
            $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
                SET conf_key='conf_plugin_semver'
                WHERE conf_key='conf_plugin_version' AND blog_id='{$validBlogId}'";
            // [SETTINGS] Change settings from 365 days to 365.25 days (average for leap year), if 1 year period was selected before
            $arrSQL[] = "UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
                SET conf_value='31622400'
                WHERE conf_key='conf_plugin_version' AND conf_value='31536000' AND blog_id='{$validBlogId}'";
        }

        // Execute queries
        $patched = $this->executeQueries($arrSQL);

        if($patched === false)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCH_ERROR_TEXT'), $this->blogId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_DATA_PATCHED_TEXT'), $this->blogId);
        }

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
        $validBlogId = StaticValidator::getValidPositiveInteger($this->blogId, 0);

        $newSemver = static::CURRENT_MAJOR.'.'.static::CURRENT_MINOR.'.'.static::LATEST_PATCH;
        $newSemver .= static::LATEST_RELEASE != "" ? "-".static::LATEST_RELEASE : "";
        $newSemver .= static::LATEST_BUILD_METADATA != "" ? "+".static::LATEST_BUILD_METADATA : "";

        // Update plugin semver till newest
        $semverUpdated = $this->executeQuery("
            UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
            SET `conf_value`='{$newSemver}'
            WHERE `conf_key` IN ('conf_plugin_semver', 'conf_plugin_version') AND blog_id='{$validBlogId}'
        ");
        // Reset counter back to 0 to say that the new update can start from the first update class query. That will be used in future updates
        $counterReset = $this->executeQuery("
            UPDATE `".$this->conf->getWP_Prefix().static::PLUGIN_PREFIX."settings`
            SET `conf_value`='0'
            WHERE `conf_key`='conf_updated' AND blog_id='{$validBlogId}'
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