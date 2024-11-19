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
namespace FleetManagement\Models\Counter;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Detect\StaticDetector;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Counter
{
    // For security reasons we do not allow to change this value from admin
    const EXPIRATION_PERIOD                 = 3600; // 1 hour

    private $conf 	                        = null;
    private $lang 		                    = null;
    private $debugMode 	                    = 0;
    private $shortDateFormat                = "m/d/Y";
    private $maxRequestsPerPeriod           = 50;
    private $maxFailedRequestsPerPeriod     = 3;

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

        $this->maxRequestsPerPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_api_max_requests_per_period', 'positive_integer', 50);
        $this->maxFailedRequestsPerPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_api_max_failed_requests_per_period', 'positive_integer', 3);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /* --------------------------------------------------------------------------------- */
    /* More methods                                                                      */
    /* --------------------------------------------------------------------------------- */

    /**
     * @note - we don't use blog_id here, because we want to count here for all sites
     * @return int
     */
    public function getTotalRequestsLeft()
    {
        $totalRequestsLeft = -1;
        if($this->maxRequestsPerPeriod > -1)
        {
            $validIP = esc_sql(sanitize_text_field($_SERVER['REMOTE_ADDR']));
            $validRealIP = esc_sql(sanitize_text_field(StaticDetector::getRealIP()));
            $validTimestampLimit = time() - static::EXPIRATION_PERIOD;

            $totalRequests = $this->conf->getInternalWPDB()->get_var("
                SELECT COUNT(*) AS total_requests
                FROM {$this->conf->getPrefix()}logs
                WHERE log_timestamp >= '{$validTimestampLimit}' AND log_type='customer-lookup'
                AND (ip='{$validIP}' OR ip='{$validRealIP}' OR real_ip='{$validIP}')
            ");

            $totalRequestsLeft = !is_null($totalRequests) ? max($this->maxRequestsPerPeriod - $totalRequests, 0) : $this->maxRequestsPerPeriod;
        }

        return $totalRequestsLeft;
    }

    /**
     * @note - we don't use blog_id here, because we want to count here for all sites
     * @return int
     */
    public function getFailedRequestsLeft()
    {
        $failedRequestsLeft = -1;
        if($this->maxFailedRequestsPerPeriod > -1)
        {
            $validIP = esc_sql(sanitize_text_field($_SERVER['REMOTE_ADDR']));
            $validRealIP = esc_sql(sanitize_text_field(StaticDetector::getRealIP()));
            $validTimestampLimit = time() - static::EXPIRATION_PERIOD;

            $failedRequests = $this->conf->getInternalWPDB()->get_var("
                SELECT COUNT(*) AS total_requests
                FROM {$this->conf->getPrefix()}logs
                WHERE status = '1' AND log_timestamp >= '{$validTimestampLimit}' AND log_type='customer-lookup'
                AND (ip='{$validIP}' OR ip='{$validRealIP}' OR real_ip='{$validRealIP}')
            ");

            $failedRequestsLeft = !is_null($failedRequests) ? max($this->maxFailedRequestsPerPeriod - $failedRequests, 0) : $this->maxFailedRequestsPerPeriod;
        }

        return $failedRequestsLeft;
    }

    /**
     * @note - we don't use blog_id here, because we want to count here for all sites
     * @param $paramEmail
     * @return int
     */
    public function getFailedEmailAttemptsLeft($paramEmail)
    {
        $failedAttemptsLeft = -1;
        if($this->maxFailedRequestsPerPeriod > -1)
        {
            $validEmail = esc_sql(sanitize_email($paramEmail));
            $validTimestampLimit = time() - static::EXPIRATION_PERIOD;

            $failedAttempts = $this->conf->getInternalWPDB()->get_var("
                SELECT COUNT(*) AS total_attempts
                FROM {$this->conf->getPrefix()}logs
                WHERE status = '1' AND log_timestamp >= '{$validTimestampLimit}' AND log_type='customer-lookup'
                AND email='{$validEmail}'
            ");

            $failedAttemptsLeft = !is_null($failedAttempts) ? max($this->maxFailedRequestsPerPeriod - $failedAttempts, 0) : $this->maxFailedRequestsPerPeriod;
        }

        return $failedAttemptsLeft;
    }
}