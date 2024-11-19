<?php
/**
 * Network status must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Status;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface NetworkStatusInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang);
    public function inDebug();
    /**
     * Additional links to show in network plugins manager
     * @return array
     */
    public function getAdditionalActionLinks();

    /**
     * Additional links to show in next to network plugin description
     * @return array
     */
    public function getInfoLinks();

    /**
     * @note1 - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @note2 - This function says if there are plugin struct of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDB_StructExistsOf($paramRequiredPluginSemver);

    /**
     * @note1 - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @note2 - This function says if the data exists for at least one extension in some blog of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDataExistsInSomeBlogOf($paramRequiredPluginSemver);

    /**
     * Differently to "Exists of semver" class method, this class method is based
     * on existence of compatible data for this exact extension in some blog
     *
     * @note1 - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @note2 - This function says if the data exists of this exact extension in some blog
     * @return bool
     */
    public function checkExtCompatibleDataExistsInSomeBlog();

    /**
     * @note1 - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @note2 - This function says if the data exists of this exact extension in some blog of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkExtDataExistsInSomeBlogOf($paramRequiredPluginSemver);

    /**
     * @note - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @return array
     */
    public function getAllExtSemversInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @return string
     */
    public function getMinExtSemverInDatabase();

    /**
     * @note - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @return string
     */
    public function getMaxExtSemverInDatabase();

    /**
     * Is the NS database semver is newer or same as code semver. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isAllBlogsWithExtDataUpToDate();

    /**
     * NOTE: Update may exist, but the system might be not compatible for update
     * @return bool
     */
    public function checkExtUpdateExistsForSomeBlog();

    /**
     * @return bool
     */
    public function canUpdateExtDataInSomeBlog();

    /**
     * Can we do a major upgrade in some blog, i.e. from 1.*.* to 2.*.* etc., not 1.0.* to 1.1.*
     * @return bool
     */
    public function canMajorlyUpgradeExtDataInSomeBlog();
}