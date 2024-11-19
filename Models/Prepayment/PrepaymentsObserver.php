<?php
/**
 * Prepayments Observer (no setup for single prepayment)
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Prepayment;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class PrepaymentsObserver implements ObserverInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings		        = array();
    private $debugMode 	            = 0;
	// Price calculation type: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
    private $priceCalculationType 	= 1;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        $this->priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1, array(1, 2, 3));
	}

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        $searchSQL = "
            SELECT prepayment_id
            FROM {$this->conf->getPrefix()}prepayments
            WHERE blog_id='{$this->conf->getBlogId()}'
            ORDER BY period_from ASC, period_till ASC
        ";

        //DEBUG
        //echo nl2br($searchSQL)."<br /><br />";

        $searchResults = $this->conf->getInternalWPDB()->get_col($searchSQL);

        return $searchResults;
    }


    /* --------------------------------------------------------------------------- */
    /* ----------------------- METHODS FOR ADMIN ACCESS ONLY --------------------- */
    /* --------------------------------------------------------------------------- */

    public function getTrustedAdminListHTML()
    {
        $retHTML = '';
        $prepaymentIds = $this->getAllIds();
        foreach($prepaymentIds AS $prepaymentId)
        {
            $objPrepayment = new Prepayment($this->conf, $this->lang, $this->settings, $prepaymentId);
            $prepaymentDetails = $objPrepayment->getDetails();

            // HTML OUTPUT
            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$prepaymentDetails['trusted_includes_html'].'</td>';
            $retHTML .= '<td>'.$prepaymentDetails['trusted_not_includes_html'].'</td>';
            $retHTML .= '<td><strong>'.esc_html($prepaymentDetails['dynamic_duration_from_text']).'</strong></td>';
            $retHTML .= '<td><strong>'.esc_html($prepaymentDetails['dynamic_duration_till_text']).'</strong></td>';
            $retHTML .= '<td>'.esc_html($prepaymentDetails['prepayment_percentage']).' %</td>';
            $retHTML .= '<td align="right"><a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-prepayment&amp;prepayment_id='.$prepaymentId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
            $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-prepayment&amp;noheader=true&amp;delete_prepayment='.$prepaymentId)).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a></td>';

            $retHTML .= '</tr>';
        }

        return  $retHTML;
    }
}