<?php
/**
 * Extra Discount Manager
 * 
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class ExtraDiscountManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $extraId                  = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramExtraId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->extraId = StaticValidator::getValidPositiveInteger($paramExtraId, 1);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * It's a total of two discounts - booking duration and booking in advance
     * @note - Appears that this method internal code is same for both - price plan and extra
     * @return int
     */
    public function getMinTotalPercentage()
    {
        $minDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MIN");
        $minDiscountOnDurationPercentage = $this->getMinOrMaxPercentage("DURATION", "MIN");

        $minTotalDiscountPercentage = $minDiscountOnDurationPercentage + $minDiscountInAdvancePercentage;

        // Protect system from min total discounts bigger than 100%
        if($minTotalDiscountPercentage > 100)
        {
            $minTotalDiscountPercentage = 100;
        }

        return $minTotalDiscountPercentage;
    }

    /**
     * It's a total of two discounts - booking duration and booking in advance
     * @note - Appears that this method internal code is same for both - price plan and extra
     * @return int
     */
    public function getMaxTotalPercentage()
    {
        $maxDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MAX");
        $maxDiscountOnDurationPercentage = $this->getMinOrMaxPercentage("DURATION", "MAX");

        $maxTotalDiscountPercentage = $maxDiscountOnDurationPercentage + $maxDiscountInAdvancePercentage;

        // Protect system from max total discounts bigger than 100%
        if($maxTotalDiscountPercentage > 100)
        {
            $maxTotalDiscountPercentage = 100;
        }

        return $maxTotalDiscountPercentage;
    }

    /**
     * @note2 - for extras there is no coupons at all
     * @param $paramDiscountPeriod
     * @return float|int
     */
    public function getTotalPercentageWithMinAdvance($paramDiscountPeriod)
    {
        $minDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MIN");
        $discountOnDurationPercentage = $this->getPercentage("DURATION", $paramDiscountPeriod);

        $totalDiscountPercentage = $discountOnDurationPercentage + $minDiscountInAdvancePercentage;

        // Protect system from total discounts bigger than 100%
        if($totalDiscountPercentage > 100)
        {
            $totalDiscountPercentage = 100;
        }

        if($this->debugMode)
        {
            echo "<br />[Extra Discount] Period Until pickup: MINIMUM, Reservation Period: ".intval($paramDiscountPeriod);
            echo "<br />[Extra Discount] Min. booking in advance + period duration: {$minDiscountInAdvancePercentage} % + ";
            echo "{$discountOnDurationPercentage} % = {$totalDiscountPercentage} %";
        }

        return $totalDiscountPercentage;
    }

    /**
     * @note2 - for extras there is no coupons at all
     * @param $paramDiscountPeriod
     * @return float|int
     */
    public function getTotalPercentageWithMaxAdvance($paramDiscountPeriod)
    {
        $maxDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MAX");
        $discountOnDurationPercentage = $this->getPercentage("DURATION", $paramDiscountPeriod);

        $totalDiscountPercentage = $discountOnDurationPercentage + $maxDiscountInAdvancePercentage;

        // Protect system from total discounts bigger than 100%
        if($totalDiscountPercentage > 100)
        {
            $totalDiscountPercentage = 100;
        }

        if($this->debugMode)
        {
            echo "<br />[Extra Discount] Period Until pickup: MAXIMUM, Reservation Period: ".intval($paramDiscountPeriod);
            echo "<br />[Extra Discount] Max. booking in advance + period duration: {$maxDiscountInAdvancePercentage} % + ";
            echo "{$discountOnDurationPercentage} % = {$totalDiscountPercentage} %";
        }

        return $totalDiscountPercentage;
    }

    /**
     * It's a total of two discounts - booking duration and booking in advance
     * @note - Appears that this method internal code is same for both - price plan and extra
     * @param int $paramPeriodUntilPickup
     * @param int $paramDiscountPeriod
     * @return int
     */
    public function getTotalPercentageByPeriod($paramPeriodUntilPickup, $paramDiscountPeriod)
    {
        $discountInAdvancePercentage = $this->getPercentage("IN_ADVANCE", $paramPeriodUntilPickup);
        $discountOnDurationPercentage = $this->getPercentage("DURATION", $paramDiscountPeriod);

        $totalDiscountPercentage = $discountOnDurationPercentage + $discountInAdvancePercentage;

        // Protect system from total discounts bigger than 100%
        if($totalDiscountPercentage > 100)
        {
            $totalDiscountPercentage = 100;
        }

        if($this->debugMode)
        {
            echo "<br />[Extra Discount] Period Until pickup: ".intval($paramPeriodUntilPickup).", Reservation Period: ".intval($paramDiscountPeriod);
            echo "<br />[Extra Discount] Discount on booking in advance + duration: {$discountInAdvancePercentage} % + ";
            echo "{$discountOnDurationPercentage} % = {$totalDiscountPercentage} %";
        }

        return $totalDiscountPercentage;
    }

    /**
     * We are making sure that we ALWAYS GET A RESULT, EVENT if there is no exact match for that period
     * Returns discount for specific period
     * @param string $paramDiscountType - "DURATION_AMOUNT", "IN_ADVANCE_PERCENTAGE", "DURATION_AMOUNT", "IN_ADVANCE_PERCENTAGE"
     * @param int $paramPeriod
     * @return float
     */
    private function getPercentage($paramDiscountType, $paramPeriod)
    {
        $discount = 0;
        $validExtraId = StaticValidator::getValidPositiveInteger($this->extraId, 0);
        $validPeriod = StaticValidator::getValidPositiveInteger($paramPeriod, 0);

        // Defaults
        $discountType = 3;
        // SPEED OPTIMIZATION NOTE: it is IMPORTANT to keep the WHERE columns in period_from, period_till order to speed up SQL search,
        // because we have an INDEX in database called 'period (period_from, period_to)'.
        // And we have to invert the sign, but not the order, if we are looking for discounts in advance
        $betweenPeriodSQL = "period_from AND period_till";

        if($paramDiscountType == "DURATION")
        {
            $discountType = 3;
            $betweenPeriodSQL = "period_from AND period_till";
        } else if($paramDiscountType == "IN_ADVANCE")
        {
            $discountType = 4;
            $betweenPeriodSQL = "period_till AND period_from";
        }

        // SPEED OPTIMIZATION NOTE: For better system speed - we combine both - zero and regular ids rows
        // ID=0,1,2...
        $sqlForZeroOrBiggerValue = "
            SELECT discount_percentage
            FROM {$this->conf->getPrefix()}discounts
            WHERE discount_type='{$discountType}' AND extra_id IN ('0', '{$validExtraId}') AND extra_id>='0' AND (
                '{$validPeriod}' BETWEEN {$betweenPeriodSQL}
            ) AND blog_id='{$this->conf->getBlogId()}'
            ORDER BY extra_id DESC, discount_percentage DESC LIMIT 1
        ";

        if($this->debugMode == 2)
        {
            // Deep debug:
            echo "<br />";
            echo "<br /><strong>FUNCTION:</strong> getDiscount(EXTRA ID={$validExtraId}, DISCOUNT TYPE=".esc_html(sanitize_text_field($paramDiscountType));
            echo "<br />SQL (ID=0,1,2...): ".nl2br($sqlForZeroOrBiggerValue);
        }

        // Search for exact or nearest alternative discount
        $dataExactOrNearestZeroOrBigger = $this->conf->getInternalWPDB()->get_row($sqlForZeroOrBiggerValue, ARRAY_A);
        if(!is_null($dataExactOrNearestZeroOrBigger))
        {
            // Same nearest discounts for each extra
            $discount = $dataExactOrNearestZeroOrBigger['discount_percentage'];
        }

        // Protect system from max total discounts bigger than 100%
        if($discount > 100)
        {
            $discount = 100;
        }

        return $discount;
    }

    /**
     * @note extras does not use coupon_discount field at all
     * @param string $paramDiscountType - "DURATION", "IN_ADVANCE"
     * @param $paramMinOrMax
     * @return int
     * @internal param int $paramExtraId
     * @internal param int $paramPricePlanId
     */
    private function getMinOrMaxPercentage($paramDiscountType, $paramMinOrMax)
    {
        $maxDiscount = 0;

        // Defaults
        $validDiscountType = 3;
        if($paramDiscountType == "DURATION")
        {
            $validDiscountType = 3;
        } else if($paramDiscountType == "IN_ADVANCE")
        {
            $validDiscountType = 4;
        }
        $validDiscountFieldOrderBy = $paramMinOrMax == "MAX" ? "DESC" : "ASC";
        $validExtraId = StaticValidator::getValidPositiveInteger($this->extraId, 0);

        $sqlForZeroOrBiggerValue = "
            SELECT discount_percentage
            FROM {$this->conf->getPrefix()}discounts
            WHERE discount_type='{$validDiscountType}' AND extra_id IN ('{$validExtraId}', '0') AND extra_id>='0'
            AND blog_id='{$this->conf->getBlogId()}'
            ORDER BY extra_id DESC, discount_percentage {$validDiscountFieldOrderBy} LIMIT 1
        ";

        // Search for exact discount
        $rowZeroOrBigger = $this->conf->getInternalWPDB()->get_row($sqlForZeroOrBiggerValue, ARRAY_A);

        if(!is_null($rowZeroOrBigger))
        {
            // Use different or same discounts for each extra (unless 0 was passed, then it will return default here)
            $maxDiscount = $rowZeroOrBigger['discount_percentage'];
        }

        return $maxDiscount;
    }
}