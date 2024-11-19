<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\API;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Log\Log;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Payment\PaymentMethod;
use FleetManagement\Models\Settings\SettingsObserver;

final class API_PaymentCallbackController
{
    protected $conf         = null;
    protected $lang 	    = null;
    protected $dbSets	    = null;
    
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set database settings
        $this->dbSets = new SettingsObserver($this->conf, $this->lang);
        $this->dbSets->setAll();
    }

    /**
     * return void
     */
    public function processAndGetResponse()
    {
        // For payment callback we DO NOT use _nonce
        $paramPaymentMethodId = isset($_REQUEST['payment_method_id']) ? $_REQUEST['payment_method_id'] : "";
        $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->dbSets->getAll(), $paramPaymentMethodId);
        $arrResponse = $objPaymentMethod->doCallback();
        $paramOrderId = isset($arrResponse['transaction_id']) ? $arrResponse['transaction_id'] : 0;
        $orderDetails = (new Order($this->conf, $this->lang, $this->dbSets->getAll(), $paramOrderId))->getDetails();
        $payerEmail = isset($orderDetails['payer_email']) ? $orderDetails['payer_email'] : "";

        // Save log
        $objLog = new Log($this->conf, $this->lang, $this->dbSets->getAll(), 0);
        // NOTE: No counter used for payments here
        $validStatus = isset($arrResponse['authorized']) && $arrResponse['authorized'] == false ? 1 : 2; // 1 - FAILED, 2 - PASSED
        $logParams = array(
            'action' => 'payment-callback',

            'dimension_1' => $this->lang->getText('LANG_TRANSACTION_PAYER_EMAIL_TEXT'),
            'value_1' => $payerEmail,

            'errors' => isset($arrResponse['error_messages']) ? implode("\n", $arrResponse['error_messages']) : "",
            'debug_log' => isset($arrResponse['debug_messages']) ? implode("\n", $arrResponse['debug_messages']) : "", // Note: do not translate debug
            'status' => $validStatus,
        );
        $objLog->save($logParams);

        return array(
            'trusted_output_html' => $arrResponse['trusted_output_html'],
        );
    }
}
