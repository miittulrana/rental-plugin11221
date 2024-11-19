<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Extras;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\ExtraDiscount;
use FleetManagement\Models\Extra\ExtraDiscountsObserver;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Extra\ExtraOption;
use FleetManagement\Models\Extra\ExtraOptionsObserver;
use FleetManagement\Models\Partner\PartnersObserver;

final class AddEditExtraController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramExtraId)
    {
        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $paramExtraId);
        if($objExtra->canEdit())
        {
            $deleted = $objExtra->delete();

            if($deleted)
            {
                // Delete corresponding discounts
                $objDiscountsObserver = new ExtraDiscountsObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $discountIds = $objDiscountsObserver->getAllIds("", $paramExtraId);
                foreach ($discountIds AS $discountId)
                {
                    $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $discountId);
                    $objDiscount->delete();
                }

                // Delete corresponding extra options
                $objOptionsObserver = new ExtraOptionsObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $optionIds = $objOptionsObserver->getAllIds($paramExtraId);
                foreach ($optionIds AS $optionId)
                {
                    $objOption = new ExtraOption($this->conf, $this->lang, $this->dbSets->getAll(), $optionId);
                    $objOption->delete();
                }
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objExtra->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objExtra->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objExtra->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extras');
        exit;
    }

    private function processSave($paramExtraId)
    {
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $paramExtraId);
        if($paramExtraId == 0 || $objExtra->canEdit())
        {
            $oldSKU = $objExtra->getSKU();
            $saved = $objExtra->save($_POST);
            $newSKU = $objExtra->getSKU();
            if($paramExtraId > 0 && $saved && $oldSKU != '' && $newSKU != $oldSKU)
            {
                $objOrdersObserver->changeExtraSKU($oldSKU, $newSKU);
            }
            if($saved && $this->lang->canTranslateSQL())
            {
                $objExtra->registerForTranslation();
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objExtra->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objExtra->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objExtra->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extras');
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
        $objPartnersObserver = new PartnersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        if(isset($_GET['delete_extra'])) { $this->processDelete($_GET['delete_extra']); }
        if(isset($_POST['save_extra']) && isset($_POST['extra_id'])) { $this->processSave($_POST['extra_id']); }

        $paramExtraId = isset($_GET['extra_id']) ? $_GET['extra_id'] : 0;
        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $paramExtraId);
        $localDetails = $objExtra->getDetailsWithItemAndPartner();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab=extras');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra&noheader=true');
        $this->view->networkEnabled = $this->conf->isNetworkEnabled();
        if (!is_null($localDetails) && $objExtra->canEdit())
        {
            $this->view->extraId = $localDetails['extra_id'];
            $this->view->extraSKU = $localDetails['extra_sku'];
            $this->view->extraName = $localDetails['extra_name'];
            $this->view->trustedPartnersDropdownOptionsHTML = $objPartnersObserver->getTrustedDropdownOptionsHTML($localDetails['partner_id'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
            if($objItemModelsObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), $localDetails['item_model_id'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            } else
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML(
                    $localDetails['item_model_id'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            }
            $this->view->trustedUnitsInStockDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(1, 100, $localDetails['units_in_stock'], "0", "", "");
            $this->view->trustedMaxUnitsPerOrderDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(1, 100, $localDetails['max_units_per_booking'], "0", "", "");
            $this->view->extraPrice = $localDetails['price'];
            $this->view->trustedPriceTypeDropdownOptionsHTML = $objExtra->getTrustedPriceTypesDropdownOptionsHTML($localDetails['price_type']);
            $this->view->fixedDeposit = $localDetails['fixed_deposit'];
        } else
        {
            $this->view->extraId = 0;
            $this->view->extraSKU = $objExtra->generateSKU();
            $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), 0);
            $this->view->extraName = "";
            $this->view->trustedPartnersDropdownOptionsHTML = $objPartnersObserver->getTrustedDropdownOptionsHTML(0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
            if($objItemModelsObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), 0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            } else
            {
                $this->view->trustedItemModelDropdownOptionsHTML = $objItemModelsObserver->getTrustedTranslatedDropdownOptionsHTML(
                    0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            }
            $this->view->trustedUnitsInStockDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(1, 100, 50, "0", "", "");
            $this->view->trustedMaxUnitsPerOrderDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(1, 100, 2, "0", "", "");
            $this->view->extraPrice = "";
            $this->view->trustedPriceTypeDropdownOptionsHTML = $objExtra->getTrustedPriceTypesDropdownOptionsHTML($this->dbSets->get('conf_price_calculation_type'));
            $this->view->fixedDeposit = "";
        }
        $this->view->isManager = current_user_can('manage_'.$this->conf->getExtPrefix().'all_extras');

        // Print the template
        $templateRelPathAndFileName = 'Extras'.DIRECTORY_SEPARATOR.'AddEditExtraForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
