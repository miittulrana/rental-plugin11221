<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\AdditionalFee;
use FleetManagement\Models\AdditionalFee\AdditionalFeesObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class AdditionalFeeController extends AbstractController
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
        $objAdditionalFeesObserver = new AdditionalFeesObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'additional-fees',
        ), 'additional-fees', isset($_GET['tab']) ? $_GET['tab'] : '');

        // 3. Set the view variables - additional fees
        $this->view->trustedAdminAdditionalFeesListHTML = $objAdditionalFeesObserver->getTrustedAdminListHTML();

        // Print the template
        $templateRelPathAndFileName = 'AdditionalFee'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
