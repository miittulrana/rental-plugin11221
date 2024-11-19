<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Extras;
use FleetManagement\Models\Block\ExtraBlocksObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\ExtraDiscountsObserver;
use FleetManagement\Models\Extra\ExtrasObserver;
use FleetManagement\Models\Extra\ExtraOptionsObserver;
use FleetManagement\Models\Extra\ExtrasPriceTable;
use FleetManagement\Controllers\Admin\AbstractController;

final class ExtrasController extends AbstractController
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
        $objExtrasObserver = new ExtrasObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOptionsObserver = new ExtraOptionsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objBlocksObserver = new ExtraBlocksObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPriceTable = new ExtrasPriceTable($this->conf, $this->lang, $this->dbSets->getAll());
        $objDiscountsObserver = new ExtraDiscountsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'price-table', 'extras', 'extra-options', 'duration-discounts', 'discounts-in-advance', 'extra-blocks'
        ), 'price-table', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Extras price table tab
        $this->view->priceTable = $objPriceTable->getPriceTable();

        // Extras tab
        $this->view->addNewExtraURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra&extra_id=0');
        $this->view->trustedAdminExtrasListHTML = $objExtrasObserver->getTrustedAdminListHTML();

        // Extra options tab
        $this->view->addNewOptionURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-option&extra_id=0');
        $this->view->trustedAdminOptionsListHTML = $objOptionsObserver->getTrustedAdminListHTML();

        // Duration discounts tab
        $this->view->addNewDurationDiscountURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-discount&discount_type=3&discount_id=0');
        $this->view->trustedAdminDurationDiscountsGroupsHTML = $objDiscountsObserver->getTrustedAdminListForDiscountDurationHTML();

        // Discounts in advance tab
        $this->view->addNewAdvanceDiscountURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-discount&discount_type=4&discount_id=0');
        $this->view->trustedAdminOrderInAdvanceDiscountsGroupsHTML = $objDiscountsObserver->getTrustedAdminListForOrderInAdvanceHTML();

        // Blocked extras tab
        $this->view->addNewBlockURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-block-extra');
        $this->view->trustedAdminBlockedListHTML = $objBlocksObserver->getTrustedAdminListHTML();

        // Print the template
        $templateRelPathAndFileName = 'Extras'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
