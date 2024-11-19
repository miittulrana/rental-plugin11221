<?php
/**
 * Assistant role

 * @note - It does not have settings param in constructor on purpose!
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Assistant;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\RoleInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class AssistantRole extends AbstractStack implements StackInterface, RoleInterface
{
    private $conf           = null;
    private $lang 		    = null;
    private $debugMode 	    = 0;
    private $roleName 	    = '';

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
	{
		// Set class settings
		$this->conf = $paramConf;
		// Already sanitized before in it's constructor. Too much sanitization will kill the system speed
		$this->lang = $paramLang;

        $this->roleName = $this->conf->getPluginPrefix().'assistant';
	}

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        $roleCapabilities = array(
            'read'                                                    => true,  // true allows this capability
            'view_'.$this->conf->getExtPrefix().'all_inventory'       => true,
            'manage_'.$this->conf->getExtPrefix().'all_inventory'     => false,
            'view_'.$this->conf->getExtPrefix().'all_items'           => true,
            'manage_'.$this->conf->getExtPrefix().'all_items'         => false,
            'view_'.$this->conf->getExtPrefix().'own_items'           => true,
            'manage_'.$this->conf->getExtPrefix().'own_items'         => false,
            'view_'.$this->conf->getExtPrefix().'all_extras'          => true,
            'manage_'.$this->conf->getExtPrefix().'all_extras'        => false,
            'view_'.$this->conf->getExtPrefix().'own_extras'          => true,
            'manage_'.$this->conf->getExtPrefix().'own_extras'        => false,
            'view_'.$this->conf->getExtPrefix().'all_locations'       => true,
            'manage_'.$this->conf->getExtPrefix().'all_locations'     => false,
            'view_'.$this->conf->getExtPrefix().'all_bookings'        => true,
            'manage_'.$this->conf->getExtPrefix().'all_bookings'      => true,
            'view_'.$this->conf->getExtPrefix().'partner_bookings'    => true,
            'manage_'.$this->conf->getExtPrefix().'partner_bookings'  => true,
            'view_'.$this->conf->getExtPrefix().'all_customers'       => true,
            'manage_'.$this->conf->getExtPrefix().'all_customers'     => true,
            'view_'.$this->conf->getExtPrefix().'all_settings'        => false,
            'manage_'.$this->conf->getExtPrefix().'all_settings'      => false,
        );

        return $roleCapabilities;
    }

    public function add()
    {
        $roleResult = add_role($this->roleName, $this->lang->getText('LANG_ASSISTANT_TEXT'), $this->getCapabilities());

        if($roleResult !== null)
        {
            // New role added!
            $newRoleAdded = true;
        } else
        {
            // The selected role already exists
            $newRoleAdded = false;
        }

        return $newRoleAdded;
    }

    public function remove()
    {
        // Remove role if exist
        // Note: When a role is removed, the users who have this role lose all rights on the site.
        remove_role($this->roleName);
    }

    /**
     * @return void
     */
    public function addCapabilities()
    {
        // Add capabilities to this role
        $objWPRole = get_role($this->roleName);
        $capabilitiesToAdd = $this->getCapabilities();
        foreach($capabilitiesToAdd AS $capability => $grant)
        {
            $objWPRole->add_cap($capability, $grant);
        }
    }

    /**
     * @return void
     */
    public function removeCapabilities()
    {
        // Remove capabilities from this role
        $objWPRole = get_role($this->roleName);
        $capabilitiesToRemove = $this->getCapabilities();
        foreach($capabilitiesToRemove AS $capability => $grant)
        {
            $objWPRole->remove_cap($capability);
        }
    }
}