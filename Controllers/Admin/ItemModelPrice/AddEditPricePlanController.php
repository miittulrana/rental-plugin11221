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
use FleetManagement\Models\PriceGroup\PriceGroup;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PriceGroup\PricePlan;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditPricePlanController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramPricePlanId)
    {
        $objPricePlan = new PricePlan($this->conf, $this->lang, $this->dbSets->getAll(), $paramPricePlanId);
        $priceGroupId = $objPricePlan->getPriceGroupId();
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $priceGroupId);
        // Allow to delete only seasonal prices
        if($objPricePlan->canEdit($objPriceGroup->getPartnerId()) && $objPricePlan->isSeasonal() === true)
        {
            $deleted = $objPricePlan->delete();

            if($deleted)
            {
                // Delete corresponding discounts
                $objDiscountsObserver = new PricePlanDiscountsObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $discountIds = $objDiscountsObserver->getAllIds("", $paramPricePlanId);
                foreach ($discountIds AS $discountId)
                {
                    $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $discountId);
                    $objDiscount->delete();
                }
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objPricePlan->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objPricePlan->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objPricePlan->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&price_group_id='.$priceGroupId.'&tab=price-plans');
        exit;
    }

    private function processSave($paramPriceGroupId, $paramPricePlanId)
    {
        $objDiscountsObserver = new PricePlanDiscountsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPricePlan = new PricePlan($this->conf, $this->lang, $this->dbSets->getAll(), $paramPricePlanId);
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $paramPriceGroupId);
        if(($paramPricePlanId == 0 && $objPriceGroup->canEdit()) || $objPricePlan->canEdit($objPriceGroup->getPartnerId()))
        {
            $oldCouponCode = $objPricePlan->getCouponCode();
            $saved = $objPricePlan->save($_POST);
            $newCouponCode = $objPricePlan->getCouponCode();
            if($paramPricePlanId > 0 && $saved && $newCouponCode != $oldCouponCode)
            {
                $hasCouponCode = $newCouponCode != '' ? true : false;
                $objDiscountsObserver->changeCouponStatus($paramPricePlanId, $hasCouponCode);
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objPricePlan->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objPricePlan->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objPricePlan->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab=price-plans&price_group_id='.intval($paramPriceGroupId));
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process actions
        if(isset($_GET['delete_price_plan'])) { $this->processDelete($_GET['delete_price_plan']); }
        if(isset($_POST['save_price_plan'], $_POST['price_group_id'], $_POST['price_plan_id'])) { $this->processSave($_POST['price_group_id'], $_POST['price_plan_id']); }

        $paramPriceGroupId = isset($_GET['price_group_id']) ? $_GET['price_group_id'] : 0;
        $paramPricePlanId = isset($_GET['price_plan_id']) ? $_GET['price_plan_id'] : 0;

        // Create mandatory instances
        $objPricePlan = new PricePlan($this->conf, $this->lang, $this->dbSets->getAll(), $paramPricePlanId);
        $validPriceGroupId = $paramPricePlanId > 0 ? $objPricePlan->getPriceGroupId() : StaticValidator::getValidPositiveInteger($paramPriceGroupId, 0);
        $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->dbSets->getAll(), $validPriceGroupId);
        $localPriceGroupDetails = $objPriceGroup->getDetailsWithPartner();
        if(is_null($localPriceGroupDetails))
        {
            // Price group do not exist
            // Note - we don't use here wp_safe_redirect, because headers already sent, so we have to use a redirect Javascript code in content
            $redirectToPage = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab=price-plans');
            echo '<script type="text/javascript">window.location="'.$redirectToPage.'"</script>';
            exit;
        } else
        {
            // Price group exists
            $localDetails = $objPricePlan->getDetails();
            $dailyRates = array();
            $hourlyRates = array();

            // Set the view variables
            $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'item-model-price-manager&tab=price-plans&price_group_id='.$validPriceGroupId);
            $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan&noheader=true');
            $this->view->priceGroupName = $localPriceGroupDetails['print_translated_price_group_name'].' '.esc_html($localPriceGroupDetails['via_partner']).' (ID='.$localPriceGroupDetails['price_group_id'].')';
            if(!is_null($localDetails) && $objPricePlan->canEdit($objPriceGroup->getPartnerId()))
            {
                $this->view->pricePlanId = $localDetails['price_plan_id'];
                $this->view->priceGroupId = $localDetails['price_group_id'];
                $this->view->couponCode = $localDetails['coupon_code'];
                $this->view->startDate = $localDetails['start_date'];
                $this->view->endDate = $localDetails['end_date'];
                $this->view->startTime = $localDetails['start_time_i18n'];
                $this->view->endTime = $localDetails['end_time_i18n'];
                foreach($objPricePlan->getDaysOfTheWeek() AS $dayOfTheWeek => $dayName)
                {
                    $dailyRates[$dayOfTheWeek] = $localDetails['daily_rate_'.$dayOfTheWeek];
                    $hourlyRates[$dayOfTheWeek] = $localDetails['hourly_rate_'.$dayOfTheWeek];
                }
            } else
            {
                $this->view->pricePlanId = 0;
                $this->view->priceGroupId = $validPriceGroupId;
                $this->view->couponCode = '';
                $this->view->startDate = $localDetails['start_date'];
                $this->view->endDate = $localDetails['end_date'];
                $this->view->startTime = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." 00:00:00"), true);
                $this->view->endTime = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." 23:59:59"), true);
                foreach($objPricePlan->getDaysOfTheWeek() AS $dayOfTheWeek => $dayName)
                {
                    $dailyRates[$dayOfTheWeek] = '';
                    $hourlyRates[$dayOfTheWeek] = '';
                }
            }
            $this->view->daysOfTheWeek = $objPricePlan->getDaysOfTheWeek();
            $this->view->displayDailyRates = in_array($this->dbSets->get('conf_price_calculation_type'), array(1, 3));
            $this->view->displayHourlyRates = in_array($this->dbSets->get('conf_price_calculation_type'), array(2, 3));
            $this->view->leftCurrencySymbol = $this->dbSets->get('conf_currency_symbol_location') == 0 ? esc_html($this->dbSets->get('conf_currency_symbol')).' ' : '';
            $this->view->rightCurrencySymbol = $this->dbSets->get('conf_currency_symbol_location') == 1 ? esc_html($this->dbSets->get('conf_currency_symbol')).' ' : '';
            $this->view->dailyRates = $dailyRates;
            $this->view->hourlyRates = $hourlyRates;

            // Print the template
            $templateRelPathAndFileName = 'ItemModelPrice'.DIRECTORY_SEPARATOR.'AddEditPricePlanForm.php';
            echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
        }
    }
}
