<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\ItemModel;
use FleetManagement\Models\AttributeGroup\Attribute1;
use FleetManagement\Models\AttributeGroup\Attribute2;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Validation\StaticValidator;

final class AddEditAttributeController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramAttributeId, $paramAttributeGroupId)
    {
        $validAttributeGroupId = StaticValidator::getValidPositiveInteger($paramAttributeGroupId, 0);
        $objAttribute = null;
        $paramAttributeId1 = -1;
        $paramAttributeId2 = -1;
        switch($paramAttributeGroupId)
        {
            case 1:
                $objAttribute = new Attribute1($this->conf, $this->lang, $this->dbSets->getAll(), $paramAttributeId);
                $paramAttributeId1 = $paramAttributeId;
                $paramAttributeId2 = -1;
                break;
            case 2:
                $objAttribute = new Attribute2($this->conf, $this->lang, $this->dbSets->getAll(), $paramAttributeId);
                $paramAttributeId1 = -1;
                $paramAttributeId2 = $paramAttributeId;
                break;
        }
        if(!is_null($objAttribute))
        {
            $deleted = $objAttribute->delete();

            if($deleted)
            {
                // Reset attributes on corresponding item models
                $objItemModelsObserver = new ItemModelsObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $itemModelIds = $objItemModelsObserver->getAllIds(-1, -1, -1, $paramAttributeId1, $paramAttributeId2);
                foreach ($itemModelIds AS $itemModelId)
                {
                    $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $itemModelId);
                    $objItemModel->resetAttributeByAttributeGroupId($paramAttributeGroupId);
                    
                    StaticSession::cacheHTML_Array('admin_debug_html', $objItemModel->getDebugMessages());
                    StaticSession::cacheValueArray('admin_okay_message', $objItemModel->getOkayMessages());
                    StaticSession::cacheValueArray('admin_error_message', $objItemModel->getErrorMessages());
                }
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objAttribute->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objAttribute->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objAttribute->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=attribute-group'.$validAttributeGroupId.'-attributes');
        exit;
    }

    private function processSave($paramAttributeId, $paramAttributeGroupId)
    {
        $validAttributeGroupId = StaticValidator::getValidPositiveInteger($paramAttributeGroupId, 0);
        $objAttribute = null;
        switch($paramAttributeGroupId)
        {
            case 1:
                $objAttribute = new Attribute1($this->conf, $this->lang, $this->dbSets->getAll(), $paramAttributeId);
                break;
            case 2:
                $objAttribute = new Attribute2($this->conf, $this->lang, $this->dbSets->getAll(), $paramAttributeId);
                break;
        }
        if(!is_null($objAttribute))
        {
            $saved = $objAttribute->save($_POST);
            if($saved && $this->lang->canTranslateSQL())
            {
                $objAttribute->registerForTranslation();
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objAttribute->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objAttribute->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objAttribute->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=attribute-group'.$validAttributeGroupId.'-attributes');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_GET['delete_attribute'], $_GET['attribute_group_id'])) { $this->processDelete($_GET['delete_attribute'], $_GET['attribute_group_id']); }
        if(isset($_POST['save_attribute'], $_POST['attribute_id'], $_POST['attribute_group_id'])) { $this->processSave($_POST['attribute_id'], $_POST['attribute_group_id']); }

        $paramAttributeId = isset($_GET['attribute_id']) ? $_GET['attribute_id'] : 0;
        $paramAttributeGroupId = isset($_GET['attribute_group_id']) ? $_GET['attribute_group_id'] : 0;
        $validAttributeGroupId = StaticValidator::getValidPositiveInteger($paramAttributeGroupId, 0);
        $objAttribute = null;
        $attributeGroupName = "";
        switch($paramAttributeGroupId)
        {
            case 1:
                $objAttribute = new Attribute1($this->conf, $this->lang, $this->dbSets->getAll(), $paramAttributeId);
                $attributeGroupName = $this->lang->getText('LANG_ATTRIBUTE_GROUP_DEFAULT_NAME1_TEXT');
                break;
            case 2:
                $objAttribute = new Attribute2($this->conf, $this->lang, $this->dbSets->getAll(), $paramAttributeId);
                $attributeGroupName = $this->lang->getText('LANG_ATTRIBUTE_GROUP_DEFAULT_NAME2_TEXT');
                break;
        }

        if(is_null($objAttribute))
        {
            // Current user is not allowed to edit current booking
            // Note - we don't use here wp_safe_redirect, because headers already sent, so we have to use a redirect Javascript code in content
            $redirectToPage = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=attribute-group'.$validAttributeGroupId.'-attributes');
            echo '<script type="text/javascript">window.location="'.$redirectToPage.'"</script>';
            exit;
        } else
        {
            // Attribute is NOT null
            $localDetails = $objAttribute->getDetails();
        
            // Set the view variables
            $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=attribute-group'.$validAttributeGroupId.'-attributes');
            $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-attribute&noheader=true');
            if(!is_null($localDetails))
            {
                $this->view->attributeGroupId = $validAttributeGroupId;
                $this->view->attributeGroupName = $attributeGroupName;
                $this->view->attributeId = $localDetails['attribute_id'];
                $this->view->attributeTitle = $localDetails['attribute_title'];
            } else
            {
                $this->view->attributeGroupId = $validAttributeGroupId;
                $this->view->attributeGroupName = $attributeGroupName;
                $this->view->attributeId = 0;
                $this->view->attributeTitle = '';
            }
        
            // Print the template
            $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'AddEditAttributeForm.php';
            echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
        }
    }
}
