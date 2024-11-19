<?php
/**
 * Tax Manager
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Tax;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class TaxManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings 	            = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getTaxPercentage($paramPickupLocationId = 0, $paramReturnLocationId = 0)
    {
        $validPickupLocationId = StaticValidator::getValidPositiveInteger($paramPickupLocationId, 0);
        $validReturnLocationId = StaticValidator::getValidPositiveInteger($paramReturnLocationId, 0);

        $sqlTaxes = "
            SELECT tax_percentage
            FROM {$this->conf->getPrefix()}taxes
            WHERE 
            (
                ((location_id='0' OR location_id='{$validPickupLocationId}') AND location_type='1') OR
                ((location_id='0' OR location_id='{$validReturnLocationId}') AND location_type='2') 
            ) AND blog_id='{$this->conf->getBlogId()}'
        ";
        $arrTaxes = $this->conf->getInternalWPDB()->get_results($sqlTaxes, ARRAY_A);

        $totalTaxPercentage = 0;
        foreach($arrTaxes AS $tax)
        {
            $totalTaxPercentage += $tax['tax_percentage'];
        }

        return $totalTaxPercentage;
    }
}