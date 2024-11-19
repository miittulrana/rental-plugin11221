<?php
/**
 * Demo import manager

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Import;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\StackInterface;

final class Demo extends AbstractStack implements StackInterface
{
    private $conf                   = null;
    private $lang                   = null;
    private $debugMode              = 0;
    private $demoId                 = 0;
    /**
     * @var array - plugin tables, ordered by table name
     */
    private static $pluginTables    = array(
        "body_types",
        "bookings", // Only Truncate
        "booking_options", // Only Truncate
        "closed_dates", // Only Truncate
        "customers",
        "deposits",
        "discounts",
        "distances",
        "emails",
        "extras",
        "features",
        "fuel_types",
        "invoices",
        "items",
        "item_features",
        "item_locations",
        "locations",
        "logs",
        "manufacturers",
        "options",
        "payment_methods",
        "prepayments",
        "price_groups",
        "price_plans",
        "settings",
        "taxes",
        "transmission_types",
    );

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramDemoId)
    {
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->conf = $paramConf;
        $this->lang = $paramLang;

        $this->demoId = intval($paramDemoId);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->demoId;
    }

    private function replaceBBCodes($trustedSQL_Data, $paramWPDemoPartnerId)
    {
        $validWPDemoPartnerId = intval($paramWPDemoPartnerId);

        // Spring
        $springStartTimestamp = strtotime(date("Y")."-03-01 00:00:00");
        $springEndTimestamp = strtotime(date("Y")."-05-31 23:59:59");

        // Summer
        $summerStartTimestamp = strtotime(date("Y")."-06-01 00:00:00");
        $summerEndTimestamp = strtotime(date("Y")."-08-31 23:59:59");

        // Autumn
        $autumnStartTimestamp = strtotime(date("Y")."-09-01 00:00:00");
        $autumnEndTimestamp = strtotime(date("Y")."-11-30 23:59:59");

        // Winter
        $winterStartTimestamp = strtotime(date("Y")."-12-01 00:00:00");
        $lastDateOfNextWinter = date("Y-m-t", strtotime((date("Y")+1)."-02-01"));
        $winterEndTimestamp = strtotime("{$lastDateOfNextWinter} 23:59:59");

        // Today & Yesterday
        $todayTimestamp = strtotime(date("Y-m-d H:00:00"));
        $yesterdayTimestamp = $todayTimestamp - 86400;

        // WP Prefix is used in demos only in one place - in WordPress user meta data table for blog prefix
        $arrFrom = array(
            '[WP_PREFIX]', '[WP_DEMO_PARTNER_ID]', '[BLOG_ID]',
            '[SITE_URL]',
            '[SPRING_START_TIMESTAMP]', '[SPRING_END_TIMESTAMP]',
            '[SUMMER_START_TIMESTAMP]', '[SUMMER_END_TIMESTAMP]',
            '[AUTUMN_START_TIMESTAMP]', '[AUTUMN_END_TIMESTAMP]',
            '[WINTER_START_TIMESTAMP]', '[WINTER_END_TIMESTAMP]',
            '[TODAY_TIMESTAMP]', '[YESTERDAY_TIMESTAMP]'
        );
        $arrTo = array(
            $this->conf->getBlogPrefix(), $validWPDemoPartnerId, $this->conf->getBlogId(),
            get_site_url(),
            $springStartTimestamp, $springEndTimestamp,
            $summerStartTimestamp, $summerEndTimestamp,
            $autumnStartTimestamp, $autumnEndTimestamp,
            $winterStartTimestamp, $winterEndTimestamp,
            $todayTimestamp, $yesterdayTimestamp
        );
        $replacedSQL_Data = str_replace($arrFrom, $arrTo, $trustedSQL_Data);

        return $replacedSQL_Data;
    }

    /**
     * Replace all content
     * @note - Replace mode helps us here to avoid conflicts with already existing regular WordPress posts
     * @return bool
     */
    public function replaceContent()
    {
        // Insert SQL
        $inserted = true;
        // If importable demo file is provided and it's file is readable
        $demoSQL_PathWithFilename = $this->getDemoSQL_PathWithFilename();
        $validDemoWP_UserLogin = esc_sql(sanitize_text_field($this->conf->getDemoWP_UserLogin()));

        // DEBUG
        if($this->debugMode > 0)
        {
            $debugMessage = "[DEMO] Demo SQL file with path: ".$demoSQL_PathWithFilename;
            $this->debugMessages[] = $debugMessage;
            //echo $debugMessage; // This class is used with redirect, do not output here
        }

        // Get WordPress '<DEMO_WP_USER_LOGIN]>' user id, if exists, users auto increment - used in the demo import file
        $sqlQuery = "SELECT ID FROM `{$this->conf->getInternalWPDB()->users}` WHERE user_login='{$validDemoWP_UserLogin}' ORDER BY ID DESC LIMIT 1";
        $wpDemoPartnerIdResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        if(!is_null($wpDemoPartnerIdResult))
        {
            $wpDemoPartnerId = $wpDemoPartnerIdResult;
        } else
        {
            // Get WordPress users table auto increment - used in the demo import file
            $sqlQuery = "SELECT ID FROM `{$this->conf->getInternalWPDB()->users}` WHERE 1 ORDER BY ID DESC LIMIT 1";
            $wpDemoPartnerIdResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
            $wpDemoPartnerId = !is_null($wpDemoPartnerIdResult) ? $wpDemoPartnerIdResult : 0;
            $wpDemoPartnerId = $wpDemoPartnerId + 1; // Increase to next user id
        }

        // Get WordPress posts table auto increment - used in the demo import file
        $sqlQuery = "SELECT ID FROM `{$this->conf->getBlogPrefix()}posts` WHERE 1 ORDER BY ID DESC LIMIT 1";
        $wpPostsAIResult = $this->conf->getInternalWPDB()->get_var($sqlQuery);
        $wpPostsAI = !is_null($wpPostsAIResult) ? $wpPostsAIResult : 0;

        // Get extension auto increment - used in the demo import file
        $extAI = $this->conf->getBlogId() > 1 ? $this->conf->getBlogId() * 100 : 0;

        if($demoSQL_PathWithFilename != '' && is_readable($demoSQL_PathWithFilename))
        {
            // Clean the values
            $arrReplaceSQL = array();
            $arrPluginReplaceSQL = array();

            // Fill the values
            require ($demoSQL_PathWithFilename);

            // Replace data in WP tables
            foreach($arrReplaceSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->replaceBBCodes($sqlData, $wpDemoPartnerId);

                // Some WordPress tables are use global data in the multisite mode
                if($sqlTable == "users")
                {
                    $prefixedSQLTable = $this->conf->getInternalWPDB()->users;
                } else if($sqlTable == "usermeta")
                {
                    $prefixedSQLTable = $this->conf->getInternalWPDB()->usermeta;
                } else
                {
                    $prefixedSQLTable = $this->conf->getBlogPrefix().$sqlTable;
                }
                $sqlQuery = "
                    REPLACE INTO `{$prefixedSQLTable}` {$replacedSQL_Data}
                ";
                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === false)
                {
                    if($this->debugMode)
                    {
                        $debugMessage = "FAILED TO REPLACE IN WP TABLE: ".esc_br_html($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        //echo $debugMessage; // This class is used with redirect, do not output here
                    }
                    $inserted = false;
                    // NOTE: Do not break the loop here - let it proceed and sum-up results at the end
                }
            }

            // Parse blog id and plugin version BB codes and replace data in plugin tables
            foreach($arrPluginReplaceSQL AS $sqlTable => $sqlData)
            {
                $replacedSQL_Data = $this->replaceBBCodes($sqlData, $wpDemoPartnerId);
                $sqlQuery = "
                    REPLACE INTO `{$this->conf->getPrefix()}{$sqlTable}` {$replacedSQL_Data}
                ";

                // DEBUG
                if($this->debugMode == 2)
                {
                    $debugMessage = "{$sqlQuery};";
                    $this->debugMessages[] = $debugMessage;
                    //echo "<br />{$debugMessage}"; // This class is used with redirect, do not output here
                }

                $ok = $this->conf->getInternalWPDB()->query($sqlQuery);
                if($ok === false)
                {
                    // DEBUG
                    if($this->debugMode > 0)
                    {
                        $debugMessage = "[DEMO] FAILED TO REPLACE IN PLUGIN TABLE: ".esc_br_html($sqlQuery);
                        $this->debugMessages[] = $debugMessage;
                        //echo $debugMessage; // This class is used with redirect, do not output here
                    }
                    $inserted = false;
                    // NOTE: Do not break the loop here - let it proceed and sum-up results at the end
                }
            }
        } else
        {
            $this->errorMessages[] = $this->lang->getText('LANG_DEMO_SQL_FILE_DOES_NOT_EXIST_OR_IS_NOT_READABLE_TEXT');
        }

        if($inserted === false)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_DEMO_INSERTION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_DEMO_INSERTED_TEXT');
        }

        return $inserted;
    }

    /**
     * @return bool
     */
    public function deleteContent()
    {
        // Clear all tables
        $deleted = true;
        foreach(static::$pluginTables AS $paramPluginTable)
        {
            $validPluginTable = esc_sql(sanitize_text_field($paramPluginTable)); // for sql queries only
            if($validPluginTable == "settings")
            {
                // Settings table
                $ok = $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}".$validPluginTable."
                    WHERE conf_key NOT IN (
                        'conf_plugin_semver'
                    ) AND blog_id='{$this->conf->getBlogId()}'
                ");
            } else
            {
                // Other table
                $ok = $this->conf->getInternalWPDB()->query("
                    DELETE FROM {$this->conf->getPrefix()}".$validPluginTable."
                    WHERE blog_id='{$this->conf->getBlogId()}'
                ");
            }

            if($ok === false)
            {
                $deleted = false;
            }
        }

        return $deleted;
    }

    /**
     * @return string
     */
    private function getDemoSQL_PathWithFilename()
    {
        $demoSQL_PathWithFilename = '';

        $extDemosPath = $this->conf->getRouting()->getSQLsPath('', false);

        $phpFiles = array();
        if(is_dir($extDemosPath))
        {
            // Get PHP folder file list
            $tmpPhpFiles = StaticFile::getFolderFileList($extDemosPath, array("php"));
            $tmpFiles = array();
            foreach ($tmpPhpFiles AS $tmpPhpFile)
            {
                if(!in_array($tmpPhpFile, $tmpFiles))
                {
                    $tmpFiles[] = $tmpPhpFile;
                    $phpFiles[] = array(
                        "file_path" => $extDemosPath,
                        "file_name" => $tmpPhpFile,
                    );
                }
            }
        }

        foreach ($phpFiles AS $phpFile)
        {
            $break = false;
            $validCurrentDemoId = 0;
            $currentDemoDisabled = false;
            // Case-insensitive check - Find the position of the last occurrence of a case-insensitive substring in a string
            $firstPHP_DemoPos = stripos($phpFile['file_name'], "DemoSQL");
            $lastPHP_Pos = strripos($phpFile['file_name'], ".php");
            $requiredPHP_Pos = strlen($phpFile['file_name']) - strlen(".php");
            $phpDemoData = array();
            if($firstPHP_DemoPos !== false && $lastPHP_Pos === $requiredPHP_Pos)
            {
                $phpDemoData = get_file_data($phpFile['file_path'].$phpFile['file_name'], array('DemoUID' => 'Demo UID', 'DemoName' => 'Demo Name', 'Disabled' => 'Disabled'));

                // Format data
                $validCurrentDemoId = intval($phpDemoData['DemoUID']);
                $currentDemoDisabled = $phpDemoData['Disabled'] == 1 ? true : false;

                if($validCurrentDemoId == $this->demoId)
                {
                    $break = true;
                    if($currentDemoDisabled === false)
                    {
                        $validFilePath = sanitize_text_field($phpFile['file_path']);
                        $validFileName = sanitize_file_name($phpFile['file_name']);
                        $demoSQL_PathWithFilename = $validFilePath.$validFileName;
                    }
                }
            }

            // DEBUG
            if($this->debugMode == 2)
            {
                $debugMessage = "<br />[DEMO] Current Demo Id: {$validCurrentDemoId}";
                $debugMessage .= "<br />[DEMO] Current Demo Disabled: ".var_export($currentDemoDisabled, true);
                $debugMessage .= "<br />[DEMO] \$phpDemoData: " . nl2br(print_r($phpDemoData, true));
                $debugMessage .= "[DEMO] File path: {$phpFile['file_path']}";
                $debugMessage .= "<br />[DEMO] Filename: {$phpFile['file_name']}";
                $debugMessage .= "<br />[DEMO] Demo SQL path with filename: {$demoSQL_PathWithFilename}";
                $debugMessage .= "<br />[DEMO] \$firstPHP_DemoPos: {$firstPHP_DemoPos} === 0";
                $debugMessage .= "<br />[DEMO] \$lastPHP_Pos: {$lastPHP_Pos} === \$requiredPHP_Pos: {$requiredPHP_Pos}";
                $debugMessage .= "<br />[DEMO] BREAK (Match found): ".var_export($break, true);
                $this->debugMessages[] = $debugMessage;
                // echo "<br />".$debugMessage; // This class is used with redirect, do not output here
            }

            if($break)
            {
                break;
            }
        }

        return $demoSQL_PathWithFilename;
    }
}