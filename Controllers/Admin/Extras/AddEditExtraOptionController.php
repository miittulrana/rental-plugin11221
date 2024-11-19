<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Extras;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\Extra\ExtraOption;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditExtraOptionController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramOptionId)
    {
        $objOption = new ExtraOption($this->conf, $this->lang, $this->dbSets->getAll(), $paramOptionId);
        $objOption->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objOption->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOption->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOption->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extra-options');
        exit;
    }

    private function processSave($paramOptionId)
    {
        $objOption = new ExtraOption($this->conf, $this->lang, $this->dbSets->getAll(), $paramOptionId);
        $saved = $objOption->save($_POST);
        if($saved && $this->lang->canTranslateSQL())
        {
            $objOption->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objOption->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOption->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOption->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extra-options');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, $this->dbSets->getAll());

        if(isset($_GET['delete_option'])) { $this->processDelete($_GET['delete_option']); }
        if(isset($_POST['save_option'], $_POST['option_id'])) { $this->processSave($_POST['option_id']); }

        $paramOptionId = isset($_GET['option_id']) ? $_GET['option_id'] : 0;
        $objOption = new ExtraOption($this->conf, $this->lang, $this->dbSets->getAll(), $paramOptionId);
        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $objOption->getExtraId());
        $localDetails = $objOption->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extra-options');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-option&noheader=true');
        if(!is_null($localDetails) && $objOption->canEdit($objExtra->getPartnerId()))
        {
            $this->view->optionId = $localDetails['option_id'];
            $this->view->optionName = $localDetails['option_name'];
            if($objExtrasObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), $localDetails['extra_id'], "", ""
                );
            } else
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML(
                    $localDetails['extra_id'], "", ""
                );
            }
        } else
        {
            $this->view->optionId = 0;
            $this->view->optionName = '';
            if($objExtrasObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), 0, "", ""
                );
            } else
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML(
                    0, "", ""
                );
            }
        }

        // Print the template
        $templateRelPathAndFileName = 'Extras'.DIRECTORY_SEPARATOR.'AddEditExtraOptionForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
