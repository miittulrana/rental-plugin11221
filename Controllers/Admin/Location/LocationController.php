<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Location;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Closing\ClosingsObserver;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Distance\DistancesObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class LocationController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objDistancesObserver = new DistancesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objClosingsObserver = new ClosingsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        $closingsForGlobalLocations = array();
        // First - add closed dates for all locations
        $closingsForGlobalLocations[] = array(
            'location_id' => '0',
            'closed_dates' => $objClosingsObserver->getAll("", false),
        );
        foreach($objLocationsObserver->getAllIds("ANY", -1) AS $locationId)
        {
            $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $locationId);
            $closingsForGlobalLocations[] = array(
                'location_id' => $locationId,
                'closed_dates' => $objClosingsObserver->getAll($objLocation->getUniqueIdentifier(), false),
            );
        }

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'locations', 'distances', 'closings',
        ), 'locations', isset($_GET['tab']) ? $_GET['tab'] : '');

        // 2. Set the view variables - locations
        $this->view->addNewLocationURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-location&location_id=0');
        $this->view->trustedAdminLocationsListHTML = $objLocationsObserver->getTrustedAdminListHTML();

        // 3. Set the view variables - distances
        $this->view->addNewDistanceURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-distance&distance_id=0');
        $this->view->trustedAdminDistancesListHTML = $objDistancesObserver->getTrustedAdminListHTML();

        // 5. Set the view variables - closed dates
        $this->view->trustedLocationDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
            "BOTH", 0, -1, -1, $this->lang->getText('LANG_LOCATIONS_ALL_TEXT')
        );
        $this->view->closingsForGlobalLocations = $closingsForGlobalLocations;

        // Print the template
        $templateRelPathAndFileName = 'Location'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
