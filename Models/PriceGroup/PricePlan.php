<?php
/**
 * Price Plan Element

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PriceGroup;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class PricePlan extends AbstractStack implements StackInterface, ElementInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $pricePlanId            = 0;
    private $shortDateFormat        = "m/d/Y";

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramPricePlanId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set price plan id
        $this->pricePlanId = StaticValidator::getValidPositiveInteger($paramPricePlanId, 0);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
    }

    private function getDataFromDatabaseById($paramPricePlanId, $paramColumns = array('*'))
    {
        $validPricePlanId = StaticValidator::getValidPositiveInteger($paramPricePlanId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);

        $sqlQuery = "
            SELECT {$validSelect}
            FROM {$this->conf->getPrefix()}price_plans
            WHERE price_plan_id='{$validPricePlanId}'
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
        return $this->pricePlanId;
    }

    /**
     * Element-specific function
     * @return int
     */
    public function getPriceGroupId()
    {
        $retPriceGroupId = 0;
        $pricePlanData = $this->getDataFromDatabaseById($this->pricePlanId, array('price_group_id'));
        if(!is_null($pricePlanData))
        {
            $retPriceGroupId = $pricePlanData['price_group_id'];
        }
        return $retPriceGroupId;
    }

    /**
     * Element-specific function
     * @return int
     */
    public function getCouponCode()
    {
        $retCouponCode = "";
        $pricePlanData = $this->getDataFromDatabaseById($this->pricePlanId, array('coupon_code'));
        if(!is_null($pricePlanData))
        {
            $retCouponCode = $pricePlanData['coupon_code'];
        }
        return $retCouponCode;
    }

    /**
     * Checks if current user can edit the element
     * @param $paramPartnerId - partner id is mandatory here, as it comes from other plugin
     * @return bool
     */
    public function canEdit($paramPartnerId)
    {
        $canEdit = false;
        if($this->pricePlanId > 0)
        {
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_items'))
            {
                $canEdit = true;
            } else if($paramPartnerId > 0 && $paramPartnerId == get_current_user_id() && current_user_can('manage_'.$this->conf->getExtPrefix().'own_items'))
            {
                $canEdit = true;
            }
        }

        return $canEdit;
    }

    /**
     * Element-specific function
     * @return bool
     */
    public function isSeasonal()
    {
        $retIsSeasonal = false;
        $pricePlanData = $this->getDataFromDatabaseById($this->pricePlanId, array('seasonal_price'));
        if(!is_null($pricePlanData))
        {
            $retIsSeasonal = $pricePlanData['seasonal_price'] == 1 ? true : false;
        }
        return $retIsSeasonal;
    }

    /**
     * @param bool $paramPrefillWhenNull - NOT USED
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->pricePlanId);

        if(!is_null($ret))
        {
            // Make raw
            $ret['coupon_code'] = stripslashes($ret['coupon_code']);

            if($ret['start_timestamp'] > 0)
            {
                $ret['start_date'] = date_i18n($this->shortDateFormat, $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['start_time'] = date_i18n('H:i:s', $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
                $startDateI18n = date_i18n(get_option('date_format'), $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
                $startTimeI18n = date_i18n(get_option('time_format'), $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['start_date'] = '';
                $ret['start_time'] = '';
                $startDateI18n = $this->lang->getText('LANG_ALL_YEAR_TEXT');
                $startTimeI18n = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." 00:00:00"), true);
            }

            if($ret['end_timestamp'] > 0)
            {
                $ret['end_date'] = date_i18n($this->shortDateFormat, $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['end_time'] = date_i18n('H:i:s', $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
                $endDateI18n = date_i18n(get_option('date_format'), $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
                $endTimeI18n = date_i18n(get_option('time_format'), $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['end_date'] = '';
                $ret['end_time'] = '';
                $endDateI18n = $this->lang->getText('LANG_ALL_YEAR_TEXT');
                $endTimeI18n = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." 23:59:59"), true);
            }

            if($ret['seasonal_price'] == 0)
            {
                $labelText = $this->lang->getText('LANG_PRICING_REGULAR_PRICE_TEXT');
            } else
            {
                $labelText = $this->lang->getText('LANG_PERIOD_TEXT').': ';
                $labelText .= date_i18n(get_option('date_format').' '.get_option('time_format'), $ret['start_timestamp'] + get_option('gmt_offset') * 3600, true);
                $labelText .= ' - '.date_i18n(get_option('date_format').' '.get_option('time_format'), $ret['end_timestamp'] + get_option('gmt_offset') * 3600, true);
            }
            if($ret['coupon_code'] != '')
            {
                $labelText .= ' ('.$this->lang->getText('LANG_COUPON_TEXT').': '.esc_html($ret['coupon_code']).')';
            }

            // Prepare output for print
            $ret['print_coupon_code'] = esc_html($ret['coupon_code']);
            $ret['print_label'] = esc_html($labelText);
            $ret['start_date_i18n'] = $startDateI18n;
            $ret['start_time_i18n'] = $startTimeI18n;
            $ret['end_date_i18n'] = $endDateI18n;
            $ret['end_time_i18n'] = $endTimeI18n;

            // Prepare output for edit
            $ret['edit_coupon_code'] = esc_attr($ret['coupon_code']); // for input field
        }

        return $ret;
    }

    /**
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validPricePlanId = StaticValidator::getValidPositiveInteger($this->pricePlanId);

        $validPriceGroupId = isset($params['price_group_id']) ? StaticValidator::getValidPositiveInteger($params['price_group_id'], 0) : 0;
        $sanitizedCouponCode = isset($params['coupon_code']) ? sanitize_text_field($params['coupon_code']) : '';
        $validCouponCode = esc_sql($sanitizedCouponCode); // for sql queries only
        $validStartTimestamp = 0;
        if(isset($params['start_date']) && $params['start_date'] != "")
        {
            $validISOStartDate = StaticValidator::getValidISO_Date($params['start_date'], $this->shortDateFormat);
            $validStartTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($validISOStartDate, '00:00:00');
        }
        $validEndTimestamp = 0;
        if($validStartTimestamp > 0 && isset($params['end_date']) && $params['end_date'] != "")
        {
            $validISOEndDate = StaticValidator::getValidISO_Date($params['end_date'], $this->shortDateFormat);
            $validEndTimestamp = StaticValidator::getUTC_TimestampFromLocalISO_DateTime($validISOEndDate, '23:59:59');
        }

        $validDailyRateMon = isset($params['daily_rate_mon']) ? floatval($params['daily_rate_mon']) : 0.00;
        $validDailyRateTue = isset($params['daily_rate_tue']) ? floatval($params['daily_rate_tue']) : 0.00;
        $validDailyRateWed = isset($params['daily_rate_wed']) ? floatval($params['daily_rate_wed']) : 0.00;
        $validDailyRateThu = isset($params['daily_rate_thu']) ? floatval($params['daily_rate_thu']) : 0.00;
        $validDailyRateFri = isset($params['daily_rate_fri']) ? floatval($params['daily_rate_fri']) : 0.00;
        $validDailyRateSat = isset($params['daily_rate_sat']) ? floatval($params['daily_rate_sat']) : 0.00;
        $validDailyRateSun = isset($params['daily_rate_sun']) ? floatval($params['daily_rate_sun']) : 0.00;
        $validHourlyRateMon = isset($params['hourly_rate_mon']) ? floatval($params['hourly_rate_mon']) : 0.00;
        $validHourlyRateTue = isset($params['hourly_rate_tue']) ? floatval($params['hourly_rate_tue']) : 0.00;
        $validHourlyRateWed = isset($params['hourly_rate_wed']) ? floatval($params['hourly_rate_wed']) : 0.00;
        $validHourlyRateThu = isset($params['hourly_rate_thu']) ? floatval($params['hourly_rate_thu']) : 0.00;
        $validHourlyRateFri = isset($params['hourly_rate_fri']) ? floatval($params['hourly_rate_fri']) : 0.00;
        $validHourlyRateSat = isset($params['hourly_rate_sat']) ? floatval($params['hourly_rate_sat']) : 0.00;
        $validHourlyRateSun = isset($params['hourly_rate_sun']) ? floatval($params['hourly_rate_sun']) : 0.00;
        $seasonalPrice = $validStartTimestamp > 0 && $validEndTimestamp > 0 ? 1 : 0;

        $SQLExistConflictingDates = "
            SELECT *
            FROM {$this->conf->getPrefix()}price_plans
            WHERE
            (
                ('{$validStartTimestamp}' BETWEEN start_timestamp AND end_timestamp)
                OR ('{$validEndTimestamp}' BETWEEN start_timestamp AND end_timestamp)
                OR (start_timestamp BETWEEN '{$validStartTimestamp}' AND '{$validEndTimestamp}')
                OR (end_timestamp BETWEEN '{$validStartTimestamp}' AND '{$validEndTimestamp}')            
            ) AND price_group_id='{$validPriceGroupId}' AND coupon_code='{$validCouponCode}'
            AND blog_id='{$this->conf->getBlogId()}' AND price_plan_id!='{$validPricePlanId}'
        ";

        // DEBUG
        //die("<br />Query: ".nl2br($SQLExistConflictingDates));
        $existConflictingDates = $this->conf->getInternalWPDB()->get_row($SQLExistConflictingDates, ARRAY_A);

        if(($validStartTimestamp > 0 || $validEndTimestamp > 0) && $validStartTimestamp >= $validEndTimestamp)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_PRICE_PLAN_LATER_DATE_ERROR_TEXT');
        }
        if($validPriceGroupId == 0)
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_PRICE_PLAN_INVALID_PRICE_GROUP_ERROR_TEXT');
        }
        if(!is_null($existConflictingDates))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_PRICE_PLAN_EXISTS_FOR_DATE_RANGE_ERROR_TEXT');
        }

        if($validPricePlanId > 0 && $ok)
        {
            $query = "UPDATE `{$this->conf->getPrefix()}price_plans` SET
                price_group_id='{$validPriceGroupId}',
                coupon_code='{$validCouponCode}',
                start_timestamp='{$validStartTimestamp}',
                end_timestamp='{$validEndTimestamp}',
                daily_rate_mon='{$validDailyRateMon}',
                daily_rate_tue='{$validDailyRateTue}',
                daily_rate_wed='{$validDailyRateWed}',
                daily_rate_thu='{$validDailyRateThu}',
                daily_rate_fri='{$validDailyRateFri}',
                daily_rate_sat='{$validDailyRateSat}',
                daily_rate_sun='{$validDailyRateSun}',
                hourly_rate_mon='{$validHourlyRateMon}',
                hourly_rate_tue='{$validHourlyRateTue}',
                hourly_rate_wed='{$validHourlyRateWed}',
                hourly_rate_thu='{$validHourlyRateThu}',
                hourly_rate_fri='{$validHourlyRateFri}',
                hourly_rate_sat='{$validHourlyRateSat}',
                hourly_rate_sun='{$validHourlyRateSun}',
                seasonal_price='{$seasonalPrice}'
                WHERE price_plan_id='{$validPricePlanId}' AND blog_id='{$this->conf->getBlogId()}'
            ";

            // DEBUG
            //echo nl2br($query)."<br /><br />";
            $saved = $this->conf->getInternalWPDB()->query($query);
            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PRICE_PLAN_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PRICE_PLAN_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO `{$this->conf->getPrefix()}price_plans`
                (
                    price_group_id,
                    coupon_code,
                    start_timestamp,
                    end_timestamp,
                    daily_rate_mon,
                    daily_rate_tue,
                    daily_rate_wed,
                    daily_rate_thu,
                    daily_rate_fri,
                    daily_rate_sat,
                    daily_rate_sun,
                    hourly_rate_mon,
                    hourly_rate_tue,
                    hourly_rate_wed,
                    hourly_rate_thu,
                    hourly_rate_fri,
                    hourly_rate_sat,
                    hourly_rate_sun,
                    seasonal_price,
                    blog_id
                ) VALUES
                (
                    '{$validPriceGroupId}',
                    '{$validCouponCode}',
                    '{$validStartTimestamp}',
                    '{$validEndTimestamp}',
                    '{$validDailyRateMon}',
                    '{$validDailyRateTue}',
                    '{$validDailyRateWed}',
                    '{$validDailyRateThu}',
                    '{$validDailyRateFri}',
                    '{$validDailyRateSat}',
                    '{$validDailyRateSun}',
                    '{$validHourlyRateMon}',
                    '{$validHourlyRateTue}',
                    '{$validHourlyRateWed}',
                    '{$validHourlyRateThu}',
                    '{$validHourlyRateFri}',
                    '{$validHourlyRateSat}',
                    '{$validHourlyRateSun}',
                    '{$seasonalPrice}',
                    '{$this->conf->getBlogId()}'
                );
            ");
            if($saved)
            {
                // Update object id with newly inserted id for future work
                $this->pricePlanId = $this->conf->getInternalWPDB()->insert_id;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_PRICE_PLAN_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_PRICE_PLAN_INSERTED_TEXT');
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
     * Seasonal price plan delete method. All data securely validated
     */
    public function delete()
    {
        $validPricePlanId = StaticValidator::getValidPositiveInteger($this->pricePlanId, 0);

        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM `{$this->conf->getPrefix()}price_plans`
            WHERE price_plan_id='{$validPricePlanId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_PRICE_PLAN_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_PRICE_PLAN_DELETED_TEXT');
        }

        return $deleted;
    }


    /*******************************************************************************/
    /************************* ELEMENT SPECIFIC FUNCTIONS **************************/
    /*******************************************************************************/

    /**
     * NOTE: Returns an unescaped array
     * @return array
     */
    public function getMonthsOfTheYear()
    {
        $monthsOfTheYear = array(
            'jan' => $this->lang->getText('LANG_JAN_TEXT'),
            'feb' => $this->lang->getText('LANG_FEB_TEXT'),
            'mar' => $this->lang->getText('LANG_MAR_TEXT'),
            'apr' => $this->lang->getText('LANG_APR_TEXT'),
            'may' => $this->lang->getText('LANG_MAY_TEXT'),
            'jun' => $this->lang->getText('LANG_JUN_TEXT'),
            'jul' => $this->lang->getText('LANG_JUL_TEXT'),
            'aug' => $this->lang->getText('LANG_AUG_TEXT'),
            'sep' => $this->lang->getText('LANG_SEP_TEXT'),
            'oct' => $this->lang->getText('LANG_OCT_TEXT'),
            'nov' => $this->lang->getText('LANG_NOV_TEXT'),
            'dec' => $this->lang->getText('LANG_DEC_TEXT'),
        );

        return $monthsOfTheYear;
    }

    /**
     * NOTE: Returns an unescaped array
     * @return array
     */
    public function getDaysOfTheWeek()
    {
        if(get_option('start_of_week') == 1)
        {
            $daysOfTheWeek = array(
                'mon' => $this->lang->getText('LANG_MON_TEXT'),
                'tue' => $this->lang->getText('LANG_TUE_TEXT'),
                'wed' => $this->lang->getText('LANG_WED_TEXT'),
                'thu' => $this->lang->getText('LANG_THU_TEXT'),
                'fri' => $this->lang->getText('LANG_FRI_TEXT'),
                'sat' => $this->lang->getText('LANG_SAT_TEXT'),
                'sun' => $this->lang->getText('LANG_SUN_TEXT'),
            );
        } else
        {
            $daysOfTheWeek = array(
                'sun' => $this->lang->getText('LANG_SUN_TEXT'),
                'mon' => $this->lang->getText('LANG_MON_TEXT'),
                'tue' => $this->lang->getText('LANG_TUE_TEXT'),
                'wed' => $this->lang->getText('LANG_WED_TEXT'),
                'thu' => $this->lang->getText('LANG_THU_TEXT'),
                'fri' => $this->lang->getText('LANG_FRI_TEXT'),
                'sat' => $this->lang->getText('LANG_SAT_TEXT'),
            );
        }

        return $daysOfTheWeek;
    }
}