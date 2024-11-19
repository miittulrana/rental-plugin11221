<?php
/**
 * Post-Type must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PostType;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface PostTypeInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param string $paramPostType
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramPostType);

    public function inDebug();
    public function getPostType();

    /**
     * Creating a function to create our page post type
     * @param string $paramSlug
     * @param int $paramMenuPosition
     */
    public function register($paramSlug, $paramMenuPosition);

    /**
     * @return bool
     */
    public function deleteAllPosts();
}