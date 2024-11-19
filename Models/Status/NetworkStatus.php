<?php
/**
 * Plugin

 * @note - It does not have settings param in constructor on purpose!
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Status;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Semver\Semver;
use FleetManagement\Models\Validation\StaticValidator;

final class NetworkStatus extends AbstractStack implements StackInterface, NetworkStatusInterface
{
    private $conf           = null;
    private $lang 		    = null;
    private $debugMode 	    = 0;
    // NOTE: It must have '500' in it, despite some extensions did not existed at that time, to avoid 'table not exists' issues during install
    private $legacy50X_SettingsTable = '';

    /**
     * CAUTION! Be careful when using echo debug, as this class is used in ajax requests,
     *          so only if it is links display call, or 'die()' is called afterwards, the echoing will work as expected.
     * @var bool
     */
    private $echoDebug 	    = false;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set legacy settings table
        if($this->conf->getExtCode() == "CAR_RENTAL")
        {
            $this->legacy50X_SettingsTable = "car_rental_settings";
        } else if($this->conf->getExtCode() == "BARGE_BOOKING")
        {
            $this->legacy50X_SettingsTable = "barge_booking_settings";
        } else if($this->conf->getExtCode() == "TOUR_BOOKING")
        {
            $this->legacy50X_SettingsTable = "tour_booking_settings";
        } else if($this->conf->getExtCode() == "SCOOTER_RENTAL")
        {
            $this->legacy50X_SettingsTable = "scooter_rental_settings";
        } else if($this->conf->getExtCode() == "BOAT_RENTAL")
        {
            $this->legacy50X_SettingsTable = "boat_rental_settings";
        } else if($this->conf->getExtCode() == "EQUIPMENT_RENTAL")
        {
            $this->legacy50X_SettingsTable = "equipment_rental_settings";
        } else
        {
            // Default
            $this->legacy50X_SettingsTable = "car_rental_settings";
        }
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Get additional links to show in network plugins manager
     * @return array
     */
    public function getAdditionalActionLinks()
    {
        $retLinks = array();

        // RULE #1: The "Populate data" link is shown if plugin struct exists of latest semver, but the data - don't,
        //          and there is no compatible data at all for this extension in some blog.
        // RULE #2: The "Drop data" link is shown only if the extension data exists and is up to date in database for all blogs
        $allBlogsWithExtDataUpToDate = $this->isAllBlogsWithExtDataUpToDate();
        if(($this->checkPluginDB_StructExistsOf($this->conf->getPluginSemver()) && $this->checkExtCompatibleDataExistsInSomeBlog() === false) || $allBlogsWithExtDataUpToDate)
        {
            // Additional links to show if the plugin has up to date database structure and no compatible extension data in some blog,
            // or if the plugin is up-to-date
            if($allBlogsWithExtDataUpToDate && $this->checkExtDataExistsInSomeBlogOf($this->conf->getPluginSemver()))
            {
                // Show additional network-enabled plugin links only if the plugin is up-to-date, and has existing extension data in some blog
                // NOTE: For this plugin no additional links are shown here, all data has to be dropped by going to individual blogs
            } else
            {
                // Show additional network-enabled plugin links only if the plugin is up-to-date, and doesn't have existing extension data in some blog
                // NOTE: For this plugin no additional links are shown here, all data has to be populated by going to individual blogs
            }
        }

        // NOTE: This link has to be in separate if statement
        if($allBlogsWithExtDataUpToDate === false && $this->canUpdateExtDataInSomeBlog())
        {
            // Show the network-update link, but only if it is allowed to update from current version
            $networkUpdatePageURL = network_admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'network-status&update=1&noheader=true');
            $retLinks[] = '<a href="'.$networkUpdatePageURL.'">'.$this->lang->escHTML('LANG_UPDATE_TEXT').'</a>';
        }

        return $retLinks;
    }

    /**
     * Get additional links to show in next to network plugin description
     * @return array
     */
    public function getInfoLinks()
    {
        $retLinks = array();

        // Additional links to show in network admin and only if the plugin is network-enabled
        if($this->isAllBlogsWithExtDataUpToDate())
        {
            // Show additional info links only if the plugin is up-to-date
            $networkStatusURL = network_admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'network-status');
            $retLinks[] = '<a href="'.esc_url($networkStatusURL).'">'.$this->lang->escHTML('LANG_STATUS_TEXT').'</a>';
        }

        return $retLinks;
    }

    /**
     * @note - This function is not compatible with FM versions before 5.1.0
     * @return bool
     */
    private function checkV510SettingsTableExists()
    {
        // NOTE #1: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
        //       with a newer framework version it won't pass
        // NOTE #2: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
        $tableToCheck = $this->conf->getWP_Prefix().$this->conf->getPluginPrefix().'settings';
        $sqlQuery = "SHOW TABLES LIKE '{$tableToCheck}'";
        $settingsTableResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $settingsTableExists = (!is_null($settingsTableResult) && $settingsTableResult === $tableToCheck) ? true : false;

        return $settingsTableExists;
    }

    /**
     * @note1 - This function maintains backwards compatibility to FM 5.0.0
     * @note2 - This function also can be used to check for the existence of FM 5.0.0 settings table
     * @return bool
     */
    private function __legacy__check_V500_SettingsTableExists()
    {
        // NOTE: 'car_rental_' is mandatory here to correctly know the version
        $tableToCheck = $this->conf->getWP_Prefix().$this->legacy50X_SettingsTable;
        $sqlQuery = "SHOW TABLES LIKE '{$tableToCheck}'";
        $settingsTableResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $settingsTableExists = (!is_null($settingsTableResult) && $settingsTableResult === $tableToCheck) ? true : false;

        return $settingsTableExists;
    }

    /**
     * @note - This function is not compatible with FM versions before 5.1.0
     * @return bool
     */
    private function checkV510ExtCodeColumnExists()
    {
        // NOTE #1: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
        //       with a newer framework version it won't pass
        // NOTE #2: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
        $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings` LIKE 'ext_code'";
        $extCodeColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $extCodeColumnExists = !is_null($extCodeColumnResult) ? true : false;

        return $extCodeColumnExists;
    }

    /**
     * @note - This function maintains backwards compatibility to FM 5.0.0 and older
     * @return bool
     */
    private function __legacy__checkV500BlogIdColumnExists()
    {
        // NOTE #1: In FM 5.0.0 and earlier database version ext_code column did not yet existed
        // NOTE: 'car_rental_' is mandatory here to correctly know the version,
        //       as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
        $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable}` LIKE 'blog_id'";
        $blogIdColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $blogIdColumnExists = !is_null($blogIdColumnResult) ? true : false;

        return $blogIdColumnExists;
    }

    /**
     * @note1 - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @note2 - This function says if there are plugin struct of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDB_StructExistsOf($paramRequiredPluginSemver)
    {
        $tableExists = false;
        $columnExists = false;
        $validRequiredPluginSemver = StaticValidator::getValidSemver($paramRequiredPluginSemver, false);

        if(version_compare($validRequiredPluginSemver, '5.1.0', '>='))
        {
            // We are looking for FM 5.1.0 or later database table
            $tableExists = $this->checkV510SettingsTableExists();
            if($tableExists)
            {
                // We are looking for FM 5.1.0 or later database table column
                $columnExists = $this->checkV510ExtCodeColumnExists();
            }
        } else if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && version_compare($validRequiredPluginSemver, '5.1.0', '<'))
        {
            // We are looking for FM 5.0.0 or earlier database table
            $tableExists = $this->__legacy__check_V500_SettingsTableExists();
            if($tableExists && version_compare($validRequiredPluginSemver, '5.0.0', '>='))
            {
                // We are looking for FM 5.0.0 database table column, when ext_code column did not existed
                $columnExists = $this->__legacy__checkV500BlogIdColumnExists();
            }
        }
        $structExist = $tableExists && $columnExists;

        // DEBUG
        if($this->debugMode)
        {
            $structText = $structExist ? "Yes" : "No";
            $tableText = $tableExists ? "Yes" : "No";
            $columnText = $columnExists ? "Yes" : "No";
            $debugMessage = "Debug: checkPluginDB_StructExistsOf(): {$structText} (Table - {$tableText}, Column - {$columnText})<br />";
            $this->debugMessages[] = $debugMessage;
            if($this->echoDebug)
            {
                echo "<br />".$debugMessage;
            }
        }

        return $structExist;
    }

    /**
     * @note1 - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @note2 - This function says if the data exists for at least one extension in some blog of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDataExistsInSomeBlogOf($paramRequiredPluginSemver)
    {
        $retExists = false;
        $sqlQuery = "";
        $validRequiredPluginSemver = StaticValidator::getValidSemver($paramRequiredPluginSemver, false);

        if(version_compare($validRequiredPluginSemver, '5.1.0', '>=') && $this->checkV510SettingsTableExists() && $this->checkV510ExtCodeColumnExists())
        {
            // We are testing FM 5.1.0 or later database version
            // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
            // NOTE #2: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
            //          with a newer framework version won't pass the test
            // NOTE #3: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings WHERE 1";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // Check if plugin is installed or not for some blog_id
            $retExists = !is_null($hasSettings) ? true : false;
        } else if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && version_compare($validRequiredPluginSemver, '5.1.0', '<') && $this->__legacy__check_V500_SettingsTableExists())
        {
            // FM 5.0.0
            if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && $this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
                // NOTE #2: 'car_rental_' is mandatory here to correctly know the version,
                //          as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable} WHERE 1";
                $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
                // NS plugins is installed or not for this blog_id
                $retExists = !is_null($hasSettings) ? true : false;
            }
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkPluginDataExistsInSomeBlogOf(): ".($retExists ? "Yes" : "No")."<br />SQL: ".esc_br_html($sqlQuery)."<br />";
            $this->debugMessages[] = $debugMessage;
            if($this->echoDebug)
            {
                echo "<br />".$debugMessage;
            }
        }

        return $retExists;
    }

    /**
     * Differently to "Exists of semver" class method, this class method is based
     * on existence of compatible data for this exact extension in some blog
     *
     * @note1 - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @note2 - This function says if the data exists of this exact extension in some blog
     * @return bool
     */
    public function checkExtCompatibleDataExistsInSomeBlog()
    {
        $retExists = false;
        $sqlQuery = "";

        if($this->checkV510SettingsTableExists() && $this->checkV510ExtCodeColumnExists())
        {
            // We are testing FM 5.1.0 or later database version
            // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
            // NOTE #2: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
            //          with a newer framework version won't pass the test
            // NOTE #3: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings WHERE ext_code='{$this->conf->getExtCode()}'";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // NS plugins is installed or not for this blog_id
            $retExists = !is_null($hasSettings) ? true : false;
        } else if($this->__legacy__check_V500_SettingsTableExists())
        {
            // FM 5.0.0
            if($this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
                // NOTE #2: 'car_rental_' is mandatory here to correctly know the version,
                //          as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable} WHERE 1";
                $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
                // NS plugins is installed or not for this blog_id
                $retExists = !is_null($hasSettings) ? true : false;
            }
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkExtCompatibleDataExistsInSomeBlog(): ".($retExists ? "Yes" : "No")."<br />SQL: ".esc_br_html($sqlQuery)."<br />";
            $this->debugMessages[] = $debugMessage;
            if($this->echoDebug)
            {
                echo "<br />".$debugMessage;
            }
        }

        return $retExists;
    }

    /**
     * @note1 - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @note2 - This function says if the data exists of this exact extension in some blog of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkExtDataExistsInSomeBlogOf($paramRequiredPluginSemver)
    {
        $retExists = false;
        $sqlQuery = "";
        $validRequiredPluginSemver = StaticValidator::getValidSemver($paramRequiredPluginSemver, false);

        if(version_compare($validRequiredPluginSemver, '5.1.0', '>=') && $this->checkV510SettingsTableExists() && $this->checkV510ExtCodeColumnExists())
        {
            // We are testing FM 5.1.0 or later database version
            // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
            // NOTE #2: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
            //          with a newer framework version won't pass the test
            // NOTE #3: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings WHERE ext_code='{$this->conf->getExtCode()}'";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // NS plugins is installed or not for this blog_id
            $retExists = !is_null($hasSettings) ? true : false;
        } else if(version_compare($validRequiredPluginSemver, '4.3.0', '>=') && version_compare($validRequiredPluginSemver, '5.1.0', '<') && $this->__legacy__check_V500_SettingsTableExists())
        {
            // FM 5.0.0 or FM 4.3.0
            if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && $this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
                // NOTE #2: 'car_rental_' is mandatory here to correctly know the version,
                //          as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable} WHERE 1";
                $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
                // NS plugins is installed or not for this blog_id
                $retExists = !is_null($hasSettings) ? true : false;
            }
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkExtDataExistsInSomeBlogOf(): ".($retExists ? "Yes" : "No")."<br />SQL: ".esc_br_html($sqlQuery)."<br />";
            $this->debugMessages[] = $debugMessage;
            if($this->echoDebug)
            {
                echo "<br />".$debugMessage;
            }
        }

        return $retExists;
    }

    /**
     * @note - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @return array
     */
    public function getAllExtSemversInDatabase()
    {
        // '0.0.0' is the semver that can be either older than oldest compatible semver, or when the semver is not detected
        // I.e. in oldest semvers the chosen row of plugin semver did not existed at all
        $arrDatabaseSemvers = array();

        if($this->checkV510SettingsTableExists())
        {
            // NOTE: The section bellow were moved to internal statement to save SQL queries for scenario when table exists but not column
            if($this->checkV510ExtCodeColumnExists())
            {
                // We are testing FM 5.1.0 or later database version

                // FM 5.1.0 and newer check
                // NOTE #1: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
                //          with a newer framework version won't pass the test
                // NOTE #2: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
                $semverSQL = "
                    SELECT conf_value AS plugin_semver
                    FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings
                    WHERE conf_key='conf_plugin_semver' AND ext_code='{$this->conf->getExtCode()}'
                ";
                $arrTmpDatabaseSemvers = $this->conf->getInternalWPDB()->get_col($semverSQL);
                foreach($arrTmpDatabaseSemvers AS $databaseSemver)
                {
                    $arrDatabaseSemvers[] = StaticValidator::getValidSemver($databaseSemver, false);
                }
            }
        }

        if($this->__legacy__check_V500_SettingsTableExists())
        {
            // NOTE: The section bellow were moved to internal statement to save SQL queries for scenario when table exists but not column
            if($this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                // NOTE: 'car_rental_' is mandatory here to correctly know the version,
                //       as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $sql = "
                    SELECT conf_value AS plugin_semver
                    FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable}
                    WHERE conf_key IN ('conf_plugin_semver', 'conf_plugin_version')
                ";
                $arrTmpDatabaseSemvers = $this->conf->getInternalWPDB()->get_col($sql);
                foreach($arrTmpDatabaseSemvers AS $databaseSemver)
                {
                    $arrDatabaseSemvers[] = StaticValidator::getValidSemver($databaseSemver, false);
                }
            }
        }

        // If no database semvers were found
        if(sizeof($arrDatabaseSemvers) == 0)
        {
            // Then add '0.0.0' semver
            $arrDatabaseSemvers[] = '0.0.0';
        }

        // DEBUG
        if($this->debugMode >= 2 && $this->echoDebug)
        {
            echo "[getAllExtSemversInDatabase()] EXT SEMVERS IN DATABASE: ";
            print_r($arrDatabaseSemvers);
            echo "<br />";
        }

        return $arrDatabaseSemvers;
    }

    /**
     * @note - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @return string
     */
    public function getMinExtSemverInDatabase()
    {
        $semvers = $this->getAllExtSemversInDatabase();

        // Select minimum database semver, or, if no semvers found, return the '0.0.0' semver
        $minSemver = sizeof($semvers) > 0 ? $semvers[0] : '0.0.0';
        foreach($semvers AS $semver)
        {
            if($semver != "0.0.0" && version_compare($semver, $minSemver, '<'))
            {
                $minSemver = $semver;
            }
        }

        return $minSemver;
    }

    /**
     * @note - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @return string
     */
    public function getMaxExtSemverInDatabase()
    {
        $semvers = $this->getAllExtSemversInDatabase();

        // Select maximum database semver, or, if no semvers found, return the '0.0.0' semver
        $maxSemver = '0.0.0';
        foreach($semvers AS $semver)
        {
            if(version_compare($semver, $maxSemver, '>'))
            {
                $maxSemver = $semver;
            }
        }

        return $maxSemver;
    }

    /**
     * Is the plugin's database semver is newer or same as code semver. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isAllBlogsWithExtDataUpToDate()
    {
        $minExtSemverInDatabase = $this->getMinExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $isUpToDate = version_compare($minExtSemverInDatabase, $codeSemver, '==') ? true : false;

        // DEBUG
        if($this->debugMode >= 2 && $this->echoDebug)
        {
            echo "[isAllBlogsWithExtDataUpToDate()] MIN. DB SEMVER: {$minExtSemverInDatabase}<br />";
            echo "[isAllBlogsWithExtDataUpToDate()] CODE SEMVER: {$codeSemver}<br />";
            echo "[isAllBlogsWithExtDataUpToDate()] ALL BLOGS IS UP TO DATE: {$isUpToDate}<br />";
        }

        return $isUpToDate;
    }

    /**
     * NOTE: Update may exist, but the system might be not compatible for update
     * @return bool
     */
    public function checkExtUpdateExistsForSomeBlog()
    {
        $canUpdate = false;
        $minExtSemverInDatabase = $this->getMinExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        if(version_compare($minExtSemverInDatabase, $codeSemver, '<'))
        {
            $canUpdate = true;
        }

        // DEBUG
        if($this->debugMode >= 2 && $this->echoDebug)
        {
            echo "[checkExtUpdateExistsForSomeBlog()] MIN DB SEMVER: {$minExtSemverInDatabase}<br />";
            echo "[checkExtUpdateExistsForSomeBlog()] CODE SEMVER: {$codeSemver}<br />";
            echo "[checkExtUpdateExistsForSomeBlog()] UPDATE EXISTS: ".var_export($canUpdate, true)."<br />";
        }

        return $canUpdate;
    }

    /**
     * @return bool
     */
    public function canUpdateExtDataInSomeBlog()
    {
        $canUpdate = false;
        $minExtSemverInDatabase = $this->getMinExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $oldestCompatibleSemver = $this->conf->getOldestCompatiblePluginSemver();
        if(version_compare($minExtSemverInDatabase, $oldestCompatibleSemver, '>=') && version_compare($minExtSemverInDatabase, $codeSemver, '<'))
        {
            $canUpdate = true;
        }

        // DEBUG
        if($this->debugMode >= 2 && $this->echoDebug)
        {
            echo "[canUpdateExtDataInSomeBlog()] MIN DB SEMVER: {$minExtSemverInDatabase}<br />";
            echo "[canUpdateExtDataInSomeBlog()] OLDEST-COMPAT SEMVER: {$oldestCompatibleSemver}<br />";
            echo "[canUpdateExtDataInSomeBlog()] CODE SEMVER: {$codeSemver}<br />";
            echo "[canUpdateExtDataInSomeBlog()] UPDATE EXISTS: ".var_export($canUpdate, true)."<br />";
        }

        return $canUpdate;
    }

    /**
     * Can we do a major upgrade in some blog, i.e. from V1.*.* to V2.*.* etc., not V1.0.* to V1.1.*
     * @return bool
     */
    public function canMajorlyUpgradeExtDataInSomeBlog()
    {
        $majorUpgrade = false;
        $minExtSemverInDatabase = $this->getMinExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $oldestCompatibleSemver = $this->conf->getOldestCompatiblePluginSemver();
        $dbMinSemverMajor = (new Semver($minExtSemverInDatabase, false))->getMajor();
        $codeSemverMajor = (new Semver($codeSemver, false))->getMajor();
        if(version_compare($minExtSemverInDatabase, $oldestCompatibleSemver, '>=') && $dbMinSemverMajor < $codeSemverMajor)
        {
            $majorUpgrade = true;
        }
        return $majorUpgrade;
    }
}