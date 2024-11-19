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
use FleetManagement\Models\Manufacturer\Manufacturer;
use FleetManagement\Models\Manufacturer\ManufacturersObserver;
use FleetManagement\Models\Language\LanguageInterface;

final class ManufacturersController extends AbstractController
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
    public function getContent($paramLayout = "Slider", $paramStyle = "")
    {
        // Create mandatory instances
        $objManufacturersObserver = new ManufacturersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        
        $gotResults = false;
        // Get all manufacturers from body type 0 and other body types (last 'false' means, that we skip body type check)
        $manufacturerIds = $objManufacturersObserver->getAllIds();
        $manufacturers = array();
        foreach($manufacturerIds AS $manufacturerId)
        {
            $objManufacturer = new Manufacturer($this->conf, $this->lang, $this->dbSets->getAll(), $manufacturerId);
            $manufacturers[] = $objManufacturer->getDetails();
            $gotResults = true;
        }

        // Get the template
        $this->view->manufacturers = $manufacturers;
        $this->view->gotResults = $gotResults;
        $retContent = $this->getTemplate('', 'Manufacturers', $paramLayout, $paramStyle);

        return $retContent;
    }
}