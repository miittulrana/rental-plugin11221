<?php
/**
 * Period
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Order;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class Period
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $settings                 = array();
    protected $debugMode 	            = 0;
    protected $timeCeiling              = 'BY_TIME_COUNT'; // 'BY_TIME_COUNT', 'BY_NOON_COUNT' or 'BY_DATE_COUNT'
    protected $timePeriod               = 'IN_DAYS_ONLY';
    protected $noonTime                 = '12:00:00';

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
        $this->settings = $paramSettings;

        $this->timeCeiling = ConfigurationInterface::TIME_CEILING;
        $this->noonTime = StaticValidator::getValidSetting($paramSettings, 'conf_noon_time', "time_format", "12:00:00");
        $priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1, array(1, 2, 3));
        
        switch($priceCalculationType)
        {
            case 1:
                $this->timePeriod = 'IN_DAYS_ONLY';
                break;
            case 2:
                $this->timePeriod = 'IN_HOURS_ONLY';
                break;
            case 3:
                $this->timePeriod = 'IN_DAYS_AND_HOURS';
                break;
            default:
                $this->timePeriod = 'IN_DAYS_ONLY';
        }
    }
    
    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * NOTE: Returns unescaped!
     * @param int $paramFromTimestamp
     * @param int $paramTillTimestamp
     * @return string
     */
    public function getDurationText($paramFromTimestamp, $paramTillTimestamp)
    {
        switch($this->timePeriod)
        {
            case "IN_MINUTES_ONLY":
                // Count by minutes only
                $period	= StaticValidator::getPeriod($paramTillTimestamp, $paramFromTimestamp, false);

                $minutes = ceil($period / 60);
                $minutesText = $this->lang->getTimeText($minutes, $this->lang->getText('LANG_MINUTE1_TEXT'), $this->lang->getText('LANG_MINUTES10_TEXT'), $this->lang->getText('LANG_MINUTES10_TEXT'));
                $durationText = $minutes.' '.$minutesText;
                break;

            case "IN_HOURS_ONLY":
                // Count by hours only
                $period	= StaticValidator::getPeriod($paramFromTimestamp, $paramTillTimestamp, false);

                $hours = ceil($period / 3600);
                $hoursText = $this->lang->getTimeText($hours, $this->lang->getText('LANG_HOUR1_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'));
                $durationText = $hours.' '.$hoursText;
                break;

            case "IN_HOURS_AND_MINUTES":
                // Combined count - hours + minutes
                $period	= StaticValidator::getPeriod($paramFromTimestamp, $paramTillTimestamp, false);

                $secondsOnLastHour = $period - (floor($period / 3600) * 3600);
                $hours = floor($period / 3600);
                $minutes = ceil($secondsOnLastHour / 60);

                $hoursText = $this->lang->getTimeText($hours, $this->lang->getText('LANG_HOUR1_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'));
                $minutesText = $this->lang->getTimeText($minutes, $this->lang->getText('LANG_MINUTE1_TEXT'), $this->lang->getText('LANG_MINUTES10_TEXT'), $this->lang->getText('LANG_MINUTES10_TEXT'));

                if($hours > 0 && $minutes > 0)
                {
                    $durationText = $hours.' '.$hoursText.' '.$minutes.' '.$minutesText;
                } else if($hours > 0 && $minutes == 0)
                {
                    $durationText = $hours.' '.$hoursText;
                } else
                {
                    $durationText = $hours.' '.$minutesText;
                }
                break;

            case "IN_DAYS_ONLY":
                $dateCount = StaticValidator::getDateCount($this->timeCeiling, $paramFromTimestamp, $paramTillTimestamp, $this->noonTime);
                $daysText = $this->lang->getTimeText($dateCount, $this->lang->getText('LANG_DAY1_TEXT'), $this->lang->getText('LANG_DAYS10_TEXT'), $this->lang->getText('LANG_DAYS10_TEXT'));
                $durationText = $dateCount.' '.$daysText;
                break;

            case "IN_DAYS_AND_HOURS":
                // Combined count - days + hours
                $period	= StaticValidator::getPeriod($paramFromTimestamp, $paramTillTimestamp, false);

                $secondsOnLastDate = $period - (floor($period / 86400) * 86400);
                $days = floor($period / 86400);
                $hours = ceil($secondsOnLastDate / 3600);

                $daysText = $this->lang->getTimeText($days, $this->lang->getText('LANG_DAY1_TEXT'), $this->lang->getText('LANG_DAYS10_TEXT'), $this->lang->getText('LANG_DAYS10_TEXT'));
                $hoursText = $this->lang->getTimeText($hours, $this->lang->getText('LANG_HOUR1_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'));

                if($days > 0 && $hours > 0)
                {
                    $durationText = $days.' '.$daysText.' '.$hours.' '.$hoursText;
                } else if($days > 0 && $hours == 0)
                {
                    $durationText = $days.' '.$daysText;
                } else
                {
                    $durationText = $hours.' '.$hoursText;
                }
                break;

            case "IN_NIGHTS_ONLY":
                // Count by nights
                $dateCount = StaticValidator::getDateCount($this->timeCeiling, $paramFromTimestamp, $paramTillTimestamp, $this->noonTime);
                $nightsText = $this->lang->getTimeText($dateCount, $this->lang->getText('LANG_NIGHT1_TEXT'), $this->lang->getText('LANG_DAYS10_TEXT'), $this->lang->getText('LANG_DAYS10_TEXT'));
                $durationText = $dateCount.' '.$nightsText;
                break;

            case "IN_NIGHTS_AND_HOURS":
                // Combined count - nights + hours
                $period	= StaticValidator::getPeriod($paramFromTimestamp, $paramTillTimestamp, false);

                $secondsOnLastDate = $period - (floor($period / 86400) * 86400);
                $nights = floor($period / 86400);
                $hours = ceil($secondsOnLastDate / 3600);

                $nightsText = $this->lang->getTimeText($nights, $this->lang->getText('LANG_NIGHT1_TEXT'), $this->lang->getText('LANG_NIGHTS2_TEXT'), $this->lang->getText('LANG_NIGHTS10_TEXT'));
                $hoursText = $this->lang->getTimeText($hours, $this->lang->getText('LANG_HOUR1_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'));

                if($nights > 0 && $hours > 0)
                {
                    $durationText = $nights.' '.$nightsText.' '.$hours.' '.$hoursText;
                } else if($nights > 0 && $hours == 0)
                {
                    $durationText = $nights.' '.$nightsText;
                } else
                {
                    $durationText = $nights.' '.$hoursText;
                }
                break;

            // NOTE: No weekly duration exists in rental engine
            case "IN_MONTHS_ONLY":
                // Count by months
                $modifiedTillTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($this->timeCeiling, $paramFromTimestamp, $paramTillTimestamp, $this->noonTime);
                // NOTE: For months we always up-round to the next integer number
                $monthCount = StaticValidator::getCeilTotalMonthsBetweenTwoTimestamps($paramFromTimestamp, $modifiedTillTimestamp);
                $monthsText = $this->lang->getTimeText($monthCount, $this->lang->getText('LANG_MONTH1_TEXT'), $this->lang->getText('LANG_MONTHS2_TEXT'), $this->lang->getText('LANG_MONTHS10_TEXT'));
                $durationText = $monthCount.' '.$monthsText;
                break;

            case "IN_YEARS_ONLY":
                // Count by years
                $modifiedTillTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($this->timeCeiling, $paramFromTimestamp, $paramTillTimestamp, $this->noonTime);
                // NOTE: For years we always up-round to the next integer number
                $yearCount = StaticValidator::getCeilTotalYearsBetweenTwoTimestamps($paramFromTimestamp, $modifiedTillTimestamp);
                $yearsText = $this->lang->getTimeText($yearCount, $this->lang->getText('LANG_YEAR1_TEXT'), $this->lang->getText('LANG_YEARS2_TEXT'), $this->lang->getText('LANG_YEARS10_TEXT'));
                $durationText = $yearCount.' '.$yearsText;
                break;

            case "IN_YEARS_AND_MONTHS":
                // Combined count - years + months
                $modifiedTillTimestamp = StaticValidator::subtractTillTimestampByTimeCeiling($this->timeCeiling, $paramFromTimestamp, $paramTillTimestamp, $this->noonTime);
                // NOTE: For months we always up-round to the next integer number
                $monthCount = StaticValidator::getCeilTotalMonthsBetweenTwoTimestamps($paramFromTimestamp, $modifiedTillTimestamp);

                $yearCount = floor($monthCount / 12);
                $monthsOnLastYear = $monthCount - (floor($monthCount / 12) * 12);

                $yearsText = $this->lang->getTimeText($yearCount, $this->lang->getText('LANG_YEAR1_TEXT'), $this->lang->getText('LANG_YEARS2_TEXT'), $this->lang->getText('LANG_YEARS10_TEXT'));
                $monthsText = $this->lang->getTimeText($monthsOnLastYear, $this->lang->getText('LANG_MONTH1_TEXT'), $this->lang->getText('LANG_MONTHS2_TEXT'), $this->lang->getText('LANG_MONTHS10_TEXT'));

                if($yearCount > 0 && $monthsOnLastYear > 0)
                {
                    $durationText = $yearCount.' '.$yearsText.' '.$monthsOnLastYear.' '.$monthsText;
                } else if($yearCount > 0 && $monthsOnLastYear == 0)
                {
                    $durationText = $yearCount.' '.$yearsText;
                } else
                {
                    $durationText = $yearCount.' '.$monthsText;
                }
                break;

            // NOTE: No 'per order' duration exists in rental engine
            default:
                $durationText = "";
                break;
        }

        return $durationText;
    }
}