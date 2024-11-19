<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Location;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\OrderNotificationsObserver;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Location\Location;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditLocationController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramLocationId)
    {
        $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $paramLocationId);
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrderNotificationsObserver = new OrderNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objLocation->delete();

        // Cancel upcoming orders by this location
        // NOTE: we do not do deletions here, as they have to be performed manually if needed,
        //       because that is related to invoicing history, and should be taken care very precisely
        $orderIds = $objOrdersObserver->getUpcomingIdsByLocationId($paramLocationId);
        foreach($orderIds AS $orderId)
        {
            $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $orderId);
            if($objOrder->isCancelled() === false)
            {
                // First - cancel
                // And send e-mails to disappointed customers if needed
                $objOrder->cancel();
                if($this->dbSets->get('conf_send_emails') == 1)
                {
                    $objOrderNotificationsObserver->sendOrderCancelledNotifications($objOrder->getId(), false);
                }

                StaticSession::cacheHTML_Array('admin_debug_html', $objOrder->getDebugMessages());
                StaticSession::cacheValueArray('admin_okay_message', $objOrder->getOkayMessages());
                StaticSession::cacheValueArray('admin_error_message', $objOrder->getErrorMessages());
            }
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objOrderNotificationsObserver->getSavedDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objOrderNotificationsObserver->getSavedOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objOrderNotificationsObserver->getSavedErrorMessages());

        StaticSession::cacheHTML_Array('admin_debug_html', $objLocation->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objLocation->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objLocation->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'location-manager&tab=locations');
        exit;
    }

    private function processSave($paramLocationId)
    {
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $paramLocationId);
        $oldUniqueIdentifier = $objLocation->getUniqueIdentifier();
        $saved = $objLocation->save($_POST);
        $newUniqueIdentifier = $objLocation->getUniqueIdentifier();
        if($paramLocationId > 0 && $saved && $oldUniqueIdentifier != '' && $newUniqueIdentifier != $oldUniqueIdentifier)
        {
            $objOrdersObserver->changeLocationUniqueIdentifier($oldUniqueIdentifier, $newUniqueIdentifier);
        }
        if($saved && $this->lang->canTranslateSQL())
        {
            $objLocation->registerForTranslation();
        }

        StaticSession::cacheHTML_Array('admin_debug_html', $objLocation->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objLocation->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objLocation->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'location-manager&tab=locations');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());

        if(isset($_GET['delete_location'])) { $this->processDelete($_GET['delete_location']); }
        if(isset($_POST['save_location']) && isset($_POST['location_id'])) { $this->processSave($_POST['location_id']); }

        $paramLocationId = isset($_GET['location_id']) ? $_GET['location_id'] : "";
        $objLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $paramLocationId);
        $localDetails = $objLocation->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'location-manager&tab=locations');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-location&noheader=true');
        $this->view->networkEnabled = $this->conf->isNetworkEnabled();
        if(!is_null($localDetails))
        {
            $this->view->locationId = $localDetails['location_id'];
            $this->view->locationUniqueIdentifier = $localDetails['location_code'];
            $this->view->locationName = $localDetails['location_name'];
            $this->view->locationPagesDropdown = $objLocationsObserver->getPagesDropdown($localDetails['location_page_id'], "location_page_id", "location_page_id");
            $this->view->streetAddress = $localDetails['street_address'];
            $this->view->city = $localDetails['city'];
            $this->view->state = $localDetails['state'];
            $this->view->zipCode = $localDetails['zip_code'];
            $this->view->country = $localDetails['country'];
            $this->view->phone = $localDetails['phone'];
            $this->view->email = $localDetails['email'];
            $this->view->pickupFee = $localDetails['pickup_fee'];
            $this->view->returnFee = $localDetails['return_fee'];

            $this->view->openMondays = $localDetails['open_mondays'] == 1 ? ' checked="checked"' : '';
            $this->view->openTuesdays = $localDetails['open_tuesdays'] == 1 ? ' checked="checked"' : '';
            $this->view->openWednesdays = $localDetails['open_wednesdays'] == 1 ? ' checked="checked"' : '';
            $this->view->openThursdays = $localDetails['open_thursdays'] == 1 ? ' checked="checked"' : '';
            $this->view->openFridays = $localDetails['open_fridays'] == 1 ? ' checked="checked"' : '';
            $this->view->openSaturdays = $localDetails['open_saturdays'] == 1 ? ' checked="checked"' : '';
            $this->view->openSundays = $localDetails['open_sundays'] == 1 ? ' checked="checked"' : '';

            $this->view->trustedOpenTimeMondaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['open_time_mon'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeTuesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['open_time_tue'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeWednesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['open_time_wed'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeThursdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['open_time_thu'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeFridaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['open_time_fri'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeSaturdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['open_time_sat'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeSundaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['open_time_sun'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));

            $this->view->trustedCloseTimeMondaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['close_time_mon'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeTuesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['close_time_tue'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeWednesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['close_time_wed'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeThursdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['close_time_thu'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeFridaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['close_time_fri'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeSaturdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['close_time_sat'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeSundaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['close_time_sun'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));

            $this->view->lunchEnabled = $localDetails['lunch_enabled'] == 1 ? ' checked="checked"' : '';
            $this->view->trustedLunchStartTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['lunch_start_time'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedLunchEndTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $localDetails['lunch_end_time'], "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));

            $this->view->locationImage1_URL = $localDetails['location_image_1_url'];
            $this->view->locationImage2_URL = $localDetails['location_image_2_url'];
            $this->view->locationImage3_URL = $localDetails['location_image_3_url'];
            $this->view->locationImage4_URL = $localDetails['location_image_4_url'];
            $this->view->locationImage1 = $localDetails['location_image_1'];
            $this->view->locationImage2 = $localDetails['location_image_2'];
            $this->view->locationImage3 = $localDetails['location_image_3'];
            $this->view->locationImage4 = $localDetails['location_image_4'];
            $this->view->demoLocationImage1 = $localDetails['demo_location_image_1'];
            $this->view->demoLocationImage2 = $localDetails['demo_location_image_2'];
            $this->view->demoLocationImage3 = $localDetails['demo_location_image_3'];
            $this->view->demoLocationImage4 = $localDetails['demo_location_image_4'];

            $this->view->afterHoursPickupAllowedChecked = $localDetails['afterhours_pickup_allowed'] == 1 ? ' checked="checked"' : '';
            $this->view->trustedAfterHoursPickupDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, $localDetails['afterhours_pickup_location_id'], 0, $this->lang->getText('LANG_IN_THIS_LOCATION_TEXT'), $localDetails['location_id']
            );
            $this->view->afterHoursPickupFee = $localDetails['afterhours_pickup_fee'];

            $this->view->afterHoursReturnAllowedChecked = $localDetails['afterhours_return_allowed'] == 1 ? ' checked="checked"' : '';
            $this->view->trustedAfterHoursReturnDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, $localDetails['afterhours_return_location_id'], 0, $this->lang->getText('LANG_IN_THIS_LOCATION_TEXT'), $localDetails['location_id']
            );
            $this->view->afterHoursReturnFee = $localDetails['afterhours_return_fee'];
            $this->view->locationOrder = $localDetails['location_order'];
            $this->view->onRemoteWebsiteChecked = $localDetails['on_remote_website'] == 1 ? ' checked="checked"' : '';
        } else
        {
            // Set the view variables
            $this->view->locationId = 0;
            $this->view->locationUniqueIdentifier = $objLocation->generateUniqueIdentifier(); // Generate new code
            $this->view->locationName = '';
            $this->view->locationPagesDropdown = '';
            $this->view->streetAddress = '';
            $this->view->city = '';
            $this->view->state = '';
            $this->view->zipCode = '';
            $this->view->country = '';
            $this->view->phone = '';
            $this->view->email = '';
            $this->view->pickupFee = '0.00';
            $this->view->returnFee = '0.00';

            $this->view->openMondays = ' checked="checked"';
            $this->view->openTuesdays = ' checked="checked"';
            $this->view->openWednesdays = ' checked="checked"';
            $this->view->openThursdays = ' checked="checked"';
            $this->view->openFridays = ' checked="checked"';
            $this->view->openSaturdays = ' checked="checked"';
            $this->view->openSundays = ' checked="checked"';

            $this->view->trustedOpenTimeMondaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '08:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeTuesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '08:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeWednesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '08:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeThursdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '08:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeFridaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '08:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeSaturdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '08:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedOpenTimeSundaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '08:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));

            $this->view->trustedCloseTimeMondaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '19:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeTuesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '19:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeWednesdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '19:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeThursdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '19:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeFridaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '19:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeSaturdaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '19:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedCloseTimeSundaysDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '19:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));

            $this->view->lunchEnabled = '';
            $this->view->trustedLunchStartTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '12:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));
            $this->view->trustedLunchEndTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, '13:00:00', "00:00:00", "23:59:59", $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'));

            $this->view->locationImage1_URL = '';
            $this->view->locationImage2_URL = '';
            $this->view->locationImage3_URL = '';
            $this->view->locationImage4_URL = '';
            $this->view->locationImage1 = '';
            $this->view->locationImage2 = '';
            $this->view->locationImage3 = '';
            $this->view->locationImage4 = '';
            $this->view->demoLocationImage1 = 0;
            $this->view->demoLocationImage2 = 0;
            $this->view->demoLocationImage3 = 0;
            $this->view->demoLocationImage4 = 0;

            $this->view->afterHoursPickupAllowedChecked = '';
            $this->view->trustedAfterHoursPickupDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, 0, 0, $this->lang->getText('LANG_IN_THIS_LOCATION_TEXT'), 0
            );
            $this->view->afterHoursPickupFee = '';

            $this->view->afterHoursReturnAllowedChecked = '';
            $this->view->trustedAfterHoursReturnDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedLocationsDropdownOptionsHTML(
                "BOTH", 0, 0, 0, $this->lang->getText('LANG_IN_THIS_LOCATION_TEXT'), 0
            );
            $this->view->afterHoursReturnFee = '';
            $this->view->locationOrder = '';
            $this->view->onRemoteWebsiteChecked = '';
        }

        // Print the template
        $templateRelPathAndFileName = 'Location'.DIRECTORY_SEPARATOR.'AddEditLocationForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
