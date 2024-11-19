<?php
/**
 * Role must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface RoleInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang);

    /**
     * Debug status
     * @return bool
     */
    public function inDebug();

    /**
     * @return string
     */
    public function getRoleName();

    /**
     * @return array
     */
    public function getCapabilities();

    /**
     * @return bool
     */
    public function add();

    /**
     * @return void
     */
    public function remove();

    /**
     * @return void
     */
    public function addCapabilities();

    /**
     * @return void
     */
    public function removeCapabilities();
}