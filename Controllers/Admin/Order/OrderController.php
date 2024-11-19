<?php
/**
 * @package FleetManagement
 * @note Variables prefixed with 'local' are not used in templates
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Order;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\ItemModel\ItemModelsAvailabilityCalendar;
use FleetManagement\Models\Extra\ExtrasAvailabilityCalendar;
use FleetManagement\Models\Validation\StaticValidator;

final class OrderController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // Create mandatory instances
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objItemModelsAvailabilityCalendar = new ItemModelsAvailabilityCalendar($this->conf, $this->lang, $this->dbSets->getAll());
        $objExtrasAvailabilityCalendar = new ExtrasAvailabilityCalendar($this->conf, $this->lang, $this->dbSets->getAll());

        // Order lists: start
        $todayStartTimestamp = StaticValidator::getTodayStartTimestamp();
        // DEBUG
        //echo "<br />TIME: ".time().", TODAY&#39;S START: {$todayStartTimestamp}";

        $backToPickupsURL_Part = "&back_page={$this->conf->getExtURL_Prefix()}order-manager&back_tab=pickups";
        $backToReturnsURL_Part = "&back_page={$this->conf->getExtURL_Prefix()}order-manager&back_tab=returns";
        $backToOrdersURL_Part = "&back_page={$this->conf->getExtURL_Prefix()}order-manager&back_tab=orders";
        $trustedAdminPickupListHTML = $objOrdersObserver->getTrustedAdminPickupsHTML($todayStartTimestamp, -1, 0, $backToPickupsURL_Part);
        $trustedAdminReturnListHTML = $objOrdersObserver->getTrustedAdminReturnsHTML($todayStartTimestamp, -1, 0, $backToReturnsURL_Part);
        $trustedAdminOrderListHTML = $objOrdersObserver->getTrustedAdminOrdersHTML($todayStartTimestamp, -1, 0, $backToOrdersURL_Part);
        // Order lists: end

        // Item Model Calendar table: Start
        $localSelectedTimestamp = strtotime(date("Y-m-d")." 00:00:00");
        $localSelectedYear = date("Y", $localSelectedTimestamp);
        $localSelectedMonth = date("m", $localSelectedTimestamp);
        $localSelectedDay = date("d", $localSelectedTimestamp);
        $itemModelsAvailabilityCalendar = $objItemModelsAvailabilityCalendar->get30DaysCalendar(-1, -1, -1, -1, -1, -1, -1, -1, $localSelectedYear, $localSelectedMonth, $localSelectedDay);
        // Item Model Calendar table: End

        // Extra Calendar table: Start
        $localSelectedTimestamp = strtotime(date("Y-m-d")." 00:00:00");
        $localSelectedYear = date("Y", $localSelectedTimestamp);
        $localSelectedMonth = date("m", $localSelectedTimestamp);
        $localSelectedDay = date("d", $localSelectedTimestamp);
        $extrasAvailabilityCalendar = $objExtrasAvailabilityCalendar->get30DaysCalendar(-1, -1, -1, $localSelectedYear, $localSelectedMonth, $localSelectedDay);
        // Extra Calendar table: End

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'pickups', 'returns', 'orders', 'item-models-availability', 'extras-availability'
        ), 'pickups', isset($_GET['tab']) ? $_GET['tab'] : '');

        // 2. Set the view variables - other variables
        $this->view->html = "";
        $this->view->noonTime = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." ".$this->dbSets->get('conf_noon_time')), true);
        $this->view->trustedAdminPickupListHTML = $trustedAdminPickupListHTML;
        $this->view->trustedAdminReturnListHTML = $trustedAdminReturnListHTML;
        $this->view->trustedAdminOrderListHTML = $trustedAdminOrderListHTML;
        $this->view->itemModelsAvailabilityCalendar = $itemModelsAvailabilityCalendar;
        $this->view->extrasAvailabilityCalendar = $extrasAvailabilityCalendar;

        // Print the template
        $templateRelPathAndFileName = 'Order'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
