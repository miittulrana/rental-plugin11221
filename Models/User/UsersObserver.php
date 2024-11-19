<?php
/**
 * Users Observer

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\User;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class UsersObserver implements ObserverInterface
{
    private $conf           = null;
    private $lang 		    = null;
    private $settings 	    = array();
    private $debugMode 	    = 0;

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
		// Set class settings
		$this->conf = $paramConf;
		// Already sanitized before in it's constructor. Too much sanitization will kill the system speed
		$this->lang = $paramLang;
        $this->settings = $paramSettings;
	}

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getNextInsertId()
    {
        $nextInsertId = 1;
        $sqlQuery = "
            SHOW TABLE STATUS LIKE '{$this->conf->getWP_Prefix()}users'
        ";
        $data = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);
        if(!is_null($data))
        {
            $nextInsertId = $data['Auto_increment'];
        }

        return $nextInsertId;
    }

    /**
     * @param string $paramEmail
     * @return int
     */
    public function getUserIdByEmail($paramEmail)
    {
        $userId = 0;

        if($paramEmail != "" &&  email_exists($paramEmail) !== false)
        {
            $user = get_user_by( 'email', $paramEmail );
            if($user)
            {
                $userId = $user->ID;
            }
        }

        return $userId;
    }

    /**
     * NOTE #1: WordPress requires WP username to be minimum 4 characters
     * NOTE #2: Only lowercase
     * @return string
     */
    public function generateUniqueUsernameFromUserId()
    {
        $uniqueUsername = "";
        // Set default
        $username = $this->conf->getWP_UsernamePrefix()."1";

        // We will retry up to 100 times
        for($i = 1; $i < 100; $i++)
        {
            if(username_exists($username) === false)
            {
                $uniqueUsername = $username;
                break;
            }

            // Add three uppercase letters at the end of username
            // NOTE: WordPress requires minimum four chars for username, so we must add at least three more
            $username = $this->conf->getWP_UsernamePrefix().$i.StaticFormatter::getIncrementalHash(3, true, false, false);
        }

        return $uniqueUsername;
    }

    /**
     * @param int $paramSelectedUserId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramPrefillWhenNull
     * @param array $paramAllowedUserIds
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedUserId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "", $paramPrefillWhenNull = false, $paramAllowedUserIds = array())
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);

        $retHTML = '';
        if($paramDefaultLabel != "SKIP")
        {
            if($paramSelectedUserId == $validDefaultValue)
            {
                $retHTML .= '<option value="'.esc_attr($validDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($validDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
            }
        }

        if($validDefaultValue != 0 && $paramPrefillWhenNull === true && $paramSelectedUserId == 0)
        {
            $retHTML .= '<option value="" selected="selected">'.$this->lang->escHTML('LANG_NOT_ASSIGNED_TEXT').'</option>';
        } else
        {
            $retHTML .= '<option value="">'.$this->lang->escHTML('LANG_NOT_ASSIGNED_TEXT').'</option>';
        }

        $arrObjWP_Users = get_users();
        // Array of WP_User objects.
        foreach($arrObjWP_Users AS $objWP_User)
        {
            $validUserId = StaticValidator::getValidPositiveInteger($objWP_User->ID, 0);
            if(sizeof($paramAllowedUserIds) == 0 || in_array($validUserId, $paramAllowedUserIds))
            {
                $printUserDisplayName = esc_html($objWP_User->display_name);
                if($validUserId == $paramSelectedUserId)
                {
                    $retHTML .= '<option value="'.esc_attr($validUserId).'" selected="selected">'.$printUserDisplayName.'</option>';
                } else
                {
                    $retHTML .= '<option value="'.esc_attr($validUserId).'">'.$printUserDisplayName.'</option>';
                }
            }
        }
        return $retHTML;
    }
}