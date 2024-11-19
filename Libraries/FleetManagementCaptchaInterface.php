<?php
/**
 * Validation must-have interface
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

if(!interface_exists('FleetManagementCaptchaInterface'))
{
    interface FleetManagementCaptchaInterface
    {
        public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings);
        public function inDebug();
        public function getDebugMessages();
        public function getOkayMessages();
        public function getErrorMessages();
        public function isValid($paramResponse);
    }
}