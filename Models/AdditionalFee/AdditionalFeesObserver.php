<?php
/**
 * Additional Fees Observer (no setup for single distance)
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\AdditionalFee;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Distance\Distance;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Validation\StaticValidator;

final class AdditionalFeesObserver implements ObserverInterface
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
        // Set saved settings
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @param array $params - an empty array is supported
     * @return array
     */
    public function getAllIdsByAnd(array $params)
    {
        return $this->getAllIds("AND", $params);
    }

    /**
     * @param array $params - an empty array is supported
     * @return array
     */
    public function getAllIdsByOr(array $params)
    {
        return $this->getAllIds("OR", $params);
    }

    /**
     * @param string $paramSearchRule - "AND" or "OR"
     * @param array $params - an empty array is supported
     * @return array
     */
    private function getAllIds($paramSearchRule, array $params)
    {
        // Set defaults
        $paramPickupLocationIds = isset($params['pickup_location_ids']) && is_array($params['pickup_location_ids']) ? $params['pickup_location_ids'] : array();
        $paramReturnLocationIds = isset($params['return_location_ids']) && is_array($params['return_location_ids']) ? $params['return_location_ids'] : array();

        $validPickupLocationIds = StaticValidator::getValidArray($paramPickupLocationIds, 'intval', 0); // -1 (All) is supported here
        $validReturnLocationIds = StaticValidator::getValidArray($paramReturnLocationIds, 'intval', 0); // -1 (All) is supported here

        // NOTE: If that additional fee is applied to all, i.e. age groups
        $arrSQLAdd = array();


        // Location
        if(sizeof($validPickupLocationIds) > 0)
        {
            $arrSQLAdd[] = "d.pickup_location_id IN (".implode(", ", $validPickupLocationIds).")";
        }
        if(sizeof($validReturnLocationIds) > 0)
        {
            $arrSQLAdd[] = "d.return_location_id IN (".implode(", ", $validReturnLocationIds).")";
        }

        $sqlAdd = "";
        if($paramSearchRule == "OR")
        {
            $sqlAdd = sizeof($arrSQLAdd) > 0 ? " AND (".implode(" OR ", $arrSQLAdd).")" : "";
        } else if($paramSearchRule == "AND")
        {
            $sqlAdd = sizeof($arrSQLAdd) > 0 ? " AND ".implode(" AND ", $arrSQLAdd) : "";
        }

        $sqlQuery = "
            SELECT d.distance_id AS additional_fee_id
            FROM {$this->conf->getPrefix()}distances d
            JOIN {$this->conf->getPrefix()}locations ploc ON ploc.location_id=d.pickup_location_id
            JOIN {$this->conf->getPrefix()}locations rloc ON rloc.location_id=d.return_location_id
            WHERE d.blog_id='{$this->conf->getBlogId()}'{$sqlAdd}
            ORDER BY ploc.location_order ASC, rloc.location_order ASC
        ";
        $additionalFeeIds = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        // DEBUG
        //echo nl2br($sqlQuery);

        return $additionalFeeIds;
    }

    public function getIdByTwoLocations($paramPickupLocationId, $paramReturnLocationId)
    {
        $retDistanceId = 0;

        $validPickupLocationId = StaticValidator::getValidPositiveInteger($paramPickupLocationId, 0);
        $validReturnLocationId = StaticValidator::getValidPositiveInteger($paramReturnLocationId, 0);

        $sql = "
            SELECT distance_id AS additional_fee_id
            FROM {$this->conf->getPrefix()}distances
            WHERE pickup_location_id='{$validPickupLocationId}' AND return_location_id='{$validReturnLocationId}'
        ";

        $additionalFeeId = $this->conf->getInternalWPDB()->get_var($sql);

        if(!is_null($additionalFeeId))
        {
            $retDistanceId = StaticValidator::getValidPositiveInteger($additionalFeeId, 0);
        }

        return $retDistanceId;
    }

    /* --------------------------------------------------------------------------- */
    /* ----------------------- METHODS FOR ADMIN ACCESS ONLY --------------------- */
    /* --------------------------------------------------------------------------- */

    public function getTrustedAdminListHTML()
    {
        $retHTML = '';

        // Create mandatory instances
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->settings);

        $additionalFeeIds = $this->getAllIds("OR", array());
        $i = 0;
        foreach ($additionalFeeIds AS $additionalFeeId)
        {
            $i++;
            $objAdditionalFee = new AdditionalFee($this->conf, $this->lang, $this->settings, $additionalFeeId);
            $additionalFeeDetails = $objAdditionalFee->getDetails();

            // Get tax percentage
            $pickupLocationId = $objAdditionalFee->getPickupLocationId();
            $returnLocationId = $objAdditionalFee->getReturnLocationId();
            $taxPercentage = $objTaxManager->getTaxPercentage($pickupLocationId, $returnLocationId);
            $objPickupLocation = new Location($this->conf, $this->lang, $this->settings, $pickupLocationId);
            $objReturnLocation = new Location($this->conf, $this->lang, $this->settings, $returnLocationId);
            $additionalFeeId = $this->getIdByTwoLocations($pickupLocationId, $returnLocationId);

            $pickupDetails = $objPickupLocation->getDetails();
            $returnDetails = $objReturnLocation->getDetails();

            $objAdditionalFeeManager = new AdditionalFeeManager($this->conf, $this->lang, $this->settings, $additionalFeeId, $taxPercentage);
            $additionalFeeCalculations = $objAdditionalFeeManager->getSingleFeeDetails(-1, -1);
            $additionalFee = array_merge($additionalFeeDetails, $additionalFeeCalculations);

            $trustedAdditionalFeeNameHTML = esc_html($additionalFee['translated_additional_fee_name']);
            if($this->lang->canTranslateSQL())
            {
                $trustedAdditionalFeeNameHTML .= '<br /><span class="not-translated" title="'.$this->lang->escAttr('LANG_WITHOUT_TRANSLATION_TEXT').'">('.esc_html($additionalFee['additional_fee_name']).')</span>';
            }

            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$i.'</td>';
            $retHTML .= '<td>'.$trustedAdditionalFeeNameHTML.'</td>';
            $retHTML .= '<td>'.$pickupDetails['print_translated_location_name'].'</td>';
            $retHTML .= '<td>'.$returnDetails['print_translated_location_name'].'</td>';
            $retHTML .= '<td>';
            $retHTML .= $additionalFeeCalculations['single_print']['total'].'<br />';
            $retHTML .= esc_html($additionalFee['fee_application_text']).', '.esc_html($additionalFee['timeframe_text']);
            $retHTML .= '</td>';
            $retHTML .= '<td>';
            $retHTML .= $additionalFeeCalculations['single_print']['total_with_tax'].'<br />';
            $retHTML .= esc_html($additionalFee['fee_application_text']).', '.esc_html($additionalFee['timeframe_text']);
            $retHTML .= '</td>';
            $retHTML .= '<td>'.esc_html($additionalFee['beneficial_entity_text']).'</td>';
            $retHTML .= '<td align="right" style="white-space: nowrap">';
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_locations'))
            {
                $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-additional-fee&amp;additional_fee_id='.$additionalFeeId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a>';
            } else
            {
                $retHTML .= '--';
            }
            $retHTML .= '</td>';
            $retHTML .= '</tr>';
        }

        return  $retHTML;
    }
}