<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\AdditionalFee;
use FleetManagement\Models\AdditionalFee\AdditionalFee;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditAdditionalFeeController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramAdditionalFeeId)
    {
        $objAdditionalFee = new AdditionalFee($this->conf, $this->lang, $this->dbSets->getAll(), $paramAdditionalFeeId);
        $objAdditionalFee->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objAdditionalFee->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objAdditionalFee->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objAdditionalFee->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'additional-fee-manager&tab=additional-fees');
        exit;
    }

    private function processSave($paramAdditionalFeeId)
    {
        $objAdditionalFee = new AdditionalFee($this->conf, $this->lang, $this->dbSets->getAll(), $paramAdditionalFeeId);
        $objAdditionalFee->save($_POST);

        StaticSession::cacheHTML_Array('admin_debug_html', $objAdditionalFee->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objAdditionalFee->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objAdditionalFee->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'additional-fee-manager&tab=additional-fees');
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

        if(isset($_GET['delete_additional_fee'])) { $this->processDelete($_GET['delete_additional_fee']); }
        if(isset($_POST['save_additional_fee']) && isset($_POST['additional_fee_id'])) { $this->processSave($_POST['additional_fee_id']); }

        $paramAdditionalFeeId = isset($_GET['additional_fee_id']) ? $_GET['additional_fee_id'] : 0;
        $objAdditionalFee = new AdditionalFee($this->conf, $this->lang, $this->dbSets->getAll(), $paramAdditionalFeeId);
        $localDetails = $objAdditionalFee->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'additional-fee-manager&tab=additional-fees');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-additional-fee&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->additionalFeeId = $localDetails['additional_fee_id'];
            $this->view->additionalFee = $localDetails['additional_fee'];
            $this->view->trustedPickupLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, $localDetails['pickup_location_id'], 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
            $this->view->trustedReturnLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, $localDetails['return_location_id'], 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
        } else
        {
            $this->view->additionalFeeId = 0;
            $this->view->additionalFee = '';
            $this->view->trustedPickupLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, 0, 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
            $this->view->trustedReturnLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, 0, 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
        }

        // Print the template
        $templateRelPathAndFileName = 'AdditionalFee'.DIRECTORY_SEPARATOR.'AddEditAdditionalFeeForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
