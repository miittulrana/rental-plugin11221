<?php
/**
 * Table must-have interface - must have a blog Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;

interface TableInterface
{
    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramBlogId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId);

    /**
     * @return bool
     */
    public function inDebug();

    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return bool
     */
    public function checkTableExists();

    /**
     * @return int
     */
    public function getBlogId();

    /**
     * @return void
     */
    public function flushMessages();

    /**
     * @return array
     */
    public function getDebugMessages();

    /**
     * @return array
     */
    public function getOkayMessages();

    /**
     * @return array
     */
    public function getErrorMessages();

    /**
     * @return bool
     */
    public function create();

    /**
     * Note: Drop method should always be in final class, as we need to keep in mind a situation of TEMPORARY TABLE vs TABLE
     * @return bool
     */
    public function drop();

    /**
     * Note: Delete Content method should always be in final class, as we need to keep in mind a situation of TEMPORARY TABLE vs TABLE
     * @return bool
     */
    public function deleteContent();
}