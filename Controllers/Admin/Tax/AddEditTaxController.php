<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Tax;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Tax\Tax;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditTaxController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramTaxId)
    {
        $objTax = new Tax($this->conf, $this->lang, $this->dbSets->getAll(), $paramTaxId);
        $objTax->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objTax->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objTax->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objTax->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=taxes');
        exit;
    }

    private function processSave($paramTaxId)
    {
        $objTax = new Tax($this->conf, $this->lang, $this->dbSets->getAll(), $paramTaxId);
        $saved = $objTax->save($_POST);
        if($saved && $this->lang->canTranslateSQL())
        {
            $objTax->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objTax->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objTax->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objTax->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=taxes');
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

        if(isset($_GET['delete_tax'])) { $this->processDelete($_GET['delete_tax']); }
        if(isset($_POST['save_tax'], $_POST['tax_id'])) { $this->processSave($_POST['tax_id']); }

        $paramTaxId = isset($_GET['tax_id']) ? $_GET['tax_id'] : 0;
        $objTax = new Tax($this->conf, $this->lang, $this->dbSets->getAll(), $paramTaxId);
        $localDetails = $objTax->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=taxes');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-tax&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->taxId = $localDetails['tax_id'];
            $this->view->taxName = $localDetails['tax_name'];
            $this->view->trustedLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, $localDetails['location_id'], 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
            $this->view->pickupTypeChecked = $localDetails['location_type'] == 1 ? ' checked="checked"' : '';
            $this->view->returnTypeChecked = $localDetails['location_type'] == 2 ? ' checked="checked"' : '';
            $this->view->taxPercentage = $localDetails['tax_percentage'];
        } else
        {
            $this->view->taxId = 0;
            $this->view->taxName = '';
            $this->view->trustedLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, 0, 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT')
            );
            $this->view->pickupTypeChecked = ' checked="checked"';
            $this->view->returnTypeChecked = '';
            $this->view->taxPercentage = $localDetails['tax_percentage'];
        }

        // Print the template
        $templateRelPathAndFileName = 'Tax'.DIRECTORY_SEPARATOR.'AddEditTaxForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
