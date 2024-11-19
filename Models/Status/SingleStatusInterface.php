<?php
/**
 * Status must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Status;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface SingleStatusInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId);
    public function getId();
    public function inDebug();

    /**
     * Get additional links to show in local plugins manager
     * @return array
     */
    public function getActionLinks();

    /**
     * Additional links to show in next to local plugin description
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
     * @note2 - This function says if the data exists for at least one extension of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDataExistsOf($paramRequiredPluginSemver);

    /**
     * Differently to "Exists of semver" class method, this class method is based
     * on existence of compatible data for this exact extension
     *
     * @note1 - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @note2 - This function says if the data exists of this exact extension
     * @return bool
     */
    public function checkExtCompatibleDataExists();

    /**
     * @note1 - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @note2 - This function says if the data exists of this exact extension of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkExtDataExistsOf($paramRequiredPluginSemver);

    /**
     * @note - This function maintains backwards compatibility to SMVC 4.3.0 and newer
     * @return string
     */
    public function getExtSemverInDatabase();

    /**
     * Is the NS database semver is newer or same as code semver. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isExtDataUpToDateInDatabase();

    /**
     * NOTE: Update may exist, but the system might be not compatible for update
     * @return bool
     */
    public function checkExtUpdateExists();

    /**
     * @return bool
     */
    public function canUpdateExtDataInDatabase();

    /**
     * Can we do a major upgrade, i.e. from 1.*.* to 2.*.* etc., not 1.0.* to 1.1.*
     * @return bool
     */
    public function canMajorlyUpgradeExtDataInDatabase();
}