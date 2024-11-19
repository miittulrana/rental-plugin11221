<?php
/**
 * Prepayments Observer (no setup for single item)
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Tax;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Validation\StaticValidator;

final class TaxesObserver implements ObserverInterface
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

    public function getAllIds($paramPickupLocationId = -1, $paramReturnLocationId = -1)
    {
        $validPickupLocationId = StaticValidator::getValidInteger($paramPickupLocationId, -1); // -1 (all) is supported here
        $validReturnLocationId = StaticValidator::getValidInteger($paramReturnLocationId, -1); // -1 (all) is supported here
        $sqlAdd = '';
        if($validPickupLocationId >= 0 || $paramReturnLocationId >= 0)
        {
            $sqlAdd .= " AND (
                ((location_id='0' OR location_id='{$validPickupLocationId}') AND location_type='1') OR
                ((location_id='0' OR location_id='{$validReturnLocationId}') AND location_type='2') 
            )";
        }

        $locationIds = $this->conf->getInternalWPDB()->get_col("
            SELECT tax_id
            FROM {$this->conf->getPrefix()}taxes
            WHERE blog_id='{$this->conf->getBlogId()}'{$sqlAdd}
            ORDER BY tax_name ASC
        ");

        return $locationIds;
    }

    /**
     * @param int $paramPickupLocationId
     * @param int $paramReturnLocationId
     * @param int $paramPrice - used to pre-calculate the taxes from gross amount
     * @return mixed
     */
    public function getTaxesForPrice($paramPickupLocationId = 0, $paramReturnLocationId = 0, $paramPrice = 0)
    {
        $taxIds = $this->getAllIds($paramPickupLocationId, $paramReturnLocationId);

        if($this->debugMode)
        {
            echo "<br />Getting all taxes for price: ".floatval($paramPrice);
        }

        $arrTaxes = array();
        foreach($taxIds AS $taxId)
        {
            $objTax = new Tax($this->conf, $this->lang, $this->settings, $taxId);
            $arrTaxes[] = $objTax->getDetailsWithAmountForPrice($paramPrice);
        }

        return $arrTaxes;
    }

    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    public function getTrustedAdminListHTML()
    {
        $taxList = '';

        $taxIds = $this->getAllIds(-1, -1);

        foreach ($taxIds AS $taxId)
        {
            $objTax = new Tax($this->conf, $this->lang, $this->settings, $taxId);
            $taxDetails = $objTax->getDetails();

            if($taxDetails['location_id'] > 0)
            {
                $objLocation = new Location($this->conf, $this->lang, $this->settings, $taxDetails['location_id']);
                $printTranslatedLocationName = $objLocation->getPrintTranslatedLocationName();
            } else
            {
                $printTranslatedLocationName = $this->lang->getText('LANG_LOCATIONS_ALL_TEXT');
            }
            $printLocationType = $this->lang->getText($taxDetails['location_type'] == 1 ? 'LANG_PICKUP_TEXT' : 'LANG_RETURN_TEXT');

            $printTranslatedTaxName = $taxDetails['print_translated_tax_name'];
            if($this->lang->canTranslateSQL())
            {
                $printTranslatedTaxName .= '<br /><span class="not-translated" title="'.$this->lang->getText('LANG_WITHOUT_TRANSLATION_TEXT').'">('.$taxDetails['print_tax_name'].')</span>';
            }

            // HTML OUTPUT
            $taxList .= '<tr>';
            $taxList .= '<td>'.$printTranslatedTaxName.'</td>';
            $taxList .= '<td>'.$printTranslatedLocationName.'</td>';
            $taxList .= '<td>'.$printLocationType.'</td>';
            $taxList .= '<td>'.esc_html($taxDetails['formatted_tax_percentage']).'</td>';
            $taxList .= '<td align="right"><a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-tax&amp;tax_id='.$taxDetails['tax_id'])).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
            $taxList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-tax&amp;noheader=true&amp;delete_tax='.$taxDetails['tax_id'])).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a></td>';
            $taxList .= '</tr>';
        }

        return $taxList;
    }
}