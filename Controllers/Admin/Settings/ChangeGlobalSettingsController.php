<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Settings;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\PostType\ItemModelPostType;
use FleetManagement\Models\PostType\LocationPostType;
use FleetManagement\Models\PostType\PagePostType;
use FleetManagement\Models\Settings\Setting;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class ChangeGlobalSettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_cancelled_payment_page_id');
        $objSetting->saveNumber(0);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_confirmation_page_id');
        $objSetting->saveNumber(0);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_terms_and_conditions_page_id');
        $objSetting->saveNumber(0);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_system_style');
        $objSetting->saveText();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_short_date_format');
        $objSetting->saveText();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_distance_measurement_unit');
        $objSetting->saveText();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_noon_time');
        $objSetting->saveTime();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_page_url_slug');
        $objSetting->saveKey();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_item_url_slug');
        $objSetting->saveKey();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_location_url_slug');
        $objSetting->saveKey();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_reveal_partner');
        $objSetting->saveNumber(1, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_classify_items');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_load_datepicker_from_plugin');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_load_fancybox_from_plugin');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_load_font_awesome_from_plugin');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_load_slick_slider_from_plugin');
        $objSetting->saveNumber(0, array(0, 1));

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_GLOBAL_SETTINGS_UPDATED_TEXT')));

        // Note: Initialize line bellow for every extension!
        if(isset($_POST['conf_page_url_slug']))
        {
            $objPostType = new PagePostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'page');
            $objPostType->register($_POST['conf_page_url_slug'], 95);
        }
        if(isset($_POST['conf_item_url_slug']))
        {
            $objPostType = new ItemModelPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'item');
            $objPostType->register($_POST['conf_item_url_slug'], 96);
        }
        if(isset($_POST['conf_location_url_slug']))
        {
            $objPostType = new LocationPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'location');
            $objPostType->register($_POST['conf_location_url_slug'], 97);
        }

        // We need this due the fact that we might have changed a path for items, locations or pages in global settings
        flush_rewrite_rules();

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=global-settings');
        exit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $retSettings = array();

        if(ConfigurationInterface::USE_SESSIONS == 1)
        {
            $trustedUseSessionsHTML  = '<option value="0" disabled="disabled">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $trustedUseSessionsHTML .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        } else
        {
            $trustedUseSessionsHTML  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $trustedUseSessionsHTML .= '<option value="1" disabled="disabled">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_use_sessions_html'] = $trustedUseSessionsHTML;


        if(ConfigurationInterface::DROPDOWN_STYLE == 1)
        {
            $trustedDropdownStyleHTML   = '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_SETTING_DROPDOWN_STYLE_1_TEXT').'</option>'."\n";
            $trustedDropdownStyleHTML  .= '<option value="2" disabled="disabled">'.$this->lang->escHTML('LANG_SETTING_DROPDOWN_STYLE_2_TEXT').'</option>'."\n";
        } else
        {
            $trustedDropdownStyleHTML  = '<option value="1" disabled="disabled">'.$this->lang->escHTML('LANG_SETTING_DROPDOWN_STYLE_1_TEXT').'</option>'."\n";
            $trustedDropdownStyleHTML .= '<option value="2" selected="selected">'.$this->lang->escHTML('LANG_SETTING_DROPDOWN_STYLE_2_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_dropdown_style_html'] = $trustedDropdownStyleHTML;


        if(ConfigurationInterface::INPUT_STYLE == 1)
        {
            $trustedInputStyleHTML   = '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_SETTING_INPUT_STYLE_1_TEXT').'</option>'."\n";
            $trustedInputStyleHTML  .= '<option value="2" disabled="disabled">'.$this->lang->escHTML('LANG_SETTING_INPUT_STYLE_2_TEXT').'</option>'."\n";
        } else
        {
            $trustedInputStyleHTML  = '<option value="1" disabled="disabled">'.$this->lang->escHTML('LANG_SETTING_INPUT_STYLE_1_TEXT').'</option>'."\n";
            $trustedInputStyleHTML .= '<option value="2" selected="selected">'.$this->lang->escHTML('LANG_SETTING_INPUT_STYLE_2_TEXT').'</option>'."\n";
        }
        $retSettings['trusted_input_style_html'] = $trustedInputStyleHTML;


        $shortDateFormatList = array(
            'Y-m-d' => 'YYYY-MM-DD. '.$this->lang->getText('LANG_TODAY_TEXT').' - '.date_i18n('Y-m-d'),
            'd/m/Y' => 'DD/MM/YYYY. '.$this->lang->getText('LANG_TODAY_TEXT').' - '.date_i18n('d/m/Y'),
            'm/d/Y' => 'MM/DD/YYYY. '.$this->lang->getText('LANG_TODAY_TEXT').' - '.date_i18n('m/d/Y'),
        );
        $selectShortDateFormat = "";
        foreach($shortDateFormatList as $key => $value)
        {
            if($key == $this->dbSets->get('conf_short_date_format'))
            {
                $selectShortDateFormat .= '<option value="'.esc_attr($key).'" selected="selected">'.esc_html($value).'</option>'."\n";
            } else
            {
                $selectShortDateFormat .= '<option value="'.esc_attr($key).'">'.esc_html($value).'</option>'."\n";
            }
        }
        $retSettings['select_short_date_format'] = $selectShortDateFormat;


        // TIME INTERVAL
        $minutesStack = array(15, 30, 60);
        $selectTimeInterval = "";
        foreach($minutesStack AS $minutes)
        {
            $inSeconds = $minutes*60;
            if(ConfigurationInterface::TIME_INTERVAL == $inSeconds)
            {
                $selectTimeInterval .= '<option value="'.esc_attr($inSeconds).'" selected="selected">'.esc_html($minutes).' '.$this->lang->escHTML('LANG_MINUTES_TEXT').'</option>';
            } else
            {
                $selectTimeInterval .= '<option value="'.esc_attr($inSeconds).'" disabled="disabled">'.esc_html($minutes).' '.$this->lang->escHTML('LANG_MINUTES_TEXT').'</option>';
            }
        }
        $retSettings['select_time_interval'] = $selectTimeInterval;


        // TIME CEILING
