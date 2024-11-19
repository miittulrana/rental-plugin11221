<?php
/**

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
*/
//error_reporting(0); - this should always be controlled by WordPress
namespace FleetManagement\Models\Notification;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

abstract class AbstractNotification extends AbstractStack implements StackInterface
{
    protected $conf 	                        = null;
    protected $lang 		                    = null;
    protected $debugMode 	                    = 0;
    protected $notificationId 	                = 0;
    protected $companyName 	                    = "";
    protected $companyPhone 	                = "";
    protected $companyEmail 	                = "";

	public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramNotificationId)
	{
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->companyName = StaticValidator::getValidSetting($paramSettings, 'conf_company_name', "textval", "");
        $this->companyPhone = StaticValidator::getValidSetting($paramSettings, 'conf_company_phone', "textval", "");
        $this->companyEmail = StaticValidator::getValidSetting($paramSettings, 'conf_company_email', "email", "");
        $this->notificationId = StaticValidator::getValidPositiveInteger($paramNotificationId);
	}
    public function getId()
    {
        return $this->notificationId;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Replace bb codes in email subject
     * @param string $trustedSubject
     * @param array $params
     * @return string
     */
    protected function replaceBBCodesInSubject($trustedSubject, array $params)
    {
        $paramOrderCode = isset($params['booking_code']) ? $params['booking_code']: '';
        $paramCustomerName = isset($params['customer_name']) ? $params['customer_name']: '';
        $paramLocationName = isset($params['location_name']) ? $params['location_name']: '';

        $printOrderCode = esc_html(sanitize_text_field($paramOrderCode));
        $printCustomerName = esc_html(sanitize_text_field($paramCustomerName));
        $printCompanyName = esc_html(sanitize_text_field($this->companyName));
        $printLocationName = esc_html(sanitize_text_field($paramLocationName));
        $from = array(
            "[BOOKING_CODE]", // Legacy
            $this->conf->getOrderCodeBBCode(), "[CUSTOMER_NAME]", "[COMPANY_NAME]", "[LOCATION_NAME]",
        );
        $to = array(
            $printOrderCode, // Legacy
            $printOrderCode, $printCustomerName, $printCompanyName, $printLocationName,
        );
        $modifiedEmailSubject = str_replace($from, $to, $trustedSubject);

        return $modifiedEmailSubject;
    }

    /**
     * Replace regular BB codes in e-mail body
     * @param string $trustedBody
     * @param array $params
     * @return string
     */
    protected function replaceBBCodesInBody($trustedBody, $params)
    {
        $paramOrderCode = isset($params['booking_code']) ? $params['booking_code']: '';
        $paramChangeOrderURL = isset($params['change_order_url']) ? $params['change_order_url']: '';
        $paramCustomerId = isset($params['customer_id']) ? $params['customer_id']: '';
        $paramCustomerName = isset($params['customer_name']) ? $params['customer_name']: '';
        $paramCustomerPhone = isset($params['customer_phone']) ? $params['customer_phone']: '';
        $paramCustomerEmail = isset($params['customer_email']) ? $params['customer_email']: '';
        $paramLocationName = isset($params['location_name']) ? $params['location_name']: '';
        $paramLocationPhone = isset($params['location_phone']) ? $params['location_phone']: '';
        $paramLocationEmail = isset($params['location_email']) ? $params['location_email']: '';

        $printOrderCode = StaticValidator::getValidCode($paramOrderCode, '', true, true, false);
        $validChangeOrderURL = esc_url(sanitize_text_field($paramChangeOrderURL));
        $validCustomerId = StaticValidator::getValidPositiveInteger($paramCustomerId, 0);
        $printCustomerName = esc_html(sanitize_text_field($paramCustomerName));
        $printCustomerPhone = esc_html(sanitize_text_field($paramCustomerPhone));
        $printCustomerEmail = esc_html(sanitize_email($paramCustomerEmail));
        $printCompanyName = esc_html(sanitize_text_field($this->companyName));
        $printCompanyPhone = esc_html(sanitize_text_field($this->companyPhone));
        $printCompanyEmail = esc_html(sanitize_text_field($this->companyEmail));
        $printLocationName = esc_html(sanitize_text_field($paramLocationName));
        $printLocationPhone = esc_html(sanitize_text_field($paramLocationPhone));
        $printLocationEmail = esc_html(sanitize_email($paramLocationEmail));

        // 3 - Replace site url
        $modifiedBody = str_replace('[SITE_URL]', site_url(), $trustedBody);

        // 3 - Replace change order url
        $modifiedBody = str_replace($this->conf->getChangeOrderURL_BBCode(), $validChangeOrderURL, $modifiedBody);

        // 4 - Replace all other shortcodes in e-mail body
        $from = array(
            "[BOOKING_CODE]", // Legacy
            $this->conf->getOrderCodeBBCode(), "[CUSTOMER_ID]", "[CUSTOMER_NAME]", "[CUSTOMER_PHONE]", "[CUSTOMER_EMAIL]",
            "[COMPANY_NAME]", "[COMPANY_PHONE]", "[COMPANY_EMAIL]",
            "[LOCATION_NAME]", "[LOCATION_PHONE]", "[LOCATION_EMAIL]",
        );
        $to = array(
            $printOrderCode,
            $printOrderCode, $validCustomerId, $printCustomerName, $printCustomerPhone, $printCustomerEmail,
            $printCompanyName, $printCompanyPhone, '<a href="mailto:'.$printCompanyEmail.'">'.$printCompanyEmail.'</a>',
            $printLocationName, $printLocationPhone, '<a href="mailto:'.$printLocationEmail.'">'.$printLocationEmail.'</a>',
        );
        $modifiedBody = str_replace($from, $to, $modifiedBody);

        return $modifiedBody;
    }

    /**
     * Replace HTML BB codes in body
     * @param string $trustedBody
     * @param array $params
     * @return string
     */
    protected function replaceHTML_BBCodesInBody($trustedBody, array $params)
    {
        // 1. Params
        $paramTrustedInvoiceHTML = isset($params['trusted_invoice_html']) ? $params['trusted_invoice_html'] : '';

        // 2. Make ready for print the invoice
        $ksesedTrustedInvoiceHTML = wp_kses_post($paramTrustedInvoiceHTML);

        // 3.1. Replace plugin shortcodes in e-mail body
        // Note: We don't need here a pick-up or return partner name, as that is a unique name for location name already,
        //       plus that helps us to ensure that partner won't be revealed if it is set to be not-disclosed

        $from = array(
            "[INVOICE]",
        );

        $to = array(
            $ksesedTrustedInvoiceHTML,
        );

        $modifiedBody = str_replace($from, $to, $trustedBody);

        // 3.2. Replace basic bb code
        $modifiedBody = preg_replace('#\[S\](.*?)\[/S\]#si', '<strong>\1</strong>', $modifiedBody);
        $modifiedBody = preg_replace('#\[EM\](.*?)\[/EM\]#si', '<em>\1</em>', $modifiedBody);
        $modifiedBody = preg_replace('#\[CENTER\](.*?)\[/CENTER\]#si', '<div style="text-align: center;width: 100%">\1</div>', $modifiedBody);
        $modifiedBody = preg_replace('#\[HR\]#si', '<hr />', $modifiedBody);

        // 3.3. Auto replace links
        $modifiedBody = preg_replace_callback(
            '#(^|[\n ])([\w]+?://[ąčęėįšųūžĄČĘĖĮŠŲŪŽ\w\#$%&~/.\-;:=,?@\(?\)?\]\|+]*)#si',
            function ($matches)
            {
                return $matches[1].'<a href="'.$matches[2].'" target="_blank">'.$matches[2].'</a>';
            },
            $modifiedBody
        );
        $modifiedBody = preg_replace_callback(
            '#(^|[\n ])((www|ftp)\.[ąčęėįšųūžĄČĘĖĮŠŲŪŽ\w\#$%&~/.\-;:=,?@\(?\)?\]\|+]*)#si',
            function ($matches)
            {
                return $matches[1].'<a href="'.$matches[2].'" target="_blank">'.$matches[2].'</a>';
            },
            $modifiedBody
        );

        // 3.4. Auto replace links
        $modifiedBody = preg_replace_callback(
            '#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#si',
            function ($matches)
            {
                return $matches[1].'<a href="mailto:'.$matches[2].'@'.$matches[3].'">'.$matches[2].'@'.$matches[3].'</a>';
            },
            $modifiedBody
        );

        // 3.5. Replace image shortcodes in email body - [IMG]URL[/IMG]
        $modifiedBody = preg_replace_callback(
            '#\[IMG\]([\r\n]*)(http://|ftp://|https://|ftps://)([ąčęėįšųūžĄČĘĖĮŠŲŪŽ\w\#$%~/.\-;:=,&?@\(?\)?\[\]\|+]*)([\r\n]*)\[/IMG\]#si',
            function ($matches)
            {
                return '<img src="'.$matches[2].$matches[3].'" alt="'.$this->lang->escAttr('LANG_IMAGE_TEXT').'" />';
            },
            $modifiedBody
        );
        // For more strict (image extension only) matches - $matches[2].$matches[3].$matches[4] use this code:
        // '#\[ZF\-LIKE\]([\r\n]*)(http://|ftp://|https://|ftps://)([ąčęėįšųūžĄČĘĖĮŠŲŪŽ\w\#$%~/.\-;:=,&?@\(?\)?\[\]\|+]*)(\.(jpg|jpeg|gif|png|JPG|JPEG|GIF|PNG))([\r\n]*)\[/ZF\-LIKE\]#si'

        return $modifiedBody;
    }
}