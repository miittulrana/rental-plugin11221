<?php
/**
 * Stripe Payment Library to Fleet Management Transpiler class
 *
 * Transpiler - It's a source-to-source compiler, it translates source code
 * from one language to another (or to another version of the same language).
 *
 * @note - Library transpiler class should not have a namespace, because all transpilers are loaded as dynamic libraries
 * and that would anyway require a full-qualified namespaces for each transpiler constructor. So to avoid that,
 * we just do not use namespaces for transpilers at all.
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

// NOTE: In libraries folder all includes is a MUST
require_once('FleetManagementPaymentInterface.php');

if(!class_exists('StripeToFleetManagementTranspiler'))
{
    class StripeToFleetManagementTranspiler implements \FleetManagementPaymentInterface
    {
        const PAYMENT_METHOD                = true;
        protected $conf                     = null;
        protected $lang                     = null;
        protected $settings                 = array();
        protected $debugMode                = 0;

        // Array holds the fields to submit to PayPal
        protected $fields                   = array();
        protected $use_ssl                  = true;

        protected $paymentMethodId          = 0;
        protected $paymentMethodCode        = 'STRIPE';
        protected $businessEmail            = '';
        protected $payInCurrencyRate        = 1.0000;
        protected $payInCurrencyCode        = 'USD';
        protected $payInCurrencySymbol      = '$';
        protected $currencyCode             = 'USD';
        protected $currencySymbol           = '$';
        // Testing (true) or regular domain (false)
        protected $useSandbox               = false;
        protected $checkCertificate         = false;
        protected $companyName              = '';
        protected $companyPhone             = '';
        protected $companyEmail             = '';
        protected $paymentCancelledPageId   = 0;
        protected $orderConfirmedPageId     = 0;
        protected $sendNotifications        = 0;
        protected $publicKey                = '';
        protected $privateKey               = '';

        /**
         * @param ConfigurationInterface &$paramConf
         * @param LanguageInterface &$paramLang
         * @param array $paramSettings
         * @param array $paramPaymentMethodDetails
         */
        public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, array $paramPaymentMethodDetails)
        {
            // Set class settings
            $this->conf = $paramConf;
            // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
            $this->lang = $paramLang;
            // Set saved settings
            $this->settings = $paramSettings;

            // Process payment method details
            $this->paymentMethodId = isset($paramPaymentMethodDetails['payment_method_id']) ? abs(intval($paramPaymentMethodDetails['payment_method_id'])) : 0;
            $this->paymentMethodCode = isset($paramPaymentMethodDetails['payment_method_code']) ? sanitize_text_field($paramPaymentMethodDetails['payment_method_code']) : "";
            $this->businessEmail = isset($paramPaymentMethodDetails['payment_method_email']) ? sanitize_email($paramPaymentMethodDetails['payment_method_email']) : "";
            $this->payInCurrencyRate = isset($paramPaymentMethodDetails['pay_in_currency_rate']) ? floatval($paramPaymentMethodDetails['private_key']) : 1.000;
            $this->payInCurrencyCode = !empty($paramPaymentMethodDetails['pay_in_currency_code']) ? sanitize_text_field($paramPaymentMethodDetails['pay_in_currency_code']) : '';
            $this->payInCurrencySymbol = !empty($paramPaymentMethodDetails['pay_in_currency_symbol']) ? sanitize_text_field($paramPaymentMethodDetails['pay_in_currency_symbol']) : '';
            $this->useSandbox = !empty($paramPaymentMethodDetails['sandbox_mode']) ? true : false;
            $this->checkCertificate = !empty($paramPaymentMethodDetails['check_certificate']) ? true : false;
            $this->publicKey = !empty($paramPaymentMethodDetails['public_key']) ? sanitize_text_field($paramPaymentMethodDetails['public_key']) : '';
            $this->privateKey = !empty($paramPaymentMethodDetails['private_key']) ? sanitize_text_field($paramPaymentMethodDetails['private_key']) : '';

            // Process settings
            $this->currencyCode = isset($paramSettings['conf_currency_code']) ? sanitize_text_field($paramSettings['conf_currency_code']) : 'USD';
            $this->currencySymbol = isset($paramSettings['conf_currency_symbol']) ? sanitize_text_field($paramSettings['conf_currency_symbol']) : '$';
            $this->companyName = isset($paramSettings['conf_company_name']) ? sanitize_text_field($paramSettings['conf_company_name']) : '';
            $this->companyPhone = isset($paramSettings['conf_company_phone']) ? sanitize_text_field($paramSettings['conf_company_phone']) : '';
            $this->companyEmail = isset($paramSettings['conf_company_email']) ? sanitize_email($paramSettings['conf_company_email']) : '';
            $this->paymentCancelledPageId = isset($paramSettings['conf_cancelled_payment_page_id']) ? abs(intval($paramSettings['conf_cancelled_payment_page_id'])) : 0;
            $this->orderConfirmedPageId = isset($paramSettings['conf_confirmation_page_id']) ? abs(intval($paramSettings['conf_confirmation_page_id'])) : 0;
            $this->sendNotifications = isset($paramSettings['conf_send_emails']) ? abs(intval($paramSettings['conf_send_emails'])) : 0;
        }

        /**
         * @return bool
         */
        public function inDebug()
        {
            return ($this->debugMode >= 1 ? true : false);
        }

        /******************************************************************************************/
        /* Default methods                                                                        */
        /******************************************************************************************/

        /**
         * Based on https://stripe.com/docs/checkout#integration-custom
         * @param string $paramCurrentDescription
         * @param string $paramTotalPayNow = '0.00'
         * @return string
         */
        public function getDescriptionHTML($paramCurrentDescription, $paramTotalPayNow = '0.00')
        {
            return $paramCurrentDescription;
        }

        /**
         * @param string $paramOrderCode
         * @param string $paramTotalPayNow
         * @return array(
         *   'payment_completed_transaction_id' => 0, // '0' if no transactions were processed
         *   'currency_code' => '',  // Leave blank if no transactions were processed
         *   'currency_symbol' => '',  // Leave blank if no transactions were processed
         *   'amount' => 0.00,  // Leave blank if no transactions were processed
         *   'errors' => array(), // Array
         *   'debug_messages' => array(),  // Array
         *   'trusted_output_html' => '', // String, leave blank if no data needs to be returned
         * );
         */
        public function getProcessingPage($paramOrderCode, $paramTotalPayNow = '0.00')
        {
            // Set defaults
            $errorMessages = array();
            $debugMessages = array();
            $finalPaymentCompletedTransactionId = 0;

            $validPositiveTotalPayNow = $paramTotalPayNow > 0 ? floatval($paramTotalPayNow) : 0.00;
            if($this->payInCurrencyCode != '')
            {
                // Use other currency and divide amount with other currency rate (if that rate is more than 0)
                $positiveAmountToPay = $this->payInCurrencyRate > 0 ? $validPositiveTotalPayNow / $this->payInCurrencyRate : $validPositiveTotalPayNow;
                $payInCurrencyCode = $this->payInCurrencyCode;
                $payInCurrencySymbol = $this->payInCurrencySymbol;
            } else
            {
                // Use site currency
                $positiveAmountToPay = $validPositiveTotalPayNow;
                $payInCurrencyCode = $this->currencyCode;
                $payInCurrencySymbol = $this->currencySymbol;
            }
            // Round and transform amount to cents
            $positiveAmountToPayInCents = round($positiveAmountToPay, 2) * 100;
            $paymentDescription = $this->companyName.': '.sanitize_text_field($paramOrderCode);


            // Initialize stripe
            $paymentFolderPathWithFileName = $this->conf->getLibrariesPath().'Stripe'.DIRECTORY_SEPARATOR.'init.php';
            require_once $paymentFolderPathWithFileName;

            //$confirmURL = $this->orderConfirmedPageId > 0 ? $this->lang->getTranslatedURL($this->orderConfirmedPageId) : site_url();
            $cancelURL = $this->paymentCancelledPageId > 0 ? $this->lang->getTranslatedURL($this->paymentCancelledPageId) : site_url();

            // Are you writing a plugin that integrates Stripe and embeds our library?
            // Then please use the setAppInfo function to identify your plugin. For example:
            \Stripe\Stripe::setAppInfo(
                'WordPress ' . $this->conf->getExtName(),      //Plugin name
                $this->conf->getPluginSemver(),     //Plugin version
                'http://codecanyon.net/user/KestutisIT',
                'pp_partner_JwOED9wh8Jxw6s'           // Used by Stripe to identify your plugin
            );

            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey(sanitize_text_field($this->privateKey));
            \Stripe\Stripe::setApiVersion('2019-05-16');

            $notifyURL = site_url().'/?__'.$this->conf->getPluginPrefix().'api=1&ext_code='.$this->conf->getExtCode();
            $notifyURL .= '&ext_action=payment-callback&payment_method_id='.$this->paymentMethodId.'&session_id={CHECKOUT_SESSION_ID}';



            $sanitizedOrderCode = sanitize_text_field($paramOrderCode);

            // Create stripe session for checkout
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'unit_amount' => sanitize_text_field($positiveAmountToPayInCents),
                            'currency' => sanitize_text_field($payInCurrencyCode),
                            'product_data' => [
                                'name' => $this->conf->getExtName(),
                                'description' => sanitize_text_field($paymentDescription),

                            ],
                        ],
                        'quantity' => 1,
                    ]],
                    'client_reference_id' => $sanitizedOrderCode,
                    'mode' => 'payment',
                    'success_url' => sanitize_text_field($notifyURL),
                    'cancel_url' => sanitize_text_field($cancelURL),
                ]);

                // Stripe checkout
                $ret = '
                <script src="https://js.stripe.com/v3/"></script>
                <script>
                    var stripe = Stripe(\''.$this->publicKey.'\');

                    stripe.redirectToCheckout({
                        // Make the id field from the Checkout Session creation API response
                        // available to this file, so you can provide it as argument here
                        // instead of the {{CHECKOUT_SESSION_ID}} placeholder.
                        sessionId: \''.$session["id"].'\',
                    }).then(function (result) {
                        // If `redirectToCheckout` fails due to a browser or network
                        // error, display the localized error message to your customer
                        // using `result.error.message`.
                        alert(result.error.message);
                    });

                </script>';
            return array(
                'payment_completed_transaction_id' => '',
                'currency_code' => $payInCurrencyCode,
                'currency_symbol' => $payInCurrencySymbol,
                'amount' => $positiveAmountToPay,
                'errors' => array(),
                'debug_messages' => array(),
                'trusted_output_html' => $ret, // Stripe does not make any output on processing page
            );
        }

        /**
         * Stripe does not use API callback process
         * @return array(
         *   'authorized' => false, // Bool
         *   'order_code' => '', // return '' if no order were processed
         *   'transaction_id' => 0, // '0' if no transactions were processed
         *   'currency_code' => '',  // Leave blank if no transactions were processed
         *   'currency_symbol' => '',  // Leave blank if no transactions were processed
         *   'amount' => 0.00,  // Leave blank if no transactions were processed
         *   'errors' => array(), // Array
         *   'debug_messages' => array(),  // Array
         *   'trusted_output_html' => '', // String, leave blank if no data needs to be returned
         * );
         */
        public function processCallback()
        {

            $errorMessages = array();
            $debugMessages = array();
            $paramOrderCode = '';
            $Verified = false;
            $payInCurrencyCode = '';
            $paramTransactionAmount = '';
            $finalPaymentCompletedTransactionId = 0;
            $action = '';
            $output = '';

            $confirmURL = $this->orderConfirmedPageId > 0 ? $this->lang->getTranslatedURL($this->orderConfirmedPageId) : site_url();
            $cancelURL = $this->paymentCancelledPageId > 0 ? $this->lang->getTranslatedURL($this->paymentCancelledPageId) : site_url();


            $session_id = $_GET['session_id'];
            if($session_id !== false)
            {
                $paymentFolderPathWithFileName = $this->conf->getLibrariesPath().'Stripe'.DIRECTORY_SEPARATOR.'init.php';
                require_once $paymentFolderPathWithFileName;

                $stripe = new \Stripe\StripeClient(
                    $this->privateKey,
                );
                $stripe_response = $stripe->checkout->sessions->retrieve(
                    $session_id,
                );

                if($stripe_response['payment_status'] == 'paid')
                {

                    $paramOrderCode = $stripe_response['client_reference_id'];
                    $payInCurrencyCode = $stripe_response['currency'];

                    $paramTransactionAmount = $stripe_response['amount_total'];
                    $positiveAmountToPay = abs(floatval($paramTransactionAmount));

                    // Create mandatory instances
                    $objOrdersObserver = new \FleetManagement\Models\Order\OrdersObserver($this->conf, $this->lang, $this->settings);
                    $objOrderNotificationsObserver = new \FleetManagement\Models\Order\OrderNotificationsObserver($this->conf, $this->lang, $this->settings);
                    $objInvoicesObserver = new \FleetManagement\Models\Invoice\InvoicesObserver($this->conf, $this->lang, $this->settings);

                    // Get order id
                    $orderId = $objOrdersObserver->getIdByCode($paramOrderCode);
                    if($orderId > 0) {
                        // Create order object
                        $objOrder = new \FleetManagement\Models\Order\Order($this->conf, $this->lang, $this->settings, $orderId);
                        $customerId = $objOrder->getCustomerId();

                        // Create customer object
                        $objCustomer = new \FleetManagement\Models\Customer\Customer($this->conf, $this->lang, $this->settings, $customerId);

                        // We add payment data only to the 'OVERALL' invoice
                        $overallInvoiceId = $objInvoicesObserver->getIdByParams('OVERALL', $orderId, -1);
                        $objOverallInvoice = new \FleetManagement\Models\Invoice\Invoice($this->conf, $this->lang, $this->settings, $overallInvoiceId);

                        $transactionDateI18n = date_i18n(get_option('date_format'), time() + get_option('gmt_offset') * 3600, true);
                        $transactionTimeI18n = date_i18n(get_option('time_format'), time() + get_option('gmt_offset') * 3600, true);
                        $transactionType = $this->lang->getText('LANG_TRANSACTION_TYPE_PAYMENT_TEXT');
                        $transactionAmount = sanitize_text_field($payInCurrencyCode) . ' ' . floatval($positiveAmountToPay);

                        $paymentHTML_ToAppend = '<!-- EXTERNAL TRANSACTION DETAILS -->
                        <br /><br />
                        <h2>Reservation confirmed</h2>
                        <p>Thank you. We received your payment. Your reservation is now confirmed.</p>
                        <table style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; background-color:#eeeeee; width:840px; border:none;" cellpadding="5" cellspacing="1">
                        <tr>
                        <td align="left" width="30%" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">' . $this->lang->escHTML('LANG_TRANSACTION_DATE_TEXT') . '</td>
                        <td align="left" style="background-color:#ffffff; padding-left:5px;">' . $transactionDateI18n . ' ' . $transactionTimeI18n . '</td>
                        </tr>
                        <tr>
                        <td align="left" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">' . $this->lang->escHTML('LANG_TRANSACTION_TYPE_TEXT') . '</td>
                        <td align="left" style="background-color:#ffffff; padding-left:5px;">' . esc_html($transactionType) . '</td>
                        </tr>
                        <tr>
                        <td align="left" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">' . $this->lang->escHTML('LANG_TRANSACTION_AMOUNT_TEXT') . '</td>
                        <td align="left" style="background-color:#ffffff; padding-left:5px;">€' . esc_html(number_format($stripe_response['amount_total']/100, 2)) . '</td>
                        </tr>
                        </table>';

                        // 2. Confirm order, append transaction HTML to final invoice & send confirmation e-mail
                        $objOrder->confirm($orderId, $objCustomer->getEmail());
                        $objOverallInvoice->appendHTML_ToFinalInvoice($paymentHTML_ToAppend);
                        if ($this->sendNotifications) {
                            $objOrderNotificationsObserver->sendOrderConfirmedNotifications($orderId, true);
                        }

                        $finalPaymentCompletedTransactionId = $orderId; // NOTE: For V5 it is the same as order id
                        $action = "PAYMENT_COMPLETED";
                        $output = $confirmURL;
                        $Verified = true;

                        // 3. Add error messages if any
                        $errorMessages = array_merge(
                            $errorMessages,
                            $objOrder->getErrorMessages(),
                            $objOverallInvoice->getErrorMessages(),
                            $objOrdersObserver->getSavedErrorMessages(),
                            $objOrderNotificationsObserver->getSavedErrorMessages()
                        );
                    }
                } else
                {
                    $errorMessages[] = "Order code - {".sanitize_text_field($paramOrderCode)." - is invalid.";
                    $output = $cancelURL;
                }
            }



            return array(
                'authorized' => $Verified,
                'order_code' => $paramOrderCode, // Stripe does not process any orders on callback
                'action' => $action,
                'transaction_id' => $finalPaymentCompletedTransactionId , // Stripe does not process any transactions on callback
                'currency_code' => $payInCurrencyCode,  // Stripe does not process any transactions
                'currency_symbol' => '',  // Stripe does not process any transactions
                'amount' => $paramTransactionAmount,  // Stripe does not process any transactions
                'errors' => $errorMessages,
                'debug_messages' => array(),
                'trusted_output_html' => header('location:' . $output), // Stripe does not make any output on processing page
            );
        }

    }
}