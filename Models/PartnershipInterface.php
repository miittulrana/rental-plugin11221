<?php
/**
 * Partner must-have interface - must have a single partner Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models;

interface PartnershipInterface
{
    public function getPartnerId();
    public function canEdit();
    public function canView();
}