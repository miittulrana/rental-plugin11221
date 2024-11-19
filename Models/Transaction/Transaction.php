<?php
/**
 * Transaction element
 *
 * Correct transactions table structure:
 * (based on https://stackoverflow.com/questions/29688982/derived-account-balance-vs-stored-account-balance-for-a-simple-bank-account/29713230#29713230 )
 *
 * <...>
 * 2.In the Transaction table, do not use negative/positive in the Amount column.
 * Money always has a positive value, there is no such thing as negative twenty dollars (or that you owe me minus fifty dollars,
 * and then working out that the double negatives mean something else).
 *
 * 3.The movement direction, or what you are going to do with the funds, is a separate and discrete fact (to the Transaction.Amount).
 * Which requires a separate column (two facts in one datum breaks Normalisation rules, with the consequence that it introduces complexity into the code).
 * -> Implement a TransactionType column, which is ( D, W ) for Deposit/Withdrawal as your starting point. As the system grows,
 *    simply add ( A, R, w, M ) for Adjustment, Refund, ATM_Withdrawal, Management_Fee, etc.
 * -> No code changes required.
 *
 * <...>
 *
 * ------------------------------------------------------------------------------------------
 * Bank code:
 * A bank code is a code assigned by a central bank, a bank supervisory body or a Bankers Association in a country
 * to all its licensed member banks or financial institutions. The rules vary to a great extent between the countries.
 * Also the name of bank codes varies. In some countries the bank codes can be viewed over the internet,
 * but mostly in the local language.
 *
 * The (national) bank codes differ from the international Bank Identifier Code
 * (BIC/ISO 9362, a normalized code - also known as Business Identifier Code, Bank International Code and SWIFT code).
 * Those countries which use International Bank Account Numbers (IBAN) have mostly integrated the bank code
 * into the prefix of specifying IBAN account numbers. The bank codes also differ from the Bank card code (CSC).
 *
 * Read more: https://en.wikipedia.org/wiki/Bank_code
 *
 * -------------------------------------------------------------------------------------------
 * IBAN:
 * The International Bank Account Number (IBAN) is an internationally agreed system of identifying bank accounts across national borders
 * to facilitate the communication and processing of cross border transactions with a reduced risk of transcription errors.
 * It was originally adopted by the European Committee for Banking Standards (ECBS), and later as an international standard under ISO 13616:1997.
 * The current standard is ISO 13616:2007, which indicates SWIFT as the formal registrar. Initially developed to facilitate payments within the European Union,
 * it has been implemented by most European countries and numerous countries in the other parts of the world, mainly in the Middle East and in the Caribbean.
 * As of February 2016, 69 countries were using the IBAN numbering system.
 *
 * The IBAN consists of up to 34 alphanumeric characters comprising: a country code; two check digits;
 * and a number that includes the domestic bank account number, branch identifier, and potential routing information.
 * The check digits enable a sanity check of the bank account number to confirm its integrity before submitting a transaction.
 *
 * Read more: https://en.wikipedia.org/wiki/International_Bank_Account_Number
 *
 * -------------------------------------------------------------------------------------------
 * SWIFT code:
 * The Society for Worldwide Interbank Financial Telecommunication (SWIFT) provides a network that enables financial institutions worldwide
 * to send and receive information about financial transactions in a secure, standardized and reliable environment.
 * SWIFT also sells software and services to financial institutions, much of it for use on the SWIFTNet Network, and ISO 9362.
 * Business Identifier Codes (BICs, previously Bank Identifier Codes) are popularly known as "SWIFT codes".
 *
 * The majority of international interbank messages use the SWIFT network. As of 2015, SWIFT linked more than 11,000 financial institutions
 * in more than 200 countries and territories, who were exchanging an average of over 15 million messages per day
 * (compared to an average of 2.4 million daily messages in 1995). SWIFT transports financial messages in a highly secure way,
 * but does not hold accounts for its members and does not perform any form of clearing or settlement.
 *
 * SWIFT does not facilitate funds transfer: rather, it sends payment orders, which must be settled by correspondent accounts
 * that the institutions have with each other. Each financial institution, to exchange banking transactions,
 * must have a banking relationship by either being a bank or affiliating itself with one (or more)
 * so as to enjoy those particular business features.
 *
 * SWIFT is a cooperative society under Belgian law owned by its member financial institutions with offices around the world.
 * SWIFT headquarters, designed by Ricardo Bofill Taller de Arquitectura are in La Hulpe, Belgium, near Brussels.
 * The chairman of SWIFT is Yawar Shah,[2] originally from Pakistan,[3] and its CEO is Gottfried Leibbrandt, originally from the Netherlands.
 * SWIFT hosts an annual conference every year, called Sibos, specifically aimed at the financial services industry.
 *
 * Read more: https://en.wikipedia.org/wiki/Society_for_Worldwide_Interbank_Financial_Telecommunication
 *
 * -------------------------------------------------------------------------------------------
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Transaction;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\Log\Log;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Transaction extends AbstractStack implements StackInterface, ElementInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $settings		            = array();
    private $debugMode 	                = 0;
    private $transactionId              = 0;
    private $shortDateFormat            = "m/d/Y";
    private $companyCountryCode         = '';
    private $currencySymbolLocation     = 0;

    /**
     * Transaction constructor.
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramTransactionId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramTransactionId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        // Set transaction id
        $this->transactionId = StaticValidator::getValidPositiveInteger($paramTransactionId, 0);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
        $this->companyCountryCode = StaticValidator::getValidSetting($paramSettings, 'conf_company_country_code', "textval", "");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    private function getDataFromDatabaseById($paramTransactionId, $paramColumns = array('*'))
    {
        $validTransactionId = StaticValidator::getValidPositiveInteger($paramTransactionId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);

        // V5 workaround
        // TODO: REMOVE IN V6
        $from = array(
            'transaction_id', 'external_transaction_id',
            'transaction_type', 'order_id',
            'transaction_status', 'transaction_timestamp'
        );
        $to = array(
            'booking_id AS transaction_id', 'payment_transaction_id AS external_transaction_id',
            'booking_id AS order_id',
            "'PAYMENT' AS transaction_type",
            "IF(payment_successful='1', 'COMPLETED', 'PENDING') AS transaction_status", 'booking_timestamp AS transaction_timestamp'
        );
        $validSelect = str_replace($from, $to, $validSelect);

        $sqlQuery = "
            SELECT {$validSelect}, payment_transaction_id AS external_transaction_id,
            booking_id AS transaction_id, 'PAYMENT' AS transaction_type,
            IF(payment_successful='1', 'COMPLETED', 'PENDING') AS transaction_status,
            '0.0.0.0' AS transaction_ip,
            '0.0.0.0' AS transaction_real_ip
            FROM {$this->conf->getPrefix()}bookings
            WHERE booking_id='{$validTransactionId}'
        ";
        $transactionData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $transactionData;
    }

    public function getId()
    {
        return $this->transactionId;
    }

    public function getExternalId()
    {
        $externalTransactionId = "";
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('external_transaction_id'));
        if(!is_null($transactionData))
        {
            $externalTransactionId = $transactionData['external_transaction_id'];
        }

        return $externalTransactionId;
    }

    public function getType()
    {
        $transactionType = "";
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('transaction_type'));
        if(!is_null($transactionData))
        {
            $transactionType = $transactionData['transaction_type'];
        }

        return $transactionType;
    }

    public function getOrderId()
    {
        $orderId = 0;
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('order_id'));
        if(!is_null($transactionData))
        {
            $orderId = $transactionData['order_id'];
        }

        return $orderId;
    }

    public function getPaymentMethodCode()
    {
        $paymentMethodCode = "";
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('payment_method_code'));
        if(!is_null($transactionData))
        {
            $paymentMethodCode = stripslashes($transactionData['payment_method_code']); // Make raw
        }

        return $paymentMethodCode;
    }

    public function isPayment()
    {
        $isPayment = false;
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('transaction_type'));
        if(!is_null($transactionData))
        {
            $isPayment = $transactionData['transaction_type'] == "PAYMENT" ? true : false;
        }

        return $isPayment;
    }

    public function isReversal()
    {
        $isReversal = false;
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('transaction_type'));
        if(!is_null($transactionData))
        {
            $isReversal = $transactionData['transaction_type'] == "REVERSAL" ? true : false;
        }

        return $isReversal;
    }

    public function isRefund()
    {
        $isRefund = false;
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('transaction_type'));
        if(!is_null($transactionData))
        {
            $isRefund = $transactionData['transaction_type'] == "REFUND" ? true : false;
        }

        return $isRefund;
    }

    public function isPending()
    {
        $isPending = false;
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('transaction_status'));
        if(!is_null($transactionData))
        {
            $isPending = $transactionData['transaction_status'] == "PENDING" ? true : false;
        }

        return $isPending;
    }

    public function isCompleted()
    {
        $isCompleted = false;
        $transactionData = $this->getDataFromDatabaseById($this->transactionId, array('transaction_status'));
        if(!is_null($transactionData))
        {
            $isCompleted = $transactionData['transaction_status'] == "COMPLETED" ? true : false;
        }

        return $isCompleted;
    }

    /**
     * Used as a initializer and data puller of existing transaction BEFORE search engine functions
     * @param bool $paramPrefillWhenNull
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->transactionId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['external_transaction_id'] = stripslashes($ret['external_transaction_id']);
            $ret['transaction_type'] = stripslashes($ret['transaction_type']);
            $ret['payer_email'] = stripslashes($ret['payer_email']);
            $ret['payment_method_code'] = stripslashes($ret['payment_method_code']);
            $ret['transaction_status'] = stripslashes($ret['transaction_status']);
            $ret['transaction_ip'] = stripslashes($ret['transaction_ip']);
            $ret['transaction_real_ip'] = stripslashes($ret['transaction_real_ip']);
            $ret['blog_id'] = $this->conf->getBlogId();
            $date_transaction = date("Y-m-d");
            $ret['transaction_timestamp']  = strtotime($date_transaction);
        } else if($paramPrefillWhenNull === true)
        {
            // Make blank data
            $ret = array();
            $ret['transaction_id'] = 0;
            $ret['external_transaction_id'] = '';
            $ret['transaction_type'] = '';
            $ret['order_id'] = 0;
            $ret['payer_name'] = '';
            $ret['payer_email'] = '';
            $ret['payment_method_code'] = '';
            $ret['transaction_status'] = 'PENDING';
            $ret['transaction_timestamp'] = 0;
            $ret['transaction_ip'] = '0.0.0.0';
            $ret['transaction_real_ip'] = '0.0.0.0';
            $ret['blog_id'] = $this->conf->getBlogId();
        }

        if(!is_null($ret) || $paramPrefillWhenNull === true)
        {
            // No translations for transactions table. It has to be like that - what was generated, that was ok
            $payerEmailHTML = '<a href="mailto:'.esc_attr($ret['payer_email']).'">'.esc_html($ret['payer_email']).'</a>';

            switch($ret['transaction_type'])
            {
                case "PAYMENT":
                    $transactionTypeText = $this->lang->getText('LANG_TRANSACTION_TYPE_PAYMENT_TEXT');
                    break;

                case "REVERSAL":
                    $transactionTypeText = $this->lang->getText('LANG_TRANSACTION_TYPE_REVERSAL_TEXT');
                    break;

                case "REFUND":
                    $transactionTypeText = $this->lang->getText('LANG_TRANSACTION_TYPE_REFUND_TEXT');
                    break;

                default:
                    $transactionTypeText = "";
                    break;
            }

            if($ret['transaction_timestamp'] > 0)
            {
                $transactionDateI18n = date_i18n(get_option('date_format'), $ret['transaction_timestamp'] + get_option('gmt_offset') * 3600, true);
                $transactionTimeI18n = date_i18n(get_option('time_format'), $ret['transaction_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $transactionDateI18n = '';
                $transactionTimeI18n = '';
            }

            // Extend $ret
            $ret['trusted_payer_email_html'] = $ret['payer_email'] != '' ? $payerEmailHTML : '';

            if($ret['transaction_timestamp'] > 0)
            {
                $ret['transaction_date'] = date_i18n($this->shortDateFormat, $ret['transaction_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['transaction_time'] = date_i18n('H:i:s', $ret['transaction_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['transaction_date'] = '';
                $ret['transaction_time'] = '';
            }

            switch($ret['transaction_status'])
            {
                case "PENDING":
                    $transactionStatusText = $this->lang->getText('LANG_TRANSACTION_STATUS_PENDING_TEXT');
                    $ret['transaction_status_color'] = "blue";
                    break;

                case "COMPLETED":
                    $transactionStatusText = $this->lang->getText('LANG_TRANSACTION_STATUS_COMPLETED_TEXT');
                    $ret['transaction_status_color'] = "green";
                    break;
                default:
                    $transactionStatusText = "";
                    $ret['transaction_status_color'] = "black";
                    break;
            }

            $ret['transaction_type_text'] = $transactionTypeText;
            $ret['transaction_date_i18n'] = $transactionDateI18n;
            $ret['transaction_time_i18n'] = $transactionTimeI18n;
            $ret['transaction_status_text'] = $transactionStatusText;

        }

        return $ret;

    }

    /**
     * Save transaction data
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        // Not yet exist
        $saved = false;

        return $saved;
    }

    public function registerForTranslation()
    {
        // Not used. Transaction has nothing to translate
    }

    /**
     * Element-specific method
     * @param $params
     * @param array $errorMessages
     * @param array $debugMessages
     * @return bool|int
     */
    public function createLog($params, array $errorMessages, array $debugMessages)
    {
        // Get details
        $transactionDetails = $this->getDetails(true);

        $logParams = array(
            'action' => 'payment-charge',

            'dimension_1' => $this->lang->getText('LANG_TRANSACTION_PAYER_EMAIL_TEXT'),
            'value_1' => $transactionDetails['payer_email'],

            'errors' => isset($arrResponse['error_messages']) ? implode("\n", $errorMessages) : "",
            'debug_log' => isset($arrResponse['debug_messages']) ? implode("\n", $debugMessages) : "", // Note: do not translate debug
            'status' => $transactionDetails['transaction_status'] == "COMPLETED" ? 2 : 1, // 1 - FAILED, 2 - PASSED
        );
        $objLog = new Log($this->conf, $this->lang, $this->settings, 0);
        $created = $objLog->save($logParams);

        return $created;
    }

    public function delete()
    {
        $deleted = false;

        $transactionData = $this->getDataFromDatabaseById($this->transactionId);
        // If there exists unpaid order under this order id
        if(!is_null($transactionData) && $transactionData['payment_successful'] == 0)
        {
            $deleted = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  payment_successful='0', payment_transaction_id='',
                  payer_email=''
                  WHERE booking_id='{$transactionData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
            ");
        }

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_TRANSACTION_NO_D_DELETION_ERROR_TEXT'), $this->transactionId);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_TRANSACTION_NO_D_DELETED_TEXT'), $this->transactionId);
        }

        return $deleted;
    }
}