<?php
/**
 * Element must-have interface - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface ElementInterface
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramElementId);
    public function getId();
    public function inDebug();
    public function getDebugMessages();
    public function getOkayMessages();
    public function getErrorMessages();
    public function getDetails($paramPrefillWhenNull = false);
    /**
     * @param array $params
     * @return false|int
     */
    public function save(array $params);
    public function registerForTranslation();
    /**
     * @return false|int
     */
    public function delete();
}