<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\ItemModelPrice;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PriceGroup\PricePlanDiscountsObserver;
use FleetManagement\Models\ItemModel\ItemModelsPriceTable;
use FleetManagement\Models\PriceGroup\PriceGroupsObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class ItemModelPriceController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objPriceTable = new ItemModelsPriceTable($this->conf, $this->lang, $this->dbSets->getAll());
        $objPriceGroupsObserver = new PriceGroupsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objDiscountsObserver = new PricePlanDiscountsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        $paramPriceGroupId = isset($_GET['price_group_id']) ? $_GET['price_group_id'] : 0;

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'price-table', 'price-groups', 'price-plans', 'duration-discounts', 'discounts-in-advance'
        ), 'price-table', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Set the view variables - price table tab
        $this->view->priceTable = $objPriceTable->getPriceTable();
        $this->view->classifyItemModels = $this->dbSets->get('conf_classify_items') == 1 ? true : false;

        // Price groups tab
        $this->view->addNewPriceGroupURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-group&price_group_id=0');
        $this->view->trustedAdminPriceGroupsListHTML = $objPriceGroupsObserver->getTrustedAdminListHTML();

        // Price plans tab
        $this->view->addNewPricePlanPage = $this->conf->getExtURL_Prefix().'add-edit-price-plan';
        if($objPriceGroupsObserver->canShowOnlyPartnerOwned())
        {
            $this->view->trustedPriceGroupDropdownOptionsHTML = $objPriceGroupsObserver->getTrustedTranslatedDropdownOptionsHTML_ByPartnerId(
                get_current_user_id(), $paramPriceGroupId, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
        } else
        {
            $this->view->trustedPriceGroupDropdownOptionsHTML = $objPriceGroupsObserver->getTrustedTranslatedDropdownOptionsHTML(
                $paramPriceGroupId, "", $this->lang->getText('LANG_DROPDOWN_SELECT3_TEXT')
            );
        }

        // Duration discounts tab
        $this->view->addNewDurationDiscountURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan-discount&discount_type=1&discount_id=0');
        $this->view->adminDurationDiscountGroups = $objDiscountsObserver->getTrustedAdminListForDiscountDurationHTML();

        // Discounts in advance tab
        $this->view->addNewAdvanceDiscountURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan-discount&discount_type=2&discount_id=0');
        $this->view->adminBookingInAdvanceDiscountGroups = $objDiscountsObserver->getTrustedAdminListForOrderInAdvanceHTML();

        // Print the template
        $templateRelPathAndFileName = 'ItemModelPrice'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
