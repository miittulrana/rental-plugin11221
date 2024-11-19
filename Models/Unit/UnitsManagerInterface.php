<?php
/**
 * Units must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Unit;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface UnitsManagerInterface
{
    public function __construct(
        ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings,
        $paramElementSKU, $paramTimestampFrom, $paramTimestampTill
    );
    public function inDebug();
    public function getTotalUnits($paramLocationUniqueIdentifier = "", $paramIgnoreFromOrderId = 0); // SQL optimized method
    public function getTotalUnitsAvailable($paramLocationUniqueIdentifier = "", $paramIgnoreFromOrderId = 0);
    public function getMaxAllowedUnitsForOrder($paramLocationUniqueIdentifier = "", $paramIgnoreFromOrderId = 0);
}