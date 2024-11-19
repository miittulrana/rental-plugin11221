<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Location;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Distance\Distance;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditDistanceController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramDistanceId)
    {
        $objDistance = new Distance($this->conf, $this->lang, $this->dbSets->getAll(), $paramDistanceId);
        $objDistance->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objDistance->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objDistance->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objDistance->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'location-manager&tab=distances');
        exit;
    }

    private function processSave($paramDistanceId)
    {
        $objDistance = new Distance($this->conf, $this->lang, $this->dbSets->getAll(), $paramDistanceId);
        $objDistance->save($_POST);

        StaticSession::cacheHTML_Array('admin_debug_html', $objDistance->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objDistance->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objDistance->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'location-manager&tab=distances');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        if(isset($_GET['delete_distance'])) { $this->processDelete($_GET['delete_distance']); }
        if(isset($_POST['save_distance']) && isset($_POST['distance_id'])) { $this->processSave($_POST['distance_id']); }

        $paramDistanceId = isset($_GET['distance_id']) ? $_GET['distance_id'] : 0;
        $objDistance = new Distance($this->conf, $this->lang, $this->dbSets->getAll(), $paramDistanceId);
        $localDetails = $objDistance->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'location-manager&tab=distances');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-distance&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->distanceId = $localDetails['distance_id'];
            $this->view->showDistance = $localDetails['show_distance'] == 1 ? ' checked="checked"' : '';
            $this->view->distance = $localDetails['distance'];
            $this->view->additionalFee = $localDetails['additional_fee'];
            $this->view->trustedPickupLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, $localDetails['pickup_location_id'], 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
            $this->view->trustedReturnLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, $localDetails['return_location_id'], 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
        } else
        {
            $this->view->distanceId = 0;
            $this->view->showDistance = ' checked="checked"';
            $this->view->distance = '';
            $this->view->additionalFee = '';
            $this->view->trustedPickupLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, 0, 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
            $this->view->trustedReturnLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, 0, 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
        }

        // Print the template
        $templateRelPathAndFileName = 'Location'.DIRECTORY_SEPARATOR.'AddEditDistanceForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
