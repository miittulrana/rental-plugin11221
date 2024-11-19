<?php
/**
 * Administrator Role

 * @note - It does not have settings param in constructor on purpose!
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Administrator;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\RoleInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class AdministratorRole extends AbstractStack implements StackInterface, RoleInterface
{
    private $conf           = null;
    private $lang 		    = null;
    private $debugMode 	    = 0;
    private $roleName       = '';

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
	{
		// Set class settings
		$this->conf = $paramConf;
		// Already sanitized before in it's constructor. Too much sanitization will kill the system speed
		$this->lang = $paramLang;

        $this->roleName = 'administrator'; // No prefix for role here, as it is official WordPress administrator role name
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
            'read'                                                    => true, // true allows this capability
            'view_'.$this->conf->getExtPrefix().'all_inventory'       => true,
            'manage_'.$this->conf->getExtPrefix().'all_inventory'     => true,
            'view_'.$this->conf->getExtPrefix().'all_items'           => true,
            'manage_'.$this->conf->getExtPrefix().'all_items'         => true, // this allows only to add/edit/delete/block the item and it's options, not it's body types etc.
            'view_'.$this->conf->getExtPrefix().'own_items'           => true,
            'manage_'.$this->conf->getExtPrefix().'own_items'         => true,
            'view_'.$this->conf->getExtPrefix().'all_extras'          => true,
            'manage_'.$this->conf->getExtPrefix().'all_extras'        => true,
            'view_'.$this->conf->getExtPrefix().'own_extras'          => true,
            'manage_'.$this->conf->getExtPrefix().'own_extras'        => true,
            'view_'.$this->conf->getExtPrefix().'all_locations'       => true,
            'manage_'.$this->conf->getExtPrefix().'all_locations'     => true,
            'view_'.$this->conf->getExtPrefix().'all_bookings'        => true,
            'manage_'.$this->conf->getExtPrefix().'all_bookings'      => true,
            'view_'.$this->conf->getExtPrefix().'partner_bookings'    => true,
            'manage_'.$this->conf->getExtPrefix().'partner_bookings'  => true,
            'view_'.$this->conf->getExtPrefix().'all_customers'       => true,
            'manage_'.$this->conf->getExtPrefix().'all_customers'     => true,
            'view_'.$this->conf->getExtPrefix().'all_settings'        => true,
            'manage_'.$this->conf->getExtPrefix().'all_settings'      => true,
        );

        return $roleCapabilities;
    }

    public function add()
    {
        // WordPress administrator role cannot be added.

        return false;
    }

    public function remove()
    {
        // WordPress administrator role cannot be remove.

        return false;
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