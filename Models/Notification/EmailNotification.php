<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
//error_reporting(0); - this should always be controlled by WordPress
namespace FleetManagement\Models\Notification;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class EmailNotification extends AbstractNotification implements StackInterface, ElementInterface
{
	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramNotificationId)
	{
        parent::__construct($paramConf, $paramLang, $paramSettings, $paramNotificationId);
	}

    /**
     * @param int $paramNotificationId
     * @return mixed
     */
    private function getDataFromDatabaseById($paramNotificationId)
    {
        $validEmailId = StaticValidator::getValidPositiveInteger($paramNotificationId, 0);

        $retData = $this->conf->getInternalWPDB()->get_row("
            SELECT *
            FROM {$this->conf->getPrefix()}emails
            WHERE email_id='{$validEmailId}'
        ", ARRAY_A);

        return $retData;
    }

    public function getId()
    {
        return $this->notificationId;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function canEdit()
    {
        $canEdit = current_user_can('manage_'.$this->conf->getExtPrefix().'all_settings');

        return $canEdit;
    }

    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->notificationId);

        if(!is_null($ret))
        {
            // Make raw
            $ret['email_subject'] = stripslashes($ret['email_subject']);
            $ret['email_body'] = stripslashes($ret['email_body']);

            // Retrieve translation
            $ret['translated_email_subject'] = $this->lang->getTranslated("em{$ret['email_type']}_email_subject", $ret['email_subject']);
            $ret['translated_email_body'] = $this->lang->getTranslated("em{$ret['email_type']}_email_body", $ret['email_body']);

            // Make print
            $ret['print_email_subject'] = esc_html($ret['email_subject']);
            $ret['print_email_body'] = nl2br(implode("\n", array_map('esc_html', explode("\n", $ret['email_body']))));

            $ret['print_translated_email_subject'] = esc_html($ret['translated_email_subject']);
            $ret['print_translated_email_body'] = nl2br(implode("\n", array_map('esc_html', explode("\n", $ret['translated_email_body']))));

            // Prepare output for edit
            $ret['edit_email_subject'] = esc_attr($ret['email_subject']); // for input field
            $ret['edit_email_body'] = esc_textarea($ret['email_body']); // for textarea field
        } else if($paramPrefillWhenNull === true)
        {
            $ret = array(
                'email_type' 		    => 0,
                'email_subject'         => '',
                'email_body' 	        => '',
                'print_email_subject'   => '',
                'print_email_body' 	    => '',
                'edit_email_subject'    => '',
                'edit_email_body' 	    => '',
            );
        }

        return $ret;
    }

    /**
     * Admin email content preview
     * @return array
     */
    public function getPreview()
    {
        $notificationDetails   	= $this->getDetails(true);

        // Demo details
        $demoOrderCode 		            = "DEMO2015A0001";// Demo
        $demoCustomerId 		        = 1001; // Demo
        $demoCustomerName               = $this->lang->getText('LANG_NOTIFICATION_DEMO_CUSTOMER_NAME_TEXT');
        $demoCustomerPhone              = $this->lang->getText('LANG_NOTIFICATION_DEMO_CUSTOMER_PHONE_TEXT');
        $demoCustomerEmail              = $this->lang->getText('LANG_NOTIFICATION_DEMO_CUSTOMER_EMAIL_TEXT');
        $demoLocationName               = $this->lang->getText('LANG_NOTIFICATION_DEMO_LOCATION_NAME_TEXT');
        $demoLocationPhone              = $this->lang->getText('LANG_NOTIFICATION_DEMO_LOCATION_PHONE_TEXT');
        $demoLocationEmail              = $this->lang->getText('LANG_NOTIFICATION_DEMO_LOCATION_EMAIL_TEXT');
        $demoChangeOrderURL             = site_url();
        $demoChangeOrderURL             .= '?'.$this->conf->getExtPrefix().$this->conf->getOrderCodeParam().'='.$demoOrderCode;

        // Select the newest invoice for testing. Invoice with booking id=0 will exist there by default as a demo
        $invoiceDetails = $this->conf->getInternalWPDB()->get_row("
			SELECT *
			FROM {$this->conf->getPrefix()}invoices
			WHERE blog_id='{$this->conf->getBlogId()}'
			ORDER BY booking_id DESC LIMIT 1
		", ARRAY_A);

        // If there exists booking under this booking id
        $sql = "
            SELECT booking_id, customer_id, booking_code
            FROM {$this->conf->getPrefix()}bookings
            WHERE booking_id='{$invoiceDetails['booking_id']}'
        ";

        $orderData = $this->conf->getInternalWPDB()->get_row($sql, ARRAY_A);

        if(!is_null($orderData))
        {
            $demoOrderCode = $orderData['booking_code'];
            $demoCustomerId = $orderData['customer_id'];
            $demoCustomerName = $invoiceDetails['customer_name'];
            $demoCustomerEmail = $invoiceDetails['customer_email'];
            $demoChangeOrderURL = site_url();
            $demoChangeOrderURL .= '?'.$this->conf->getExtPrefix().$this->conf->getOrderCodeParam().'='.$orderData['booking_code'];
        }

        $params = array(
            "booking_code" => $demoOrderCode,
            "change_order_url" => $demoChangeOrderURL,
            "trusted_invoice_html" => $invoiceDetails['invoice'],
            "customer_id" => $demoCustomerId,
            "customer_name" => $demoCustomerName,
            "customer_phone" => $demoCustomerPhone,
            "customer_email" => $demoCustomerEmail,
            "location_name" => $demoLocationName,
            "location_phone" => $demoLocationPhone,
            "location_email" => $demoLocationEmail
        );

        // 1 - Replace shortcodes in e-mail subject
        $printSubject = $this->replaceBBCodesInSubject(
            $notificationDetails['print_email_subject'], $params
        );
        // 2.1. - Replace regular BB codes in e-mail body
        $printBody = $this->replaceBBCodesInBody(
            $notificationDetails['print_email_body'], $params
        );
        // 2.2. - Replace HTML BB codes in e-mail body
        $printBody = $this->replaceHTML_BBCodesInBody($printBody, $params);

        // 3 - Replace shortcodes in translated email subject
        $printTranslatedEmailSubject = $this->replaceBBCodesInSubject(
            $notificationDetails['print_translated_email_subject'], $params
        );
        // 4.1. - Replaces regular BB codes in translated email body
        $printTranslatedEmailBody = $this->replaceBBCodesInBody(
            $notificationDetails['print_translated_email_body'], $params
        );
        // 4.2. - Replaces HTML BB codes in email body
        $printTranslatedEmailBody = $this->replaceHTML_BBCodesInBody($printTranslatedEmailBody, $params);

        $preview = array(
            'print_email_subject' => $printSubject,
            'print_email_body' => $printBody,
            'print_translated_email_subject' => $printTranslatedEmailSubject,
            'print_translated_email_body' => $printTranslatedEmailBody,
        );

        return $preview;
    }

    /**
     * @param array $params
     * @return bool|false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validNotificationId = StaticValidator::getValidPositiveInteger($this->notificationId, 0);
        $sanitizedNotificationSubject = sanitize_text_field($params['email_subject']);
        $validNotificationSubject = esc_sql($sanitizedNotificationSubject);
        $sanitizedNotificationBody = implode("\n", array_map('sanitize_text_field', explode("\n", $params['email_body'])));
        $validNotificationBody = esc_sql($sanitizedNotificationBody);

        $notificationSubjectAndBodyExistsForThisType = $this->conf->getInternalWPDB()->get_row("
			SELECT email_type
			FROM {$this->conf->getPrefix()}emails
			WHERE email_subject='{$validNotificationSubject}' AND email_body='{$validNotificationBody}'
			AND email_id!='{$validNotificationId}' AND blog_id='{$this->conf->getBlogId()}'
        ", ARRAY_A);

        if(!is_null($notificationSubjectAndBodyExistsForThisType))
        {
            $ok = false;
            $this->errorMessages[] = $this->lang->getText('LANG_EMAIL_NOTIFICATION_SUBJECT_AND_BODY_EXISTS_FOR_THIS_TYPE_ERROR_TEXT');
        }

        if($validNotificationId > 0 && $ok)
        {
            $saved = $this->conf->getInternalWPDB()->query("
				UPDATE {$this->conf->getPrefix()}emails SET
				email_subject='{$validNotificationSubject}', email_body='{$validNotificationBody}'
				WHERE email_id='{$validNotificationId}' AND blog_id='{$this->conf->getBlogId()}'
		   ");

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_EMAIL_NOTIFICATION_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_EMAIL_NOTIFICATION_UPDATED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        $emailDetails = $this->getDetails();
        if(!is_null($emailDetails))
        {
            $this->lang->register("em{$this->notificationId}_email_subject", $emailDetails['email_subject']);
            $this->lang->register("em{$this->notificationId}_email_body", $emailDetails['email_body']);
            $this->okayMessages[] = $this->lang->getText('LANG_EMAIL_NOTIFICATION_REGISTERED_TEXT');
        }
    }

    /**
     * @note - Emails are not allowed to be deleted at all
     * @return false
     */
    public function delete()
    {
        // Not allowed
        return false;
    }


    /*******************************************************************************/
    /************************* ELEMENT SPECIFIC FUNCTIONS **************************/
    /*******************************************************************************/

    /**
     * @param string $paramRecipientEmail
     * @param string $paramReplyToName
     * @param string $paramReplyToEmail
     * @param array $params
     * @return bool
     */
    public function sendTranslated($paramRecipientEmail, $paramReplyToName = "", $paramReplyToEmail = "", $params = array())
    {
        return $this->send($paramRecipientEmail, $paramReplyToName, $paramReplyToEmail, $params, true);
    }

    /**
     * @param string $paramRecipientEmail
     * @param string $paramReplyToName
     * @param string $paramReplyToEmail
     * @param array $params
     * @param bool $paramTranslated
     * @return bool
     */
    public function send($paramRecipientEmail, $paramReplyToName = "", $paramReplyToEmail = "", $params = array(), $paramTranslated = false)
    {
        $validRecipientEmail = sanitize_email($paramRecipientEmail);
        $notificationDetails = $this->getDetails(true);

        $printSubject = $notificationDetails[$paramTranslated ? 'print_translated_email_subject' : 'print_email_subject'];
        $printBody = $notificationDetails[$paramTranslated ? 'print_translated_email_body' : 'print_email_body'];

        // 1 - Replace shortcodes in email subject
        $printSubject = $this->replaceBBCodesInSubject($printSubject, $params);
        // 2 - Replaces bb codes in e-mail body
        $printBody = $this->replaceBBCodesInBody($printBody, $params);
        // 2.2. - Replaces HTML BB codes in email body
        $printBody = $this->replaceHTML_BBCodesInBody($printBody, $params);

        // Send an e-mail
        $notificationSent = $this->doSend(
            $paramRecipientEmail, $paramReplyToName, $paramReplyToEmail, $printSubject, $printBody
        );

        if($notificationSent === false)
        {
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_EMAIL_NOTIFICATION_UNABLE_TO_SEND_TO_S_ERROR_TEXT'), $validRecipientEmail);
        } else
        {
            $this->okayMessages[] = sprintf($this->lang->getText('LANG_EMAIL_NOTIFICATION_SENT_TO_S_TEXT'), $validRecipientEmail);
        }


        return $notificationSent;
    }

    private function doSend($paramEmailTo, $paramReplyToName, $paramReplyToEmail, $paramSubject, $trustedBody)
    {
        $emailSentSuccessfully = false;
        $validEmailTo = esc_html(sanitize_email($paramEmailTo));
        $validReplyToName = esc_html(sanitize_text_field($paramReplyToName));
        $validReplyToEmail = esc_html(sanitize_email($paramReplyToEmail));
        $validEmailSubject = esc_html(sanitize_text_field($paramSubject));
        $emailHeaders = array();
        $emailHeaders[] = 'From: '.$this->companyName.' <'.$this->companyEmail.'>';
        if($validReplyToName != "" && $validReplyToEmail != "")
        {
            $emailHeaders[] = 'Reply-To: '.$validReplyToName.' <'.$validReplyToEmail.'>';
        }
        $emailHeaders[] = 'MIME-Version: 1.0';
        $emailHeaders[] = 'Content-Type: text/html; charset=UTF-8';

        if($validEmailTo != "")
        {
            $emailSentSuccessfully = wp_mail($validEmailTo, $validEmailSubject, $trustedBody, $emailHeaders);
        }

        if($this->debugMode)
        {
            $debugMessage = "<br />Send email to: ".$validEmailTo;
            $debugMessage .= "<br />Email subject: ".$validEmailSubject;
            $debugMessage .= "<br />Reply-To full name: ".$validReplyToName;
            $debugMessage .= "<br />Reply-To email: ".$validReplyToEmail;
            $debugMessage .= "<br />Email headers: ".nl2br(esc_html(print_r($emailHeaders, true)));
            $debugMessage .= "<br />Email sent successfully: ".var_export($emailSentSuccessfully, true);
            $this->debugMessages[] = $debugMessage;
            echo $debugMessage;
        }

        return $emailSentSuccessfully;
    }
}