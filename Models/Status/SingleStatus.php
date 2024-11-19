<?php
/**
 * Single Status

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

final class SingleStatus extends AbstractStack implements StackInterface, SingleStatusInterface
{
    private $conf           = null;
    private $lang 		    = null;
    private $debugMode 	    = 0;

    /**
     * CAUTION! Be careful when using echo debug, as this class is used in ajax requests,
     *          so only if it is links display call, or 'die()' is called afterwards, the echoing will work as expected.
     * @var bool
     */
    private $echoDebug 	    = false;
    private $blogId         = false;
    // NOTE: It must have '500' in it, despite some extensions did not existed at that time, to avoid 'table not exists' issues during install
    private $legacy50X_SettingsTable = '';

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->blogId = intval($paramBlogId);
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

    public function getId()
    {
        return $this->blogId;
    }

    /**
     * Get additional links to show in local plugins manager
     * @return array
     */
    public function getActionLinks()
    {
        $retLinks = array();

        if($this->conf->isNetworkEnabled())
        {
            // Additional local links to show, but only if the plugin is network-enabled
            // NOTE: for network-enabled plugins the update link is not displayed here, it is shown under plugin's network admin action links

            // RULE #1: The "Populate data" link is shown if plugin struct exists of latest semver, but the data - don't,
            //          and there is no compatible data at all for this extension.
            // RULE #2: The "Drop data" link is shown only if the extension data exists and is up to date in database
            $extDataUpToDate = $this->isExtDataUpToDateInDatabase();
            if(($this->checkPluginDB_StructExistsOf($this->conf->getPluginSemver()) && $this->checkExtCompatibleDataExists() === false) || $extDataUpToDate)
            {
                if($extDataUpToDate && $this->checkExtDataExistsOf($this->conf->getPluginSemver()))
                {
                    // Show additional locally-enabled plugin links only if the plugin is up-to-date, and has existing extension data for Blog ID=X
                    $dropDataPageURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&drop_data=1&noheader=true');
                    $retLinks[] = '<a href="'.esc_url($dropDataPageURL).'">'.$this->lang->escHTML('LANG_SETTINGS_DROP_DATA_TEXT').'</a>';
                } else
                {
                    // Show additional locally-enabled plugin links only if the plugin is up-to-date, and doesn't have existing extension data for Blog ID=X
                    $populateDataPageURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&populate_data=1&noheader=true');
                    $retLinks[] = '<a href="'.esc_url($populateDataPageURL).'">'.$this->lang->escHTML('LANG_SETTINGS_POPULATE_DATA_TEXT').'</a>';
                }
            }
        } else
        {
            // Additional local links to show, but only if the plugin is locally enabled

            // RULE #1: The "Populate data" link is shown if plugin struct exists of latest semver, but the data - don't,
            //          and there is no compatible data at all for this extension.
            // RULE #2: The "Drop data" link is shown only if the extension data exists and is up to date in database
            $extDataUpToDate = $this->isExtDataUpToDateInDatabase();
            if(($this->checkPluginDB_StructExistsOf($this->conf->getPluginSemver()) && $this->checkExtCompatibleDataExists() === false) || $extDataUpToDate)
            {
                if($extDataUpToDate && $this->checkExtDataExistsOf($this->conf->getPluginSemver()))
                {
                    // Show additional locally-enabled plugin links only if the plugin is up-to-date, and has existing extension data for Blog ID=X
                    $dropDataPageURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&drop_data=1&noheader=true');
                    $retLinks[] = '<a href="'.esc_url($dropDataPageURL).'">'.$this->lang->escHTML('LANG_SETTINGS_DROP_DATA_TEXT').'</a>';
                } else
                {
                    // Show additional locally-enabled plugin links only if the plugin has up-to-date database structure, and doesn't have existing compatible extension data for Blog ID=X
                    $populateDataPageURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&populate_data=1&noheader=true');
                    $retLinks[] = '<a href="'.esc_url($populateDataPageURL).'">'.$this->lang->escHTML('LANG_SETTINGS_POPULATE_DATA_TEXT').'</a>';
                }
            }

            // NOTE: This link has to be in separate if statement
            if($extDataUpToDate === false && $this->canUpdateExtDataInDatabase())
            {
                // Show update link, but only if the plugin is not network enabled and is allowed to update from current version
                $updatePageURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&update=1&noheader=true');
                $retLinks[] = '<a href="'.esc_url($updatePageURL).'">'.$this->lang->escHTML('LANG_UPDATE_TEXT').'</a>';
            }
        }

        return $retLinks;
    }

    /**
     * Get additional links to show in next to local plugin description
     * @return array
     */
    public function getInfoLinks()
    {
        $retLinks = array();

        if($this->conf->isNetworkEnabled())
        {
            // Additional local links to show, but only if the plugin is network-enabled
            if($this->isExtDataUpToDateInDatabase())
            {
                // Show additional info links only if the plugin is up-to-date
                $statusURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status');
                $retLinks[] = '<a href="'.esc_url($statusURL).'">'.$this->lang->escHTML('LANG_STATUS_TEXT').'</a>';
            }
        } else
        {
            // Additional local links to show, but only if the plugin is locally enabled
            if($this->isExtDataUpToDateInDatabase())
            {
                // Show additional info links only if the plugin is up-to-date
                $statusURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status');
                $retLinks[] = '<a href="'.esc_url($statusURL).'">'.$this->lang->escHTML('LANG_STATUS_TEXT').'</a>';
            }
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
     * @note2 - This function says if the data exists for at least one extension of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkPluginDataExistsOf($paramRequiredPluginSemver)
    {
        $retExists = false;
        $sqlQuery = "";
        $validRequiredPluginSemver = StaticValidator::getValidSemver($paramRequiredPluginSemver, false);

        if(version_compare($validRequiredPluginSemver, '5.1.0', '>=') && $this->checkV510SettingsTableExists() && $this->checkV510ExtCodeColumnExists())
        {
            // We are testing FM 5.1.0 or later database version
            $validBlogId = intval($this->blogId);
            // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
            // NOTE #2: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
            //          with a newer framework version won't pass the test
            // NOTE #3: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings WHERE blog_id='{$validBlogId}'";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // Check if plugin is installed or not for this blog_id
            $retExists = !is_null($hasSettings) ? true : false;
        } else if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && version_compare($validRequiredPluginSemver, '5.1.0', '<') && $this->__legacy__check_V500_SettingsTableExists())
        {
            // FM 5.0.0
            if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && $this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                $validBlogId = intval($this->blogId);
                // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
                // NOTE #2: 'car_rental_' is mandatory here to correctly know the version,
                //          as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable} WHERE blog_id='{$validBlogId}'";
                $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
                // NS plugins is installed or not for this blog_id
                $retExists = !is_null($hasSettings) ? true : false;
            }
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkPluginDataExistsOf(\$paramRequiredPluginSemver: ".esc_html($paramRequiredPluginSemver)."): ".($retExists ? "Yes" : "No")."<br />SQL: ".esc_br_html($sqlQuery)."<br />";
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
     * on existence of compatible data for this exact extension
     *
     * @note1 - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @note2 - This function says if the data exists of this exact extension
     * @return bool
     */
    public function checkExtCompatibleDataExists()
    {
        $retExists = false;
        $sqlQuery = "";

        if($this->checkV510SettingsTableExists() && $this->checkV510ExtCodeColumnExists())
        {
            // We are testing FM 5.1.0 or later database version
            $validBlogId = intval($this->blogId);
            // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
            // NOTE #2: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
            //          with a newer framework version won't pass the test
            // NOTE #3: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings WHERE ext_code='{$this->conf->getExtCode()}' AND blog_id='{$validBlogId}'";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // NS plugins is installed or not for this blog_id
            $retExists = !is_null($hasSettings) ? true : false;
        } else if($this->__legacy__check_V500_SettingsTableExists())
        {
            // FM 5.0.0
            if($this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                $validBlogId = intval($this->blogId);
                // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
                // NOTE #2: 'car_rental_' is mandatory here to correctly know the version,
                //          as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable} WHERE blog_id='{$validBlogId}'";
                $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
                // NS plugins is installed or not for this blog_id
                $retExists = !is_null($hasSettings) ? true : false;
            }
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkCompatibleExtDataExists(): ".($retExists ? "Yes" : "No")."<br />SQL: ".esc_br_html($sqlQuery)."<br />";
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
     * @note2 - This function says if the data exists of this exact extension of required semver
     * @param string $paramRequiredPluginSemver
     * @return bool
     */
    public function checkExtDataExistsOf($paramRequiredPluginSemver)
    {
        $retExists = false;
        $sqlQuery = "";
        $validRequiredPluginSemver = StaticValidator::getValidSemver($paramRequiredPluginSemver, false);

        if(version_compare($validRequiredPluginSemver, '5.1.0', '>=') && $this->checkV510SettingsTableExists() && $this->checkV510ExtCodeColumnExists())
        {
            // We are testing FM 5.1.0 or later database version
            $validBlogId = intval($this->blogId);
            // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
            // NOTE #2: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
            //          with a newer framework version won't pass the test
            // NOTE #3: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
            $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings WHERE ext_code='{$this->conf->getExtCode()}' AND blog_id='{$validBlogId}'";
            $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
            // NS plugins is installed or not for this blog_id
            $retExists = !is_null($hasSettings) ? true : false;
        } else if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && version_compare($validRequiredPluginSemver, '5.1.0', '<') && $this->__legacy__check_V500_SettingsTableExists())
        {
            // FM 5.0.0
            if(version_compare($validRequiredPluginSemver, '5.0.0', '>=') && $this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                $validBlogId = intval($this->blogId);
                // NOTE #1: SELECT 1 is not supported by WordPress, PHP, or get_var, so it has to be an exact field name
                // NOTE #2: 'car_rental_' is mandatory here to correctly know the version,
                //          as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $sqlQuery = "SELECT conf_key FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable} WHERE blog_id='{$validBlogId}'";
                $hasSettings = $this->conf->getInternalWPDB()->get_var($sqlQuery, 0, 0);
                // NS plugins is installed or not for this blog_id
                $retExists = !is_null($hasSettings) ? true : false;
            }
        }

        // DEBUG
        if($this->debugMode)
        {
            $debugMessage = "Debug: checkExtDataExistsOf(\$paramRequiredPluginSemver: ".esc_html($paramRequiredPluginSemver)."): ".($retExists ? "Yes" : "No")."<br />SQL: ".esc_br_html($sqlQuery)."<br />";
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
     * @return string
     */
    public function getExtSemverInDatabase()
    {
        // '0.0.0' is the semver that can be either older than oldest compatible semver, or when the semver is not detected
        // I.e. in oldest semvers the chosen row of plugin semver did not existed at all
        $databaseSemver = '0.0.0';

        if($this->checkV510SettingsTableExists())
        {
            // NOTE: The section bellow were moved to internal statement to save SQL queries for scenario when table exists but not column
            if($this->checkV510ExtCodeColumnExists())
            {
                // We are testing FM 5.1.0 or later database version
                $validBlogId = intval($this->blogId);

                // FM 5.1.0 and newer check
                // NOTE #1: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
                //          with a newer framework version won't pass the test
                // NOTE #2: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
                $semverSQL = "
                    SELECT conf_value AS plugin_semver
                    FROM {$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings
                    WHERE conf_key='conf_plugin_semver' AND ext_code='{$this->conf->getExtCode()}' AND blog_id='{$validBlogId}'
                ";
                $databaseSemverResult = $this->conf->getInternalWPDB()->get_var($semverSQL);
                if(!is_null($databaseSemverResult))
                {
                    // FM 5.1.0 and newer
                    $databaseSemver = StaticValidator::getValidSemver($databaseSemverResult, false);
                }
            }
        } else if($this->__legacy__check_V500_SettingsTableExists())
        {
            // NOTE: The section bellow were moved to internal statement to save SQL queries for scenario when table exists but not column
            if($this->__legacy__checkV500BlogIdColumnExists())
            {
                // We are testing FM 5.0.0 database version
                $validBlogId = intval($this->blogId);
                // NOTE: 'car_rental_' is mandatory here to correctly know the version,
                //       as in V5 the tables are named 'car_rental_x', while in V6 they are named under 'rental_x'
                $semverSQL = "
                    SELECT conf_value AS plugin_semver
                    FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable}
                    WHERE conf_key='conf_plugin_semver' AND blog_id='{$validBlogId}'
                ";
                $databaseSemverResult = $this->conf->getInternalWPDB()->get_var($semverSQL);
                if(!is_null($databaseSemverResult))
                {
                    // FM 5.0.1 and newer
                    $databaseSemver = StaticValidator::getValidSemver($databaseSemverResult, false);
                } else
                {
                    // FM 5.0.0 check
                    $versionSQL = "
                        SELECT conf_value AS plugin_version
                        FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable}
                        WHERE conf_key='conf_plugin_version' AND blog_id='{$validBlogId}'
                    ";
                    $databaseVersionResult = $this->conf->getInternalWPDB()->get_var($versionSQL);
                    if(!is_null($databaseVersionResult))
                    {
                        $databaseSemver = StaticValidator::getValidSemver($databaseVersionResult, false);
                    }
                }
            }
        }

        return $databaseSemver;
    }

    /**
     * Is the plugin's database semver is same as code semver. If no - we should be read for update
     * @note make sure the blog id here is ok for network
     * @return bool
     */
    public function isExtDataUpToDateInDatabase()
    {
        $extSemverInDatabase = $this->getExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $isUpToDate = version_compare($extSemverInDatabase, $codeSemver, '==') ? true : false;

        // DEBUG
        if($this->debugMode >= 2 && $this->echoDebug)
        {
            echo "[isExtDataUpToDateInDatabase()] DB SEMVER: {$extSemverInDatabase}<br />";
            echo "[isExtDataUpToDateInDatabase()] CODE SEMVER: {$codeSemver}<br />";
            echo "[isExtDataUpToDateInDatabase()] IS EXT DATA UP TO DATE IN DB: ".var_export($isUpToDate, true)."<br />";
        }

        return $isUpToDate;
    }

    /**
     * NOTE: Update may exist, but the system might be not compatible for update
     * @return bool
     */
    public function checkExtUpdateExists()
    {
        $canUpdate = false;
        $extSemverInDatabase = $this->getExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        if(version_compare($extSemverInDatabase, $codeSemver, '<'))
        {
            $canUpdate = true;
        }

        // DEBUG
        if($this->debugMode >= 2 && $this->echoDebug)
        {
            echo "[checkExtUpdateExists()] DB SEMVER: {$extSemverInDatabase}<br />";
            echo "[checkExtUpdateExists()] CODE SEMVER: {$codeSemver}<br />";
            echo "[checkExtUpdateExists()] UPDATE EXISTS: ".var_export($canUpdate, true)."<br />";
        }

        return $canUpdate;
    }

    /**
     * @return bool
     */
    public function canUpdateExtDataInDatabase()
    {
        $canUpdate = false;
        $extSemverInDatabase = $this->getExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $oldestCompatibleSemver = $this->conf->getOldestCompatiblePluginSemver();
        if(version_compare($extSemverInDatabase, $oldestCompatibleSemver, '>=') && version_compare($extSemverInDatabase, $codeSemver, '<'))
        {
            $canUpdate = true;
        }

        // DEBUG
        if($this->debugMode >= 2 && $this->echoDebug)
        {
            echo "[canUpdateExtDataInDatabase()] DB SEMVER: {$extSemverInDatabase}<br />";
            echo "[canUpdateExtDataInDatabase()] OLDEST-COMPAT SEMVER: {$oldestCompatibleSemver}<br />";
            echo "[canUpdateExtDataInDatabase()] CODE SEMVER: {$codeSemver}<br />";
            echo "[canUpdateExtDataInDatabase()] CAN UPDATE: ".var_export($canUpdate, true)."<br />";
        }

        return $canUpdate;
    }

    /**
     * Can we do a major upgrade, i.e. from V1.*.* to V2.*.* etc., not V1.0.* to V1.1.*
     * @return bool
     */
    public function canMajorlyUpgradeExtDataInDatabase()
    {
        $majorUpgrade = false;
        $extSemverInDatabase = $this->getExtSemverInDatabase();
        $codeSemver = $this->conf->getPluginSemver();
        $oldestCompatibleSemver = $this->conf->getOldestCompatiblePluginSemver();
        $dbSemverMajor = (new Semver($extSemverInDatabase, false))->getMajor();
        $codeSemverMajor = (new Semver($codeSemver, false))->getMajor();
        if(version_compare($extSemverInDatabase, $oldestCompatibleSemver, '>=') && $dbSemverMajor < $codeSemverMajor)
        {
            $majorUpgrade = true;
        }
        return $majorUpgrade;
    }
}