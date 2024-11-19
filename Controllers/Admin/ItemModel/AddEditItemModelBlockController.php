<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\ItemModel;
use FleetManagement\Models\Block\Block;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Order\OrderItemModel;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Block\ItemModelBlockManager;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditItemModelBlockController extends AbstractController
{
    private $objBlockManager = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
        $this->objBlockManager = new ItemModelBlockManager($this->conf, $this->lang, $this->dbSets->getAll());

        // First - process request
        $this->processRequest();
    }

    private function processRequest()
    {
        // Second - set the object variables
        $this->objBlockManager->setVariables();

        // Second: Validate Time input
        $this->objBlockManager->validateTimeInput($this->objBlockManager->getStartTimestamp(), $this->objBlockManager->getEndTimestamp());

        // Data defined successfully, now remove session variables
        $this->objBlockManager->unsetVariablesCache();

        // Set fresh session variables
        $this->objBlockManager->cacheVariables();
    }

    private function processBlock($paramBlockName)
    {
        $objBlock = new Block($this->conf, $this->lang, $this->dbSets->getAll(), 0);
        $arrItemModels = $this->objBlockManager->getSelectedWithDetails($this->objBlockManager->getIds());

        // If there is items to block
        if(sizeof($arrItemModels) > 0)
        {
            // Then create the block
            $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $this->objBlockManager->getLocationId());
            $locationUniqueIdentifier = $objLocation->getUniqueIdentifier();

            $blocked = $objBlock->save(
                $paramBlockName, $locationUniqueIdentifier, $this->objBlockManager->getStartTimestamp(), $this->objBlockManager->getEndTimestamp()
            );
            if($blocked)
            {
                $blockId = $objBlock->getId();

                foreach($arrItemModels AS $itemModel)
                {
                    $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $itemModel['item_model_id']);
                    if($objItemModel->canEdit())
                    {
                        $objOrderOption = new OrderItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $blockId, $objItemModel->getSKU());
                        $objOrderOption->save(0, $itemModel['selected_quantity']);

                        StaticSession::cacheHTML_Array('admin_debug_html', $objOrderOption->getDebugMessages());
                        StaticSession::cacheValueArray('admin_okay_message', $objOrderOption->getOkayMessages());
                        StaticSession::cacheValueArray('admin_error_message', $objOrderOption->getErrorMessages());
                    }
                }
            }
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objBlock->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objBlock->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objBlock->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-model-blocks');
        exit;
    }

    private function processUnblock($paramBlockId, $paramItemModelId)
    {
        // Create mandatory instances
        $objBlock = new Block($this->conf, $this->lang, $this->dbSets->getAll(), $paramBlockId);
        $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $paramItemModelId);
        if($objItemModel->canEdit())
        {
            $objOrderOption = new OrderItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $paramBlockId, $objItemModel->getSKU());

            // Delete booking option
            $objOrderOption->delete();

            // If no related elements found to this block
            if($objBlock->isEmpty())
            {
                // Then also remove the block itself as well
                $objBlock->delete();
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objOrderOption->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objOrderOption->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objOrderOption->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-model-blocks');
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

        // Process actions
        if(isset($_POST['block'], $_POST['block_name'])) { $this->processBlock($_POST['block_name']); }
        if(isset($_GET['unblock'], $_GET['item_model_id'])) { $this->processUnblock($_GET['unblock'], $_GET['item_model_id']); }

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-model-blocks');
        $this->view->blockFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-item-model');
        $this->view->blockResultsFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-item-model&noheader=true');
        if(isset($_POST['search_for_block']))
        {
            // Block search results
            $itemModels = $this->objBlockManager->getAvailableWithDetails($this->objBlockManager->getAvailable());
            $this->view->gotBlockResults = sizeof($itemModels) > 0 ? true : false;
            $this->view->itemModels = $itemModels;
            $this->view->locationId = $this->objBlockManager->getLocationId();
            $this->view->trustedLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML("BOTH", 0, $this->objBlockManager->getLocationId(), 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT'));
            $this->view->startDate = $this->objBlockManager->getShortStartDate();
            $this->view->startTime = $this->objBlockManager->getShortStartTime();
            $this->view->trustedStartTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $this->objBlockManager->getShortStartTime(), "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->endDate = $this->objBlockManager->getShortEndDate();
	        $this->view->endTime = $this->objBlockManager->getShortEndTime();
            $this->view->trustedEndTimeDropdownOptionsHTML = $this->objBlockManager->getShortEndTime();
            $this->view->trustedEndTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $this->objBlockManager->getShortEndTime(), "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->startDateTimeLabel = $this->objBlockManager->getI18nStartDate().' '.$this->objBlockManager->getI18nStartTime();
            $this->view->endDateTimeLabel = $this->objBlockManager->getI18nEndDate().' '.$this->objBlockManager->getI18nEndTime();
        } else
        {
            $this->view->gotBlockResults = false;
            $this->view->itemModels = array();
            $this->view->trustedLocationsDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML("BOTH", 0, 0, 0, $this->lang->getText('LANG_LOCATION_SELECT2_TEXT'));
            $this->view->startDate = $this->objBlockManager->getShortStartDate();
            $this->view->trustedStartTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '09:00:00', "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->endDate = $this->objBlockManager->getShortEndDate();
            $this->view->trustedEndTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '09:00:00', "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
        }

        // Print the template
        $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'AddEditItemModelBlockForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
