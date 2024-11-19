<?php
/**
 * Search step no. 4
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Front\Search;
use FleetManagement\Models\AdditionalFee\AdditionalFeesObserver;
use FleetManagement\Models\Order\OrdersObserver;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Distance\Distance;
use FleetManagement\Models\Distance\DistancesObserver;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Order\Order;
use FleetManagement\Models\Location\Location;
use FleetManagement\Models\AdditionalFee\AdditionalFeeManager;
use FleetManagement\Models\Order\Period;
use FleetManagement\Models\Tax\TaxManager;
use FleetManagement\Models\Location\LocationFeeManager;
use FleetManagement\Models\Search\FrontEndSearchManager;
use FleetManagement\Models\Search\SearchItemModelsManager;
use FleetManagement\Models\Search\SearchExtrasManager;
use FleetManagement\Controllers\Front\AbstractController;

final class Step4OptionsController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramArrLimits = array())
    {
        parent::__construct($paramConf, $paramLang, $paramArrLimits);
    }

    /**
     * @param string $paramLayout
     * @param string $paramStyle
     * @param string $paramFailureLayout
     * @param string $paramFailureStyle
     * @return string
     * @throws \Exception
     */
    public function getContent($paramLayout = "List", $paramStyle = "", $paramFailureLayout = "Details", $paramFailureStyle = "")
    {
        // Load local mandatory classes
        $objSearch = new FrontEndSearchManager($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrdersObserver = new OrdersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objOrdersObserver->cancelExpired();
        $objDistancesObserver = new DistancesObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objAdditionalFeesObserver = new AdditionalFeesObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // DEBUG
        //echo "INITIAL REQUEST VARS: ".nl2br(print_r($_REQUEST, true));
        //echo "INITIAL SESSION VARS: ".nl2br(print_r($_SESSION, true));
        //echo "INITIAL COOKIE VARS: ".nl2br(print_r($_COOKIE, true));

        // First - set defaults
        $errorMessages = array();

        // Second - process the order code if provided
        $orderId = 0;
        $isFrozenOrder = false;
        if($this->orderCode != '')
        {
            $orderId = $objOrdersObserver->getIdByCode($this->orderCode);
            $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $orderId);
            $isFrozenOrder = $objOrder->isFrozen();
            if($isFrozenOrder === false)
            {
                // Can proceed
                $objSearch->setVariablesByOrderId($orderId);
            }
        }

        // Third - set object variables - allow to override by _POST, _GET or _SESSION
        $objSearch->setVariables();

        // Fourth - validate time input
        $objSearch->validateInBeforeOut($objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp());
        $objSearch->validateTimeInput($objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp());

        // Fifth - validate pick-up
        $objSearch->validatePickupInput($objSearch->getPickupLocationId(), $objSearch->getExpectedPickupTimestamp());

        // Sixth - validate return
        $objSearch->validateReturnInput($objSearch->getReturnLocationId(), $objSearch->getExpectedReturnTimestamp());

        if ($objSearch->searchEnabled() && $objSearch->isValidSearch())
        {
            // Data defined successfully, now remove session variables
            $objSearch->unsetVariablesCache();
            // Set fresh session variables
            $objSearch->cacheVariables();
        }

        // DEBUG
        //echo "<br />UPDATED SESSION VARS: ".nl2br(print_r($_SESSION, true));
        //echo "<br />UPDATED COOKIE VARS: ".nl2br(print_r($_COOKIE, true));

        $objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $objOrdersObserver->getIdByCode($this->orderCode));
        $objTaxManager = new TaxManager($this->conf, $this->lang, $this->dbSets->getAll());
        $taxPercentage = $objTaxManager->getTaxPercentage($objSearch->getPickupLocationId(), $objSearch->getReturnLocationId());
        $objPickupLocation = new Location(
            $this->conf, $this->lang, $this->dbSets->getAll(), $objSearch->getPickupLocationId()
        );
        $locationUniqueIdentifier = $objPickupLocation->getUniqueIdentifier(); // We use pickup location code for availability checks
        $objReturnLocation = new Location(
            $this->conf, $this->lang, $this->dbSets->getAll(), $objSearch->getReturnLocationId()
        );
        $distanceId = $objDistancesObserver->getIdByTwoLocations($objSearch->getPickupLocationId(), $objSearch->getReturnLocationId());
        $objDistance = new Distance($this->conf, $this->lang, $this->dbSets->getAll(), $distanceId);
        $objPickupFeeManager = new LocationFeeManager(
            $this->conf, $this->lang, $this->dbSets->getAll(), $objSearch->getPickupLocationId(), $taxPercentage
        );
        $objReturnFeeManager = new LocationFeeManager(
            $this->conf, $this->lang, $this->dbSets->getAll(), $objSearch->getReturnLocationId(), $taxPercentage
        );
        $additionalFeeId = $objAdditionalFeesObserver->getIdByTwoLocations($objSearch->getPickupLocationId(), $objSearch->getReturnLocationId());
        $objAdditionalFeeManager = new AdditionalFeeManager(
            $this->conf, $this->lang, $this->dbSets->getAll(), $additionalFeeId, $taxPercentage
        );
        $objAfterHoursPickupLocation = new Location(
            $this->conf, $this->lang, $this->dbSets->getAll(), $objPickupLocation->getAfterHoursPickupLocationId()
        );
        $objAfterHoursReturnLocation = new Location(
            $this->conf, $this->lang, $this->dbSets->getAll(), $objReturnLocation->getAfterHoursReturnLocationId()
        );

        $itemModels = array();
        $extras = array();
        $gotResults = false;
        $gotAnyExtras = false;

        // Get all data
        $pickupInAfterHours = $objPickupLocation->isAfterHoursTime($objSearch->getLocalPickupDayOfWeek(), $objSearch->getLocalPickupTime());
        $returnInAfterHours = $objReturnLocation->isAfterHoursTime($objSearch->getLocalReturnDayOfWeek(), $objSearch->getLocalReturnTime());
        $pickupDetails = $objPickupLocation->getDetailsByDayOfWeek($objSearch->getLocalPickupDayOfWeek(), true);
        $returnDetails = $objReturnLocation->getDetailsByDayOfWeek($objSearch->getLocalReturnDayOfWeek(), true);
        $pickupFees = $objPickupFeeManager->getUnitDetails($objAdditionalFeeManager->getSingleFee(), $pickupInAfterHours);
        $returnFees = $objReturnFeeManager->getUnitDetails($objAdditionalFeeManager->getSingleFee(), $returnInAfterHours);

        $pickupOpenTime = isset($pickupDetails['open_time']) ? $pickupDetails['open_time'] : "";
        $pickupCloseTime = isset($pickupDetails['close_time']) ? $pickupDetails['close_time'] : "";
        $returnOpenTime = isset($returnDetails['open_time']) ? $returnDetails['open_time'] : "";
        $returnCloseTime = isset($returnDetails['close_time']) ? $returnDetails['close_time'] : "";
        $afterHoursPickupDetails = $objAfterHoursPickupLocation->getAfterHoursDetails($pickupOpenTime, $pickupCloseTime, $objSearch->getLocalPickupDayOfWeek());
        $afterHoursReturnDetails = $objAfterHoursReturnLocation->getAfterHoursDetails($returnOpenTime, $returnCloseTime, $objSearch->getLocalReturnDayOfWeek());

        $pickupIsWorkingInAfterHours = $objAfterHoursPickupLocation->isValidForAfterHoursPickup($objSearch->getLocalPickupDayOfWeek(), $pickupOpenTime, $pickupCloseTime);
        $returnIsWorkingInAfterHours = $objAfterHoursReturnLocation->isValidForAfterHoursReturn($objSearch->getLocalReturnDayOfWeek(), $returnOpenTime, $returnCloseTime);

        if($this->orderCode != "")
        {
            $pageLabel = $this->lang->getText('LANG_ORDER_CODE_TEXT')." ".$this->orderCode." ".$this->lang->getText('LANG_ORDER_EDIT_TEXT');
            if($objSearch->getCouponCode() != '')
            {
                $pageLabel .= '. '.$this->lang->getText('LANG_COUPON_TEXT').': '.$objSearch->getCouponCode();
            }
        } else
        {
            $pageLabel = $this->lang->getText('LANG_ORDER_DATA_TEXT');
            if($objSearch->getCouponCode() != '')
            {
                $pageLabel .= '. '.$this->lang->getText('LANG_COUPON_TEXT').': '.$objSearch->getCouponCode();
            }
        }

        $isValid = $objSearch->isValidSearch() && ($this->orderCode == '' || ($this->orderCode != '' && $isFrozenOrder === false));
        if($isValid)
        {
            $gotResults = false;
            $gotAnyExtras = false;
            $objSearchItemModelsManager = new SearchItemModelsManager(
                $this->conf, $this->lang, $this->dbSets->getAll(), $taxPercentage, $locationUniqueIdentifier,
                $orderId, $objSearch->getCouponCode()
            );
            $availableItemModelIds = $objSearchItemModelsManager->getAvailableItemModelIds(
                $objSearch->getPickupLocationId(),
                $objSearch->getReturnLocationId(),
                $objSearch->getFleetPartnerId(),
                $objSearch->getManufacturerId(),
                $objSearch->getClassId(),
                $objSearch->getAttributeId1(),
                $objSearch->getAttributeId2()
            );
            $selectedItemModelIds = $objSearchItemModelsManager->getExistingSelectedItemModelIds($objSearch->getItemModelIds(), $availableItemModelIds);
            $objSearchExtrasManager = new SearchExtrasManager(
                $this->conf, $this->lang, $this->dbSets->getAll(), $taxPercentage, $locationUniqueIdentifier,
                $orderId, $selectedItemModelIds
            );
            $availableExtraIds = $objSearchExtrasManager->getAvailableExtraIds();

            $itemModels = $objSearchItemModelsManager->getItemModelsWithPricesAndOptions(
                $selectedItemModelIds, $objSearch->getItemModelUnits(), $objSearch->getItemModelOptions(),
                $objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp(), false
            );
            $extras = $objSearchExtrasManager->getExtrasWithPricesAndOptions(
                $availableExtraIds, $objSearch->getExtraUnits(), $objSearch->getExtraOptions(),
                $objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp(), false
            );

            if(sizeof($itemModels) > 0)
            {
                $gotResults = true;
            }
            if(sizeof($extras) > 0)
            {
                $gotAnyExtras = true;
            }
        }

        $showLocationSimpleFees = false;
        if($pickupFees['unit'][$pickupInAfterHours ? 'afterhours_return_fee_dynamic' : 'return_fee_dynamic'] > 0.00)
        {
            $showLocationSimpleFees = true;
        } else if($returnFees['unit'][$returnInAfterHours ? 'afterhours_return_fee_dynamic' : 'return_fee_dynamic'] > 0.00)
        {
            $showLocationSimpleFees = true;
        }

        $showWorkingHours = false;

        // Pick-up details always has an array content here
        if($pickupDetails['afterhours_pickup_allowed'] == 0 && ($pickupDetails['open_time'] != "00:00:00" || $pickupDetails['close_time'] != "23:59:59"))
        {
            $showWorkingHours = true;
        }
        // Return details always has an array content here
        if($returnDetails['afterhours_return_allowed'] == 0 && ($returnDetails['open_time'] != "00:00:00" || $returnDetails['close_time'] != "23:59:59"))
        {
            $showWorkingHours = true;
        }

        // Period
        $objPeriod = new Period($this->conf, $this->lang, $this->dbSets->getAll());

        // Set the view variables
        $this->fillSearchFieldsView(); // Fill search fields view
        $this->fillCustomerFieldsView(); // Fill customer fields view
        $this->view->objSearch = $objSearch;
        $this->view->pageLabel = $pageLabel;
        $this->view->orderCodeParam = $this->conf->getOrderCodeParam();
        $this->view->orderCode = $this->orderCode;
        $this->view->couponCode = $objSearch->getCouponCode();
        $this->view->showAll = $objSearch->getShowAllArray();
        $this->view->cameFromSingleStep1 = isset($_REQUEST[$this->conf->getExtPrefix().'came_from_single_item_model_step1']) ? true : false;
        $this->view->itemModels = $itemModels;
        $this->view->extras = $extras;
        $this->view->pickup = array_merge($pickupDetails, $pickupFees); // Pick-up details and fees always have array content here
        $this->view->return = array_merge($returnDetails, $returnFees); // Return details and fees always have array content here
        $this->view->distance = $objDistance->getDetails(true);
        $this->view->complexPickup = $objPickupLocation->isComplexLocation();
        $this->view->complexReturn = $objReturnLocation->isComplexLocation();
        $this->view->expectedDurationText = $objPeriod->getDurationText($objSearch->getExpectedPickupTimestamp(), $objSearch->getExpectedReturnTimestamp());
        $this->view->showLocationSimpleFees = $showLocationSimpleFees;
        $this->view->showWorkingHours = $showWorkingHours;
        $this->view->showWorkingHours = $showWorkingHours;
        $this->view->pickupIsWorkingInAfterHours = $pickupIsWorkingInAfterHours;
        $this->view->returnIsWorkingInAfterHours = $returnIsWorkingInAfterHours;
        $this->view->pickupInAfterHours = $pickupInAfterHours;
        $this->view->returnInAfterHours = $returnInAfterHours;
        $this->view->afterHoursPickupDetails = $afterHoursPickupDetails;
        $this->view->afterHoursReturnDetails = $afterHoursReturnDetails;
        $this->view->gotResults = $gotResults;
        $this->view->gotAnyExtras = $gotAnyExtras;
        $this->view->newOrder = $this->orderCode != "" ? false : true;
        $this->view->searchPageAction = $this->actionPageId > 0 ? $this->lang->getTranslatedURL($this->actionPageId) : '';
        $this->view->goBackURL = $this->actionPageId > 0 ? $this->lang->getTranslatedURL($this->actionPageId) : site_url();
        $this->view->errorMessages = implode("\n\n", array_merge($errorMessages, $objSearch->getErrorMessages()));

        // Get template name
        if($isValid && $gotResults)
        {
            $templateName = 'Step4Options';
            $layout = sanitize_text_field($paramLayout);
            $style = sanitize_text_field($paramStyle);
        } else if($isValid)
        {
            $templateName = 'FailureWithSearchAll';
            $layout = sanitize_text_field($paramFailureLayout);
            $style = sanitize_text_field($paramFailureStyle);
        } else
        {
            $templateName = 'Failure'; // Failure template
            $layout = sanitize_text_field($paramFailureLayout);
            $style = sanitize_text_field($paramFailureStyle);
        }

        // Get the template
        $retContent = $objSearch->searchEnabled() ? $this->getTemplate('Search', $templateName, $layout, $style) : '';

        return $retContent;
    }
}