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
use FleetManagement\Models\Feature\Feature;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditFeatureController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramFeatureId)
    {
        $objFeature = new Feature($this->conf, $this->lang, $this->dbSets->getAll(), $paramFeatureId);
        $objFeature->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objFeature->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objFeature->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objFeature->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=features');
        exit;
    }

    private function processSave($paramFeatureId)
    {
        $objFeature = new Feature($this->conf, $this->lang, $this->dbSets->getAll(), $paramFeatureId);
        $saved = $objFeature->save($_POST);
        if($saved && $this->lang->canTranslateSQL())
        {
            $objFeature->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objFeature->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objFeature->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objFeature->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=features');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_GET['delete_feature'])) { $this->processDelete($_GET['delete_feature']); }
        if(isset($_POST['save_feature'], $_POST['feature_id'])) { $this->processSave($_POST['feature_id']); }

        $paramFeatureId = isset($_GET['feature_id']) ? $_GET['feature_id'] : 0;
        $objFeature = new Feature($this->conf, $this->lang, $this->dbSets->getAll(), $paramFeatureId);
        $localDetails = $objFeature->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=features');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-feature&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->featureId = $localDetails['feature_id'];
            $this->view->featureTitle = $localDetails['feature_title'];
            $this->view->isKeyFeature = $localDetails['key_feature'] == 1;
            $this->view->addToAllItemModels = false;
        } else
        {
            $this->view->featureId = 0;
            $this->view->featureTitle = '';
            $this->view->isKeyFeature = false;
            $this->view->addToAllItemModels = false;
        }

        // Print the template
        $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'AddEditFeatureForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
