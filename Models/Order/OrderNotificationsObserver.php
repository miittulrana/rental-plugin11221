<?php
/**
 * Orders Observer (no setup for single order)
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Order;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Invoice\InvoicesObserver;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Notification\EmailNotification;
use FleetManagement\Models\Notification\EmailNotificationsObserver;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Customer\Customer;

final class OrderNotificationsObserver implements ObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $settings                   = array();
    private $savedMessages              = array();
    private $minOrderPeriod             = 0;
    private $maxOrderPeriod             = 0;
    private $notificationPhone 	        = "";
    private $notificationEmail 	        = "";
    private $sendNotifications          = false;
    private $sendCompanyNotifications   = false;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        // set minimum booking period
        $this->minOrderPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_minimum_booking_period', 'positive_integer', 0);
        // Set maximum booking period
        $this->maxOrderPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_maximum_booking_period', 'positive_integer', 0);
        $this->notificationPhone = StaticValidator::getValidSetting($paramSettings, 'conf_company_phone', "textval", "");
        $this->notificationEmail = StaticValidator::getValidSetting($paramSettings, 'conf_company_email', "email", "");

        if(isset($paramSettings['conf_send_emails']))
        {
            $this->sendNotifications = $paramSettings['conf_send_emails'] == 1 ? true : false;
        }
        if(isset($paramSettings['conf_company_notification_emails']))
        {
            $this->sendCompanyNotifications = $paramSettings['conf_company_notification_emails'] == 1 ? true : false;
        }
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

    /* --------------------------------------------------------------------------------- */
    /* --------------------------- Notifications sending ------------------------------- */
    /* --------------------------------------------------------------------------------- */

    public function sendOrderReceivedNotifications($paramOrderId, $paramSendNotificationToAdmin = true)
    {
        // ORDER RECEIVED
      //  return $this->sendNotifications(1, 4, $paramOrderId, $paramSendNotificationToAdmin);
    }

    public function sendOrderConfirmedNotifications($paramOrderId, $paramSendNotificationToAdmin = true)
    {
        // ORDER CONFIRMED
        return $this->sendNotifications(2, 5, $paramOrderId, $paramSendNotificationToAdmin);
    }

    public function sendOrderCancelledNotifications($paramOrderId, $paramSendNotificationToAdmin = true)
    {
        // ORDER CANCELLED
        return $this->sendNotifications(3, 6, $paramOrderId, $paramSendNotificationToAdmin);
    }

    /**
     * @param int $paramNotificationType - in later version with will be a string type
     * @param int $paramAdminNotificationType - in later version with will be a string type
     * @param int $paramOrderId
     * @param bool $paramSendNotificationToAdmin
     * @return bool
     */
    private function sendNotifications($paramNotificationType, $paramAdminNotificationType, $paramOrderId, $paramSendNotificationToAdmin = true)
    {
        $sent = false;
        $ok = true;

        // Create mandatory instances
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->settings);
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->settings);
        $objInvoicesObserver = new InvoicesObserver($this->conf, $this->lang, $this->settings);
        $objEmailNotificationsObserver = new EmailNotificationsObserver($this->conf, $this->lang, $this->settings);
        $objEmailNotification = new EmailNotification($this->conf, $this->lang, $this->settings, $objEmailNotificationsObserver->getIdByType($paramNotificationType));
        $objAdminEmailNotification = new EmailNotification($this->conf, $this->lang, $this->settings, $objEmailNotificationsObserver->getIdByType($paramAdminNotificationType));
        $objOrder = new Order($this->conf, $this->lang, $this->settings, $paramOrderId);
        $overallInvoiceId = $objInvoicesObserver->getIdByParams('OVERALL', $paramOrderId, -1);
        $objInvoice = new Invoice($this->conf, $this->lang, $this->settings, $overallInvoiceId);

        // 3. Get invoice and order objects and invoice id
        $orderDetails = $objOrder->getDetails();
        $invoiceDetails = $objInvoice->getDetails();
        $invoiceId = $objInvoice->getId();

        // 4. Check if we can send notifications
        if($this->sendNotifications === false)
        {
            $ok = false;
            $error = $this->lang->getText('LANG_NOTIFICATIONS_ARE_DISABLED_TEXT');
            $this->savedMessages['error'][] = $error;
        }

        // 5. Check if order exists
        if($orderDetails['booking_id'] == 0)
        {
            $ok = false;
            $error = $this->lang->getText('LANG_ORDER_DOES_NOT_EXIST_ERROR_TEXT');
            $this->savedMessages['error'][] = $error;
        }

        // 6. Check if customer exists
        if($orderDetails['customer_id'] > 0 && $objCustomersObserver->checkExists($orderDetails['customer_id']) === false)
        {
            $ok = false;
            $error = $this->lang->getText('LANG_CUSTOMER_DOES_NOT_EXIST_ERROR_TEXT');
            $this->savedMessages['error'][] = $error;
        }

        // 7. Check if invoice exists
        if($invoiceId == 0)
        {
            $ok = false;
            $error = $this->lang->getText('LANG_INVOICE_DOES_NOT_EXIST_ERROR_TEXT');
            $this->savedMessages['error'][] = $error;
        }
        // 8. Send notifications
        if($ok && !is_null($orderDetails) && !is_null($invoiceDetails))
        {
            $locationEmailNotificationSent = true;
            $adminEmailNotificationSent = true;

            $objLocation = new Location($this->conf, $this->lang, $this->settings, $objLocationsObserver->getIdByCode($orderDetails['pickup_location_code']));
            $objCustomer = new Customer($this->conf, $this->lang, $this->settings, $orderDetails['customer_id']);
            $customerPhone = $objCustomer->getPhone();
            $locationDetails = $objLocation->getDetails();
            $locationName = '';
            $locationPhone = '';
            $locationEmail = '';
            if(!is_null($locationDetails))
            {
                $locationName = $locationDetails['translated_location_name'];
                $locationPhone = $locationDetails['phone'];
                $locationEmail = $locationDetails['email'];
            }

            $changeOrderURL = site_url();
            $changeOrderURL .= '?'.$this->conf->getExtPrefix().$this->conf->getOrderCodeParam().'='.$orderDetails['booking_code'];
            $notificationParams = array(
                "booking_code" => $orderDetails['booking_code'],
                "change_order_url" => $changeOrderURL,
                "trusted_invoice_html" => $invoiceDetails['invoice'],
                "customer_id" => $orderDetails['customer_id'],
                "customer_name" => $invoiceDetails['customer_name'],
                "customer_phone" => $customerPhone,
                "customer_email" => $invoiceDetails['customer_email'],
                "location_name" => $locationName,
                "location_phone" => $locationPhone,
                "location_email" => $locationEmail
            );

            // NOTE: We do not allow customers to reply directly to admin
            $emailNotificationSent = $objEmailNotification->sendTranslated($invoiceDetails['customer_email'], '', '', $notificationParams);
            if($emailNotificationSent == true && $paramSendNotificationToAdmin == true)
            {
                // Send an admin e-mail to location e-mail address, if set
                if($locationEmail != "")
                {
                    $locationEmailNotificationSent = $objAdminEmailNotification->sendTranslated(
                        $locationEmail, $invoiceDetails['customer_name'], $invoiceDetails['customer_email'], $notificationParams
                    );
                }
            }
            if($emailNotificationSent == true && $paramSendNotificationToAdmin == true && $this->sendCompanyNotifications == true)
            {
                if($locationEmail != $this->notificationEmail && $this->notificationEmail != "")
                {
                    // Send an admin e-mail to company headquarters, if set
                    $adminEmailNotificationSent = $objAdminEmailNotification->sendTranslated(
                        $this->notificationEmail, $invoiceDetails['customer_name'], $invoiceDetails['customer_email'], $notificationParams
                    );
                }
            }

            if(
                $emailNotificationSent
                && ($locationEmail == "" || $locationEmailNotificationSent)
                && ($this->notificationEmail == "" || ($locationEmail != $this->notificationEmail && $adminEmailNotificationSent))
            ) {
                $sent = true;
            }

            // Save all messages
            $this->saveAllMessages($objEmailNotification->getAllMessages());
            $this->saveAllMessages($objAdminEmailNotification->getAllMessages());
        }

        return $sent;
    }
}