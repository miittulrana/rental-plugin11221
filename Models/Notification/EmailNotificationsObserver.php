<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
namespace FleetManagement\Models\Notification;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Validation\StaticValidator;

final class EmailNotificationsObserver implements ObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $savedMessages              = array();
    private $debugMode 	                = 0;
    private $settings                   = array();

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;
	}

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getSavedDebugMessages()
    {
        return isset($this->savedMessages['debug']) ? $this->savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset($this->savedMessages['okay']) ? $this->savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset($this->savedMessages['error']) ? $this->savedMessages['error'] : array();
    }

    private function saveAllMessages($paramArrMessages)
    {
        if(isset($paramArrMessages['debug']))
        {
            $this->savedMessages['debug'] = array_merge($this->getSavedDebugMessages(), $paramArrMessages['debug']);
        }
        if(isset($paramArrMessages['okay']))
        {
            $this->savedMessages['okay'] = array_merge($this->getSavedOkayMessages(), $paramArrMessages['okay']);
        }
        if(isset($paramArrMessages['error']))
        {
            $this->savedMessages['error'] = array_merge($this->getSavedErrorMessages(), $paramArrMessages['error']);
        }
    }

    public function getIdByType($paramEmailType)
    {
        $retEmailId = 0;
        $validEmailType = StaticValidator::getValidPositiveInteger($paramEmailType, 0); // For sql query only

        $emailData = $this->conf->getInternalWPDB()->get_row("
                SELECT email_id
                FROM {$this->conf->getPrefix()}emails
                WHERE email_type='{$validEmailType}' AND blog_id='{$this->conf->getBlogId()}'
            ", ARRAY_A);
        if(!is_null($emailData))
        {
            $retEmailId = $emailData['email_id'];
        }

        return $retEmailId;
    }

    public function getAllIds()
    {
        $locationIds = $this->conf->getInternalWPDB()->get_col("
            SELECT email_id
            FROM {$this->conf->getPrefix()}emails
            WHERE blog_id='{$this->conf->getBlogId()}'
            ORDER BY email_type ASC
        ");

        return $locationIds;
    }


    /* --------------------------------------------------------------------------------- */
    /* --------------------------- Notifications sending ------------------------------- */
    /* --------------------------------------------------------------------------------- */

    public function sendOrderReceivedNotifications($paramOrderId, $paramSendNotificationToAdmin = true)
    {
        // DETAILS
    }

    public function sendOrderConfirmedNotifications($paramOrderId, $paramSendNotificationToAdmin = true)
    {
        // CONFIRMED
    }

    public function sendOrderCancelledNotifications($paramOrderId, $paramSendNotificationToAdmin = true)
    {
        // CANCELLED
    }

    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    /**
     * @param int $selectedEmailId
     * @return string
     */
	public function getTrustedAdminListHTML($selectedEmailId = 0)
	{
		$selected = $selectedEmailId == 0 ? ' selected="selected"' : '';
		$emailList = '<option value="0"'.$selected.'>'.$this->lang->escHTML('LANG_SELECT_EMAIL_TYPE_TEXT').'</option>';
		$emailIds = $this->getAllIds();
		foreach ($emailIds AS $emailId)
		{
		    $objEmail = new EmailNotification($this->conf, $this->lang, $this->settings, $emailId);
		    $emailDetails = $objEmail->getDetails();
			$selected = $selectedEmailId == $emailId ? ' selected="selected"' : '';
			$emailList .= '<option value="'.$emailId.'"'.$selected.'>'.$emailDetails['email_type'].'. '.$emailDetails['print_translated_email_subject'].'</option>';
		}

		return $emailList;
	}
}