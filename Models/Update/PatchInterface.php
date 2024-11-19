<?php
/**
 * Element must-have interface - must have a single element Id
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * NOTE: Patching must not impact the roles or capabilities. It it does have to impact that, then it is an update, not a patch.
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;

interface PatchInterface
{
    public function patchDatabaseEarlyStructure();
    public function patchData();
    // NOTE: For patches late struct patching is not possible at all, due to fact that we can update struct on the same first site that has data,
    //          but we cannot do the same for late struct, as we the would not have clear data patching
    public function updateDatabaseSemver();
}