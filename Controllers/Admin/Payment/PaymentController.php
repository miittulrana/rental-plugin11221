<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Payment;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Log\LogsObserver;
use FleetManagement\Models\Payment\PaymentMethodsObserver;
use FleetManagement\Models\Prepayment\PrepaymentsObserver;
use FleetManagement\Models\Tax\TaxesObserver;
use FleetManagement\Controllers\Admin\AbstractController;

final class PaymentController extends AbstractController
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
        $objPrepaymentsObserver = new PrepaymentsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objTaxesObserver = new TaxesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPaymentMethodsObserver = new PaymentMethodsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objLogsObserver = new LogsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'prepayments', 'payment-methods',
        ), 'prepayments', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Set the view variables - Prepayments tab
        $this->view->addNewPrepaymentURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-prepayment&prepayment_id=0');
        $this->view->trustedAdminPrepaymentsListHTML = $objPrepaymentsObserver->getTrustedAdminListHTML();

        // Set the view variables - Payment methods tab
        $this->view->addNewPaymentMethodURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-payment-method&payment_method_id=0');
        $this->view->trustedAdminPaymentMethodsListHTML = $objPaymentMethodsObserver->getTrustedAdminListHTML();

        // Print the template
        $templateRelPathAndFileName = 'Payment'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
