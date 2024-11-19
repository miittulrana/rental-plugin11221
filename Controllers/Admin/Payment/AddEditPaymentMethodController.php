<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Payment;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Payment\PaymentMethod;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditPaymentMethodController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramPaymentMethodId)
    {
        $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->dbSets->getAll(), $paramPaymentMethodId);
        $objPaymentMethod->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objPaymentMethod->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objPaymentMethod->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objPaymentMethod->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=payment-methods');
        exit;
    }

    private function processSave($paramPaymentMethodId)
    {
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->dbSets->getAll(), $paramPaymentMethodId);
        $oldCode = $objPaymentMethod->getCode();
        $saved = $objPaymentMethod->save($_POST);
        $newCode = $objPaymentMethod->getCode();
        if($paramPaymentMethodId > 0 && $saved && $oldCode != '' && $newCode != $oldCode)
        {
            $objOrdersObserver->changePaymentMethodCode($oldCode, $newCode);
        }
        if($saved && $this->lang->canTranslateSQL())
        {
            $objPaymentMethod->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objPaymentMethod->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objPaymentMethod->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objPaymentMethod->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=payment-methods');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Process action only if prepayments are enabled
        if($this->dbSets->get('conf_prepayment_enabled') == 1)
        {
            if(isset($_POST['save_payment_method'], $_POST['payment_method_id'])) { $this->processSave($_POST['payment_method_id']); }
            if(isset($_GET['delete_payment_method'])) { $this->processDelete($_GET['delete_payment_method']); }
        }

        $paramPaymentMethodId = isset($_GET['payment_method_id']) ? $_GET['payment_method_id'] : "";
        $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->dbSets->getAll(), $paramPaymentMethodId);
        $localDetails = $objPaymentMethod->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=payment-methods');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-payment-method&noheader=true');
        $this->view->networkEnabled = $this->conf->isNetworkEnabled();
        if(!is_null($localDetails))
        {
            $this->view->paymentMethodId = $localDetails['payment_method_id'];
            $this->view->paymentMethodCode = $localDetails['payment_method_code'];
            $this->view->trustedPaymentMethodClassesDropdownOptionsHTML = $objPaymentMethod->getTrustedClassesDropdownOptionsHTML(
                $localDetails['payment_method_class'], "", ""
            );
            $this->view->paymentMethodName = $localDetails['payment_method_name'];
            $this->view->paymentMethodEmail = $localDetails['payment_method_email'];
            $this->view->paymentMethodDescription = $localDetails['payment_method_description'];
            $this->view->publicKey = $localDetails['public_key'];
            $this->view->privateKey = $localDetails['private_key'];
            $this->view->payInCurrencyRate = $localDetails['pay_in_currency_rate'];
            $this->view->payInCurrencyCode = $localDetails['pay_in_currency_code'];
            $this->view->payInCurrencySymbol = $localDetails['pay_in_currency_symbol'];
            $this->view->inSandboxMode = $localDetails['sandbox_mode'] == 1;
            $this->view->checkCertificate = $localDetails['check_certificate'] == 1;
            $this->view->sslOnly = $localDetails['ssl_only'] == 1;
            $this->view->isOnlinePayment = $localDetails['online_payment'] == 1;
            $this->view->paymentMethodEnabled = $localDetails['payment_method_enabled'] == 1;
            $this->view->trustedExpirationTimeDropdownOptionsHTML = $objPaymentMethod->getTrustedExpirationTimeDropdownOptionsHTML($localDetails['expiration_time'], 0, 7776000);
            $this->view->paymentMethodOrder = $localDetails['payment_method_order'];
        } else
        {
            $this->view->paymentMethodId = 0;
            $this->view->paymentMethodCode = $objPaymentMethod->generateCode();
            $this->view->trustedPaymentMethodClassesDropdownOptionsHTML = $objPaymentMethod->getTrustedClassesDropdownOptionsHTML("", "", "");
            $this->view->paymentMethodName = '';
            $this->view->paymentMethodEmail = '';
            $this->view->paymentMethodDescription = '';
            $this->view->publicKey = '';
            $this->view->privateKey = '';
            $this->view->payInCurrencyRate = '1.000'; // Non-editable in FM 501
            $this->view->payInCurrencyCode = $this->dbSets->get('conf_currency_code'); // Non-editable in FM 501
            $this->view->payInCurrencySymbol = $this->dbSets->get('conf_currency_symbol'); // Non-editable in FM 501
            $this->view->inSandboxMode = false;
            $this->view->checkCertificate = false;
            $this->view->sslOnly = false;
            $this->view->isOnlinePayment = false;
            $this->view->paymentMethodEnabled = false;
            $this->view->trustedExpirationTimeDropdownOptionsHTML = $objPaymentMethod->getTrustedExpirationTimeDropdownOptionsHTML(0, 0, 7776000);
            $this->view->paymentMethodOrder = '';
        }

        // Print the template
        $templateRelPathAndFileName = 'Payment'.DIRECTORY_SEPARATOR.'AddEditPaymentMethodForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
