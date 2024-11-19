<?php
/**
 * Distances Observer (no setup for single distance)
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Distance;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class DistancesObserver implements ObserverInterface
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

    public function getAllIds()
    {
        $distanceIds = $this->conf->getInternalWPDB()->get_col("
            SELECT distance_id
            FROM {$this->conf->getPrefix()}distances d
            JOIN {$this->conf->getPrefix()}locations ploc ON ploc.location_id=d.pickup_location_id
            JOIN {$this->conf->getPrefix()}locations rloc ON rloc.location_id=d.return_location_id
            WHERE d.blog_id='{$this->conf->getBlogId()}'
            ORDER BY ploc.location_order ASC, rloc.location_order ASC
        ");

        return $distanceIds;
    }

    public function getIdByTwoLocations($paramPickupLocationId, $paramReturnLocationId)
    {
        $retDistanceId = 0;

        $validPickupLocationId = StaticValidator::getValidPositiveInteger($paramPickupLocationId, 0);
        $validReturnLocationId = StaticValidator::getValidPositiveInteger($paramReturnLocationId, 0);

        $sql = "
            SELECT distance_id
            FROM {$this->conf->getPrefix()}distances
            WHERE pickup_location_id='{$validPickupLocationId}' AND return_location_id='{$validReturnLocationId}'
        ";

        $distanceId = $this->conf->getInternalWPDB()->get_var($sql);

        if(!is_null($distanceId))
        {
            $retDistanceId = StaticValidator::getValidPositiveInteger($distanceId, 0);
        }

        return $retDistanceId;
    }

    /**
     * @return string
     */
    public function getTrustedAdminListHTML()
    {
        $retHTML = '';
        $distanceIds = $this->getAllIds();

        $i = 0;
        foreach ($distanceIds AS $distanceId)
        {
            $i++;
            $objDistance = new Distance($this->conf, $this->lang, $this->settings, $distanceId);
            $pickupLocationId = $objDistance->getPickupLocationId();
            $returnLocationId = $objDistance->getReturnLocationId();
            $objPickupLocation = new Location($this->conf, $this->lang, $this->settings, $pickupLocationId);
            $objReturnLocation = new Location($this->conf, $this->lang, $this->settings, $returnLocationId);

            $pickupDetails = $objPickupLocation->getDetails();
            $returnDetails = $objReturnLocation->getDetails();
            $distanceDetails = $objDistance->getDetails(true);

            $retHTML .= '<tr>';
            $retHTML .= '<td>'.$i.'</td>';
            $retHTML .= '<td>'.$pickupDetails['print_translated_location_name'].'</td>';
            $retHTML .= '<td>'.$returnDetails['print_translated_location_name'].'</td>';
            $retHTML .= '<td style="white-space: nowrap">'.$distanceDetails['print_distance'].'</td>';
            $retHTML .= '<td align="right" style="white-space: nowrap">';
            if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_locations'))
            {
                $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-distance&amp;distance_id='.$distanceId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $retHTML .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-distance&amp;noheader=true&amp;delete_distance='.$distanceId)).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';

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