<?php
/**
 * Payment must-have interface - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
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

if(!interface_exists('FleetManagementPaymentInterface'))
{
    interface FleetManagementPaymentInterface
    {
        /**
         * @param ConfigurationInterface &$paramConf
         * @param LanguageInterface &$paramLang
         * @param array $paramSettings
         * @param array $paramPaymentMethodDetails
         */
        public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, array $paramPaymentMethodDetails);

        /**
         * @return bool
         */
        public function inDebug();

        /**
         * @param string $paramCurrentDescription
         * @param string $paramTotalPayNow = '0.00'
         * @return string
         */
        public function getDescriptionHTML($paramCurrentDescription, $paramTotalPayNow = '0.00');

        /**
         * @param string $paramOrderCode
         * @param string $paramTotalPayNow
         * @return array(
         *   'payment_completed_transaction_id' => 0, // '0' if no transactions were processed
         *   'currency_symbol' => '',  // Leave blank if no transactions were processed
         *   'currency_code' => '',  // Leave blank if no transactions were processed
         *   'amount' => 0.00,  // Leave blank if no transactions were processed
         *   'errors' => array(), // Array
         *   'debug_messages' => array(),  // Array
         *   'trusted_output_html' => '', // String, leave blank if no data needs to be returned
         * );
         */
        public function getProcessingPage($paramOrderCode, $paramTotalPayNow = '0.00');

        /**
         * @return string
         * @return array(
         *   'authorized' => false, // Bool
         *   'order_code' => '', // Leave blank if no order were processed
         *   'action' => '', // Leave blank if no order were processed
         *   'transaction_id' => 0, // return '0' if no transactions were processed
         *   'currency_symbol' => '',  // Leave blank if no transactions were processed
         *   'currency_code' => '',  // Leave blank if no transactions were processed
         *   'amount' => 0.00,  // Leave blank if no transactions were processed
         *   'errors' => array(), // Array
         *   'debug_messages' => array(),  // Array
         *   'trusted_output_html' => '', // String, leave blank if no data needs to be returned
         * );
         */
        public function processCallback();
    }
}