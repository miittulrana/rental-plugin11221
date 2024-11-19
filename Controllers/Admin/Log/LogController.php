<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Log;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Log\LogsObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class LogController extends AbstractController
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
        $objLogsObserver = new LogsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // First - delete expired logs
        $objLogsObserver->deleteExpired();

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'customer-lookups', 'payments',
        ), 'customer-lookups', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Set the view variables - Customer lookups log tab
        $this->view->trustedCustomerLookupLogListHTML = $objLogsObserver->getTrustedAdminListForCustomerLookupsHTML();

        // Set the view variables - Payments log tab
        $this->view->trustedPaymentLogListHTML = $objLogsObserver->getTrustedAdminListForPaymentsHTML();

        // Print the template
        $templateRelPathAndFileName = 'Log'.DIRECTORY_SEPARATOR.'Tabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
