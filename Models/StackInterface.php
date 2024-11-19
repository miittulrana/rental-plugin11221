<?php
/**
 * Stack must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;

interface StackInterface
{
    public function flushMessages();
    public function getAllMessages();
    public function getDebugMessages();
    public function getOkayMessages();
    public function getErrorMessages();
}