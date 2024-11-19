<?php
/**
 * Item Model Deposit Manager

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\ItemModel;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class ItemModelDepositManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings                 = array();
    protected $depositEnabled 		    = 1;
    protected $currencySymbol		    = '$';
    protected $currencyCode			    = 'USD';
    protected $currencySymbolLocation	= 0;
    protected $itemModelId			        = 0;
    
	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramSettings, $paramItemModelId)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        $this->itemModelId = StaticValidator::getValidValue($paramItemModelId, 'positive_integer', 0);

        $this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));

        if(isset($paramSettings['conf_deposit_enabled']))
        {
            // Set deposit status
            $this->depositEnabled = StaticValidator::getValidPositiveInteger($paramSettings['conf_deposit_enabled'], 1) == 1 ? true : false;
        }
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

	/**
	 * @return int
	 */
	public function getAmount()
	{
		$fixedRentalDeposit = 0;

		if($this->depositEnabled)
		{
			$validItemModelId = StaticValidator::getValidPositiveInteger($this->itemModelId, 0);

            $query = "
			  	SELECT fixed_rental_deposit AS fixed_deposit
			  	FROM {$this->conf->getPrefix()}items
			  	WHERE item_id='{$validItemModelId}'
			";

            $row = $this->conf->getInternalWPDB()->get_row($query, ARRAY_A);
			if(!is_null($row))
			{
				// Use different deposits for each item model (unless 0 was passed, than it will return default here)
				$fixedRentalDeposit = $row['fixed_deposit'];
			}
		}

		return $fixedRentalDeposit;
	}

	public function getDetails()
    {
        $retDeposit = array();
        $unitFixedDeposit = $this->getAmount();
        // We need the line bellow, to not have printed out 1.019 as 1.01. We always want to print 1.02 instead.
        $roundedDeposit = round($unitFixedDeposit, 2);

        $retDeposit['unit']['fixed_deposit'] = $unitFixedDeposit;
        // Unit prints
        if($roundedDeposit == 0.00)
        {
            $retDeposit['unit_tiny_print']['fixed_deposit'] = $this->lang->getText('LANG_NOT_REQ_TEXT');
            $retDeposit['unit_tiny_without_fraction_print']['fixed_deposit'] = $this->lang->getText('LANG_NOT_REQ_TEXT');
            $retDeposit['unit_print']['fixed_deposit'] = $this->lang->getText('LANG_NOT_REQ_TEXT');
            $retDeposit['unit_without_fraction_print']['fixed_deposit'] = $this->lang->getText('LANG_NOT_REQ_TEXT');
            $retDeposit['unit_long_print']['fixed_deposit'] = $this->lang->getText('LANG_NOT_REQUIRED_TEXT');
            $retDeposit['unit_long_without_fraction_print']['fixed_deposit'] = $this->lang->getText('LANG_NOT_REQUIRED_TEXT');
        } else
        {
            $retDeposit['unit_tiny_print']['fixed_deposit'] = StaticFormatter::getFormattedPrice($roundedDeposit, "tiny", $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
            $retDeposit['unit_tiny_without_fraction_print']['fixed_deposit'] = StaticFormatter::getFormattedPrice($roundedDeposit, "tiny_without_fraction", $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
            $retDeposit['unit_print']['fixed_deposit'] = StaticFormatter::getFormattedPrice($roundedDeposit, "regular", $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
            $retDeposit['unit_without_fraction_print']['fixed_deposit'] = StaticFormatter::getFormattedPrice($roundedDeposit, "regular_without_fraction", $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
            $retDeposit['unit_long_print']['fixed_deposit'] = StaticFormatter::getFormattedPrice($roundedDeposit, "long", $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
            $retDeposit['unit_long_without_fraction_print']['fixed_deposit'] = StaticFormatter::getFormattedPrice($roundedDeposit, "long_without_fraction", $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation);
        }

        return $retDeposit;
    }
}
