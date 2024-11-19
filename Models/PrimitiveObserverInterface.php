<?php
/**
 * Root interface for classes needed for setup, install etc., that can't
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface PrimitiveObserverInterface
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang);
    public function inDebug();
}