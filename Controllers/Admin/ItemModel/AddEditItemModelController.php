<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\ItemModel;
use FleetManagement\Models\AttributeGroup\AttributesObserver;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Class_\ClassesObserver;
use FleetManagement\Models\Feature\FeaturesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\ItemModel\ItemModel;
use FleetManagement\Models\ItemModel\ItemModelsObserver;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Manufacturer\ManufacturersObserver;
use FleetManagement\Models\PriceGroup\PriceGroupsObserver;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Partner\PartnersObserver;

final class AddEditItemModelController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramItemModelId)
    {
        $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $paramItemModelId);
        if($objItemModel->canEdit())
        {
            $deleted = $objItemModel->delete();

            if($deleted)
            {
                // Delete corresponding extras and all data related to them
                $objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $objExtrasObserver->explicitDeleteByItemModelId($paramItemModelId);
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objItemModel->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objItemModel->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objItemModel->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-models');
        exit;
    }

    private function processSave($paramItemModelId)
    {
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $paramItemModelId);
        if($paramItemModelId == 0 || $objItemModel->canEdit())
        {
            $oldSKU = $objItemModel->getSKU();
            $saved = $objItemModel->save($_POST);
            $newSKU = $objItemModel->getSKU();
            if($paramItemModelId > 0 && $saved && $oldSKU != '' && $newSKU != $oldSKU)
            {
                $objOrdersObserver->changeExtraSKU($oldSKU, $newSKU);
            }
            if($saved && $this->lang->canTranslateSQL())
            {
                $objItemModel->registerForTranslation();
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objItemModel->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objItemModel->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objItemModel->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-models');
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
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPartnersObserver = new PartnersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objManufacturersObserver = new ManufacturersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objClassesObserver = new ClassesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objAttributesObserver = new AttributesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPriceGroupsObserver = new PriceGroupsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objFeaturesObserver = new FeaturesObserver($this->conf, $this->lang, $this->dbSets->getAll());

        if(isset($_GET['delete_item_model'])) { $this->processDelete($_GET['delete_item_model']); }
        if(isset($_POST['save_item_model'], $_POST['item_model_id'])) { $this->processSave($_POST['item_model_id']); }

        $paramItemModelId = isset($_GET['item_model_id']) ? $_GET['item_model_id'] : 0;
        $objItemModel = new ItemModel($this->conf, $this->lang, $this->dbSets->getAll(), $paramItemModelId);
        $localDetails = $objItemModel->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-manager&tab=item-models');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-item-model&noheader=true');
        $this->view->networkEnabled = $this->conf->isNetworkEnabled();
        $this->view->isManager = current_user_can('manage_'.$this->conf->getExtPrefix().'all_items');
        if (!is_null($localDetails) && $objItemModel->canEdit())
        {
            $this->view->itemModelId = $localDetails['item_model_id'];
            $this->view->itemModelSKU = $localDetails['item_model_sku'];
            $this->view->itemModelName = $localDetails['item_model_name'];

            $this->view->itemModelPagesDropdown = $objItemModelsObserver->getPagesDropdown(
                $localDetails['item_page_id'], "item_page_id", "item_page_id"
            );
            
            $this->view->trustedPartnersDropdownOptionsHTML = $objPartnersObserver->getTrustedDropdownOptionsHTML(
                $localDetails['partner_id'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedManufacturersDropdownOptionsHTML = $objManufacturersObserver->getTrustedTranslatedDropdownOptionsHTML(
                $localDetails['manufacturer_id'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedClassesDropdownOptionsHTML = $objClassesObserver->getTrustedTranslatedDropdownOptionsHTML(
                $localDetails['class_id'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedAttributeGroup1AttributesDropdownOptionsHTML = $objAttributesObserver->getTrustedTranslatedDropdownOptionsHTML(
                1, $localDetails['attribute_id1'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedAttributeGroup2AttributesDropdownOptionsHTML = $objAttributesObserver->getTrustedTranslatedDropdownOptionsHTML(
                2, $localDetails['attribute_id2'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->attribute3 = $localDetails['attribute3'];
            $this->view->trustedAttribute4DropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 100, $localDetails['max_passengers'], $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->attribute5 = $localDetails['attribute5'];
            $this->view->trustedAttribute6DropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 100, $localDetails['max_luggage'], $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->trustedAttribute7DropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 10, $localDetails['item_doors'], $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->attribute8 = $localDetails['attribute8'];
            $this->view->trustedItemModelFeaturesHTML = $objFeaturesObserver->getCheckboxes($localDetails['item_model_id']);
            $this->view->itemsInStock = $localDetails['items_in_stock'];
            $this->view->trustedItemsInStockDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 1000, $localDetails['items_in_stock'], "0", "", "");
            $this->view->trustedMaxItemsPerOrderDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(1, 1000, $localDetails['max_items_per_order'], "0", "", "");
            $this->view->pickupSelectOptions = $objLocationsObserver->getTranslatedPickupSelectOptions($localDetails['item_model_id']);
            $this->view->returnSelectOptions = $objLocationsObserver->getTranslatedReturnSelectOptions($localDetails['item_model_id']);
            $this->view->trustedMinAllowedAgeDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 30, $localDetails['min_driver_age'], $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->itemModelImage1_URL = $localDetails['item_model_image_1_url'];
            $this->view->itemModelImage2_URL = $localDetails['item_model_image_2_url'];
            $this->view->itemModelImage3_URL = $localDetails['item_model_image_3_url'];
            $this->view->demoItemModelImage1 = $localDetails['demo_item_image_1'];
            $this->view->demoItemModelImage2 = $localDetails['demo_item_image_2'];
            $this->view->demoItemModelImage3 = $localDetails['demo_item_image_3'];
            if($objPriceGroupsObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedPriceGroupsDropdownOptionsHTML = $objPriceGroupsObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), $localDetails['price_group_id'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            } else
            {
                $this->view->trustedPriceGroupsDropdownOptionsHTML = $objPriceGroupsObserver->getTrustedTranslatedDropdownOptionsHTML(
                    $localDetails['price_group_id'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            }
            $this->view->fixedDeposit = $localDetails['fixed_deposit'];
            $this->view->displayInSliderChecked = $localDetails['display_in_slider'] == 1 ? ' checked="checked"' : '';
            $this->view->displayInItemModelListChecked = $localDetails['display_in_item_list'] == 1 ? ' checked="checked"' : '';
            $this->view->displayInPriceTableChecked = $localDetails['display_in_price_table'] == 1 ? ' checked="checked"' : '';
            $this->view->displayInCalendarChecked = $localDetails['display_in_calendar'] == 1 ? ' checked="checked"' : '';
        } else
        {
            $this->view->itemModelId = 0;
            $this->view->itemModelSKU = $objItemModel->generateSKU();
            $this->view->itemModelName = "";
            $this->view->itemModelPagesDropdown = '';
            
            $this->view->trustedPartnersDropdownOptionsHTML = $objPartnersObserver->getTrustedDropdownOptionsHTML(
                0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedManufacturersDropdownOptionsHTML = $objManufacturersObserver->getTrustedTranslatedDropdownOptionsHTML(
                0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedClassesDropdownOptionsHTML = $objClassesObserver->getTrustedTranslatedDropdownOptionsHTML(
                0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedAttributeGroup1AttributesDropdownOptionsHTML = $objAttributesObserver->getTrustedTranslatedDropdownOptionsHTML(
                1, 0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->trustedAttributeGroup2AttributesDropdownOptionsHTML = $objAttributesObserver->getTrustedTranslatedDropdownOptionsHTML(
                2, 0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
            
            $this->view->attribute3 = '';
            $this->view->trustedAttribute4DropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 100, 0, $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->attribute5 = '';
            $this->view->trustedAttribute6DropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 100, 0, $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->trustedAttribute7DropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 10, 0, $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->attribute8 = '';
            $this->view->trustedItemModelFeaturesHTML = $objFeaturesObserver->getCheckboxes(0);
            $this->view->itemsInStock = 1;
            $this->view->trustedItemsInStockDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 1000, 1, "0", "", "");
            $this->view->trustedMaxItemsPerOrderDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(1, 100, 1, "0", "", "");
            $this->view->pickupSelectOptions = $objLocationsObserver->getTranslatedPickupSelectOptions(0);
            $this->view->returnSelectOptions = $objLocationsObserver->getTranslatedReturnSelectOptions(0);
            $this->view->trustedMinAllowedAgeDropdownOptionsHTML = StaticFormatter::getTrustedProgressiveNumberDropdownOptionsHTML(0, 30, 0, $this->lang->getText('LANG_DONT_DISPLAY_TEXT'), "", "");
            $this->view->itemModelImage1_URL = '';
            $this->view->itemModelImage2_URL = '';
            $this->view->itemModelImage3_URL = '';
            $this->view->demoItemModelImage1 = 0;
            $this->view->demoItemModelImage2 = 0;
            $this->view->demoItemModelImage3 = 0;
            if($objPriceGroupsObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedPriceGroupsDropdownOptionsHTML = $objPriceGroupsObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(get_current_user_id(), 0, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
            } else
            {
                $this->view->trustedPriceGroupsDropdownOptionsHTML = $objPriceGroupsObserver->getTrustedTranslatedDropdownOptionsHTML(0, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
            }
            $this->view->fixedDeposit = "";
            $this->view->displayInSliderChecked = ' checked="checked"';
            $this->view->displayInItemModelListChecked = ' checked="checked"';
            $this->view->displayInPriceTableChecked = ' checked="checked"';
            $this->view->displayInCalendarChecked = ' checked="checked"';
        }

        // Print the template
        $templateRelPathAndFileName = 'ItemModel'.DIRECTORY_SEPARATOR.'AddEditItemModelForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
