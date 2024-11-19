<?php
/**
 * Units Manager
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Unit;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

abstract class AbstractUnitManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0; // 1 - items (3 - deep), 2 - extras (4 - deep), 0 - disabled
    protected $debugPickupTimestamp     = -1; // timestamp for exact pickup, or -1 - for all
    protected $elementSKU               = "";
    protected $pickupTimestamp          = 0;
    protected $returnTimestamp          = 0;
    protected $blockPeriod              = 0;

    public function __construct(
        ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings,
        $paramElementSKU, $paramTimestampFrom, $paramTimestampTill
    ) {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->elementSKU = sanitize_text_field($paramElementSKU);
        $this->pickupTimestamp = StaticValidator::getValidPositiveInteger($paramTimestampFrom, 0);
        $this->returnTimestamp = StaticValidator::getValidPositiveInteger($paramTimestampTill, 0);
        // Set block interval between two booking
        $this->setBlockPeriod($paramSettings);
    }

    /**
     * Set block interval between two booking - called in class constructor
     * @param array $paramOptionalSettings
     */
    private function setBlockPeriod($paramOptionalSettings = array())
    {
        if(isset($paramOptionalSettings['conf_minimum_block_period_between_bookings']))
        {
            // Fast processing mode (for availability and price table), to reduce sql query amount
            $this->blockPeriod = StaticValidator::getValidPositiveInteger($paramOptionalSettings['conf_minimum_block_period_between_bookings'], 0);
        } else
        {
            $sql = "
				SELECT conf_value AS minimum_block_period_between_bookings
				FROM {$this->conf->getPrefix()}settings
				WHERE conf_key='conf_minimum_block_period_between_bookings' AND blog_id='{$this->conf->getBlogId()}'
			";
            $blockPeriod = $this->conf->getInternalWPDB()->get_var($sql);

            if(!is_null($blockPeriod))
            {
                $this->blockPeriod = StaticValidator::getValidPositiveInteger($blockPeriod, 0);
            } else
            {
                $this->blockPeriod = 0;
            }
        }
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * Supports units
     * @param string $paramType - "ITEM" or "EXTRA"
     * @param string $paramLocationUniqueIdentifier
     * @param int $paramIgnoreFromOrderId (DEFAULT = 0, when it will not exclude any booking from calculation any booking)
     * @return int
     * @internal param int $paramLocationId (DEFAULT = 0, when it is applied to any location)
     */
    protected function getTotalUnitsOrderedByType($paramType = "ITEM", $paramLocationUniqueIdentifier = "", $paramIgnoreFromOrderId = 0)
    {
        $arrDebugCodes = $paramType == "EXTRA" ? array(2, 4) :  array(1, 3);
        $deepDebugCode = $paramType == "EXTRA" ? 4 : 3;
        $sanitizedElementSKU = sanitize_text_field($this->elementSKU);
        $validElementSKU = esc_sql($sanitizedElementSKU);
        $sanitizedLocationUniqueIdentifier = sanitize_text_field($paramLocationUniqueIdentifier);
        $validIgnoreFromOrderId = StaticValidator::getValidPositiveInteger($paramIgnoreFromOrderId, 0);
        $validStartTimestampMinusBlockPeriod = StaticValidator::getValidPositiveInteger($this->pickupTimestamp - $this->blockPeriod, 0);
        $validStartTimestamp = StaticValidator::getValidPositiveInteger($this->pickupTimestamp, 0);
        $validEndTimestamp = StaticValidator::getValidPositiveInteger($this->returnTimestamp, 0);
        $validEndTimestampPlusBlockPeriod = StaticValidator::getValidPositiveInteger($this->returnTimestamp + $this->blockPeriod, 0);

        if(in_array($this->debugMode, $arrDebugCodes) && in_array($this->debugPickupTimestamp, array(-1,$this->pickupTimestamp)))
        {
            echo "<br />----------------------------------------------";
        }

        // ------- Intersection Algorithm -------
        //  (C) 2016, Kestutis Matuliauskas
        // All plugin's code, including this complex algorithm described bellow
        // is COPYRIGHTED BY GNU AFFERO GENERAL PUBLIC LICENSE (AGPL)
        // And is STRICTLY PROHIBITED TO REPLICATE, COPY, SELL OR USE anywhere else, EXCEPT IN THIS PLUGIN!
        //
        // ---------- New Algorithm description ----------
        // 1. Make intervals list
        // 2. For each interval - sum and save the number of totally booked units
        // 3. Get the max number
        //
        // NEW ORDER:
        // 2016-01-09 - 2016-01-31
        //
        // EXISTING ORDERS:
        // 2016-01-04 - 2016-01-10
        // 2016-01-01 - 2016-01-10
        // 2016-01-11 - 2016-01-13
        // 2016-01-09 - 2016-01-11
        // 2016-01-14 - 2016-01-20
        //
        // WHERE LINE 1. SEARCH FOR OTHER ORDERS BETWEEN:    15th -> (30+1)th    [NEW ORDER FROM/TO: 10th -> 25th]
        // WHERE LINE 2. SEARCH FOR OTHER ORDERS BETWEEN: (5+1)th ->  15th       [NEW ORDER FROM/TO: 10th -> 25th]
        // WHERE LINE 3. SEARCH FOR OTHER ORDERS BETWEEN:    5th  ->  30th       [NEW ORDER FROM/TO: 10th -> 25th]
        // WHERE LINE 4. SEARCH FOR OTHER ORDERS BETWEEN: 5th->15h, 20th->30th   [NEW ORDER FROM/TO: 10th -> 25th]
        // And sometimes there is 2 or 3 bookings in between, i.e. 6th-8th, 9th-10th, 10th-12th, 11th-13th,
        // which only partly intersects the 5th-30th booking
        // -------------------------------------------------------------------
        // -------------------------------------------------------------------
        // -------------------------------------------------------------------

        // 1. We need to run a SQL query to find all previous bookings, which:
        //   1.1. has pickup date between 10th and (25+BLOCK_PERIOD)th, or
        //   1.2. has return date between (10-BLOCK_PERIOD)th and 25th
        //   1.3. has pickup date before 10th and return date after (25+BLOCK_PERIOD)th

        if($paramType == "EXTRA")
        {
            $searchSQLAdd = "bopt.extra_sku='{$validElementSKU}' AND";
        } else
        {
            $searchSQLAdd = "bopt.item_sku='{$validElementSKU}' AND";
        }
        $searchSQL = "
			SELECT bopt.booking_id, bopt.units_booked AS units_ordered,
			bb.booking_code, bb.pickup_timestamp, bb.return_timestamp, bb.pickup_location_code AS location_code
			FROM {$this->conf->getPrefix()}booking_options bopt
			JOIN {$this->conf->getPrefix()}bookings bb ON bopt.booking_id=bb.booking_id
			WHERE {$searchSQLAdd} bb.booking_id!='{$validIgnoreFromOrderId}' AND bb.is_cancelled='0'
			AND (
                (
                    bb.pickup_timestamp BETWEEN '{$validStartTimestamp}' AND '{$validEndTimestampPlusBlockPeriod}'
                ) OR (
                    bb.return_timestamp BETWEEN '{$validStartTimestampMinusBlockPeriod}' AND '{$validEndTimestamp}'
                ) OR (
                    bb.pickup_timestamp < '{$validStartTimestamp}' AND bb.return_timestamp > '{$validEndTimestampPlusBlockPeriod}'
                )
            )
		";

        $orderedData = $this->conf->getInternalWPDB()->get_results($searchSQL, ARRAY_A);

        $allUnitsOrdered = false;
        $arrPickupTimestamps = array();
        $arrReturnTimestamps = array();
        $arrDebugReturnTimestampsWithoutBlockPeriod = array();

        foreach ($orderedData AS $orderedRow)
        {
            // Make edit-ready
            $orderedRow['location_code'] = stripslashes($orderedRow['location_code']);

            // 3. Check if all units are blocked
            if($orderedRow['units_ordered'] == -1)
            {
                if($orderedRow['location_code'] == "" || $orderedRow['location_code'] == $sanitizedLocationUniqueIdentifier)
                {
                    // Network block: this block is applied for this location, or if the block is applied to all locations
                    $allUnitsOrdered = true;
                    // Exit foreach
                    break;
                } else
                {
                    // This block is applied for other location
                    // Skip to the next booking
                    continue;
                }
            }

            if(
                !in_array($orderedRow['pickup_timestamp'], $arrPickupTimestamps) &&
                $orderedRow['pickup_timestamp'] >= $validStartTimestamp &&
                $orderedRow['pickup_timestamp'] <= $validEndTimestampPlusBlockPeriod
            ) {
                // 4. Add current pickup date to the list
                $arrPickupTimestamps[] = $orderedRow['pickup_timestamp'];
            }

            if(
                !in_array($orderedRow['return_timestamp']+$this->blockPeriod, $arrReturnTimestamps) &&
                $orderedRow['return_timestamp'] >= $validStartTimestamp &&
                $orderedRow['return_timestamp'] <= $validEndTimestampPlusBlockPeriod
            ) {
                // 5. Add current pickup date to the list
                $arrReturnTimestamps[] = $orderedRow['return_timestamp']+$this->blockPeriod;
                $arrDebugReturnTimestampsWithoutBlockPeriod[] = $orderedRow['return_timestamp'];
            }
        }

        $arrPeriods = array();
        $maxUnitsOrdered = 0;
        if($allUnitsOrdered === true)
        {
            $maxUnitsOrdered = -1;
        } else
        {
            // Now we are sure, that there is no blocks in the list, and no non-valid blocks

            // 6. Sort the arrays from lowest to biggest
            sort($arrPickupTimestamps, SORT_NUMERIC);
            sort($arrReturnTimestamps, SORT_NUMERIC);
            sort($arrDebugReturnTimestampsWithoutBlockPeriod, SORT_NUMERIC);

            // 7. Get first pickup timestamps
            if(sizeof($arrPickupTimestamps) >= 1)
            {
                $firstPickupTimestamp = $arrPickupTimestamps[0];

                // 7.1. If search start time is earlier than earliest matching pickup
                if($validStartTimestamp < $firstPickupTimestamp)
                {
                    // Then add search start time to array
                    array_unshift($arrPickupTimestamps, $validStartTimestamp);

                    if($this->debugMode == $deepDebugCode && in_array($this->debugPickupTimestamp, array(-1, $this->pickupTimestamp)))
                    {
                        echo "<br />Search start timestamp {$validStartTimestamp} added to pickup timestamps array.";
                    }
                }
            } else
            {
                $firstPickupTimestamp = $validStartTimestamp;
                array_unshift($arrPickupTimestamps, $validStartTimestamp);

                if($this->debugMode == $deepDebugCode && in_array($this->debugPickupTimestamp, array(-1, $this->pickupTimestamp)))
                {
                    echo "<br />Search start timestamp {$validStartTimestamp} added to pickup timestamps array.";
                }
            }

            // 8. Get last return timestamps
            if(sizeof($arrReturnTimestamps) >= 1)
            {
                $lastReturnTimestamp = $arrReturnTimestamps[sizeof($arrReturnTimestamps)-1];

                // 8.1. If search end time plus block period is later than latest matching return
                if($validEndTimestampPlusBlockPeriod > $lastReturnTimestamp)
                {
                    // Then add search end time + block period to array
                    $arrReturnTimestamps[] = $validEndTimestampPlusBlockPeriod;
                    $arrDebugReturnTimestampsWithoutBlockPeriod[] = $validEndTimestamp;

                    if($this->debugMode == $deepDebugCode && in_array($this->debugPickupTimestamp, array(-1, $this->pickupTimestamp)))
                    {
                        echo "<br />Search end timestamp ({$validEndTimestamp}) plus block period ({$this->blockPeriod}) ";
                        echo "- {$validEndTimestampPlusBlockPeriod} added to return timestamps array.";
                    }
                }
            } else
            {
                $lastReturnTimestamp = $validEndTimestampPlusBlockPeriod;
                $arrReturnTimestamps[] = $validEndTimestampPlusBlockPeriod;
                $arrDebugReturnTimestampsWithoutBlockPeriod[] = $validEndTimestamp;
            }

            if($this->debugMode == $deepDebugCode && in_array($this->debugPickupTimestamp, array(-1, $this->pickupTimestamp)))
            {
                echo "<br />First Pickup Timestamp: {$firstPickupTimestamp}";
                echo "<br />Last Return Timestamp: {$lastReturnTimestamp}";
            }

            // 10. Make timestamp periods for each pickup
            foreach($arrPickupTimestamps AS $pickupTimestamp)
            {
                // 10.1 By default - set closest pickup to search start timestamp
                $closestReturnTimestamp = $validEndTimestampPlusBlockPeriod;
                $closestReturnTimeDistance = -1;

                foreach($arrReturnTimestamps AS $returnTimestampWithBlockPeriod)
                {
                    // 10.1. Get only those returns, which is later or equal to pickup
                    if($pickupTimestamp <= $returnTimestampWithBlockPeriod)
                    {
                        if(
                            $closestReturnTimeDistance == -1 ||
                            (
                                $closestReturnTimeDistance >= 0 &&
                                $closestReturnTimeDistance > $returnTimestampWithBlockPeriod - $pickupTimestamp
                            )
                        ) {
                            // If previous closest return distance was longer, then make it sorter
                            $closestReturnTimeDistance = $returnTimestampWithBlockPeriod - $pickupTimestamp;
                            $closestReturnTimestamp = $returnTimestampWithBlockPeriod;
                        }
                    }
                }

                // 10.2. Add new period
                $arrPeriods[] = array(
                    "from" => $pickupTimestamp,
                    "till" => $closestReturnTimestamp,
                );
            }

            // 11. Get total size of periods
            $totalPeriods = sizeof($arrPeriods);

            // 12. Loop over all timestamp periods
            for($i = 0; $i < $totalPeriods; $i++)
            {
                $totalUnitsOrdered = 0;
                $periodStart = $arrPeriods[$i]['from'];
                $periodEnd = $arrPeriods[$i]['till'];
                foreach ($orderedData AS $orderedRow)
                {
                    if(
                        (
                            $orderedRow['pickup_timestamp'] >= $periodStart && $orderedRow['pickup_timestamp'] <= $periodEnd
                        ) || (
                            $orderedRow['return_timestamp']+$this->blockPeriod >= $periodStart &&
                            $orderedRow['return_timestamp']+$this->blockPeriod <= $periodEnd
                        ) || (
                            $orderedRow['pickup_timestamp'] < $periodStart && $orderedRow['return_timestamp'] > $periodEnd
                        )
                    ) {
                        $totalUnitsOrdered += $orderedRow['units_ordered'];
                    }
                }
                $arrPeriods[$i]['total_units_ordered'] = $totalUnitsOrdered;
            }

            // 13. Find the period with maximum total units booked
            // 13.1. Iterate through bookings and their intersections
            foreach($arrPeriods AS $periodDetails)
            {
                // 13.2. And assign to MAX_UNITS_ORDERED the maximum value of TOTAL_UNITS_WITH_INTERSECTIONS
                if($periodDetails['total_units_ordered'] > $maxUnitsOrdered)
                {
                    $maxUnitsOrdered = $periodDetails['total_units_ordered'];
                }
            }
        }

        if(in_array($this->debugMode, $arrDebugCodes) && in_array($this->debugPickupTimestamp, array(-1, $this->pickupTimestamp)))
        {
            $pickupTMZStamp = $this->pickupTimestamp + get_option( 'gmt_offset' ) * 3600;
            $returnTMZStamp = $this->returnTimestamp + get_option( 'gmt_offset' ) * 3600;
            $printGMTPickupDate = date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), $this->pickupTimestamp, true);
            $printGMTReturnDate = date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), $this->returnTimestamp, true);
            $printPickupDate = date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), $pickupTMZStamp, true);
            $printReturnDate = date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), $returnTMZStamp, true);

            echo "<br /><strong>OVERALL SUMMARY: </strong>";
            echo "<br />UNIX local FROM-TO timestamps:{$this->pickupTimestamp} - {$this->returnTimestamp}";
            echo "<br />Server GMT dates &amp; times: {$printGMTPickupDate} - {$printGMTReturnDate}";
            echo "<br />Website&#39;s dates &amp; times: {$printPickupDate} - {$printReturnDate}";
            echo "<br /><strong>Location Code</strong> (to count element units,  blank - all)<strong>:</strong> ".esc_html(sanitize_text_field($paramLocationUniqueIdentifier));
            echo "<br /><strong>Order Id</strong> (to exclude element units from counting)<strong>:</strong> ".esc_html(intval($paramIgnoreFromOrderId));
            if($this->debugMode == $deepDebugCode)
            {
                // Deep debug
                echo "<br /><strong>Search SQL query:</strong>";
                echo nl2br($searchSQL);
                echo "<br /><strong>SQL search result:</strong>";
                echo nl2br(print_r($orderedData, true));
            }
            echo "<br /><strong>All pickups:</strong>";
            echo nl2br(print_r($arrPickupTimestamps, true));
            echo "<br /><strong>All debug returns (w/o block period):</strong>";
            echo nl2br(print_r($arrDebugReturnTimestampsWithoutBlockPeriod, true));
            echo "<br /><strong>All returns:</strong>";
            echo nl2br(print_r($arrReturnTimestamps, true));
            echo "<br /><strong>All periods with total bookings in them:</strong>";
            echo nl2br(print_r($arrPeriods, true));
            echo "<br />Returned Maximum Booked Unit (for ".esc_html(sanitize_text_field($paramType))." SKU={$this->elementSKU}): {$maxUnitsOrdered}";
            echo "<br />----------------------------------------------";
            echo "<br />";
        }

        return $maxUnitsOrdered;
    }
}