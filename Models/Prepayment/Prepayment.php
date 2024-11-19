<?php
/**
 * Prepayment Element. Used in administration side only

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Prepayment;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Prepayment extends AbstractStack implements ElementInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $prepaymentId           = 0;
    /**
     * @var int - price calculation: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
     */
    private $priceCalculationType   = 1;
    private $timeInterval           = 1800; // 30 minutes in seconds
    private $timePeriod             = 'IN_DAYS_ONLY';

    /**
     * Prepayment constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramPrepaymentId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramPrepaymentId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set prepayment id
        $this->prepaymentId = StaticValidator::getValidPositiveInteger($paramPrepaymentId, 0);

        // Additional settings
        $this->timeInterval = ConfigurationInterface::TIME_INTERVAL;
        $this->priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1);

        switch($this->priceCalculationType)
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

    private function getDataFromDatabaseById($paramPrepaymentId, $paramColumns = array('*'))
    {
        $validPrepaymentId = StaticValidator::getValidPositiveInteger($paramPrepaymentId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);
        $validSelect = str_replace(
            array("distance_fees_included"),
            array("distance_fees_included AS additional_fees_included"),
            $validSelect
        );
        $sqlQuery = "
            SELECT {$validSelect}, distance_fees_included AS additional_fees_included
            FROM {$this->conf->getPrefix()}prepayments
            WHERE prepayment_id='{$validPrepaymentId}'
        ";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retData;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->prepaymentId;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getPercentage()
    {
        $retPercentage = 0.000;
        $prepaymentData = $this->getDataFromDatabaseById($this->prepaymentId, array('prepayment_percentage'));
        if(!is_null($prepaymentData))
        {
            // Make raw
            $retPercentage = $prepaymentData['prepayment_percentage'];
        }
        return $retPercentage;
    }

    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->prepaymentId);
        // NOTE: For not null there is nothing to rawify
        if(is_null($ret) && $paramPrefillWhenNull === true)
        {
            // Set defaults
            $monthsFrom = -1;
            $monthsTill = -1;
            $periodFrom = -1;
            $periodTill = -1;

            switch($this->timePeriod)
            {
                case "IN_MINUTES_ONLY":
                    $periodFrom = 0;
                    $periodTill = $this->timeInterval + 60-1; // Based on time interval, plus 59 seconds of the last minute
                    break;

                case "IN_HOURS_ONLY":
                case "IN_HOURS_AND_MINUTES":
                    $periodFrom = 0;
                    $periodTill = 3600 + 3600 - 1; // Plus seconds of the last hour except the last second
                    break;

                case "IN_DAYS_ONLY":
                case "IN_NIGHTS_ONLY":
                case "IN_DAYS_AND_HOURS":
                case "IN_NIGHTS_AND_HOURS":
                    $periodFrom = 0;
                    $periodTill = 86400 + 86400 - 1; // Plus seconds of the last day except the last second
                    break;

                case "IN_MONTHS_ONLY":
                    $monthsFrom = 0;
                    $monthsTill = 1;
                    break;

                case "IN_YEARS_ONLY":
                case "IN_YEARS_AND_MONTHS":
                    $monthsFrom = 0;
                    $monthsTill = 12 + 12 - 1; // Plus months of the last year except the last month
                    break;
            }

            // Create default for unclassified
            $ret = array();
            $ret['prepayment_id'] = 0;
            $ret['months_from'] = $monthsFrom;
            $ret['months_till'] = $monthsTill;
            $ret['period_from'] = $periodFrom;
            $ret['period_till'] = $periodTill;
            $ret['item_prices_included'] = 0;
            $ret['item_deposits_included'] = 0;
            $ret['extra_prices_included'] = 0;
            $ret['extra_deposits_included'] = 0;
            $ret['pickup_fees_included'] = 0;
            $ret['return_fees_included'] = 0;
            $ret['additional_fees_included'] = 0;
            $ret['prepayment_percentage'] = 0.000;
            $ret['blog_id'] = $this->conf->getBlogId();
        }

        if(!is_null($ret) || $paramPrefillWhenNull === true)
        {
        // No need for edit ready preparation here - as this class corresponding database table does not have string fields

            // Set defaults
            $durationFrom = -1;
            $durationTill = -1;
            $timeExt = "";

            switch($this->timePeriod)
            {
                case "IN_MINUTES_ONLY":
                    // Minutes
                    $durationFrom = StaticValidator::getFloorMinutesFromSeconds($ret['period_from']);
                    $durationTill = StaticValidator::getFloorMinutesFromSeconds($ret['period_till']);
                    $timeExt = $this->lang->getTimeText(
                        $durationTill,
                        $this->lang->getText('LANG_MINUTE1_TEXT'),
                        $this->lang->getText('LANG_MINUTES2_TEXT'),
                        $this->lang->getText('LANG_MINUTES10_TEXT')
                    );
                    break;

                case "IN_HOURS_ONLY":
                case "IN_HOURS_AND_MINUTES":
                    // Hours
                    $durationFrom = StaticValidator::getFloorHoursFromSeconds($ret['period_from']);
                    $durationTill = StaticValidator::getFloorHoursFromSeconds($ret['period_till']);
                    $timeExt = $this->lang->getTimeText(
                        $durationTill,
                        $this->lang->getText('LANG_HOUR1_TEXT'),
                        $this->lang->getText('LANG_HOURS2_TEXT'),
                        $this->lang->getText('LANG_HOURS10_TEXT')
                    );
                    break;

                case "IN_DAYS_ONLY":
                case "IN_DAYS_AND_HOURS":
                    // Days
                    $durationFrom = StaticValidator::getFloorDaysFromSeconds($ret['period_from']);
                    $durationTill = StaticValidator::getFloorDaysFromSeconds($ret['period_till']);
                    $timeExt = $this->lang->getTimeText(
                        $durationTill,
                        $this->lang->getText('LANG_DAY1_TEXT'),
                        $this->lang->getText('LANG_DAYS2_TEXT'),
                        $this->lang->getText('LANG_DAYS10_TEXT')
                    );
                    break;

                case "IN_NIGHTS_ONLY":
                case "IN_NIGHTS_AND_HOURS":
                    // Nights
                    $durationFrom = StaticValidator::getFloorDaysFromSeconds($ret['period_from']);
                    $durationTill = StaticValidator::getFloorDaysFromSeconds($ret['period_till']);
                    $timeExt = $this->lang->getTimeText(
                        $durationTill,
                        $this->lang->getText('LANG_NIGHT1_TEXT'),
                        $this->lang->getText('LANG_NIGHTS2_TEXT'),
                        $this->lang->getText('LANG_NIGHTS10_TEXT')
                    );
                    break;

                case "IN_MONTHS_ONLY":
                    // Months
                    $durationFrom = round($ret['period_from'] / (86400*30), 0);
                    $durationTill = round($ret['period_till'] / (86400*30), 0);
                    $timeExt = $this->lang->getTimeText(
                        $durationTill,
                        $this->lang->getText('LANG_MONTH1_TEXT'),
                        $this->lang->getText('LANG_MONTHS2_TEXT'),
                        $this->lang->getText('LANG_MONTHS10_TEXT')
                    );
                    break;

                case "IN_YEARS_ONLY":
                case "IN_YEARS_AND_MONTHS":
                    // Years
                    $durationFrom = StaticValidator::getFloorYearsFromMonths($ret['period_from'] / (86400*30));
                    $durationTill = StaticValidator::getFloorYearsFromMonths($ret['period_till'] / (86400*30));
                    $timeExt = $this->lang->getTimeText(
                        $durationTill,
                        $this->lang->getText('LANG_YEAR1_TEXT'),
                        $this->lang->getText('LANG_YEARS2_TEXT'),
                        $this->lang->getText('LANG_YEARS10_TEXT')
                    );
                    break;
            }

            $arrIncludes = array();
            $arrNotIncludes = array();

            // Item prices
            if($ret['item_prices_included'] == 1)
            {
                $arrIncludes[] = $this->lang->getText('LANG_PREPAYMENT_ITEMS_PRICE_TEXT');
            } else
            {
                $arrNotIncludes[] = $this->lang->getText('LANG_PREPAYMENT_ITEMS_PRICE_TEXT');
            }

            // Item deposits
            if($ret['item_deposits_included'] == 1)
            {
                $arrIncludes[] = $this->lang->getText('LANG_PREPAYMENT_ITEMS_DEPOSIT_TEXT');
            } else
            {
                $arrNotIncludes[] = $this->lang->getText('LANG_PREPAYMENT_ITEMS_DEPOSIT_TEXT');
            }

            // Extra prices
            if($ret['extra_prices_included'] == 1)
            {
                $arrIncludes[] = $this->lang->getText('LANG_PREPAYMENT_EXTRAS_PRICE_TEXT');
            } else
            {
                $arrNotIncludes[] = $this->lang->getText('LANG_PREPAYMENT_EXTRAS_PRICE_TEXT');
            }

            // Extra deposits
            if($ret['extra_deposits_included'] == 1)
            {
                $arrIncludes[] = $this->lang->getText('LANG_PREPAYMENT_EXTRAS_DEPOSIT_TEXT');
            } else
            {
                $arrNotIncludes[] = $this->lang->getText('LANG_PREPAYMENT_EXTRAS_DEPOSIT_TEXT');
            }

            // Pick-up fees
            if($ret['pickup_fees_included'] == 1)
            {
                $arrIncludes[] = $this->lang->getText('LANG_PREPAYMENT_PICKUP_FEES_TEXT');
            } else
            {
                $arrNotIncludes[] = $this->lang->getText('LANG_PREPAYMENT_PICKUP_FEES_TEXT');
            }

            // Return fees
            if($ret['return_fees_included'] == 1)
            {
                $arrIncludes[] = $this->lang->getText('LANG_PREPAYMENT_RETURN_FEES_TEXT');
            } else
            {
                $arrNotIncludes[] = $this->lang->getText('LANG_PREPAYMENT_RETURN_FEES_TEXT');
            }

            // Additional fees
            if($ret['additional_fees_included'] == 1)
            {
                $arrIncludes[] = $this->lang->getText('LANG_PREPAYMENT_ADDITIONAL_FEES_TEXT');
            } else
            {
                $arrNotIncludes[] = $this->lang->getText('LANG_PREPAYMENT_ADDITIONAL_FEES_TEXT');
            }

            $trustedIncludesHTML = implode(",<br />", array_map('esc_html', $arrIncludes));
            $trustedNotIncludesHTML = implode(",<br />", array_map('esc_html', $arrNotIncludes));

            $ret['dynamic_duration_from'] = $durationFrom;
            $ret['dynamic_duration_till'] = $durationTill;
            $ret['dynamic_duration_from_text'] = $durationFrom.' '.$timeExt;
            $ret['dynamic_duration_till_text'] = $durationTill.' '.$timeExt;
            $ret['dynamic_duration_ext'] = $timeExt;
            $ret['dynamic_time_label'] = $durationFrom.'-'.$durationTill.' '.$timeExt;
            $ret['trusted_includes_html'] = $trustedIncludesHTML;
            $ret['trusted_not_includes_html'] = $trustedNotIncludesHTML;
        }

        return $ret;
    }

    /**
     * @note - We use 'blog_id' here because we don't want to allow plugin Managers from one multisite site to edit items in other multisite site
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $paramDaysFrom = isset($params['days_from']) ? $params['days_from'] : 0;
        $paramHoursFrom = isset($params['hours_from']) ? $params['hours_from'] : 0;
        $paramDaysTill = isset($params['days_till']) ? $params['days_till'] : 0;
        $paramHoursTill = isset($params['hours_till']) ? $params['hours_till'] : 0;

        $validPrepaymentId = StaticValidator::getValidPositiveInteger($this->prepaymentId, 0);
        $validPeriodFrom = $this->getPeriodByPriceType($paramDaysFrom, $paramHoursFrom, "FROM");
        $validPeriodTill = $this->getPeriodByPriceType($paramDaysTill, $paramHoursTill, "TILL");
        $validItemPricesIncluded = isset($params['item_prices_included']) ? 1 : 0;
        $validItemDepositsIncluded = isset($params['item_deposits_included']) ? 1 : 0;
        $validExtraPricesIncluded = isset($params['extra_prices_included']) ? 1 : 0;
        $validExtraDepositsIncluded = isset($params['extra_deposits_included']) ? 1 : 0;
        $validPickupFeesIncluded = isset($params['pickup_fees_included']) ? 1 : 0;
        $validAdditionalFeesIncluded = isset($params['additional_fees_included']) ? 1 : 0;
        $validReturnFeesIncluded = isset($params['return_fees_included']) ? 1 : 0;
        if(isset($params['prepayment_percentage']) && $params['prepayment_percentage'] > 0)
        {
            // Allow only positive prepayment percentage
            $validPrepaymentPercentage = floatval($params['prepayment_percentage']);
        } else
        {
            $validPrepaymentPercentage = 0.00;
        }

        // Do not allow to have prepayments more than 100%
        if($validPrepaymentPercentage > 100)
        {
            $validPrepaymentPercentage = 100;
        }

        // If expr is greater than or equal to min and expr is less than or equal to max, BETWEEN returns 1, otherwise it returns 0
        $minDaysValueCheck = $this->conf->getInternalWPDB()->get_results("
            SELECT prepayment_id
            FROM {$this->conf->getPrefix()}prepayments
            WHERE prepayment_id!='{$validPrepaymentId}' AND (
              '{$validPeriodFrom}' BETWEEN period_from AND period_till
              OR '{$validPeriodTill}' BETWEEN period_from AND period_till
            ) AND blog_id='{$this->conf->getBlogId()}'
        ", ARRAY_A);

        if(sizeof($minDaysValueCheck) > 0)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_PREPAYMENT_DAYS_INTERSECTION_ERROR_TEXT');
        }

        if($validPrepaymentId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}prepayments SET
                period_from='{$validPeriodFrom}', period_till='{$validPeriodTill}',
                item_prices_included='{$validItemPricesIncluded}',
                item_deposits_included='{$validItemDepositsIncluded}',
                extra_prices_included='{$validExtraPricesIncluded}',
                extra_deposits_included='{$validExtraDepositsIncluded}',
                pickup_fees_included='{$validPickupFeesIncluded}',
                distance_fees_included='{$validAdditionalFeesIncluded}',
                return_fees_included='{$validReturnFeesIncluded}',
                prepayment_percentage='{$validPrepaymentPercentage}'
                WHERE prepayment_id='{$validPrepaymentId}' AND blog_id='{$this->conf->getBlogId()}'
            ");

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PREPAYMENT_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PREPAYMENT_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}prepayments
                (
                    period_from, period_till,
                    item_prices_included,
                    item_deposits_included,
                    extra_prices_included,
                    extra_deposits_included,
                    pickup_fees_included,
                    distance_fees_included,
                    return_fees_included,
                    prepayment_percentage,
                    blog_id
                ) VALUES
                (
                    '{$validPeriodFrom}', '{$validPeriodTill}',
                    '{$validItemPricesIncluded}',
                    '{$validItemDepositsIncluded}',
                    '{$validExtraPricesIncluded}',
                    '{$validExtraDepositsIncluded}',
                    '{$validPickupFeesIncluded}',
                    '{$validAdditionalFeesIncluded}',
                    '{$validReturnFeesIncluded}',
                    '{$validPrepaymentPercentage}',
                    '{$this->conf->getBlogId()}'
                )
            ");
            if($saved)
            {
                // Update object id with newly inserted id for future work
                $this->prepaymentId = $this->conf->getInternalWPDB()->insert_id;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PREPAYMENT_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PREPAYMENT_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    /**
     * Not used for this element
     */
    public function registerForTranslation()
    {
        // not used
    }

    /**
     * @note - We use 'blog_id' here because we don't want to allow plugin managers from one multisite site to delete items in other multisite site
     */
    public function delete()
    {
        $validPrepaymentId = StaticValidator::getValidPositiveInteger($this->prepaymentId);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}prepayments
            WHERE prepayment_id='{$validPrepaymentId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_PREPAYMENT_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_PREPAYMENT_DELETED_TEXT');
        }


        return $deleted;
    }


    /* ------------------------------------------------------------------------------------ */
    /* Element-specific methods                                                             */
    /* ------------------------------------------------------------------------------------ */

    /**
     * Based on price calculation type from duration days / hours it will return period (in seconds)
     * @param $paramDays
     * @param $paramHours
     * @param string $paramType
     * @return int
     */
    protected function getPeriodByPriceType($paramDays, $paramHours, $paramType)
    {
        $validDays =  StaticValidator::getValidPositiveInteger($paramDays, 0);
        $validHours = StaticValidator::getValidPositiveInteger($paramHours, 0);
        if($this->priceCalculationType == 1)
        {
            // Days only
            $retPeriod = $paramType == "FROM" ? $validDays*86400 : $validDays*86400+86400-1;
        } else if($this->priceCalculationType == 2)
        {
            // Hours only
            $retPeriod = $paramType == "FROM" ? $validHours*3600 : $validHours*3600+3600-1;
        } else
        {
            // Mixed - Days & Hours
            $retPeriod = $paramType == "FROM" ? $validDays*86400 + $validHours*3600 : ($validDays*86400+86400-1) + ($validHours*3600+3600-1);
        }

        return $retPeriod;
    }

    /**
     * Displayed only in yearly & monthly modes
     * NOTE: This drop-down is INDEPENDENT from min/max order durations, as we don't want to get the values impacted
     * @param int $paramMonths
     * @return string
     */
    public function getYearsDropdownOptionsHTML($paramMonths = 0)
    {
        $retHTML = "";
        $selectedYears = StaticValidator::getFloorYearsFromMonths($paramMonths);
        for($years = 1; $years <= 10; $years++)
        {
            if($selectedYears == $years)
            {
                $retHTML .= '<option value="'.esc_attr($years).'" selected="selected">'.$years.' '.$this->lang->getPrint($years == 1 ? 'LANG_YEAR1_TEXT' : 'LANG_YEARS2_TEXT').'</option>'."\n";
            } else
            {
                $retHTML .= '<option value="'.esc_attr($years).'">'.$years.' '.$this->lang->getPrint($years == 1 ? 'LANG_YEAR1_TEXT' : 'LANG_YEARS2_TEXT').'</option>'."\n";
            }
        }

        return $retHTML;
    }

    /**
     * Displayed only in monthly mode
     * NOTE: This drop-down is INDEPENDENT from min/max order durations, as we don't want to get the values impacted
     * @param int $paramMonths
     * @return string
     */
    public function getMonthsDropdownOptionsHTML($paramMonths = 0)
    {
        $retHTML = "";
        $selectedHours = StaticValidator::getMonthsOnLastYearFromMonths($paramMonths);
        for($months = 1; $months <= 12; $months++)
        {
            if($selectedHours == $months)
            {
                $retHTML .= '<option value="'.esc_attr($months).'" selected="selected">'.$months.' '.$this->lang->getPrint($months == 1 ? 'LANG_MONTH1_TEXT' : 'LANG_MONTHS2_TEXT').'</option>'."\n";
            } else
            {
                $retHTML .= '<option value="'.esc_attr($months).'">'.$months.' '.$this->lang->getPrint($months == 1 ? 'LANG_MONTH1_TEXT' : 'LANG_MONTHS2_TEXT').'</option>'."\n";
            }
        }

        return $retHTML;
    }

    // NOTE: No days dropdown - days number is entered, if allowed (not on yearly or monthly time_periods)

    /**
     * NOTE: This drop-down is INDEPENDENT from min/max order durations, as we don't want to get the values impacted
     * @param int $paramPeriod
     * @return string
     */
    public function getHoursDropdownOptionsHTML($paramPeriod = 0)
    {
        $retHTML = "";
        $selectedHours = StaticValidator::getFloorHoursOnLastDayFromSeconds($paramPeriod);
        for($hours = 0; $hours <= 23; $hours++)
        {
            if($selectedHours == $hours)
            {
                $retHTML .= '<option value="'.esc_attr($hours).'" selected="selected">'.$hours.' '.$this->lang->getPrint($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>'."\n";
            } else
            {
                $retHTML .= '<option value="'.esc_attr($hours).'">'.$hours.' '.$this->lang->getPrint($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>'."\n";
            }
        }

        return $retHTML;
    }

    /**
     * NOTE: This drop-down is INDEPENDENT from min/max order durations, as we don't want to get the values impacted
     * @param int $paramPeriod
     * @return string
     */
    public function getMinutesDropdownOptionsHTML($paramPeriod = 0)
    {
        // Get minutes interval
        $minutesInterval = round($this->timeInterval / 60, 0);
        $selectedMinutes = StaticValidator::getFloorMinutesOnLastHourFromSeconds($paramPeriod);

        $retHTML = "";
        for($minutes = 0; $minutes < 60; $minutes = $minutes+$minutesInterval)
        {
            if($selectedMinutes == $minutes)
            {
                $retHTML .= '<option value="'.esc_attr($minutes).'" selected="selected">'.$minutes.' '.$this->lang->getPrint('LANG_MINUTES_TEXT').'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($minutes).'">'.$minutes.' '.$this->lang->getPrint('LANG_MINUTES_TEXT').'</option>';
            }
        }

        return $retHTML;
    }
}