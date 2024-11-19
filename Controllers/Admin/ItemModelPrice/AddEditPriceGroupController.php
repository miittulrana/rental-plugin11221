<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\ItemModelPrice;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\PriceGroup\PricePlanDiscount;
use FleetManagement\Models\PriceGroup\PricePlanDiscountsObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PriceGroup\PriceGroup;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\PriceGroup\PricePlan;
use FleetManagement\Models\PriceGroup\PricePlansObserver;
use FleetManagement\Models\Partner\PartnersObserver;

final class AddEditPriceGroupController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramPriceGroupId)
    {
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $paramPriceGroupId);
        if($objPriceGroup->canEdit())
        {
            $deleted = $objPriceGroup->delete();

            if($deleted)
            {
                // Delete corresponding items
                $objPricePlansObserver = new PricePlansObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $pricePlanIds = $objPricePlansObserver->getAllIds($paramPriceGroupId);
                foreach ($pricePlanIds AS $pricePlanId)
                {
                    $objPricePlan = new PricePlan($this->conf, $this->lang, $this->dbSets->getAll(), $pricePlanId);
                    $deleted2 = $objPricePlan->delete();

                    if($deleted2)
                    {
                        // Delete corresponding discounts
                        $objDiscountsObserver = new PricePlanDiscountsObserver($this->conf, $this->lang, $this->dbSets->getAll());
                        $discountIds = $objDiscountsObserver->getAllIds("", $pricePlanId);
                        foreach ($discountIds AS $discountId)
                        {
                            $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $discountId);
                            $objDiscount->delete();
                        }
                    }
                }
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objPriceGroup->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objPriceGroup->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objPriceGroup->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab=price-groups');
        exit;
    }

    private function processSave($paramPriceGroupId)
    {
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $paramPriceGroupId);
        if($paramPriceGroupId == 0 || $objPriceGroup->canEdit())
        {
            $saved = $objPriceGroup->save($_POST);
            if($saved && $this->lang->canTranslateSQL())
            {
                $objPriceGroup->registerForTranslation();
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objPriceGroup->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objPriceGroup->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objPriceGroup->getErrorMessages());
        }
        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab=price-groups');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objPartnersObserver = new PartnersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // Process actions
        if(isset($_GET['delete_price_group'])) { $this->processDelete($_GET['delete_price_group']); }
        if(isset($_POST['save_price_group'], $_POST['price_group_id'])) { $this->processSave($_POST['price_group_id']); }

        $paramPriceGroupId = isset($_GET['price_group_id']) ? $_GET['price_group_id'] : 0;
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $paramPriceGroupId);
        $localDetails = $objPriceGroup->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab=price-groups');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-group&noheader=true');
        if(!is_null($localDetails) && $objPriceGroup->canEdit())
        {
            // Override default value
            $this->view->priceGroupId = $objPriceGroup->getId();
            $this->view->priceGroupName = $localDetails['price_group_name'];
            $this->view->trustedPartnersDropdownOptionsHTML = $objPartnersObserver->getTrustedDropdownOptionsHTML($localDetails['partner_id'], 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
        } else
        {
            $this->view->priceGroupId = 0;
            $this->view->priceGroupName = '';
            $this->view->trustedPartnersDropdownOptionsHTML = $objPartnersObserver->getTrustedDropdownOptionsHTML(0, 0, $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
        }
        $this->view->isManager = current_user_can('manage_'.$this->conf->getExtPrefix().'all_items');

        // Print the template
        $templateRelPathAndFileName = 'ItemModelPrice'.DIRECTORY_SEPARATOR.'AddEditPriceGroupForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
