<?php
/**
 * NOTE: Word 'order' here is correct, as these settings impacting both - order and block
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Settings;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Settings\Setting;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class ChangeOrderSettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_minimum_booking_period');
        $objSetting->saveNumber(0);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_maximum_booking_period');
        $objSetting->saveNumber(0);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_minimum_block_period_between_bookings');
        $objSetting->saveNumber(0);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_minimum_period_until_pickup');
        $objSetting->saveNumber(0);

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_ORDER_SETTINGS_UPDATED_TEXT')));

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=order-settings');
        exit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $retSettings = array();

        // Get minutes interval
        $currentTimeInterval = ConfigurationInterface::TIME_INTERVAL;
        $minutesInterval = round($currentTimeInterval / 60, 0);


        // LOGIN FORM
        if(ConfigurationInterface::SHOW_LOGIN_FORM == 1)
        {
            $trustedShowLoginFormHTML  = '<option value="0" disabled="disabled">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $trustedShowLoginFormHTML .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        } else
        {
            $trustedShowLoginFormHTML  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $trustedShowLoginFormHTML .= '<option value="1" disabled="disabled">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_show_login_form_html'] = $trustedShowLoginFormHTML;


        // GUEST CUSTOMER LOOKUP
        if(ConfigurationInterface::GUEST_CUSTOMER_LOOKUP_ALLOWED == 1)
        {
            $trustedGuestCustomerLookupHTML  = '<option value="0" disabled="disabled">'.$this->lang->escHTML('LANG_NOT_ALLOWED_TEXT').'</option>'."\n";
            $trustedGuestCustomerLookupHTML .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_ALLOWED_TEXT').'</option>'."\n";
        } else
        {
            $trustedGuestCustomerLookupHTML  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_NOT_ALLOWED_TEXT').'</option>'."\n";
            $trustedGuestCustomerLookupHTML .= '<option value="1" disabled="disabled">'.$this->lang->escHTML('LANG_ALLOWED_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_guest_customer_lookup_allowed_html'] = $trustedGuestCustomerLookupHTML;


        // AUTOMATICALLY CREATE ACCOUNT
        if(ConfigurationInterface::AUTOMATICALLY_CREATE_ACCOUNT == 1)
        {
            $trustedAutomaticallyCreateAccountHTML  = '<option value="0" disabled="disabled">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $trustedAutomaticallyCreateAccountHTML .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        } else
        {
            $trustedAutomaticallyCreateAccountHTML  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $trustedAutomaticallyCreateAccountHTML .= '<option value="1" disabled="disabled">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_automatically_create_account_html'] = $trustedAutomaticallyCreateAccountHTML;


        // MIN ORDER PERIOD (RELATES TO TIME INTERVAL SETTING)
        $selectMinOrderPeriod = "";
        for($minutes = 0; $minutes < 60; $minutes = $minutes+$minutesInterval)
        {
            $inSeconds = $minutes*60;
            if($this->dbSets->get('conf_minimum_booking_period') == $inSeconds)
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$minutes.' '.$this->lang->escHTML('LANG_MINUTES2_TEXT').'</option>';
            } else
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$minutes.' '.$this->lang->escHTML('LANG_MINUTES2_TEXT').'</option>';
            }
        }
        for($hours = 1; $hours <= 23; $hours++)
        {
            $inSeconds = $hours*3600;
            if($this->dbSets->get('conf_minimum_booking_period') == $inSeconds)
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$hours.' '.$this->lang->escHTML($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$hours.' '.$this->lang->escHTML($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>'."\n";
            }
        }
        for($days = 1; $days <= 6; $days++)
        {
            $inSeconds = $days*86400;
            if($this->dbSets->get('conf_minimum_booking_period') == $inSeconds)
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$days.' '.$this->lang->escHTML($days == 1 ? 'LANG_DAY1_TEXT' : 'LANG_DAYS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$days.' '.$this->lang->escHTML($days == 1 ? 'LANG_DAY1_TEXT' : 'LANG_DAYS2_TEXT').'</option>'."\n";
            }
        }
        for($weeks = 1; $weeks <= 4; $weeks++)
        {
            $inSeconds = $weeks*7*86400;
            if($this->dbSets->get('conf_minimum_booking_period') == $inSeconds)
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$weeks.' '.$this->lang->escHTML($weeks == 1 ? 'LANG_WEEK1_TEXT' : 'LANG_WEEKS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$weeks.' '.$this->lang->escHTML($weeks == 1 ? 'LANG_WEEK1_TEXT' : 'LANG_WEEKS2_TEXT').'</option>'."\n";
            }
        }
        for($months = 1; $months <= 11; $months++)
        {
            // NOTE: We are always sure here, that the number never will be less than minimum possible days (for leap years)
            $inSeconds = $months*28*86400; // Sums to 28 days, 56 days, 84 days, etc. As this is MIN period, we FLOOR numbers
            if($this->dbSets->get('conf_minimum_booking_period') == $inSeconds)
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$months.' '.$this->lang->escHTML($months == 1 ? 'LANG_MONTH1_TEXT' : 'LANG_MONTHS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$months.' '.$this->lang->escHTML($months == 1 ? 'LANG_MONTH1_TEXT' : 'LANG_MONTHS2_TEXT').'</option>'."\n";
            }
        }
        for($years = 1; $years <= 10; $years++)
        {
            $inSeconds = floor($years*365.25)*86400; // Rounds to 365 days, 730 days, 1095 days, 1461 days (leap-year), etc. As this is MIN period, we FLOOR numbers
            if($this->dbSets->get('conf_minimum_booking_period') == $inSeconds)
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$years.' '.$this->lang->escHTML($years == 1 ? 'LANG_YEAR1_TEXT' : 'LANG_YEARS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMinOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$years.' '.$this->lang->escHTML($years == 1 ? 'LANG_YEAR1_TEXT' : 'LANG_YEARS2_TEXT').'</option>'."\n";
            }
        }
        $retSettings['trusted_min_order_period_dropdown_options_html'] = $selectMinOrderPeriod;


        // MAX ORDER PERIOD
        $selectMaxOrderPeriod = "";
        for($minutes = 0; $minutes < 60; $minutes = $minutes+$minutesInterval)
        {
            $inSeconds = $minutes*60;
            if($this->dbSets->get('conf_maximum_booking_period') == $inSeconds)
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$minutes.' '.$this->lang->escHTML('LANG_MINUTES2_TEXT').'</option>';
            } else
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$minutes.' '.$this->lang->escHTML('LANG_MINUTES2_TEXT').'</option>';
            }
        }
        for($hours = 1; $hours <= 23; $hours++)
        {
            $inSeconds = $hours*3600;
            if($this->dbSets->get('conf_maximum_booking_period') == $inSeconds)
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$hours.' '.$this->lang->escHTML($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$hours.' '.$this->lang->escHTML($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>'."\n";
            }
        }
        for($days = 1; $days <= 6; $days++)
        {
            $inSeconds = $days*86400;
            if($this->dbSets->get('conf_maximum_booking_period') == $inSeconds)
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$days.' '.$this->lang->escHTML($days == 1 ? 'LANG_DAY1_TEXT' : 'LANG_DAYS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$days.' '.$this->lang->escHTML($days == 1 ? 'LANG_DAY1_TEXT' : 'LANG_DAYS2_TEXT').'</option>'."\n";
            }
        }
        for($weeks = 1; $weeks <= 4; $weeks++)
        {
            $inSeconds = $weeks*7*86400;
            if($this->dbSets->get('conf_maximum_booking_period') == $inSeconds)
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$weeks.' '.$this->lang->escHTML($weeks == 1 ? 'LANG_WEEK1_TEXT' : 'LANG_WEEKS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$weeks.' '.$this->lang->escHTML($weeks == 1 ? 'LANG_WEEK1_TEXT' : 'LANG_WEEKS2_TEXT').'</option>'."\n";
            }
        }
        for($months = 1; $months <= 11; $months++)
        {
            // NOTE: We are always sure here, that the number never will be less than maximum possible days (for 31 days month, 32 days for 2 months)
            $inSeconds = $months*31*86400; // Maxes to 31 day, 62 day, 93 days, etc. As this is MAX period, we CEIL numbers
            if($this->dbSets->get('conf_maximum_booking_period') == $inSeconds)
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$months.' '.$this->lang->escHTML($months == 1 ? 'LANG_MONTH1_TEXT' : 'LANG_MONTHS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$months.' '.$this->lang->escHTML($months == 1 ? 'LANG_MONTH1_TEXT' : 'LANG_MONTHS2_TEXT').'</option>'."\n";
            }
        }
        for($years = 1; $years <= 10; $years++)
        {
            // NOTE: We are always sure here, that the number never will be less than maximum possible days (for leap years)
            $inSeconds = ceil($years*365.25)*86400; // Rounds to 366 days (leap-year), 731 days, 1096 days, 1461 days (leap-year), etc. As this is MAX period, we CEIL numbers
            if($this->dbSets->get('conf_maximum_booking_period') == $inSeconds)
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$years.' '.$this->lang->escHTML($years == 1 ? 'LANG_YEAR1_TEXT' : 'LANG_YEARS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMaxOrderPeriod .= '<option value="'.esc_attr($inSeconds).'">'.$years.' '.$this->lang->escHTML($years == 1 ? 'LANG_YEAR1_TEXT' : 'LANG_YEARS2_TEXT').'</option>'."\n";
            }
        }
        $retSettings['trusted_max_order_period_dropdown_options_html'] = $selectMaxOrderPeriod;


        // CLEANING PERIODS
        $cleaningPeriods = array(
            '1' => '0 '.$this->lang->getText('LANG_MINUTES10_TEXT'),
            '899' => '15 '.$this->lang->getText('LANG_MINUTES2_TEXT'),
            '1799' => '30 '.$this->lang->getText('LANG_MINUTES10_TEXT'),
            '2599' => '45 '.$this->lang->getText('LANG_MINUTES2_TEXT'),
            '3599' => '1 '.$this->lang->getText('LANG_HOUR1_TEXT'),
            '7199' => '2 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '10799' => '3 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '14399' => '4 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '17999' => '5 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '21599' => '6 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '25199' => '7 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '28799' => '8 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '32399' => '9 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '35999' => '10 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '39599' => '11 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '43199' => '12 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '46799' => '13 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '50399' => '14 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '53999' => '15 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '57599' => '16 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '61199' => '17 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '64799' => '18 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '68399' => '19 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '71999' => '20 '.$this->lang->getText('LANG_HOURS10_TEXT'),
            '75599' => '21 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '79199' => '22 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '82799' => '23 '.$this->lang->getText('LANG_HOURS2_TEXT'),
            '86399' => '24 '.$this->lang->getText('LANG_HOURS2_TEXT'),
        );

        // ITEM CLEANING PERIOD
        // NOTE: 'ITEM' is ok here, as it is per exact item
        $selectItemCleaningPeriod = "";
        foreach($cleaningPeriods as $key => $value)
        {
            if($key == $this->dbSets->get('conf_minimum_block_period_between_bookings'))
            {
                $selectItemCleaningPeriod .= '<option value="'.esc_attr($key).'" selected="selected">'.esc_html($value).'</option>'."\n";
            } else
            {
                $selectItemCleaningPeriod .= '<option value="'.esc_attr($key).'">'.esc_html($value).'</option>'."\n";
            }
        }
        $retSettings['trusted_item_cleaning_period_dropdown_options_html'] = $selectItemCleaningPeriod;


        // MIN PERIOD UNTIL PICK-UP
        $selectMinPeriodUntilPickup = "";
        for($minutes = 0; $minutes < 60; $minutes = $minutes+15)
        {
            $inSeconds = $minutes*60;
            if($inSeconds == $this->dbSets->get('conf_minimum_period_until_pickup'))
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$minutes.' '.$this->lang->escHTML('LANG_MINUTES2_TEXT').'</option>';
            } else
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'">'.$minutes.' '.$this->lang->escHTML('LANG_MINUTES2_TEXT').'</option>';
            }
        }
        for($hours = 1; $hours <= 23; $hours++)
        {
            $inSeconds = $hours*3600;
            if($inSeconds == $this->dbSets->get('conf_minimum_period_until_pickup'))
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$hours.' '.$this->lang->escHTML($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>';
            } else
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'">'.$hours.' '.$this->lang->escHTML($hours == 1 ? 'LANG_HOUR1_TEXT' : 'LANG_HOURS2_TEXT').'</option>';
            }
        }
        for($days = 1; $days <= 6; $days++)
        {
            $inSeconds = $days*86400;
            if($inSeconds == $this->dbSets->get('conf_minimum_period_until_pickup'))
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$days.' '.$this->lang->escHTML($days == 1 ? 'LANG_DAY1_TEXT' : 'LANG_DAYS2_TEXT').'</option>';
            } else
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'">'.$days.' '.$this->lang->escHTML($days == 1 ? 'LANG_DAY1_TEXT' : 'LANG_DAYS2_TEXT').'</option>';
            }
        }
        for($weeks = 1; $weeks <= 4; $weeks++)
        {
            $inSeconds = $weeks*7*86400;
            if($inSeconds == $this->dbSets->get('conf_minimum_period_until_pickup'))
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.$weeks.' '.$this->lang->escHTML($weeks == 1 ? 'LANG_WEEK1_TEXT' : 'LANG_WEEKS2_TEXT').'</option>'."\n";
            } else
            {
                $selectMinPeriodUntilPickup .= '<option value="'.esc_attr($inSeconds).'">'.$weeks.' '.$this->lang->escHTML($weeks == 1 ? 'LANG_WEEK1_TEXT' : 'LANG_WEEKS2_TEXT').'</option>'."\n";
            }
        }
        $retSettings['trusted_min_period_until_pickup_dropdown_options_html'] = $selectMinPeriodUntilPickup;

        return $retSettings;
    }

    /**
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_order_settings'])) { $this->processSave(); }
    }
}
