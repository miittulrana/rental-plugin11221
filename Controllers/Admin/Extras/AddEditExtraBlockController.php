<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Extras;
use FleetManagement\Models\Block\Block;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Order\OrderExtra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Block\ExtraBlockManager;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditExtraBlockController extends AbstractController
{
    private $objBlockManager = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
        $this->objBlockManager = new ExtraBlockManager($this->conf, $this->lang, $this->dbSets->getAll());

        // First - process request
        $this->processRequest();
    }

    private function processRequest()
    {
        // Set class variables
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
        $arrExtras = $this->objBlockManager->getSelectedWithDetails($this->objBlockManager->getIds());

        // If there is items to block
        if(sizeof($arrExtras) > 0)
        {
            // Then create the block
            $blocked = $objBlock->save(
                $paramBlockName, "", $this->objBlockManager->getStartTimestamp(), $this->objBlockManager->getEndTimestamp()
            );
            if($blocked)
            {
                $blockId = $objBlock->getId();

                foreach($arrExtras AS $extra)
                {
                    $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $extra['extra_id']);
                    if($objExtra->canEdit())
                    {
                        $objOrderOption = new OrderExtra($this->conf, $this->lang, $this->dbSets->getAll(), $blockId, $objExtra->getSKU());
                        $objOrderOption->save(0, $extra['selected_quantity']);

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

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extra-blocks');
        exit;
    }

    private function processUnblock($paramBlockId, $paramExtraId)
    {
        // Create mandatory instances
        $objBlock = new Block($this->conf, $this->lang, $this->dbSets->getAll(), $paramBlockId);
        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $paramExtraId);
        if($objExtra->canEdit())
        {
            $objOrderOption = new OrderExtra($this->conf, $this->lang, $this->dbSets->getAll(), $paramBlockId, $objExtra->getSKU());

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

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extra-blocks');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_POST['block'], $_POST['block_name'])) { $this->processBlock($_POST['block_name']); }
        if(isset($_GET['unblock'], $_GET['extra_id'])) { $this->processUnblock($_GET['unblock'], $_GET['extra_id']); }

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extra-blocks');
        $this->view->blockFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-extra');
        $this->view->blockResultsFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-extra&noheader=true');
        if(isset($_POST['search_to_block']))
        {
            // Block search results
            $extras = $this->objBlockManager->getAvailableWithDetails($this->objBlockManager->getAvailable());
            $this->view->gotBlockResults = sizeof($extras) > 0 ? true : false;
            $this->view->extras = $extras;
            $this->view->startDate = $this->objBlockManager->getShortStartDate();
            $this->view->startTime = $this->objBlockManager->getShortStartTime();
            $this->view->trustedStartTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $this->objBlockManager->getShortStartTime(), "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->endDate = $this->objBlockManager->getShortEndDate();
            $this->view->endTime = $this->objBlockManager->getShortEndTime();
            $this->view->trustedEndTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $this->objBlockManager->getShortEndTime(), "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->startDateTimeLabel = $this->objBlockManager->getI18nStartDate().' '.$this->objBlockManager->getI18nStartTime();
            $this->view->endDateTimeLabel = $this->objBlockManager->getI18nEndDate().' '.$this->objBlockManager->getI18nEndTime();
        } else
        {
            $this->view->gotBlockResults = false;
            $this->view->extras = array();
            $this->view->startDate = $this->objBlockManager->getShortStartDate();
            $this->view->trustedStartTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '09:00:00', "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->endDate = $this->objBlockManager->getShortEndDate();
            $this->view->trustedEndTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '09:00:00', "00:00:00", "23:30:00", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
        }

        // Print the template
        $templateRelPathAndFileName = 'Extras'.DIRECTORY_SEPARATOR.'AddEditExtraBlockForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}

