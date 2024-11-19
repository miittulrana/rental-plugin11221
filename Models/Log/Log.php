<?php
/**
 * Log Element
 * 
 * @note - Log types:
 *      1. customer-lookup - customer lookup log,
 *      2. payment-callback - payment api log
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Log;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Detect\StaticDetector;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Log
{
    // For security reasons we do not allow to change this value from admin
    const EXPIRATION_PERIOD                 = 3600; // 1 hour

    private $conf 	                        = null;
    private $lang 		                    = null;
    private $debugMode 	                    = 0;
    private $shortDateFormat                = "m/d/Y";
    private $maxRequestsPerPeriod           = 50;
    private $maxFailedRequestsPerPeriod     = 3;
    private $logId                          = 0;

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramLogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramLogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set log id
        $this->logId = StaticValidator::getValidPositiveInteger($paramLogId, 0);

        $this->maxRequestsPerPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_api_max_requests_per_period', 'positive_integer', 50);
        $this->maxFailedRequestsPerPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_api_max_failed_requests_per_period', 'positive_integer', 3);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
    }

    private function getDataFromDatabaseById($paramLogId)
    {
        $validLogId = StaticValidator::getValidPositiveInteger($paramLogId, 0);

        $retData = $this->conf->getInternalWPDB()->get_row("
            SELECT *, error_message AS errors, log_type AS action,

            'E-mail' AS dimension_1, 'Year' AS dimension_2, 'Year required' AS dimension_3,
            IF(log_type='customer-lookup', 'Total requests left', '') AS dimension_4,
            IF(log_type='customer-lookup', 'Failed requests left', '') AS dimension_5,
            IF(log_type='customer-lookup', 'E-mail attempts left', '') AS dimension_6,
            '' AS dimension_7, '' AS dimension_8,
            '' AS dimension_9, '' AS dimension_10, '' AS dimension_11, '' AS dimension_12, '' AS dimension_13,
            '' AS dimension_14, '' AS dimension_15, '' AS dimension_16, '' AS dimension_17, '' AS dimension_18,
            '' AS dimension_19, '' AS dimension_20, '' AS dimension_21, '' AS dimension_22, '' AS dimension_23,
            '' AS dimension_24, '' AS dimension_25, '' AS dimension_26, '' AS dimension_27, '' AS dimension_28,
            '' AS dimension_29, '' AS dimension_30, '' AS dimension_31, '' AS dimension_32, '' AS dimension_33,
            '' AS dimension_34, '' AS dimension_35, '' AS dimension_36, '' AS dimension_37, '' AS dimension_38,
            '' AS dimension_39, '' AS dimension_40,
            
            email AS value_1, `year` AS value_2,  year_required AS value_3,
            IF(log_type='customer-lookup', total_requests_left, '') AS value_4,
            IF(log_type='customer-lookup', failed_requests_left, '') AS value_5,
            IF(log_type='customer-lookup', email_attempts_left, '') AS value_6,
            '' AS value_7, '' AS value_8,
            '' AS value_9, '' AS value_10, '' AS value_11, '' AS value_12, '' AS value_13,
            '' AS value_14, '' AS value_15, '' AS value_16, '' AS value_17, '' AS value_18,
            '' AS value_19, '' AS value_20, '' AS value_21, '' AS value_22, '' AS value_23,
            '' AS value_24, '' AS value_25, '' AS value_26, '' AS value_27, '' AS value_28,
            '' AS value_29, '' AS value_30, '' AS value_31, '' AS value_32, '' AS value_33,
            '' AS value_34, '' AS value_35, '' AS value_36, '' AS value_37, '' AS value_38,
            '' AS value_39, '' AS value_40,
            
            '' AS agent, 'Unknown' AS results_found
            FROM {$this->conf->getPrefix()}logs
            WHERE log_id='{$validLogId}'
        ", ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->logId;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @param bool $paramPrefillWhenNull
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->logId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['action'] = stripslashes($ret['action']);

            // 1-10
            $ret['dimension_1'] = stripslashes($ret['dimension_1']);
            $ret['value_1'] = stripslashes($ret['value_1']);
            $ret['dimension_2'] = stripslashes($ret['dimension_2']);
            $ret['value_2'] = stripslashes($ret['value_2']);
            $ret['dimension_3'] = stripslashes($ret['dimension_3']);
            $ret['value_3'] = stripslashes($ret['value_3']);
            $ret['dimension_4'] = stripslashes($ret['dimension_4']);
            $ret['value_4'] = stripslashes($ret['value_4']);
            $ret['dimension_5'] = stripslashes($ret['dimension_5']);
            $ret['value_5'] = stripslashes($ret['value_5']);
            $ret['dimension_6'] = stripslashes($ret['dimension_6']);
            $ret['value_6'] = stripslashes($ret['value_6']);
            $ret['dimension_7'] = stripslashes($ret['dimension_7']);
            $ret['value_7'] = stripslashes($ret['value_7']);
            $ret['dimension_8'] = stripslashes($ret['dimension_8']);
            $ret['value_8'] = stripslashes($ret['value_8']);
            $ret['dimension_9'] = stripslashes($ret['dimension_9']);
            $ret['value_9'] = stripslashes($ret['value_9']);
            $ret['dimension_10'] = stripslashes($ret['dimension_10']);
            $ret['value_10'] = stripslashes($ret['value_10']);

            // 11-20
            $ret['dimension_11'] = stripslashes($ret['dimension_11']);
            $ret['value_11'] = stripslashes($ret['value_11']);
            $ret['dimension_12'] = stripslashes($ret['dimension_12']);
            $ret['value_12'] = stripslashes($ret['value_12']);
            $ret['dimension_13'] = stripslashes($ret['dimension_13']);
            $ret['value_13'] = stripslashes($ret['value_13']);
            $ret['dimension_14'] = stripslashes($ret['dimension_14']);
            $ret['value_14'] = stripslashes($ret['value_14']);
            $ret['dimension_15'] = stripslashes($ret['dimension_15']);
            $ret['value_15'] = stripslashes($ret['value_15']);
            $ret['dimension_16'] = stripslashes($ret['dimension_16']);
            $ret['value_16'] = stripslashes($ret['value_16']);
            $ret['dimension_17'] = stripslashes($ret['dimension_17']);
            $ret['value_17'] = stripslashes($ret['value_17']);
            $ret['dimension_18'] = stripslashes($ret['dimension_18']);
            $ret['value_18'] = stripslashes($ret['value_18']);
            $ret['dimension_19'] = stripslashes($ret['dimension_19']);
            $ret['value_19'] = stripslashes($ret['value_19']);
            $ret['dimension_20'] = stripslashes($ret['dimension_20']);
            $ret['value_20'] = stripslashes($ret['value_20']);

            // 21-30
            $ret['dimension_21'] = stripslashes($ret['dimension_21']);
            $ret['value_21'] = stripslashes($ret['value_21']);
            $ret['dimension_22'] = stripslashes($ret['dimension_22']);
            $ret['value_22'] = stripslashes($ret['value_22']);
            $ret['dimension_23'] = stripslashes($ret['dimension_23']);
            $ret['value_23'] = stripslashes($ret['value_23']);
            $ret['dimension_24'] = stripslashes($ret['dimension_24']);
            $ret['value_24'] = stripslashes($ret['value_24']);
            $ret['dimension_25'] = stripslashes($ret['dimension_25']);
            $ret['value_25'] = stripslashes($ret['value_25']);
            $ret['dimension_26'] = stripslashes($ret['dimension_26']);
            $ret['value_26'] = stripslashes($ret['value_26']);
            $ret['dimension_27'] = stripslashes($ret['dimension_27']);
            $ret['value_27'] = stripslashes($ret['value_27']);
            $ret['dimension_28'] = stripslashes($ret['dimension_28']);
            $ret['value_28'] = stripslashes($ret['value_28']);
            $ret['dimension_29'] = stripslashes($ret['dimension_29']);
            $ret['value_29'] = stripslashes($ret['value_29']);
            $ret['dimension_30'] = stripslashes($ret['dimension_30']);
            $ret['value_30'] = stripslashes($ret['value_30']);

            // 31-40
            $ret['dimension_31'] = stripslashes($ret['dimension_31']);
            $ret['value_31'] = stripslashes($ret['value_31']);
            $ret['dimension_32'] = stripslashes($ret['dimension_32']);
            $ret['value_32'] = stripslashes($ret['value_32']);
            $ret['dimension_33'] = stripslashes($ret['dimension_33']);
            $ret['value_33'] = stripslashes($ret['value_33']);
            $ret['dimension_34'] = stripslashes($ret['dimension_34']);
            $ret['value_34'] = stripslashes($ret['value_34']);
            $ret['dimension_35'] = stripslashes($ret['dimension_35']);
            $ret['value_35'] = stripslashes($ret['value_35']);
            $ret['dimension_36'] = stripslashes($ret['dimension_36']);
            $ret['value_36'] = stripslashes($ret['value_36']);
            $ret['dimension_37'] = stripslashes($ret['dimension_37']);
            $ret['value_37'] = stripslashes($ret['value_37']);
            $ret['dimension_38'] = stripslashes($ret['dimension_38']);
            $ret['value_38'] = stripslashes($ret['value_38']);
            $ret['dimension_39'] = stripslashes($ret['dimension_39']);
            $ret['value_39'] = stripslashes($ret['value_39']);
            $ret['dimension_40'] = stripslashes($ret['dimension_40']);
            $ret['value_40'] = stripslashes($ret['value_40']);

            $ret['errors'] = stripslashes($ret['errors']);
            $ret['debug_log'] = stripslashes($ret['debug_log']);
            $ret['ip'] = stripslashes($ret['ip']);
            $ret['real_ip'] = stripslashes($ret['real_ip']);
            $ret['host'] = stripslashes($ret['host']);
            $ret['agent'] = stripslashes($ret['agent']);
            $ret['browser'] = stripslashes($ret['browser']);
            $ret['os'] = stripslashes($ret['os']);

            if($ret['log_timestamp'] > 0)
            {
                $ret['log_date'] = date_i18n($this->shortDateFormat, $ret['log_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['log_time'] = date_i18n('H:i:s', $ret['log_timestamp'] + get_option('gmt_offset') * 3600, true);
                $logDateI18n = date_i18n(get_option('date_format'), $ret['log_timestamp'] + get_option('gmt_offset') * 3600, true);
                $logTimeI18n = date_i18n(get_option('time_format'), $ret['log_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['log_date'] = '';
                $ret['log_time'] = '';
                $logDateI18n = '';
                $logTimeI18n = '';
            }

            if($ret['action'] == "customer-lookup")
            {
                $actionText = $this->lang->getText('LANG_CUSTOMER_LOOKUP_TEXT');
            } else if($ret['action'] == "payment-callback")
            {
                $actionText = $this->lang->getText('LANG_PAYMENT_CHARGE_TEXT');
            } else
            {
                $actionText = $ret['action'];
            }

            // Prepare output for print
            $ret['action_text']             = $actionText;
            $ret['log_date_i18n']           = $logDateI18n;
            $ret['log_time_i18n']           = $logTimeI18n;
            $ret['is_robot_text'] = $this->lang->getText($ret['is_robot'] == 1 ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT');
            $ret['status_text'] = "";
            $ret['status_color'] = "black";
            // TODO: In V6 replace 0<=>2
            if($ret['status'] == 1)
            {
                $ret['status_text'] = $this->lang->getText('LANG_LOG_STATUS_FAILED_TEXT');
                $ret['status_color'] = "black";
            } else if($ret['status'] == 2)
            {
                $ret['status_text'] = $this->lang->getText('LANG_LOG_STATUS_ALLOWED_TEXT');
                $ret['status_color'] = "green";
            } else if($ret['status'] == 0)
            {
                $ret['status_text'] = $this->lang->getText('LANG_LOG_STATUS_BLOCKED_TEXT');
                $ret['status_color'] = "red";
            }
        } elseif($paramPrefillWhenNull === true)
        {
            $ret['action'] = "";


            // 1-10
            $ret['dimension_1'] = "";
            $ret['value_1'] = "";
            $ret['dimension_2'] = "";
            $ret['value_2'] = "";
            $ret['dimension_3'] = "";
            $ret['value_3'] = "";
            $ret['dimension_4'] = "";
            $ret['value_4'] = "";
            $ret['dimension_5'] = "";
            $ret['value_5'] = "";
            $ret['dimension_6'] = "";
            $ret['value_6'] = "";
            $ret['dimension_7'] = "";
            $ret['value_7'] = "";
            $ret['dimension_8'] = "";
            $ret['value_8'] = "";
            $ret['dimension_9'] = "";
            $ret['value_9'] = "";
            $ret['dimension_10'] = "";
            $ret['value_10'] = "";
            // 11-20
            $ret['dimension_11'] = "";
            $ret['value_11'] = "";
            $ret['dimension_12'] = "";
            $ret['value_12'] = "";
            $ret['dimension_13'] = "";
            $ret['value_13'] = "";
            $ret['dimension_14'] = "";
            $ret['value_14'] = "";
            $ret['dimension_15'] = "";
            $ret['value_15'] = "";
            $ret['dimension_16'] = "";
            $ret['value_16'] = "";
            $ret['dimension_17'] = "";
            $ret['value_17'] = "";
            $ret['dimension_18'] = "";
            $ret['value_18'] = "";
            $ret['dimension_19'] = "";
            $ret['value_19'] = "";
            $ret['dimension_20'] = "";
            $ret['value_20'] = "";

            // 21-30
            $ret['dimension_21'] = "";
            $ret['value_21'] = "";
            $ret['dimension_22'] = "";
            $ret['value_22'] = "";
            $ret['dimension_23'] = "";
            $ret['value_23'] = "";
            $ret['dimension_24'] = "";
            $ret['value_24'] = "";
            $ret['dimension_25'] = "";
            $ret['value_25'] = "";
            $ret['dimension_26'] = "";
            $ret['value_26'] = "";
            $ret['dimension_27'] = "";
            $ret['value_27'] = "";
            $ret['dimension_28'] = "";
            $ret['value_28'] = "";
            $ret['dimension_29'] = "";
            $ret['value_29'] = "";
            $ret['dimension_30'] = "";
            $ret['value_30'] = "";

            // 31-40
            $ret['dimension_31'] = "";
            $ret['value_31'] = "";
            $ret['dimension_32'] = "";
            $ret['value_32'] = "";
            $ret['dimension_33'] = "";
            $ret['value_33'] = "";
            $ret['dimension_34'] = "";
            $ret['value_34'] = "";
            $ret['dimension_35'] = "";
            $ret['value_35'] = "";
            $ret['dimension_36'] = "";
            $ret['value_36'] = "";
            $ret['dimension_37'] = "";
            $ret['value_37'] = "";
            $ret['dimension_38'] = "";
            $ret['value_38'] = "";
            $ret['dimension_39'] = "";
            $ret['value_39'] = "";
            $ret['dimension_40'] = "";
            $ret['value_40'] = "";

            $ret['errors'] = "";
            $ret['debug_log'] = "";
            $ret['ip'] = '0.0.0.0';
            $ret['real_ip'] = '0.0.0.0';
            $ret['host'] = "";
            $ret['agent'] = "";
            $ret['browser'] = "";
            $ret['os'] = "";

            $ret['log_date'] = '';
            $ret['log_time'] = '';
            $ret['action_text'] = "";
            $ret['log_date_i18n'] = "";
            $ret['log_time_i18n'] = "";
            $ret['is_robot_text'] = "";
            $ret['status_text'] = "";
            $ret['status_color'] = "";
            $ret['results_found'] = 0;
        }


        return $ret;
    }

    /**
     * @param array $params
     * @return bool|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validLogId = StaticValidator::getValidPositiveInteger($this->logId, 0);

        // Expected - "payment-callback", "customer-lookup" but can be any
        // Note: We do not explicitly log sent e-mails, as these logs has to be covered by the orders that send e-mails
        $validAction = StaticValidator::getValidCode($params['action'], '', true, false, false);

        // Dimensions and their values

        // 1-3
        $validValue1 = isset($params['value_1']) ? esc_sql(sanitize_text_field($params['value_1'])) : ''; // for sql queries only
        $validValue2 = isset($params['value_2']) ? esc_sql(sanitize_text_field($params['value_2'])) : ''; // for sql queries only
        $validValue3 = isset($params['value_3']) ? esc_sql(sanitize_text_field($params['value_3'])) : ''; // for sql queries only
        $validValue4 = isset($params['value_4']) ? esc_sql(sanitize_text_field($params['value_4'])) : ''; // for sql queries only
        $validValue5 = isset($params['value_5']) ? esc_sql(sanitize_text_field($params['value_5'])) : ''; // for sql queries only
        $validValue6 = isset($params['value_6']) ? esc_sql(sanitize_text_field($params['value_6'])) : ''; // for sql queries only

        $paramErrors = isset($params['errors']) ? $params['errors'] : '';
        $validErrors = esc_sql(implode("\n", array_map('sanitize_text_field', explode("\n", $paramErrors)))); // for sql queries only
        $paramDebugLog = isset($params['debug_log']) ? $params['debug_log'] : '';
        $validDebugLog = esc_sql(implode("\n", array_map('sanitize_text_field', explode("\n", $paramDebugLog)))); // for sql queries only

        $validIP = esc_sql(sanitize_text_field($_SERVER['REMOTE_ADDR'])); // for sql queries only
        $validRealIP = esc_sql(sanitize_text_field(StaticDetector::getRealIP())); // for sql queries only
        $validHost = esc_sql(sanitize_text_field(gethostbyaddr($_SERVER['REMOTE_ADDR']))); // for sql queries only
        $agent = StaticDetector::getAgent();
        $validAgent = esc_sql(sanitize_text_field($agent)); // for sql queries only
        $validBrowser = esc_sql(sanitize_text_field(StaticDetector::getBrowser($agent))); // for sql queries only
        $validOS = esc_sql(sanitize_text_field(StaticDetector::getOS($agent))); // for sql queries only
        $validIsRobot = StaticDetector::isRobot($agent) ? 1 : 0;

        // 0 - BLOCKED, 1 - FAILED, 2 - PASSED
        // TODO: In V6 replace 0<=>2
        $validStatus = (isset($params['status']) && in_array($params['status'], array(0, 1, 2))) ? intval($params['status']) : 0;


        // Logs support only data insertion, not updating
        if($validLogId == 0 && $ok)
        {
            /* insert the invoice data in {$this->conf->getPrefix()}invoice table */
            $insertSQL = "
                INSERT INTO {$this->conf->getPrefix()}logs
                (
                    log_type,
                    
                    email, year, year_required,
                    total_requests_left, failed_requests_left, email_attempts_left,
                    
                    error_message, debug_log,
                    ip, real_ip, host,
                    agent, browser, os,
                    is_robot, status, log_timestamp, blog_id
                ) VALUES
                (
                    '{$validAction}',
                    
                    '{$validValue1}', '{$validValue2}', '{$validValue3}',
                    '{$validValue4}', '{$validValue5}', '{$validValue6}',
                    
                    '{$validErrors}', '{$validDebugLog}',
                    '{$validIP}', '{$validRealIP}', '{$validHost}',
                    '{$validAgent}', '{$validBrowser}', '{$validOS}',
                    '{$validIsRobot}', '{$validStatus}', '".time()."', '{$this->conf->getBlogId()}'
                )
            ";
    
            // Debug
            //echo esc_html($insertSQL); die();
            $saved = $this->conf->getInternalWPDB()->query($insertSQL);
        }

        return $saved;
    }
}