$timeCeilings = array(
    'BY_TIME_COUNT' => $this->lang->getText('LANG_BY_TIME_COUNT_TEXT'),
    'BY_NOON_COUNT' => $this->lang->getText('LANG_BY_NOON_COUNT_TEXT'),
    'BY_DATE_COUNT' => $this->lang->getText('LANG_BY_DATE_COUNT_TEXT'),
);

// Default selection
$defaultOption = 'BY_DATE_COUNT';
$selectTimeCeiling = '<option value="' . $defaultOption . '" selected="selected">' . esc_html($timeCeilings[$defaultOption]) . '</option>' . "\n";

foreach ($timeCeilings as $key => $value) {
    if ($key !== $defaultOption) {
        $selectTimeCeiling .= '<option value="' . esc_attr($key) . '">' . esc_html($value) . '</option>' . "\n";
    }
}

$retSettings['select_time_ceiling'] = $selectTimeCeiling;


        // WEEKEND
        $weekendList = array(
            'FRI' => $this->lang->getText('LANG_ONLY_ON_FRIDAY_TEXT'),
            'FRI_SAT' => $this->lang->getText('LANG_FRIDAY_AND_SATURDAY_TEXT'),
            'SAT_SUN' => $this->lang->getText('LANG_SATURDAY_AND_SUNDAY_TEXT'),
        );
        $selectWeekend = "";
        foreach($weekendList as $key => $value)
        {
            if($key == ConfigurationInterface::WEEKEND)
            {
                $selectWeekend .= '<option value="'.esc_attr($key).'" selected="selected">'.esc_html($value).'</option>'."\n";
            } else
            {
                $selectWeekend .= '<option value="'.esc_attr($key).'" disabled="disabled">'.esc_html($value).'</option>'."\n";
            }
        }
        $retSettings['select_weekend'] = $selectWeekend;

        $paymentCancelledPageArgs = array(
            'depth' => 1,
            'child_of' => 0,
            'selected' => $this->dbSets->get('conf_cancelled_payment_page_id'),
            'echo' => 0,
            'name' => 'conf_cancelled_payment_page_id',
            'id' => 'conf_cancelled_payment_page_id', // string
            'show_option_none' => $this->lang->getText('LANG_PAGE_SELECT_TEXT'), // string, no escaping needed
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'post_type' => $this->conf->getPostTypePrefix().'page',
        );
        $orderConfirmedPageArgs = array(
            'depth' => 1,
            'child_of' => 0,
            'selected' => $this->dbSets->get('conf_confirmation_page_id'),
            'echo' => 0,
            'name' => 'conf_confirmation_page_id',
            'id' => 'conf_confirmation_page_id', // string
            'show_option_none' => $this->lang->getText('LANG_PAGE_SELECT_TEXT'), // string, no escaping needed
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'post_type' => $this->conf->getPostTypePrefix().'page',
        );
        $termsAndConditionsPageArgs = array(
            'depth' => 1,
            'child_of' => 0,
            'selected' => $this->dbSets->get('conf_terms_and_conditions_page_id'),
            'echo' => 0,
            'name' => 'conf_terms_and_conditions_page_id',
            'id' => 'conf_terms_and_conditions_page_id', // string
            'show_option_none' => $this->lang->getText('LANG_PAGE_SELECT_TEXT'), // string
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'post_type' => $this->conf->getPostTypePrefix().'page',
        );
        $retSettings['trusted_payment_cancelled_page_select_html'] = wp_dropdown_pages($paymentCancelledPageArgs);
        $retSettings['trusted_order_confirmed_page_select_html'] = wp_dropdown_pages($orderConfirmedPageArgs);
        $retSettings['trusted_terms_and_conditions_page_select_html'] = wp_dropdown_pages($termsAndConditionsPageArgs);


        // NOON TIME
        $select_noon_time = '';
        for($hour = 10; $hour < 17; $hour++)
        {
            for($min = 0; $min < 60; $min = $min+30)
            {
                $currentHour = sprintf("%02d", $hour);
                $currentMin = sprintf("%02d", $min);

                $currentTime = $currentHour.':'.$currentMin.':00';

                $UTCUnixTime = strtotime(date("Y-m-d")." ".$currentTime);
                $currentTimeI18n = date_i18n(get_option('time_format'), $UTCUnixTime, true);

                if($currentTime == $this->dbSets->get('conf_noon_time'))
                {
                    $select_noon_time .= '<option value="'.esc_attr($currentTime).'" selected="selected">'.esc_html($currentTimeI18n).'</option>';
                } else
                {
                    $select_noon_time .= '<option value="'.esc_attr($currentTime).'">'.esc_html($currentTimeI18n).'</option>';
                }
            }
        }
        $retSettings['select_noon_time'] = $select_noon_time;


        if($this->dbSets->get('conf_classify_items') == 1)
        {
            $selectClassifyItemModels  = '<option value="0">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $selectClassifyItemModels .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        } else
        {
            $selectClassifyItemModels  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $selectClassifyItemModels .= '<option value="1">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        }
        $retSettings['select_classify_item_models'] = $selectClassifyItemModels;


        if($this->dbSets->get('conf_reveal_partner') == 1)
        {
            $selectRevealPartner  = '<option value="0">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $selectRevealPartner .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        } else
        {
            $selectRevealPartner  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_NO_TEXT').'</option>'."\n";
            $selectRevealPartner .= '<option value="1">'.$this->lang->escHTML('LANG_YES_TEXT').'</option>'."\n";
        }
        $retSettings['select_reveal_partner'] = $selectRevealPartner;


        if($this->dbSets->get('conf_load_datepicker_from_plugin') == 1)
        {
            $selectLoadDatepickerFromPlugin  = '<option value="0">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadDatepickerFromPlugin .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        } else
        {
            $selectLoadDatepickerFromPlugin  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadDatepickerFromPlugin .= '<option value="1">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        }
        $retSettings['select_load_datepicker_from_plugin'] = $selectLoadDatepickerFromPlugin;


        if($this->dbSets->get('conf_load_fancybox_from_plugin') == 1)
        {
            $selectLoadFancyBoxFromPlugin  = '<option value="0">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadFancyBoxFromPlugin .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        } else
        {
            $selectLoadFancyBoxFromPlugin  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadFancyBoxFromPlugin .= '<option value="1">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        }
        $retSettings['select_load_fancybox_from_plugin'] = $selectLoadFancyBoxFromPlugin;


        if($this->dbSets->get('conf_load_font_awesome_from_plugin') == 1)
        {
            $selectLoadFontAwesomeFromPlugin  = '<option value="0">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadFontAwesomeFromPlugin .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        } else
        {
            $selectLoadFontAwesomeFromPlugin  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadFontAwesomeFromPlugin .= '<option value="1">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        }
        $retSettings['select_load_font_awesome_from_plugin'] = $selectLoadFontAwesomeFromPlugin;


        if($this->dbSets->get('conf_load_slick_slider_from_plugin') == 1)
        {
            $selectLoadSlickSliderFromPlugin  = '<option value="0">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadSlickSliderFromPlugin .= '<option value="1" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        } else
        {
            $selectLoadSlickSliderFromPlugin  = '<option value="0" selected="selected">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_OTHER_PLACE_TEXT').'</option>'."\n";
            $selectLoadSlickSliderFromPlugin .= '<option value="1">'.$this->lang->escHTML('LANG_SETTING_LOAD_FROM_PLUGIN_TEXT').'</option>'."\n";
        }
        $retSettings['select_load_slick_slider_from_plugin'] = $selectLoadSlickSliderFromPlugin;


        return $retSettings;
    }

    /**
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_global_settings'])) { $this->processSave(); }
    }
}