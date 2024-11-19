<?php
/**
 * Search step no. 1
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Search;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Class_\ClassesObserver;
use FleetManagement\Models\AttributeGroup\AttributesObserver;
use FleetManagement\Models\Manufacturer\ManufacturersObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Closing\ClosingsObserver;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\Location\LocationsObserver;
use FleetManagement\Models\Partner\PartnersObserver;
use FleetManagement\Controllers\Front\AbstractController;
use FleetManagement\Models\Search\FrontEndSearchManager;
use FleetManagement\Models\Validation\StaticValidator;

final class Step1EditController extends AbstractController
{
    private $objSearch	                = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);

        $this->objSearch = new FrontEndSearchManager($this->conf, $this->lang, $this->dbSets->getAll());
        // No prepare request needed here - it is a new order
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "Form", $paramStyle = "")
    {
        // Create mandatory instances
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $orderId = $objOrdersObserver->getIdByCode($this->orderCode);
        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $orderId);

        // Second - validate the order code
        if($this->orderCode != '')
        {
            $this->objSearch->setVariablesByOrderId($orderId);
        }

        // Third - set object variables - allow to override by _POST, _GET or _SESSION
        $this->objSearch->setVariables();

        if ($this->objSearch->searchEnabled() && $this->objSearch->isValidSearch())
        {
            // Data defined successfully, now remove session variables
            $this->objSearch->unsetVariablesCache();

            // Set fresh session variables
            $this->objSearch->cacheVariables();
        }

        // Load local mandatory classes
        $objPartnersObserver = new PartnersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objManufacturersObserver = new ManufacturersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objClassesObserver = new ClassesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objAttributesObserver = new AttributesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objClosingsObserver = new ClosingsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        if($this->locationId > 0)
        {
            // If there is a pickup coming from shortcode parameters
            $pickupLocationId = $this->locationId;
            $returnLocationId = $this->locationId;
        } else
        {
            if($this->pickupLocationId > 0)
            {
                // If there is a pickup coming from shortcode parameters
                $pickupLocationId = $this->pickupLocationId;
            } else
            {
                // If there is only one pickup location for this item or for all items (-1)
                $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $locationIds = $objLocationsObserver->getItemPickupIds($this->itemModelId);
                $pickupLocationId = sizeof($locationIds) == 1 ? $locationIds[0] : -1;
            }
            if($this->returnLocationId > 0)
            {
                // If there is a pickup coming from shortcode parameters
                $returnLocationId = $this->pickupLocationId;
            } else
            {
                // If there is only one pickup location for this item or for all items (-1)
                $objLocationsObserver = new LocationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
                $locationIds = $objLocationsObserver->getItemReturnIds($this->itemModelId);
                $returnLocationId = sizeof($locationIds) == 1 ? $locationIds[0] : -1;
            }
        }
        $objPickupLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $pickupLocationId);
        $objReturnLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $returnLocationId);

        // Search fields visibility settings
        $pickupLocationVisible = $this->dbSets->getSearchFieldStatus("pickup_location", "VISIBLE");
        $pickupDateVisible = $this->dbSets->getSearchFieldStatus("pickup_date", "VISIBLE");
        $returnLocationVisible = $this->dbSets->getSearchFieldStatus("return_location", "VISIBLE");
        $returnDateVisible = $this->dbSets->getSearchFieldStatus("return_date", "VISIBLE");
        $partnerVisible = $this->dbSets->getSearchFieldStatus("partner", "VISIBLE");
        $manufacturerVisible = $this->dbSets->getSearchFieldStatus("manufacturer", "VISIBLE");
        $classVisible = $this->dbSets->getSearchFieldStatus("body_type", "VISIBLE");
        $attribute2Visible = $this->dbSets->getSearchFieldStatus("transmission_type", "VISIBLE");
        $attribute1Visible = $this->dbSets->getSearchFieldStatus("fuel_type", "VISIBLE");

        // Check if display blocks
        $displaySearchBlock1 = false;
        $displaySearchBlock2 = false;
        $displaySearchBlock3 = false;

        $cancelButtonBlock = 2;
        $searchButtonBlock = 3;
        if($partnerVisible == false && $manufacturerVisible == false && $classVisible == false && $attribute2Visible == false && $attribute1Visible == false)
        {
            if($returnLocationVisible || $returnDateVisible)
            {
                $searchButtonBlock = 2;
            } else
            {
                $searchButtonBlock = 1;
            }
        }

        if($searchButtonBlock == 1 || ($searchButtonBlock == 2 && ($pickupLocationVisible || $pickupDateVisible)))
        {
            $cancelButtonBlock = 1;
        } else if($searchButtonBlock == 3)
        {
            if($returnLocationVisible || $returnDateVisible)
            {
                $cancelButtonBlock = 2;
            } else if($pickupLocationVisible || $pickupDateVisible)
            {
                $cancelButtonBlock = 1;
            } else
            {
                $cancelButtonBlock = 3;
            }
        }

        if($cancelButtonBlock == 1 || $searchButtonBlock == 1 || $pickupLocationVisible || $pickupDateVisible)
        {
            $displaySearchBlock1 = true;
        }
        if($cancelButtonBlock == 2 || $searchButtonBlock == 2 || $returnLocationVisible || $returnDateVisible)
        {
            $displaySearchBlock2 = true;
        }
        if($cancelButtonBlock == 3 || $searchButtonBlock == 3 || $partnerVisible || $manufacturerVisible || $classVisible || $attribute2Visible || $attribute1Visible)
        {
            $displaySearchBlock3 = true;
        }

        // Get pickup FROM-TO times
        if($pickupLocationId > 0)
        {
            $earliestPickupTime = $objPickupLocation->getWeekEarliestPickupTime();
            $latestPickupTime = $objPickupLocation->getWeekLatestPickupTime();
            $afterHoursPickupLocationId = $objPickupLocation->getAfterHoursPickupLocationId();
            if($afterHoursPickupLocationId > 0)
            {
                $objAfterHoursPickupLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $afterHoursPickupLocationId);
                $earliestAfterHoursPickupTime = $objAfterHoursPickupLocation->getWeekEarliestPickupTime();
                $latestAfterHoursPickupTime = $objAfterHoursPickupLocation->getWeekLatestPickupTime();
                if(strtotime(date("Y-m-d")." ".$earliestAfterHoursPickupTime) < strtotime(date("Y-m-d")." ".$earliestPickupTime))
                {
                    $earliestPickupTime = $earliestAfterHoursPickupTime;
                }
                if(strtotime(date("Y-m-d")." ".$latestAfterHoursPickupTime) > strtotime(date("Y-m-d")." ".$latestPickupTime))
                {
                    $latestPickupTime = $latestAfterHoursPickupTime;
                }
            }
        } else
        {
            $earliestPickupTime = "09:00:00";
            $latestPickupTime = "17:00:00";
        }

        // Get return FROM-TO times
        if($returnLocationId > 0)
        {
            $earliestReturnTime = $objReturnLocation->getWeekEarliestReturnTime();
            $latestReturnTime = $objReturnLocation->getWeekLatestReturnTime();
            $afterHoursReturnLocationId = $objReturnLocation->getAfterHoursReturnLocationId();
            if($afterHoursReturnLocationId > 0)
            {
                $objAfterHoursReturnLocation = new Location($this->conf, $this->lang, $this->dbSets->getAll(), $afterHoursReturnLocationId);
                $earliestAfterHoursReturnTime = $objAfterHoursReturnLocation->getWeekEarliestReturnTime();
                $latestAfterHoursReturnTime = $objAfterHoursReturnLocation->getWeekLatestReturnTime();
                if(strtotime(date("Y-m-d")." ".$earliestAfterHoursReturnTime) < strtotime(date("Y-m-d")." ".$earliestReturnTime))
                {
                    $earliestReturnTime = $earliestAfterHoursReturnTime;
                }
                if(strtotime(date("Y-m-d")." ".$latestAfterHoursReturnTime) > strtotime(date("Y-m-d")." ".$latestReturnTime))
                {
                    $latestReturnTime = $latestAfterHoursReturnTime;
                }
            }
        } else
        {
            $earliestReturnTime = "09:00:00";
            $latestReturnTime = "17:00:00";
        }
        $orderPeriod = StaticValidator::getValidPositiveInteger($this->objSearch->getExpectedReturnTimestamp() - $this->objSearch->getExpectedPickupTimestamp(), 0);

        // Set the view variables
        $this->view->pickupLocationId = $this->pickupLocationId;
        $this->view->returnLocationId = $this->returnLocationId;
        $this->view->partnerId = $this->fleetPartnerId;
        $this->view->manufacturerId = $this->manufacturerId;
        $this->view->classId = $this->classId;
        $this->view->attributeId1 = $this->attributeId1;
        $this->view->attributeId2 = $this->attributeId2;
        $this->fillSearchFieldsView(); // Fill search fields view
        $this->fillCustomerFieldsView(); // Fill customer fields view
        $this->view->formAction = $this->actionPageId > 0 ? $this->lang->getTranslatedURL($this->actionPageId) : '';
        $this->view->inputStyle = ConfigurationInterface::INPUT_STYLE;
        $this->view->minDate = intval(($this->dbSets->get('conf_minimum_period_until_pickup') - StaticFormatter::WORLD_TIMEZONES_MAX_DIFFERENCE_IN_SECONDS) / 86400);
        $this->view->newOrder = false;

        $this->view->selectedPickupDate = $this->objSearch->getShortPickupDate();
        $this->view->trustedPickupTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $this->objSearch->getISOPickupTime(), $earliestPickupTime, $latestPickupTime, $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'), array("23:59:59"));
        $this->view->selectedReturnDate = $this->objSearch->getShortReturnDate();
        $this->view->trustedReturnTimeDropdownOptionsHTML = StaticFormatter::getTrustedTimeDropdownOptionsHTML(1800, $this->objSearch->getISOReturnTime(), $earliestReturnTime, $latestReturnTime, $this->lang->getText('LANG_MIDNIGHT_TEXT'), $this->lang->getText('LANG_NOON_TEXT'), array("23:59:59"));

        $this->view->itemModelId = 0;
        $this->view->orderCodeParam = $this->conf->getOrderCodeParam();
        $this->view->orderCode = $objOrder->getCode();
        $this->view->couponCode = $this->objSearch->getCouponCode();

        $this->view->cancelButtonBlock = $cancelButtonBlock;
        $this->view->searchButtonBlock = $searchButtonBlock;

        $this->view->displaySearchBlock1 = $displaySearchBlock1;
        $this->view->displaySearchBlock2 = $displaySearchBlock2;
        $this->view->displaySearchBlock3 = $displaySearchBlock3;

        $this->view->pickupClosedDates = $objClosingsObserver->getAll($this->pickupLocationId > 0 ? $objPickupLocation->getUniqueIdentifier() : "", true); // Limitation is only if provided via param
        $this->view->returnClosedDates = $objClosingsObserver->getAll($this->returnLocationId > 0 ? $objReturnLocation->getUniqueIdentifier() : "", true); // Limitation is only if provided via param
        $this->view->pickupLocationId = $pickupLocationId;
        $this->view->returnLocationId = $returnLocationId;
        $this->view->pickupLocationName = $objPickupLocation->getPrintTranslatedLocationName();
        $this->view->returnLocationName = $objReturnLocation->getPrintTranslatedLocationName();
        
        // Use data from objSearch bellow, because only that data can be selected in dropdown, otherwise it will not be used at all if it was set from shortcode
        $this->view->trustedPickupDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedPickupDropdownOptionsHTML(
            $this->itemModelId, $this->objSearch->getPickupLocationId(), 0, $this->dbSets->getSelect('LANG_SEARCH_PICKUP_CITY_AND_LOCATION_SELECT_TEXT', 'LANG_SEARCH_PICKUP_CITY_AND_LOCATION_SELECT2_TEXT'), -1
        );
        
        $this->view->trustedReturnDropdownOptionsHTML = $objLocationsObserver->getTrustedTranslatedReturnDropdownOptionsHTML(
            $this->itemModelId, $this->objSearch->getReturnLocationId(), 0, $this->dbSets->getSelect('LANG_SEARCH_RETURN_CITY_AND_LOCATION_SELECT_TEXT', 'LANG_SEARCH_RETURN_CITY_AND_LOCATION_SELECT2_TEXT'), -1
        );
        
        $this->view->trustedOrderPeriodsDropdownOptionsHTML = $objOrdersObserver->getTrustedPeriodsDropdownOptionsHTML(
            $this->dbSets->getSelect('LANG_ORDER_PERIOD_SELECT_TEXT', 'LANG_ORDER_PERIOD_SELECT2_TEXT'), $orderPeriod, ""
        );
        
        $this->view->trustedPartnersDropdownOptionsHTML = $objPartnersObserver->getTrustedDropdownOptionsHTML(
            $this->objSearch->getFleetPartnerId(), -1, $this->dbSets->getSelect('LANG_PARTNER_SELECT_TEXT', 'LANG_PARTNER_SELECT2_TEXT')
        );
        
        $this->view->trustedManufacturersDropdownOptionsHTML = $objManufacturersObserver->getTrustedTranslatedDropdownOptionsHTML(
            $this->objSearch->getManufacturerId(), -1, $this->dbSets->getSelect('LANG_MANUFACTURER_SELECT_TEXT', 'LANG_MANUFACTURER_SELECT2_TEXT')
        );
        
        $this->view->trustedClassesDropdownOptionsHTML = $objClassesObserver->getTrustedTranslatedDropdownOptionsHTML(
            $this->objSearch->getClassId(), -1, $this->dbSets->getSelect('LANG_CLASS_SELECT_TEXT', 'LANG_CLASS_SELECT2_TEXT')
        );

        $this->view->trustedAttributeGroup1AttributesDropdownOptionsHTML = $objAttributesObserver->getTrustedTranslatedDropdownOptionsHTML(
            1, $this->objSearch->getAttributeId1(), -1, $this->dbSets->sprintfSelect('LANG_DROPDOWN_SELECT_S_TEXT', 'LANG_DROPDOWN_SELECT2_S_TEXT', $this->lang->getText('LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL1_TEXT'))
        );
        
        $this->view->trustedAttributeGroup2AttributesDropdownOptionsHTML = $objAttributesObserver->getTrustedTranslatedDropdownOptionsHTML(
            2, $this->objSearch->getAttributeId2(), -1, $this->dbSets->sprintfSelect('LANG_DROPDOWN_SELECT_S_TEXT', 'LANG_DROPDOWN_SELECT2_S_TEXT', $this->lang->getText('LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT'))
        );

        // Get the template
        // NOTE: For search edit, the is only one template possible
        $retContent = $this->objSearch->searchEnabled() ? $this->getTemplate('Search', 'Step1Input', $paramLayout, $paramStyle) : '';

        return $retContent;
    }
}