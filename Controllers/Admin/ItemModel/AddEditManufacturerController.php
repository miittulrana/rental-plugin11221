<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\ItemModel;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Manufacturer\Manufacturer;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditManufacturerController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramManufacturerId)
    {
        $objManufacturer = new Manufacturer($this->conf, $this->lang, $this->dbSets->getAll(), $paramManufacturerId);
        $deleted = $objManufacturer->delete();

        if($deleted)
        {
            // Delete corresponding items
            $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
            $itemModelIds = $objItemModelsObserver->getAllIds(-1, $paramManufacturerId);
            foreach ($itemModelIds AS $itemModelId)
            {
                $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $itemModelId);
                $deleted2 = $objItemModel->delete();

                if($deleted2)
                {
                    // Delete corresponding extras and all data related to them
                    $objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, $this->dbSets->getAll());
                    $objExtrasObserver->explicitDeleteByItemModelId($itemModelId);
                }
            }
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objManufacturer->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objManufacturer->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objManufacturer->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=manufacturers');
        exit;
    }

    private function processSave($paramManufacturerId)
    {
        $objManufacturer = new Manufacturer($this->conf, $this->lang, $this->dbSets->getAll(), $paramManufacturerId);
        $saved = $objManufacturer->save($_POST);
        if($saved && $this->lang->canTranslateSQL())
        {
            $objManufacturer->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objManufacturer->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objManufacturer->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objManufacturer->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=manufacturers');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_GET['delete_manufacturer'])) { $this->processDelete($_GET['delete_manufacturer']); }
        if(isset($_POST['save_manufacturer'], $_POST['manufacturer_id'])) { $this->processSave($_POST['manufacturer_id']); }

        $paramManufacturerId = isset($_GET['manufacturer_id']) ? $_GET['manufacturer_id'] : 0;
        $objManufacturer = new Manufacturer($this->conf, $this->lang, $this->dbSets->getAll(), $paramManufacturerId);
        $localDetails = $objManufacturer->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=manufacturers');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-manufacturer&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->manufacturerId = $localDetails['manufacturer_id'];
            $this->view->manufacturerName = $localDetails['manufacturer_name'];
            $this->view->manufacturerLogoURL = $localDetails['manufacturer_logo_url'];
            $this->view->demoManufacturerLogo = $localDetails['demo_manufacturer_logo'];
        } else
        {
            $this->view->manufacturerId = 0;
            $this->view->manufacturerName = '';
            $this->view->manufacturerLogoURL = '';
            $this->view->demoManufacturerLogo = 0;
        }

        // Print the template
        $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'AddEditManufacturerForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
