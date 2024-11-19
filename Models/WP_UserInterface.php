<?php
/**
 * WP User (Element) must-have interface (without settings array) - must have a single WP User Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface WP_UserInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramWP_UserId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramWP_UserId);

    /**
     * @return int
     */
    public function getId();

    /**
     * Debug status
     * @return bool
     */
    public function inDebug();

    /**
     * @return string
     */
    public function getDisplayName();
}