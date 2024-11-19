<?php
/**
 * Logs Observer (no setup for single log)
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Log;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class LogsObserver implements ObserverInterface
{
    // For security reasons we do not allow to change this value from admin
    const LOG_EXPIRATION_PERIOD             = 2592000; // 30 Days

    private $conf 	                        = null;
    private $lang 		                    = null;
    private $settings		                = array();
    private $debugMode 	                    = 0;

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @note - we don't use blog_id here, because we want to see logs here for all sites
     * @param array $paramActions
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @return array
     */
    public function getAllIds(array $paramActions, $paramFromTimestamp, $paramTillTimestamp)
    {
        // Show logs in defined period
        $validFromTimestamp = StaticValidator::getValidPositiveInteger($paramFromTimestamp, time());
        $validTillTimestamp = StaticValidator::getValidPositiveInteger($paramTillTimestamp, time());

        $validActions = array();
        foreach ($paramActions AS $paramAction)
        {
            $validActions[] = esc_sql(sanitize_key($paramAction));
        }
        $sqlAdd = '';
        if(sizeof($validActions) > 0)
        {
            $validActionsSQL = "'".implode("', '", $validActions)."'";
            $sqlAdd = " AND log_type IN ({$validActionsSQL})";
        }

        $searchSQL = "
            SELECT log_id
            FROM {$this->conf->getPrefix()}logs
            WHERE log_timestamp BETWEEN '{$validFromTimestamp}' AND '{$validTillTimestamp}'
            {$sqlAdd}
            ORDER BY log_timestamp DESC
		";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResult = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResult;
    }

    /**
     * @note1 - we don't use blog_id here, because we want to delete here for all sites
     * @note2 - we never delete payment callback logs
     */
    public function deleteExpired()
    {
        // Delete logs older than 30 days
        $deleteOlderThan = time() - static::LOG_EXPIRATION_PERIOD;

        $deleteSQL = "
            DELETE FROM {$this->conf->getPrefix()}logs
            WHERE log_timestamp < '{$deleteOlderThan}' AND log_type IN('customer-lookup')
        ";

        // Debug
        //echo esc_html($deleteSQL); die();
        $this->conf->getInternalWPDB()->query($deleteSQL);
    }


    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    public function getTrustedAdminListForCustomerLookupsHTML()
    {
        return $this->getTrustedAdminListHTML(array('customer-lookup'));
    }

    public function getTrustedAdminListForPaymentsHTML()
    {
        return $this->getTrustedAdminListHTML(array('payment-charge', 'payment-callback'));
    }

    private function getTrustedAdminListHTML(array $paramActions)
    {
        $retHTML = '';

        // Last 30 days
        $fromTimestamp = time() - 86400*30;
        $tillTimestamp = time();
        $logIds = $this->getAllIds($paramActions, $fromTimestamp, $tillTimestamp);

        foreach ($logIds AS $logId)
        {
            $objLog = new Log($this->conf, $this->lang, $this->settings, $logId);
            $logDetails = $objLog->getDetails(true);

            $retHTML .= '<tr>';
            $retHTML .= '<td>'.esc_html($logDetails['log_id']).'</td>';
            $retHTML .= '<td>'.esc_html($logDetails['log_date_i18n']).' '.esc_html($logDetails['log_time_i18n']).'</td>';
            $retHTML .= '<td>'.esc_html($logDetails['action_text']).'</td>';

            // Dimensions & values
            $retHTML .= '<td style="text-align: left">';



            // 1-10
            if($logDetails['dimension_1'] != '' || $logDetails['value_1'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_1']).': '.esc_html($logDetails['value_1']).'<br />';
            }

            if($logDetails['dimension_2'] != '' || $logDetails['value_2'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_2']).': '.esc_html($logDetails['value_2']).'<br />';
            }

            if($logDetails['dimension_3'] != '' || $logDetails['value_3'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_3']).': '.esc_html($logDetails['value_3']).'<br />';
            }

            if($logDetails['dimension_4'] != '' || $logDetails['value_4'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_4']).': '.esc_html($logDetails['value_4']).'<br />';
            }

            if($logDetails['dimension_5'] != '' || $logDetails['value_5'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_5']).': '.esc_html($logDetails['value_5']).'<br />';
            }

            if($logDetails['dimension_6'] != '' || $logDetails['value_6'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_6']).': '.esc_html($logDetails['value_6']).'<br />';
            }

            if($logDetails['dimension_7'] != '' || $logDetails['value_7'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_7']).': '.esc_html($logDetails['value_7']).'<br />';
            }

            if($logDetails['dimension_8'] != '' || $logDetails['value_8'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_8']).': '.esc_html($logDetails['value_8']).'<br />';
            }

            if($logDetails['dimension_9'] != '' || $logDetails['value_9'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_9']).': '.esc_html($logDetails['value_9']).'<br />';
            }

            if($logDetails['dimension_10'] != '' || $logDetails['value_10'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_10']).': '.esc_html($logDetails['value_10']).'<br />';
            }



            // 11-20
            if($logDetails['dimension_11'] != '' || $logDetails['value_11'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_11']).': '.esc_html($logDetails['value_11']).'<br />';
            }

            if($logDetails['dimension_12'] != '' || $logDetails['value_12'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_12']).': '.esc_html($logDetails['value_12']).'<br />';
            }

            if($logDetails['dimension_13'] != '' || $logDetails['value_13'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_13']).': '.esc_html($logDetails['value_13']).'<br />';
            }

            if($logDetails['dimension_14'] != '' || $logDetails['value_14'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_14']).': '.esc_html($logDetails['value_14']).'<br />';
            }

            if($logDetails['dimension_15'] != '' || $logDetails['value_15'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_15']).': '.esc_html($logDetails['value_15']).'<br />';
            }

            if($logDetails['dimension_16'] != '' || $logDetails['value_16'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_16']).': '.esc_html($logDetails['value_16']).'<br />';
            }

            if($logDetails['dimension_17'] != '' || $logDetails['value_17'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_17']).': '.esc_html($logDetails['value_17']).'<br />';
            }

            if($logDetails['dimension_18'] != '' || $logDetails['value_18'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_18']).': '.esc_html($logDetails['value_18']).'<br />';
            }

            if($logDetails['dimension_19'] != '' || $logDetails['value_19'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_19']).': '.esc_html($logDetails['value_19']).'<br />';
            }

            if($logDetails['dimension_20'] != '' || $logDetails['value_20'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_20']).': '.esc_html($logDetails['value_20']).'<br />';
            }



            // 21-30
            if($logDetails['dimension_21'] != '' || $logDetails['value_21'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_21']).': '.esc_html($logDetails['value_21']).'<br />';
            }

            if($logDetails['dimension_22'] != '' || $logDetails['value_22'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_22']).': '.esc_html($logDetails['value_22']).'<br />';
            }

            if($logDetails['dimension_23'] != '' || $logDetails['value_23'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_23']).': '.esc_html($logDetails['value_23']).'<br />';
            }

            if($logDetails['dimension_24'] != '' || $logDetails['value_24'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_24']).': '.esc_html($logDetails['value_24']).'<br />';
            }

            if($logDetails['dimension_25'] != '' || $logDetails['value_25'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_25']).': '.esc_html($logDetails['value_25']).'<br />';
            }

            if($logDetails['dimension_26'] != '' || $logDetails['value_26'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_26']).': '.esc_html($logDetails['value_26']).'<br />';
            }

            if($logDetails['dimension_27'] != '' || $logDetails['value_27'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_27']).': '.esc_html($logDetails['value_27']).'<br />';
            }

            if($logDetails['dimension_28'] != '' || $logDetails['value_28'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_28']).': '.esc_html($logDetails['value_28']).'<br />';
            }

            if($logDetails['dimension_29'] != '' || $logDetails['value_29'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_29']).': '.esc_html($logDetails['value_29']).'<br />';
            }

            if($logDetails['dimension_30'] != '' || $logDetails['value_30'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_30']).': '.esc_html($logDetails['value_30']).'<br />';
            }



            // 31-40
            if($logDetails['dimension_31'] != '' || $logDetails['value_31'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_31']).': '.esc_html($logDetails['value_31']).'<br />';
            }

            if($logDetails['dimension_32'] != '' || $logDetails['value_32'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_32']).': '.esc_html($logDetails['value_32']).'<br />';
            }

            if($logDetails['dimension_33'] != '' || $logDetails['value_33'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_33']).': '.esc_html($logDetails['value_33']).'<br />';
            }

            if($logDetails['dimension_34'] != '' || $logDetails['value_34'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_34']).': '.esc_html($logDetails['value_34']).'<br />';
            }

            if($logDetails['dimension_35'] != '' || $logDetails['value_35'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_35']).': '.esc_html($logDetails['value_35']).'<br />';
            }

            if($logDetails['dimension_36'] != '' || $logDetails['value_36'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_36']).': '.esc_html($logDetails['value_36']).'<br />';
            }

            if($logDetails['dimension_37'] != '' || $logDetails['value_37'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_37']).': '.esc_html($logDetails['value_37']).'<br />';
            }

            if($logDetails['dimension_38'] != '' || $logDetails['value_38'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_38']).': '.esc_html($logDetails['value_38']).'<br />';
            }

            if($logDetails['dimension_39'] != '' || $logDetails['value_39'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_39']).': '.esc_html($logDetails['value_39']).'<br />';
            }

            if($logDetails['dimension_40'] != '' || $logDetails['value_40'] != '')
            {
                $retHTML .= esc_html($logDetails['dimension_40']).': '.esc_html($logDetails['value_40']).'<br />';
            }


            $retHTML .= '</td>';

            $retHTML .= '<td style="text-align: left">'.esc_br_html($logDetails['errors']).'</td>';
            $retHTML .= '<td style="text-align: left">'.esc_br_html($logDetails['debug_log']).'</td>';

            $retHTML .= '<td>';
                $retHTML .= $this->lang->escHTML('LANG_IP_TEXT').': '.esc_html($logDetails['ip']).'</span><br />';
                $retHTML .= $this->lang->escHTML('LANG_REAL_IP_TEXT').': '.esc_html($logDetails['real_ip']).'</span><br />';
                $retHTML .= $this->lang->escHTML('LANG_HOST_TEXT').': '.esc_html($logDetails['host']).'</span><br />';
                $retHTML .= $this->lang->escHTML('LANG_BROWSER_TEXT').': '.esc_html($logDetails['browser']).'<br />';
                $retHTML .= $this->lang->escHTML('LANG_OS_TEXT').': '.esc_html($logDetails['os']).'<br />';
                $retHTML .= $this->lang->escHTML('LANG_AGENT_TEXT').': '.esc_html($logDetails['agent']);
            $retHTML .= '</td>';

            $retHTML .= '<td>'.esc_html($logDetails['is_robot_text']).'</td>';
            $retHTML .= '<td>';
            $retHTML .= '<span style="color: '.esc_attr($logDetails['status_color']).';">'.esc_html($logDetails['status_text']).'</span><br />';
            $retHTML .= sprintf($this->lang->escHTML('LANG_LOG_D_RESULTS_FOUND_TEXT'), $logDetails['results_found']);
            $retHTML .= '</td>';
            $retHTML .= '<td style="text-align: right">';
            $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'view-log&amp;log_id='.$logId)).'">'.$this->lang->escHTML('LANG_VIEW_DETAILS_TEXT').'</a>';
            $retHTML .= '</td>';
            $retHTML .= '</tr>';
        }
        return $retHTML;
    }
}