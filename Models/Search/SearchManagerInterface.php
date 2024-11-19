<?php
/**
 * Search must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Search;

interface SearchManagerInterface
{
    // System methods
    public function inDebug();
    public function getOkayMessages();
    public function getErrorMessages();
    public function isValidSearch();

    // Main methods
    public function setVariables();

    // Time & Date methods for printing to the user screen (Applies formatting, localization and GMT zone adjustment
    public function getI18nExpectedPickupDate();
    public function getI18nExpectedPickupTime();
    public function getI18nExpectedReturnDate();
    public function getI18nExpectedReturnTime();
    public function getShortPickupDate();
    public function getISOPickupTime();
    public function getShortReturnDate();
    public function getISOReturnTime();

    // Time & Date methods for system use
    public function getLocalPickupDate();
    public function getLocalPickupTime();
    public function getLocalReturnDate();
    public function getLocalReturnTime();
    public function getLocalPickupDayOfWeek();
    public function getLocalReturnDayOfWeek();
    public function getExpectedPickupTimestamp();
    public function getExpectedReturnTimestamp();

    // Methods to retrieve booking location, item, customer and booking details
    public function getCouponCode();
    public function getPrintCouponCode();
    public function getEditCouponCode();
    public function getPickupLocationId();
    public function getReturnLocationId();
    public function getClassId();
    public function getAttributeId1();
    public function getAttributeId2();
    public function getInputDataArray();
    public function getItemModelIds();
    public function getItemModelUnits();
    public function getItemModelOptions();
    public function getExtraIds();
    public function getExtraUnits();
    public function getExtraOptions();
    public function getItemQuantity($paramItemModelId);
    public function getItemModelOption($paramItemModelId);
    public function getExtraQuantity($paramExtraId);
    public function getExtraOption($paramExtraId);
}