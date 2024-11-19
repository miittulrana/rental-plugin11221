<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Order;
use FleetManagement\Models\Order\OrderNotificationsObserver;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Search\FrontEndSearchManager;
use FleetManagement\Controllers\Front\AbstractController;

final class CancellationController extends AbstractController
{
    private $objSearch = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
        $this->objSearch = new FrontEndSearchManager($this->conf, $this->lang, $this->dbSets->getAll());

        // First - process request
        $this->processRequest();
    }

    private function processRequest()
    {
        // DEBUG
        //echo "INITIAL REQUEST VARS: ".nl2br(print_r($_REQUEST, true));
        //echo "INITIAL SESSION VARS: ".nl2br(print_r($_SESSION, true));
        //echo "INITIAL COOKIE VARS: ".nl2br(print_r($_COOKIE, true));

        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // First - clear old expired bookings
        $objOrdersObserver->cancelExpired();

        // Second - validate the order code
        if($this->orderCode != '')
        {
            $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
            $this->objSearch->setVariablesByOrderId($objOrdersObserver->getIdByCode($this->orderCode));
        }

        // Finally, destroy the session
        // Note: Requires PHP 5.4+
        if(session_status() === PHP_SESSION_ACTIVE)
        {
            session_destroy();
        }

        // DEBUG
        //echo "UPDATED SESSION VARS: ".nl2br(print_r($_SESSION, true));
        //echo "UPDATED COOKIE VARS: ".nl2br(print_r($_COOKIE, true));
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @param string $paramFailureLayout
     * @param string $paramFailureStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "Details", $paramStyle = "", $paramFailureLayout = "Details", $paramFailureStyle = "")
    {
        // Create mandatory instances
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrdersObserver->cancelExpired();
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        $errorMessages = array();
        $cancelledSuccessfully = false;
        $orderCode = '';
        
        // Process, if the order code is provided
        if($this->orderCode != '')
        {
            $orderId = $objOrdersObserver->getIdByCode($this->orderCode);
            $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $orderId);

            // Unset the variables cache
            $this->objSearch->unsetVariablesCache();

            // Finally, destroy the session
            // Note: Requires PHP 5.4+
            if(session_status() === PHP_SESSION_ACTIVE)
            {
                session_destroy();
            }

            // DEBUG
            // echo "[DEBUG] UPDATED SESSION VARS: ".nl2br(print_r($_SESSION, true));
            // echo "[DEBUG] UPDATED COOKIE VARS: ".nl2br(print_r($_COOKIE, true));
            
            // CANCEL RESERVATION
            $cancelledSuccessfully = $objOrder->cancel();
            if($cancelledSuccessfully)
            {
                if($this->dbSets->get('conf_send_emails') == 1)
                {
                    $allNotificationsSent = $objOrderNotificationsObserver->sendOrderCancelledNotifications($objOrder->getId(), true);
                    if($allNotificationsSent === false)
                    {
                        // Add errors
                        $errorMessages = array_merge($errorMessages, $objOrderNotificationsObserver->getSavedErrorMessages());
                    }
                }
            } else
            {
                // Add error
                $errorMessages[] = sprintf($this->lang->getText('LANG_ORDER_NOT_CANCELLED_CODE_S_DOES_NOT_EXIST_TEXT'), $this->orderCode);
            }
        } else
        {
            // Add error
            $errorMessages[] = $this->lang->getText('LANG_ORDER_NO_CODE_ERROR_TEXT');
        }
        
        // Set the view variables
        $this->view->orderCodeParam = $this->conf->getOrderCodeParam();
        $this->view->orderCode = $orderCode;
        $this->view->errorMessages = implode("\n\n", $errorMessages);
        $this->view->goBackURL = $this->actionPageId > 0 ? $this->lang->getTranslatedURL($this->actionPageId) : site_url();
        $this->view->goToHomePageURL = site_url();

        // Get the template
        // Must be 2 equalities because it gives two 1
        if($this->objSearch->searchEnabled() && $cancelledSuccessfully == true)
        {
            $retContent = $this->getTemplate('Order', 'Cancelled', $paramLayout, $paramStyle);
        } else if($this->objSearch->searchEnabled() && $cancelledSuccessfully === false)
        {
            $retContent = $this->getTemplate('Order', 'CancellationFailure', $paramFailureLayout, $paramFailureStyle);
        } else
        {
            // Search is disabled
            $retContent = '';
        }

        return $retContent;
    }
}