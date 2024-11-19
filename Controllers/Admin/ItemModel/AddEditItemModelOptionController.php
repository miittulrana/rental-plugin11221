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
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\ItemModel\ItemModelOption;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditItemModelOptionController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramOptionId)
    {
        $objOption = new ItemModelOption($this->conf, $this->lang, $this->dbSets->getAll(), $paramOptionId);
        $objOption->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objOption->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOption->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOption->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-model-options');
        exit;
    }

    private function processSave($paramOptionId)
    {
        $objOption = new ItemModelOption($this->conf, $this->lang, $this->dbSets->getAll(), $paramOptionId);
        $saved = $objOption->save($_POST);
        if($saved && $this->lang->canTranslateSQL())
        {
            $objOption->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objOption->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOption->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOption->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-model-options');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        if(isset($_GET['delete_option'])) { $this->processDelete($_GET['delete_option']); }
        if(isset($_POST['save_option'], $_POST['option_id'])) { $this->processSave($_POST['option_id']); }

        $paramOptionId = isset($_GET['option_id']) ? $_GET['option_id'] : 0;

        $objOption = new ItemModelOption($this->conf, $this->lang, $this->dbSets->getAll(), $paramOptionId);
        $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $objOption->getItemModelId());
        $localDetails = $objOption->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-model-options');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-item-model-option&noheader=true');
        if(!is_null($localDetails) && $objOption->canEdit($objItemModel->getPartnerId()))
        {
            $this->view->optionId = $localDetails['option_id'];
            $this->view->optionName = $localDetails['option_name'];
            if($objItemModelsObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), $localDetails['item_model_id'], "", ""
                );
            } else
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML(
                    $localDetails['item_model_id'], "", ""
                );
            }
        } else
        {
            $this->view->optionId = 0;
            $this->view->optionName = '';
            if($objItemModelsObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), 0, "", ""
                );
            } else
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML(
                    0, "", ""
                );
            }
        }

        // Print the template
        $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'AddEditItemModelOptionForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
