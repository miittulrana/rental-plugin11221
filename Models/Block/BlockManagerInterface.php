<?php
/**
 * Search must-have interface
 * Interface purpose is describe all public methods used available in the class and enforce to use them
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Block;

interface BlockManagerInterface
{
    // System methods
    public function inDebug();
    public function getOkayMessages();
    public function getErrorMessages();
    public function isValidBlock();

    // Main methods
    public function setVariables();

    // Time & Date methods for printing to the user screen (Applies formatting, localization and GMT zone adjustment
    public function getI18nStartDate();
    public function getI18nStartTime();
    public function getI18nEndDate();
    public function getI18nEndTime();
    public function getShortStartDate();
    public function getShortStartTime();
    public function getShortEndDate();
    public function getShortEndTime();

    // Time & Date methods for system use
    public function getLocalStartDate();
    public function getLocalStartTime();
    public function getLocalEndDate();
    public function getLocalEndTime();
    public function getLocalStartDayOfWeek();
    public function getLocalEndDayOfWeek();
    public function getBlockPeriod();
    public function getStartTimestamp();
    public function getEndTimestamp();

    // Methods to retrieve ids, units and elements
    public function getIds();
    public function getUnits($paramElementId);
    public function getAvailable();
    public function getSelectedWithDetails($paramElementIds);
    public function getAvailableWithDetails($paramElementIds);
}