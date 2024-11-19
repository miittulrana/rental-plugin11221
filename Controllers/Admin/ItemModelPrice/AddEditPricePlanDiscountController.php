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
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PriceGroup\PricePlanDiscount;
use FleetManagement\Models\PriceGroup\PriceGroup;
use FleetManagement\Models\PriceGroup\PricePlan;
use FleetManagement\Models\PriceGroup\PricePlansObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditPricePlanDiscountController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramDiscountId, $paramDiscountType)
    {
        $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $paramDiscountId);
        $objPricePlan = new PricePlan($this->conf, $this->lang, $this->dbSets->getAll(), $objDiscount->getPricePlanId());
        $priceGroupId = $objPricePlan->getPriceGroupId();
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $priceGroupId);
        if($objDiscount->canEdit($objPriceGroup->getPartnerId()))
        {
            $objDiscount->delete();

            StaticSession::cacheHTML_Array('admin_debug_html', $objDiscount->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objDiscount->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objDiscount->getErrorMessages());
        }

        $discountTabToReturn = 'duration-discounts';
        if($paramDiscountType == 1)
        {
            $discountTabToReturn = 'duration-discounts';
        } else if($paramDiscountType == 2)
        {
            $discountTabToReturn = 'discounts-in-advance';
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab='.$discountTabToReturn);
        exit;
    }

    private function processSave($paramDiscountId, $paramDiscountType)
    {
        $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $paramDiscountId);
        $objPricePlan = new PricePlan($this->conf, $this->lang, $this->dbSets->getAll(), $objDiscount->getPricePlanId());
        $priceGroupId = $objPricePlan->getPriceGroupId();
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $priceGroupId);
        if($paramDiscountId == 0 || $objDiscount->canEdit($objPriceGroup->getPartnerId()))
        {
            $objDiscount->save($_POST);

            StaticSession::cacheHTML_Array('admin_debug_html', $objDiscount->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objDiscount->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objDiscount->getErrorMessages());
        }

        $discountTabToReturn = 'duration-discounts';
        if($paramDiscountType == 1)
        {
            $discountTabToReturn = 'duration-discounts';
        } else if($paramDiscountType == 2)
        {
            $discountTabToReturn = 'discounts-in-advance';
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab='.$discountTabToReturn);
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objPricePlansObserver = new PricePlansObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // Process actions
        if(isset($_GET['delete_discount'], $_GET['discount_type'])) { $this->processDelete($_GET['delete_discount'], $_GET['discount_type']); }
        if(isset($_POST['save_discount'], $_POST['discount_id'], $_POST['discount_type'])) { $this->processSave($_POST['discount_id'], $_POST['discount_type']); }

        $paramDiscountType = isset($_GET['discount_type']) ? $_GET['discount_type'] : 1;
        $paramDiscountId = isset($_GET['discount_id']) ? $_GET['discount_id'] : 0;
        $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $paramDiscountId);
        $objPricePlan = new PricePlan($this->conf, $this->lang, $this->dbSets->getAll(), $objDiscount->getPricePlanId());
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $objPricePlan->getPriceGroupId());
        $localDetails = $objDiscount->getDetails();

        // Set the view variables
        $localDiscountTabToReturn = $paramDiscountType == 2 ? 'discounts-in-advance' : 'duration-discounts';
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab='.$localDiscountTabToReturn);
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan-discount&noheader=true');
        if(!is_null($localDetails) && $objDiscount->canEdit($objPriceGroup->getPartnerId()))
        {
            $this->view->discountId = $localDetails['discount_id'];
            if($objPricePlansObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedPricePlanDropdownOptionsHTML = $objPricePlansObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), $localDetails['price_plan_id'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            } else
            {
                $this->view->trustedPricePlanDropdownOptionsHTML = $objPricePlansObserver->getTrustedTranslatedDropdownOptionsHTML(
                    $localDetails['price_plan_id'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            }
            $this->view->discountType = $localDetails['discount_type'];
            $this->view->durationFromDays = $this->dbSets->getAdminDaysByPriceTypeFromPeriod($localDetails['period_from']);
            $this->view->durationFromHours = $this->dbSets->getAdminHoursByPriceTypeFromPeriod($localDetails['period_from']);
            $this->view->durationTillDays = $this->dbSets->getAdminDaysByPriceTypeFromPeriod($localDetails['period_till']);
            $this->view->durationTillHours = $this->dbSets->getAdminHoursByPriceTypeFromPeriod($localDetails['period_till']);
            $this->view->discountPercentage = $localDetails['discount_percentage'];
        } else
        {
            $this->view->discountId = 0;
            if($objPricePlansObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedPricePlanDropdownOptionsHTML = $objPricePlansObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), 0, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT'));
            } else
            {
                $this->view->trustedPricePlanDropdownOptionsHTML = $objPricePlansObserver->getTrustedTranslatedDropdownOptionsHTML(
                    0, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            }
            $this->view->discountType = in_array($paramDiscountType, array(1, 2)) ? intval($paramDiscountType) : 1;
            $this->view->durationFromDays = '';
            $this->view->durationFromHours = '';
            $this->view->durationTillDays = '';
            $this->view->durationTillHours = '';
            $this->view->discountPercentage = '';
        }
        if($paramDiscountType == 2)
        {
            $this->view->discountTabToReturn = 'discounts-in-advance';
            $this->view->pageTitle = $this->lang->getText('LANG_DISCOUNT_ITEM_ORDER_IN_ADVANCE_TEXT');
            $this->view->fromTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_BEFORE_TEXT');
            $this->view->toTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_UNTIL_TEXT');
        } else
        {
            $this->view->discountTabToReturn = 'duration-discounts';
            $this->view->pageTitle = $this->lang->getText('LANG_DISCOUNT_ITEM_ORDER_DURATION_TEXT');
            $this->view->fromTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_FROM_TEXT');
            $this->view->toTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_TILL_TEXT');
        }

        // Print the template
        $templateRelPathAndFileName = 'ItemModelPrice'.DIRECTORY_SEPARATOR.'AddEditDiscountForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
