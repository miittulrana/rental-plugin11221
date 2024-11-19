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
use FleetManagement\Models\Class_\Class_;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditClassController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramClassId)
    {
        $objClass = new Class_($this->conf, $this->lang, $this->dbSets->getAll(), $paramClassId);
        $deleted = $objClass->delete();

        if($deleted)
        {
            // Delete corresponding items
            $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
            $itemModelIds = $objItemModelsObserver->getAllIds(-1, -1, $paramClassId);
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

        StaticSession::cacheHTML_Array('admin_debug_html', $objClass->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objClass->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objClass->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=classes');
        exit;
    }

    private function processSave($paramClassId)
    {
        $objClass = new Class_($this->conf, $this->lang, $this->dbSets->getAll(), $paramClassId);
        $saved = $objClass->save($_POST);
        if($saved && $this->lang->canTranslateSQL())
        {
            $objClass->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objClass->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objClass->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objClass->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=classes');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_GET['delete_class'])) { $this->processDelete($_GET['delete_class']); }
        if(isset($_POST['save_class'], $_POST['class_id'])) { $this->processSave($_POST['class_id']); }

        $paramClassId = isset($_GET['class_id']) ? $_GET['class_id'] : 0;
        $objClass = new Class_($this->conf, $this->lang, $this->dbSets->getAll(), $paramClassId);
        $localDetails = $objClass->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=classes');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-class&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->classId = $localDetails['class_id'];
            $this->view->className = $localDetails['class_name'];
            $this->view->classOrder = $localDetails['class_order'];
        } else
        {
            $this->view->classId = 0;
            $this->view->className = '';
            $this->view->classOrder = '';
        }

        // Print the template
        $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'AddEditClassForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
