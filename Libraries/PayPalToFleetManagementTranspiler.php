<?php
/**
 * PayPal Payment Library to Fleet Management Transpiler class
 *
 * Transpiler - It's a source-to-source compiler, it translates source code
 * from one language to another (or to another version of the same language).
 *
 * @note - Library transpiler class should not have a namespace, because all transpilers are loaded as dynamic libraries
 * and that would anyway require a full-qualified namespaces for each transpiler constructor. So to avoid that,
 * we just do not use namespaces for transpilers at all.
 *
 * @link - https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/#id08CTB0S055Z
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

if(!class_exists('PayPalToFleetManagementTranspiler'))
{
    class PayPalToFleetManagementTranspiler implements \FleetManagementPaymentInterface
    {
        const PAYMENT_METHOD                = true;
        const PRODUCTION_HOST               = 'www.paypal.com';
        const SANDBOX_HOST                  = 'www.sandbox.paypal.com';
        protected $conf                     = null;
        protected $lang                     = null;
        protected $settings                 = array();
        protected $debugMode                = 0;

        // Array holds the fields to submit to <HOST>
        protected $fields                   = array();
        protected $use_ssl                  = true;

        protected $paymentMethodId          = 0;
        protected $paymentMethodCode        = 'PAYPAL';
        protected $businessEmail            = '';
        protected $payInCurrencyRate        = 1.0000;
        protected $payInCurrencyCode        = 'USD';
        protected $payInCurrencySymbol      = '$';
        protected $currencyCode             = 'USD';
        protected $currencySymbol           = '$';
        // www.<SANDBOX_HOST>.com (true) or www.<PRODUCTION_HOST>.com (false)
        protected $useSandbox               = false;
        protected $checkCertificate         = false;
        protected $companyName              = '';
        protected $companyPhone             = '';
        protected $companyEmail             = '';
        protected $paymentCancelledPageId   = 0;
        protected $orderConfirmedPageId     = 0;
        protected $sendNotifications        = 0;

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
            // NOTE: PayPal does not use nor 'private_key', nor 'public_key' parameters
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
            $sanitizedOrderCode = sanitize_text_field($paramOrderCode);
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

            // Process to order page
            if ($this->businessEmail != "")
            {
                $confirmURL = $this->orderConfirmedPageId > 0 ? $this->lang->getTranslatedURL($this->orderConfirmedPageId) : site_url();
                $cancelURL = $this->paymentCancelledPageId > 0 ? $this->lang->getTranslatedURL($this->paymentCancelledPageId) : site_url();
                $notifyURL = site_url().'/?__'.$this->conf->getPluginPrefix().'api=1&ext_code='.$this->conf->getExtCode();
                $notifyURL .= '&ext_action=payment-callback&payment_method_id='.$this->paymentMethodId;

                $this->addField('rm','2');           // Return method = POST
                $this->addField('cmd','_xclick');
                // If there is a valid business email set, process the transfer to PayPal payment page
                $this->addField('business', $this->businessEmail);
                $this->addField('notify_url', $notifyURL);
                $this->addField('return', $notifyURL);
                $this->addField('cancel_return', $cancelURL);
                $this->addField('item_name', $this->companyName);
                $this->addField('invoice', $sanitizedOrderCode);
                $this->addField('currency_code', $payInCurrencyCode);
                $this->addField('amount', number_format($positiveAmountToPay, 2, '.', ''));
                //$this->objPayPal->submitPayPalPost(); // submit the fields to paypal
                //$this->objPayPal->dumpFields();      // for debugging, output a table of all the fields
            }

            $output = '<h2>'.$this->lang->escHTML('LANG_PAYMENT_PROCESSING_TEXT').'</h2>
                    <div class="info-content">
                        '.$this->lang->escHTML('LANG_ORDER_CODE2_TEXT').': '.esc_html($sanitizedOrderCode).'.<br />
                        '.$this->lang->escHTML('LANG_ORDER_PLEASE_WAIT_UNTIL_WILL_BE_PROCESSED_TEXT').'
                        <form method="post" name="paypal_form" action="'.$this->getFormSubmitURL().'">
                        '.$this->getFormFields().'
                        </form>
                        <script type="text/javascript">setTimeout("document.paypal_form.submit()",1500);</script>
                    </div>';

            return array(
                'payment_completed_transaction_id' => 0, // PayPal does not process any transactions
                'currency_code' => $payInCurrencyCode,
                'currency_symbol' => $payInCurrencySymbol,
                'amount' => $positiveAmountToPay,
                'errors' => array(),
                'debug_messages' => array(),
                'trusted_output_html' => $output, // PayPal outputs HTML code with Javascript on processing page
            );
        }

        /**
         * We prefer not to check certificate in this method, because we don't want to have a buggy plugin because of expired certificate or so,
         * there is much less damage in getting marked as paid without certificate authorization than not making it paid at all
         * @return array(
         *   'authorized' => false, // Bool
         *   'order_code' => '', // Leave blank if no order were processed
         *   'action' => '', // Leave blank if no order were processed
         *   'transaction_id' => 0, // Leave '0' if no transactions were processed
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
            $ipnVerified = false;
            $canProcess = true;
            $paramTransactionCurrency = '';
            $paramTransactionAmount = '';
            $objPayPalIPN = null;
            $finalTransactionId = 0;
            $action = '';
            $output = '';

            // Process API
            $paymentFolderPathWithFileName = $this->conf->getLibrariesPath().'PayPal'.DIRECTORY_SEPARATOR.'PayPalIPN.php';
            if (is_readable($paymentFolderPathWithFileName))
            {
                require_once $paymentFolderPathWithFileName;
                $objPayPalIPN = new \PayPalIPN($this->getWebHost());

                /*
                 * API CALLBACK PATH: https://yourdomain.com/?__[PLUGIN_PREFIX]api=1&ext_code=[EXT_CODE]&ext_action=payment-callback&payment_method_id=1
                 * i.e.
                 *      https://nativerental.com/?__fleet_management_api=1&ext_code=CAR_RENTAL&ext_action=payment-callback&payment_method_id=1
                 * NOTES:
                 *      Since this script is executed on the back end between the PayPal server and this
                 *      script, you will want to log errors to a file or email. Do not try to use echo
                 *      or print--it will not work!
                 */
                try
                {
                    $objPayPalIPN->requirePostMethod();
                    $ipnVerified = $objPayPalIPN->processIpn();
                } catch (Exception $e)
                {
                    $canProcess = false;
                    $errorMessages[] = $e->getMessage();
                }
            }

            /*
            The processIpn() method returned true if the IPN was "VERIFIED" and false if it
            was "INVALID".
            */
            if (($ipnVerified && $canProcess) || $this->checkCertificate == false)
            {
                /*
                Once you have a verified IPN you need to do a few more checks on the POST
                fields--typically against data you stored in your database during when the
                end user made a purchase (such as in the "success" page on a web payments
                standard button). The fields PayPal recommends checking are:

                    1. Check the $_POST['payment_status'] is "Completed"
                    2. Check that $_POST['txn_id'] has not been previously processed
                    3. Check that $_POST['invoice'] is your Invoice Id is correct
                    4. Check that $_POST['receiver_email'] is your Primary PayPal email
                    5. Check that $_POST['payment_amount'] and $_POST['payment_currency']
                       are correct

                Since implementations on this varies, I will leave these checks out of this
                example and just send an email using the getTextReport() method to get all
                of the details about the IPN with 'Verified IPN' and $listener->getTextReport();
                */

                // All PayPal IPN params listed here: https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/
                $ipnData = $objPayPalIPN->getIPN_Data();
                $paramOrderCode = isset($ipnData['invoice']) ? $ipnData['invoice'] : '';
                $validOrderCode = esc_html(sanitize_text_field($paramOrderCode));
                $paramPaymentStatus = isset($ipnData['payment_status']) ? $ipnData['payment_status'] : '';
                $sanitizedPaymentStatus = sanitize_text_field($paramPaymentStatus);
                $validPaymentStatus = esc_html($sanitizedPaymentStatus);
                $paramExternalTransactionId = isset($ipnData['txn_id']) ? $ipnData['txn_id'] : '';
                $paramTransactionCurrency = isset($ipnData['mc_currency']) ? $ipnData['mc_currency'] : '';
                $paramTransactionAmount = isset($ipnData['mc_gross']) ? $ipnData['mc_gross'] : '';
                $paramPayerExternalId = isset($ipnData['payer_id']) ? $ipnData['payer_id'] : ''; // Note: will be used in future
                $paramPayerName = isset($ipnData['first_name'], $ipnData['last_name']) ? $ipnData['first_name'].' '.$ipnData['last_name'] : ''; // Note: will be used in future
                $paramPayerCountryCode = isset($ipnData['address_country_code']) ? $ipnData['address_country_code'] : ''; // Note: will be used in future
                $paramPayerPhone = isset($ipnData['contact_phone']) ? $ipnData['contact_phone'] : ''; // Note: will be used in future
                $paramPayerEmail = isset($ipnData['payer_email']) ? $ipnData['payer_email'] : '';

                // Add to debug (V5-only)
                $debugMessages[] = "Payer external id: ". sanitize_text_field($paramPayerExternalId);
                $debugMessages[] = "Payer name: ". sanitize_text_field($paramPayerName);
                $debugMessages[] = "Payer country code: ". sanitize_text_field($paramPayerCountryCode);
                $debugMessages[] = "Payer payer phone: ". sanitize_text_field($paramPayerPhone);
                $debugMessages[] = "Payer e-mail: ". sanitize_text_field($paramPayerEmail);

                // Convert transaction amount to valid positive
                $validPositiveTransactionAmount = abs(floatval($paramTransactionAmount));

                // Create mandatory instances
                $objOrdersObserver = new \FleetManagement\Models\Order\OrdersObserver($this->conf, $this->lang, $this->settings);
                $objOrderNotificationsObserver = new \FleetManagement\Models\Order\OrderNotificationsObserver($this->conf, $this->lang, $this->settings);
                $objInvoicesObserver = new \FleetManagement\Models\Invoice\InvoicesObserver($this->conf, $this->lang, $this->settings);

                // Get order id
                $orderId = $objOrdersObserver->getIdByCode($paramOrderCode);

                // Create other instances
                $objOrder = new \FleetManagement\Models\Order\Order(
                    $this->conf, $this->lang, $this->settings, $orderId
                );
                // We add payment data only to the 'OVERALL' invoice
                $overallInvoiceId = $objInvoicesObserver->getIdByParams('OVERALL', $orderId, -1);
                $objOverallInvoice = new \FleetManagement\Models\Invoice\Invoice($this->conf, $this->lang, $this->settings, $overallInvoiceId);
                if($objOrder->isUpcoming() && $objOrder->isCancelled() === false)
                {
                    if($paramPaymentStatus == "Pending")
                    {
                        /* There are two possible reasons of getting "Pending" IPN payment status:
                        1. Seller account has option to accept or decline payment
                        In this case you need to login as seller and accept payment and you will get another IPN with payment_status=Completed
                        Steps:
                        1.login to your developer central
                        2.press "test accounts" on the left
                        3.select seller account and press "Enter sandbox test site" on the bottom
                        4.You should see seller dashboard with option to accept or decline any payment
                            (you might need to re-login at this step using seller test account credentials)

                        2. Payment review is enabled
                        Solution:
                        1. Login to your Developer Central.
                        2. Click on Test Accounts tab on the left. You should have created these test accounts for testing on Sandbox.
                        3. Find the column ‘Payment Review’.
                        4. Find the sandbox account you are using and click on “Enabled” in the ‘Payment Review’ column.
                            This should change to “Disabled” and now payments funded from your balance and credit card will complete instantly.
                        */

                        // Do nothing. Just log this transaction, and wait for the final IPN call with 'payment_status' == "COMPLETED"
                        // NOTE: If you have payment reviews enabled, make sure that your payment method expiration date is set to infinite,
                        // or the order will get expired
                    } elseif($paramPaymentStatus == "Completed")
                    {
                        // 1. Get the append HTML
                        $sanitizedExternalTransactionId = sanitize_text_field($paramExternalTransactionId);
                        $transactionDateI18n = date_i18n(get_option('date_format'), time() + get_option('gmt_offset') * 3600, true);
                        $transactionTimeI18n = date_i18n(get_option('time_format'), time() + get_option('gmt_offset') * 3600, true);
                        $transactionType = $this->lang->getText('LANG_TRANSACTION_TYPE_PAYMENT_TEXT');
                        $sanitizedTransactionAmount = sanitize_text_field($paramTransactionCurrency).' '.$validPositiveTransactionAmount;
                        $sanitizedPayerEmail = sanitize_text_field($paramPayerEmail);

                        $paymentHTML_ToAppend = '<!-- EXTERNAL TRANSACTION DETAILS -->
<br /><br />
<table style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; background-color:#eeeeee; width:840px; border:none;" cellpadding="5" cellspacing="1">
<tr>
<td align="left" width="30%" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">'.$this->lang->escHTML('LANG_TRANSACTION_EXTERNAL_ID_TEXT').'</td>
<td align="left" style="background-color:#ffffff; padding-left:5px;">'.esc_html($sanitizedExternalTransactionId).'</td>
</tr>
<tr>
<td align="left" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">'.$this->lang->escHTML('LANG_TRANSACTION_DATE_TEXT').'</td>
<td align="left" style="background-color:#ffffff; padding-left:5px;">'.esc_html($transactionDateI18n).' '.esc_html($transactionTimeI18n).'</td>
</tr>
<tr>
<td align="left" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">'.$this->lang->escHTML('LANG_TRANSACTION_TYPE_TEXT').'</td>
<td align="left" style="background-color:#ffffff; padding-left:5px;">'.esc_html($transactionType).'</td>
</tr>
<tr>
<td align="left" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">'.$this->lang->escHTML('LANG_TRANSACTION_AMOUNT_TEXT').'</td>
<td align="left" style="background-color:#ffffff; padding-left:5px;">'.esc_html($sanitizedTransactionAmount).'</td>
</tr>
<tr>
<td align="left" style="font-weight:bold; background-color:#ffffff; padding-left:5px;">'.$this->lang->escHTML('LANG_TRANSACTION_PAYER_EMAIL_TEXT').'</td>
<td align="left" style="background-color:#ffffff; padding-left:5px;">'.esc_html($sanitizedPayerEmail).'</td>
</tr>
</table>';
                        $appended = $objOverallInvoice->appendHTML_ToFinalInvoice($paymentHTML_ToAppend);

                        $markedAsPaid = $objOrder->confirm($paramExternalTransactionId, $paramPayerEmail);
                        $emailNotificationSent = false;
                        if($this->sendNotifications == 1)
                        {
                            $emailNotificationSent = $objOrderNotificationsObserver->sendOrderConfirmedNotifications($objOrder->getId(), true);
                        }
                        if($markedAsPaid && $this->sendNotifications == 1 && $emailNotificationSent === false)
                        {
                            $errorMessages[] = 'Failed: Reservation was marked as paid, but system was unable to send the confirmation email!';
                        } else if($markedAsPaid === false)
                        {
                            $errorMessages[] = 'Failed: Reservation was not marked as paid!';
                        } else if($appended === false)
                        {
                            $errorMessages[] = 'Failed: Transaction data was not appended to invoice!';
                        }

                        $finalTransactionId = $orderId; // NOTE: In future it should be a separate 'transactions' table row ID
                        $action = "PAYMENT_COMPLETED";
                        $output = $this->orderConfirmedPageId > 0 ? $this->lang->getTranslatedURL($this->orderConfirmedPageId) : site_url();
                    } else if ($paramPaymentStatus == "Reversed")
                    {
                        // Reverse transactions do not cancel the orders, but instead of that - the pending payment comes back again in
                        // Unconfirm order
                        $unconfirmed = $objOrder->unconfirm();
                        // NOTE: Do not send any e-mails on reversals

                        if($unconfirmed === false)
                        {
                            $errorMessages[] = 'Failed: Reservation was not unconfirmed!';
                        }


                        $finalTransactionId = $orderId; // NOTE: In future it should be a separate 'transactions' table row ID
                        $action = "REVERSAL_COMPLETED";
                    } else if ($paramPaymentStatus == "Refunded")
                    {
                        $refunded = $objOrder->refund();
                        if($this->sendNotifications == 1)
                        {
                            $objOrderNotificationsObserver->sendOrderCancelledNotifications($orderId, true);
                        }

                        if($refunded === false)
                        {
                            $errorMessages[] = 'Failed: Reservation was not refunded!';
                        }

                        $finalTransactionId = $orderId; // NOTE: In future it should be a separate 'transactions' table row ID
                        $action = "REFUND_COMPLETED";
                    } else
                    {
                        $errorMessages[] = "Payment status - ".$validPaymentStatus." - is unknown.";
                    }
                } else
                {
                    $output = $this->paymentCancelledPageId > 0 ? $this->lang->getTranslatedURL($this->paymentCancelledPageId) : site_url();
                    $errorMessages[] = "Order code - {$validOrderCode} - is invalid.";
                }
            } else
            {
                $output = $this->paymentCancelledPageId > 0 ? $this->lang->getTranslatedURL($this->paymentCancelledPageId) : site_url();
                $errorMessages[] = "Invalid IPN";
                /*
                An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
                a good idea to have a developer or sys admin manually investigate any
                invalid IPN with 'Invalid IPN' and, $listener->getTextReport().
                */
            }

            $debugMessages[] = $ipnVerified ? "Verified IPN" : "Invalid IPN";
            if(!is_null($objPayPalIPN))
            {
                $debugMessages[] = $objPayPalIPN->getTextReport();
            }

            return array(
                'authorized' => $ipnVerified,
                'order_code' => $paramOrderCode,
                'action' => $action,
                'transaction_id' => $finalTransactionId,
                'currency_code' => $paramTransactionCurrency,
                'currency_symbol' => '',  // Leave blank if no transactions were processed
                'amount' => $paramTransactionAmount,
                'errors' => $errorMessages,
                'debug_messages' => $debugMessages,
                'trusted_output_html' => header('location:' . $output), // PayPal does not make any output on callback
            );
        }
        /******************************************************************************************/

        private function getWebHost()
        {
            if ($this->useSandbox)
            {
                return static::SANDBOX_HOST;
            } else
            {
                return static::PRODUCTION_HOST;
            }
        }

        private function addField($field, $value)
        {
            // adds a key=>value pair to the fields array, which is what will be
            // sent to paypal as POST variables.  If the value is already in the
            // array, it will be overwritten.
            $this->fields[sanitize_key($field)] = sanitize_text_field($value);
        }

        private function getFormSubmitURL()
        {
            if ($this->use_ssl)
            {
                $uri = 'https://'.$this->getWebHost().'/cgi-bin/webscr';
            } else
            {
                $uri = 'http://'.$this->getWebHost().'/cgi-bin/webscr';
            }

            return $uri;
        }

        private function getFormFields()
        {
            $ret = "";
            foreach ($this->fields as $name => $value)
            {
                $ret .= '<input type="hidden" name="'.esc_attr($name).'" value="'.esc_attr($value).'">';
            }

            return $ret;
        }
    }
}