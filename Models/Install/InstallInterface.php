<?php
/**
 * Install must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Install;

interface InstallInterface
{
    /**
     * @return array
     */
    public static function getTableClasses();

    /**
     * Insert all content
     * @note - for security and standardization reasons the concrete file name is encoded into this method
     * @return bool
     */
    public function insertContent();

    /**
     * Replace special content
     * @note1 - fires every time when plugin is enabled, or enabled->disabled->enabled, etc.
     * @note2 - used mostly to set image dimensions right
     * @note3 - for security and standardization reasons the concrete file name is encoded into this method
     * @return bool
     */
    public function resetContent();
}