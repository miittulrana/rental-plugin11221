<?php
/**
 * Assistants Observer

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Assistant;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Validation\StaticValidator;

final class AssistantsObserver implements ObserverInterface
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
     * @param int $paramSelectedWP_UserId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedWP_UserId = -1, $paramDefaultValue = -1, $paramDefaultLabel = "")
    {
        $validDefaultValue = StaticValidator::getValidInteger($paramDefaultValue, -1);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);

        $retHTML = '';
        if($paramSelectedWP_UserId == $validDefaultValue)
        {
            $retHTML .= '<option value="'.esc_attr($validDefaultValue).'" selected="selected">'.esc_html($sanitizedDefaultLabel).'</option>';
        } else
        {
            $retHTML .= '<option value="'.esc_attr($validDefaultValue).'">'.esc_html($sanitizedDefaultLabel).'</option>';
        }
        $roleName = (new AssistantRole($this->conf, $this->lang))->getRoleName();
        $arrObjWP_Users = get_users(array('role' => $roleName));
        // Array of WP_User objects.
        foreach($arrObjWP_Users AS $objWP_User)
        {
            $validWP_UserId = StaticValidator::getValidPositiveInteger($objWP_User->ID, 0);
            $printWP_UserDisplayName = esc_html($objWP_User->display_name);
            if($validWP_UserId == $paramSelectedWP_UserId)
            {
                $retHTML .= '<option value="'.esc_attr($validWP_UserId).'" selected="selected">'.$printWP_UserDisplayName.'</option>';
            } else
            {
                $retHTML .= '<option value="'.esc_attr($validWP_UserId).'">'.$printWP_UserDisplayName.'</option>';
            }
        }
        return $retHTML;
    }
}