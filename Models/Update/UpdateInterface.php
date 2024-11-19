<?php
/**
 * Element must-have interface - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * NOTE: Updating must not have any patch. For patching we have a separate interface. If after update we saw,
 *      that we missed something, i.e. we need to fix a bug, rename database field,
 *      or add an add new index to the database table, then it is a patch, and should be performed via patch interface.
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;

interface UpdateInterface
{
    public function alterDatabaseEarlyStructure();
    public function updateDatabaseData();
    public function alterDatabaseLateStructure();
    public function updateCustomRoles();
    public function updateCustomCapabilities();
    public function updateDatabaseSemver();
}