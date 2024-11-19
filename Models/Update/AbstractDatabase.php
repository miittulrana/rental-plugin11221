<?php
/**
 * Database updater
 * NOTE: This is in-the-middle class, so it must not be final, and it's variables should not be private

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Semver\Semver;
use FleetManagement\Models\Validation\StaticValidator;

abstract class AbstractDatabase extends AbstractStack
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0; // 0 - off, 1 - standard, 2 - deep debug
    protected $extCode                  = '';
    protected $blogId                   = 0;

    // NOTE: The 3.2.0 semver number here is ok, because it defines the case of older plugin semvers,
    // when plugin semver data was not saved to db
    protected $extSemverInDatabase      = '3.2.0';
    protected $currentMajor             = 0; // Positive integer [X]
    protected $currentMinor             = 0; // Positive integer [Y]
    protected $currentPatch             = 0; // Positive integer [Z]
    protected $internalCounter          = 0;
    // NOTE: It must have '500' in it, despite some extensions did not existed at that time, to avoid 'table not exists' issues during install
    private $legacy50X_SettingsTable    = '';

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param string $paramExtCode - for 5.1.0 and later
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramExtCode, $paramBlogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->extCode = StaticValidator::getValidCode($paramExtCode, "", true, false, false); // For 5.1.0 and later
        $this->blogId = StaticValidator::getValidPositiveInteger($paramBlogId, 0);
        // Reset internal counter and use it class-wide to count all queries processed (but maybe not executed)
        $this->internalCounter = 0;
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

        // Set database semver
        $this->setExtSemverInDatabase();

        // Set current
        $objSemver = new Semver($this->extSemverInDatabase, false);
        $this->currentMajor = $objSemver->getMajor();
        $this->currentMinor = $objSemver->getMinor();
        $this->currentPatch = $objSemver->getPatch();

        if($this->debugMode)
        {
            $debugMessage = "AbstractDatabase::__construct: \$this->blogId: {$this->blogId}, \$this->extSemverInDatabase: {$this->extSemverInDatabase}";
            $debugMessage .= "<br />AbstractDatabase::__construct: CURRENT MAJOR-MINOR-PATCH: {$this->currentMajor} - {$this->currentMinor} - {$this->currentPatch}";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
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
     * @note - This function maintains backwards compatibility to FM 5.0.0 and older
     */
    private function setExtSemverInDatabase()
    {
        // In case if version is not found, we will use '0.0.0'
        $databaseSemver = '0.0.0';
        $doV5FormatCheck = false;

        // NOTE #1: We must use here getWP_Prefix() + getPluginPrefix() check, to make sure that on older plugin version
        //          with a newer framework version won't pass the test
        // NOTE #2: getWP_Prefix() supports both - network-enabled and locally-enabled plugin version
        $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getWP_Prefix()}{$this->conf->getPluginPrefix()}settings` LIKE 'ext_code'";
        $extCodeColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);

        // As version is not yet set, we use blog column to check
        // Do V6 or later check
        if(!is_null($extCodeColumnResult))
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
            } else
            {
                $doV5FormatCheck = true;
            }
        } else
        {
            $doV5FormatCheck = true;
        }
        if($doV5FormatCheck)
        {
            // NOTE: 'car_rental_' is mandatory here to correctly know the version
            $sqlQuery = "SHOW COLUMNS FROM `{$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable}` LIKE 'blog_id'";
            $v5BlogIdColumnResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            // As version is not yet set, we use blog column to check
            if(!is_null($v5BlogIdColumnResult))
            {
                // We are testing FM 5.0.0 or newer database version
                $validBlogId = intval($this->blogId);
                // NOTE: 'car_rental_' is mandatory here to correctly know the version
                $semverSQL = "
                    SELECT conf_value AS plugin_semver
                    FROM {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable}
                    WHERE conf_key='conf_plugin_semver' AND blog_id='{$validBlogId}'
                ";
                $databaseSemverResult = $this->conf->getInternalWPDB()->get_var($semverSQL);
                if(!is_null($databaseSemverResult))
                {
                    // FM 5.0.1
                    $databaseSemver = StaticValidator::getValidSemver($databaseSemverResult, false);
                } else
                {
                    // FM 5.0.0
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

        $this->extSemverInDatabase = $databaseSemver;

        if($this->debugMode)
        {
            $debugMessage = "DB SEMVER: {$databaseSemver}";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
        }

        return $databaseSemver;
    }

    /**
     * This method for internal use only
     * @note - This function maintains backwards compatibility to FM 5.0.0 and newer
     * @param int $paramNewValue
     * @return int
     */
    protected function setCounter($paramNewValue)
    {
        $updated = false;
        $validNewValue = $paramNewValue > 0 ? intval($paramNewValue) : 0;
        if(version_compare($this->extSemverInDatabase, '5.1.0', '>='))
        {
            // We are testing FM 5.1.0 or later database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				UPDATE {$this->conf->getPrefix()}settings SET conf_value='{$validNewValue}'
				WHERE conf_key='conf_updated' AND ext_code='{$this->conf->getExtCode()}' AND blog_id='{$validBlogId}'
			";
            $ok = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if($ok !== false)
            {
                $updated = true;
            }
        } else if(version_compare($this->extSemverInDatabase, '5.0.0', '>=') && version_compare($this->extSemverInDatabase, '5.1.0', '<'))
        {
            // We are testing FM 5.0.Z database version
            // NOTE: 'car_rental_' is mandatory here to correctly know the version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				UPDATE {$this->conf->getWP_Prefix()}{$this->legacy50X_SettingsTable} SET conf_value='{$validNewValue}'
				WHERE conf_key='conf_updated' AND blog_id='{$validBlogId}'
			";
            $ok = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if($ok !== false)
            {
                $updated = true;
            }
        }

        if($this->debugMode == 2)
        {
            if($updated === false)
            {
                $debugMessage = '<span style="font-weight:bold;color: red;">FAILED</span> TO SET DB UPDATE COUNTER TO: '.$validNewValue;
            } else
            {
                $debugMessage = 'DB UPDATE COUNTER SET TO: '.$validNewValue;
            }
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
        }

        return $updated;
    }

    /**
     * This method for internal use only
     * @note - This function maintains backwards compatibility to FM 5.0.0 and older
     */
    protected function getCounter()
    {
        // If that is not the newest semver, then for sure the database update counter is 0
        $updateCounter = 0;
        if(version_compare($this->extSemverInDatabase, '5.1.0', '>='))
        {
            // We are testing FM 5.1.0 or later database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				SELECT conf_value AS counter
				FROM {$this->conf->getPrefix()}settings
				WHERE conf_key='conf_updated' AND ext_code='{$this->conf->getExtCode()}' AND blog_id='{$validBlogId}'
			";
            $dbUpdateCounterValue = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if(!is_null($dbUpdateCounterValue) && $dbUpdateCounterValue > 0)
            {
                $updateCounter = intval($dbUpdateCounterValue);
            }
        } else if(version_compare($this->extSemverInDatabase, '5.0.0', '>=') && version_compare($this->extSemverInDatabase, '5.1.0', '<'))
        {
            // We are testing FM 5.Y.Z database version
            $validBlogId = intval($this->blogId);
            $sqlQuery = "
				SELECT conf_value AS counter
				FROM {$this->conf->getPrefix()}settings
				WHERE conf_key='conf_updated' AND blog_id='{$validBlogId}'
			";
            $dbUpdateCounterValue = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            if(!is_null($dbUpdateCounterValue) && $dbUpdateCounterValue > 0)
            {
                $updateCounter = intval($dbUpdateCounterValue);
            }
        }

        if($this->debugMode)
        {
            $debugMessage = "GOT CURRENT DB UPDATE COUNTER: {$updateCounter}";
            $this->debugMessages[] = $debugMessage;
            // Do not echo here, as this class is used in redirect
            //echo "<br />".$debugMessage;
        }

        return $updateCounter;
    }

    /**
     * Insert/Update/Alter data to database
     * @param array $paramArrTrustedSQLs
     * @return bool
     */
    protected function executeQueries(array $paramArrTrustedSQLs)
    {
        $currentCounter = $this->getCounter();

        $completed = true;
        foreach($paramArrTrustedSQLs AS $sqlQuery)
        {
            // Increase internal queries counter
            $this->internalCounter = $this->internalCounter + 1;
            if($currentCounter > $this->internalCounter)
            {
                // Do nothing Just SKIP this query
            } else
            {
                $ok = $this->executeQuery($sqlQuery);
                if($ok === false)
                {
                    // Stop executing any more queries
                    $completed = false;
                    break;
                } else
                {
                    // Increase currently executed queries counter
                    $this->setCounter($this->internalCounter);
                }
            }
        }

        return $completed;
    }

    /**
     * Insert/Update/Alter data to database
     * @param string $paramTrustedSQLQuery
     * @return bool
     */
    protected function executeQuery($paramTrustedSQLQuery)
    {
        // Try to execute current query
        $executed = $this->conf->getInternalWPDB()->query($paramTrustedSQLQuery);
        if($executed === false)
        {
            $executed = false;
            $startIdentifier = '`'.$this->conf->getPrefix();
            $endIdentifier = '`';
            $startCharPosOfTableName = strpos($paramTrustedSQLQuery, $startIdentifier) + strlen($startIdentifier);
            $tableLength = strpos($paramTrustedSQLQuery, $endIdentifier, $startCharPosOfTableName) - $startCharPosOfTableName;
            $tableName = '';
            if($startCharPosOfTableName > 0 && $tableLength > 0)
            {
                $tableName = substr($paramTrustedSQLQuery, $startCharPosOfTableName, $tableLength);
            }
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_DATABASE_UPDATE_QUERY_FAILED_FOR_S_TABLE_AT_COUNTER_D_ERROR_TEXT'), $this->blogId, $tableName, $this->internalCounter);
            if($this->debugMode)
            {
                $debugMessage = "FAILED AT QUERY:<br />".nl2br($paramTrustedSQLQuery);
                $this->debugMessages[] = $debugMessage;
                // Do not echo here, as this class is used in redirect
                //echo "<br />".$debugMessage;
            }
        }

        return $executed;
    }
}