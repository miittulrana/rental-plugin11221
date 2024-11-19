<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Tax;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Tax\TaxesObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class TaxController extends AbstractController
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
        $objTaxesObserver = new TaxesObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'taxes',
        ), 'taxes', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Set the view variables - Taxes tab
        $this->view->addNewTaxURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-tax&tax_id=0');
        $this->view->trustedAdminTaxesListHTML = $objTaxesObserver->getTrustedAdminListHTML();

        // Print the template
        $templateRelPathAndFileName = 'Tax'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
