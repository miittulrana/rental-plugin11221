<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Shortcodes;
use FleetManagement\Controllers\Front\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Location\LocationFeeManager;
use FleetManagement\Models\Tax\TaxManager;

final class LocationsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "List", $paramStyle = "")
    {
        // Create mandatory instances
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        $gotResults = false;
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $locationIds = $objLocationsObserver->getItemPickupIds($this->itemModelId);
        $locations = array();
        foreach($locationIds AS $locationId)
        {
            $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $locationId);
            $taxPercentage = $objTaxManager->getTaxPercentage($locationId, $locationId);
            $objFeeManager = new LocationFeeManager(
                $this->conf, $this->lang, $this->dbSets->getAll(), $locationId, $taxPercentage
            );
            $locationDetails = $objLocation->getDetails();
            // Extend location details
            $locationDetails['business_hours'] = $objLocation->getBusinessHours();

            // Expand the $location array with new values
            $locations[] = array_merge($locationDetails, $objFeeManager->getUnitDetails());

            $gotResults = true;
        }

        // Get the template
        $this->view->locations = $locations;
        $this->view->gotResults = $gotResults;
        $retContent = $this->getTemplate('', 'Locations', $paramLayout, $paramStyle);

        return $retContent;
    }
}