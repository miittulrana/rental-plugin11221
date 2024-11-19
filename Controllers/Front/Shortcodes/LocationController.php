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
use FleetManagement\Models\Location\LocationFeeManager;
use FleetManagement\Models\Tax\TaxManager;

final class LocationController extends AbstractController
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
    public function getContent($paramLayout = "Details", $paramStyle = "")
    {
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $taxPercentage = $objTaxManager->getTaxPercentage($this->locationId, $this->locationId);
        $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $this->locationId);
        $locationDetails = $objLocation->getDetails();
        if(!is_null($locationDetails))
        {
            $objFeeManager = new LocationFeeManager(
                $this->conf, $this->lang, $this->dbSets->getAll(), $this->locationId, $taxPercentage
            );
            $locationDetails = $objLocation->getDetails();
            // Extend location details
            $locationDetails['business_hours'] = $objLocation->getBusinessHours();

            // Expand the $location array with new values
            $location = array_merge($locationDetails, $objFeeManager->getUnitDetails());

            // Get the template
            $this->view->location = $location;

            $retContent = $this->getTemplate('', 'Location', $paramLayout, $paramStyle);
        } else
        {
            $retContent = '';
        }
        return $retContent;
    }
}