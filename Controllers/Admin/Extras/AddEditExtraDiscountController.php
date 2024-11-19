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
use FleetManagement\Models\Extra\ExtraDiscount;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditExtraDiscountController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramDiscountId, $paramDiscountType)
    {
        $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $paramDiscountId);
        $extraId = $objDiscount->getExtraId();
        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $extraId);
        if($objExtra->canEdit())
        {
            $objDiscount->delete();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objDiscount->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objDiscount->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objDiscount->getErrorMessages());

        $discountTabToReturn = 'duration-discounts';
        if($paramDiscountType == 3)
        {
            $discountTabToReturn = 'duration-discounts';
        } else if($paramDiscountType == 4)
        {
            $discountTabToReturn = 'discounts-in-advance';
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab='.$discountTabToReturn);
        exit;
    }

    private function processSave($paramDiscountId, $paramDiscountType)
    {
        $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $paramDiscountId);
        $extraId = $objDiscount->getExtraId();
        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $extraId);
        if($extraId == 0 || $objDiscount->canEdit($objExtra->getPartnerId()))
        {
            $objDiscount->save($_POST);

            StaticSession::cacheHTML_Array('admin_debug_html', $objDiscount->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objDiscount->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objDiscount->getErrorMessages());
        }

        $discountTabToReturn = 'duration-discounts';
        if($paramDiscountType == 3)
        {
            $discountTabToReturn = 'duration-discounts';
        } else if($paramDiscountType == 4)
        {
            $discountTabToReturn = 'discounts-in-advance';
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'extras-manager&tab='.$discountTabToReturn);
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

        // Process actions
        if(isset($_GET['delete_discount'], $_GET['discount_type'])) { $this->processDelete($_GET['delete_discount'], $_GET['discount_type']); }
        if(isset($_POST['save_discount'], $_POST['discount_id'], $_POST['discount_type'])) { $this->processSave($_POST['discount_id'], $_POST['discount_type']); }

        $paramDiscountId = isset($_GET['discount_id']) ? $_GET['discount_id'] : 0;
        $paramDiscountType = isset($_GET['discount_type']) ? $_GET['discount_type'] : 3;
        $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->dbSets->getAll(), $paramDiscountId);

        $objExtra = new Extra($this->conf, $this->lang, $this->dbSets->getAll(), $objDiscount->getExtraId());
        $localDetails = $objDiscount->getDetails();

        // Set the view variables
        $localDiscountTabToReturn = $paramDiscountType == 4 ? 'discounts-in-advance' : 'duration-discounts';
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'discount-manager&tab='.$localDiscountTabToReturn);
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-discount&noheader=true');
        if(!is_null($localDetails) && $objDiscount->canEdit($objExtra->getPartnerId()))
        {
            $this->view->discountId = $localDetails['discount_id'];
            if($objExtrasObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), $localDetails['extra_id'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            } else
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML(
                    $localDetails['extra_id'], "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
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
            if($objExtrasObserver->canShowOnlyPartnerOwned())
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML_ByPartnerId(
                    get_current_user_id(), 0, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            } else
            {
                $this->view->trustedExtrasDropdownOptionsHTML = $objExtrasObserver->getTrustedTranslatedExtrasDropdownOptionsHTML(
                    0, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
                );
            }
            $this->view->discountType = in_array($paramDiscountType, array(3, 4)) ? intval($paramDiscountType) : 3;
            $this->view->durationFromDays = '';
            $this->view->durationFromHours = '';
            $this->view->durationTillDays = '';
            $this->view->durationTillHours = '';
            $this->view->discountPercentage = '';
        }
        if($paramDiscountType == 4)
        {
            $this->view->discountTabToReturn = 'discounts-in-advance';
            $this->view->pageTitle = $this->lang->getText('LANG_DISCOUNT_EXTRA_ORDER_IN_ADVANCE_TEXT');
            $this->view->fromTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_BEFORE_TEXT');
            $this->view->toTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_UNTIL_TEXT');
        } else
        {
            $this->view->discountTabToReturn = 'duration-discounts';
            $this->view->pageTitle = $this->lang->getText('LANG_DISCOUNT_EXTRA_ORDER_DURATION_TEXT');
            $this->view->fromTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_FROM_TEXT');
            $this->view->toTitle = $this->lang->getText('LANG_DISCOUNT_DURATION_TILL_TEXT');
        }

        // Print the template
        $templateRelPathAndFileName = 'Extras'.DIRECTORY_SEPARATOR.'AddEditDiscountForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
