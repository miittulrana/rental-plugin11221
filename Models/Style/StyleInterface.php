<?php
/**
 * Configuration class dependant on template
 * Note 1: This is a root class and do not depend on any other plugin classes

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Style;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface StyleInterface
{
    // Constructor
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramStyleName);

    // Setters
    public function setSitewideStyles();
    public function setCompatibilityStyles();
    public function setLocalStyles();

    // Debug
    public function inDebug();

    // Getters
    public function getParentThemeCompatibilityCSS_URL();
    public function getCurrentThemeCompatibilityCSS_URL();
    public function getSitewideCSS_URL();
    public function getLocalCSS_URL();
}