<?php
/**
 * Prepayment processor. Used in administration side only

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Tax;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Tax extends AbstractStack implements ElementInterface
{
    private $conf 	        = null;
    private $lang 		    = null;
    private $debugMode 	    = 0;
    private $taxId          = 0;

    private $currencySymbol		    = '$';
    private $currencyCode			= 'USD';
    private $currencySymbolLocation	= 0;

    /**
     * Tax constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings - not used
     * @param int $paramTaxId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramTaxId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set tax id
        $this->taxId = StaticValidator::getValidPositiveInteger($paramTaxId, 0);

        $this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->taxId;
    }

    /**
     * For internal class use only
     * @param $paramTaxId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramTaxId)
    {
        $validTaxId = StaticValidator::getValidPositiveInteger($paramTaxId, 0);
        $sqlData = "
            SELECT *
            FROM {$this->conf->getPrefix()}taxes
            WHERE tax_id='{$validTaxId}'
        ";

        $taxData = $this->conf->getInternalWPDB()->get_row($sqlData, ARRAY_A);

        return $taxData;
    }

    /**
     * @param bool $paramPrefillWhenNull - not used
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        return $this->getAllDetails(false);
    }

    public function getDetailsWithAmountForPrice($paramPrice)
    {
        return $this->getAllDetails(true, $paramPrice);
    }

    private function getAllDetails($paramWithAmount = false, $paramPrice = 0.00)
    {
        $ret = $this->getDataFromDatabaseById($this->taxId);

        if(!is_null($ret))
        {
            // Make raw
            $ret['tax_name'] = stripslashes($ret['tax_name']);

            // Process new fields
            $ret['translated_tax_name'] = $this->lang->getTranslated("ta{$ret['tax_id']}_tax_name", $ret['tax_name']);
            $ret['formatted_tax_percentage'] =  StaticFormatter::getFormattedPercentage($ret['tax_percentage'], "regular");

            // Prepare output for print
            $ret['print_tax_name'] = esc_html($ret['tax_name']);
            $ret['print_translated_tax_name'] = esc_html($ret['translated_tax_name']);

            if($paramWithAmount === true)
            {
                $ret['tax_amount'] = floatval($paramPrice) * ($ret['tax_percentage'] / 100);
                $ret['print_tax_amount'] = StaticFormatter::getFormattedPrice(
                    $ret['tax_amount'], "regular", $this->currencySymbol, $this->currencyCode, $this->currencySymbolLocation
                );
            }

            // Prepare output for edit
            $ret['edit_tax_name'] = esc_attr($ret['tax_name']); // for input field
        }

        return $ret;
    }

    /**
     * @note - Always use blog_id for save (insert / update) and delete, to avoid access rights violation
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $ok = true;
        $saved = false;
        $validTaxId = StaticValidator::getValidPositiveInteger($this->taxId, 0);
        $sanitizedTaxName = isset($params['tax_name']) ? sanitize_text_field($params['tax_name']) : '';
        $validTaxName = esc_sql($sanitizedTaxName); // for sql query only
        $validLocationId = isset($params['location_id']) ? StaticValidator::getValidPositiveInteger($params['location_id'], 0) : 0;
        $validLocationType = isset($params['location_type']) && $params['location_type'] == 1 ? 1 : 2;
        $validTaxPercentage = isset($params['tax_percentage']) && $params['tax_percentage'] >= 0.00 ? floatval($params['tax_percentage']) : 0.00;

        // Do not allow to have prepayments more than 100%
        if($validTaxPercentage > 100)
        {
            $validTaxPercentage = 100;
        }

        if($validTaxId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}taxes SET
                tax_name='{$validTaxName}', location_id='{$validLocationId}', location_type='{$validLocationType}',
                tax_percentage='{$validTaxPercentage}'
                WHERE tax_id='{$validTaxId}' AND blog_id='{$this->conf->getBlogId()}'
            ");
            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_TAX_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_TAX_UPDATED_TEXT');
            }
        } else if($ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
                INSERT INTO {$this->conf->getPrefix()}taxes
                (
                    tax_name, location_id, location_type, tax_percentage, blog_id
                ) VALUES
                (
                    '{$validTaxName}', '{$validLocationId}', '{$validLocationType}', '{$validTaxPercentage}', '{$this->conf->getBlogId()}'
                )
            ");

            if($saved)
            {
                // Update object id with newly inserted tax id for future work
                $this->taxId = $this->conf->getInternalWPDB()->insert_id;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_TAX_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_TAX_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $taxDetails = $this->getDetails();
        if(!is_null($taxDetails))
        {
            $this->lang->register("ta{$this->taxId}_tax_name", $taxDetails['tax_name']);
            $this->okayMessages[] = $this->lang->getText('LANG_TAX_REGISTERED_TEXT');
        }
    }

    public function delete()
    {
        $validTaxId = StaticValidator::getValidPositiveInteger($this->taxId, 0);
        $deleted = $this->conf->getInternalWPDB()->query("
          DELETE FROM {$this->conf->getPrefix()}taxes
          WHERE tax_id='{$validTaxId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_TAX_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_TAX_DELETED_TEXT');
        }

        return $deleted;
    }
}