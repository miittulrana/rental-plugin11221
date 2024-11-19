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
use FleetManagement\Models\Settings\Setting;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class ChangePriceSettingsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $objSetting = new Setting($this->conf, $this->lang, 'conf_price_calculation_type');
        $objSetting->saveNumber(1, array(1, 2, 3));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_currency_symbol');
        $objSetting->saveText(true);

        $objSetting = new Setting($this->conf, $this->lang, 'conf_currency_symbol_location');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_currency_code');
        $objSetting->saveText();

        $objSetting = new Setting($this->conf, $this->lang, 'conf_show_price_with_taxes');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_deposit_enabled');
        $objSetting->saveNumber(0, array(0, 1));

        $objSetting = new Setting($this->conf, $this->lang, 'conf_prepayment_enabled');
        $objSetting->saveNumber(0, array(0, 1));

        StaticSession::cacheValueArray('admin_okay_message', array($this->lang->getText('LANG_SETTINGS_PRICE_SETTINGS_UPDATED_TEXT')));

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'settings&tab=price-settings');
        exit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $retSettings = array();

        // Price calculation type
        $price_array = array(
            1 => $this->lang->getText('LANG_PRICING_DAILY_TEXT'),
            2 => $this->lang->getText('LANG_PRICING_HOURLY_TEXT'),
            3 => $this->lang->getText('LANG_PRICING_COMBINED_DAILY_AND_HOURLY_TEXT'),
        );
        $select_price_calculation_type = "";
        foreach($price_array as $key => $value)
        {
            if($key == $this->dbSets->get('conf_price_calculation_type'))
            {
                $select_price_calculation_type .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
            } else
            {
                $select_price_calculation_type .= '<option value="'.$key.'" >'.$value.'</option>';
            }
        }
        $retSettings['select_price_calculation_type'] = $select_price_calculation_type;



        // Currency symbol is on the left or on the right side to the price
        if($this->dbSets->get('conf_currency_symbol_location') == 0)
        {
            $select_currency_symbol_location  = '<option value="0" selected="selected">'.$this->lang->getText('LANG_SETTING_ON_THE_LEFT_TEXT').'</option>' . "\n";
            $select_currency_symbol_location .= '<option value="1">'.$this->lang->getText('LANG_SETTING_ON_THE_RIGHT_TEXT').'</option>' . "\n";
        } else
        {
            $select_currency_symbol_location  = '<option value="0">'.$this->lang->getText('LANG_SETTING_ON_THE_LEFT_TEXT').'</option>' . "\n";
            $select_currency_symbol_location .= '<option value="1" selected="selected">'.$this->lang->getText('LANG_SETTING_ON_THE_RIGHT_TEXT').'</option>' . "\n";
        }
        $retSettings['select_currency_symbol_location'] = $select_currency_symbol_location;


        // Show prices with VAT
        if($this->dbSets->get('conf_show_price_with_taxes') == 1)
        {
            $select_show_price_with_taxes  = '<option value="1" selected="selected">'.$this->lang->getText('LANG_YES_TEXT').'</option>' . "\n";
            $select_show_price_with_taxes .= '<option value="0">'.$this->lang->getText('LANG_NO_TEXT').'</option>' . "\n";
        } else
        {
            $select_show_price_with_taxes  = '<option value="1">'.$this->lang->getText('LANG_YES_TEXT').'</option>' . "\n";
            $select_show_price_with_taxes .= '<option value="0" selected="selected">'.$this->lang->getText('LANG_NO_TEXT').'</option>' . "\n";
        }
        $retSettings['select_show_price_with_taxes'] = $select_show_price_with_taxes;


        if($this->dbSets->get('conf_deposit_enabled') == 0)
        {
            $selectDepositEnabled  = '<option value="0" selected="selected">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectDepositEnabled .= '<option value="1">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>'."\n";
        } else
        {
            $selectDepositEnabled  = '<option value="0">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectDepositEnabled .= '<option value="1" selected="selected">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>'."\n";
        }
        $retSettings['select_deposit_enabled'] = $selectDepositEnabled;


        if($this->dbSets->get('conf_prepayment_enabled') == 0)
        {
            $selectPrepaymentEnabled  = '<option value="0" selected="selected">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectPrepaymentEnabled .= '<option value="1">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>'."\n";
        } else
        {
            $selectPrepaymentEnabled  = '<option value="0">'.$this->lang->getText('LANG_DISABLED_TEXT').'</option>'."\n";
            $selectPrepaymentEnabled .= '<option value="1" selected="selected">'.$this->lang->getText('LANG_ENABLED_TEXT').'</option>'."\n";
        }
        $retSettings['select_prepayment_enabled'] = $selectPrepaymentEnabled;


        return $retSettings;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_price_settings'])){ $this->processSave();}
    }
}
