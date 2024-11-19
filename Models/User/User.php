<?php
/**
 * User element (account)

 * @note - It does not have settings param in constructor on purpose!
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\User;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\WP_UserInterface;

final class User extends AbstractStack implements StackInterface, WP_UserInterface
{
    private $conf           = null;
    private $lang 		    = null;
    private $debugMode 	    = 0;
    private $wpUserId       = 0;

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param int $paramWP_UserId
     */
	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramWP_UserId)
	{
		// Set class settings
		$this->conf = $paramConf;
		// Already sanitized before in it's constructor. Too much sanitization will kill the system speed
		$this->lang = $paramLang;

		$this->wpUserId = StaticValidator::getValidPositiveInteger($paramWP_UserId, 0);
	}

    /**
     * @return bool
     */
    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->wpUserId;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        $objWP_User = get_user_by('ID', $this->wpUserId);
        $displayName = '';
        if($objWP_User !== false)
        {
            $displayName = $objWP_User->display_name;
        }

        return $displayName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        $objWP_User = get_user_by('ID', $this->wpUserId);
        $email = '';
        if($objWP_User !== false)
        {
            $email = $objWP_User->user_email;
        }

        return $email;
    }
}