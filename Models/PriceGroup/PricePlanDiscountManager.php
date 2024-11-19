<?php
/**
 * Price Plan Discount Manager (with setup for single price plan)
 * Abstract class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PriceGroup;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class PricePlanDiscountManager
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $pricePlanId              = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramPricePlanId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->pricePlanId = StaticValidator::getValidPositiveInteger($paramPricePlanId, 1);
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
    public function getMinTotalPercentageWithoutCoupon()
    {
        $minDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MIN_WITHOUT_COUPON");
        $minDiscountOnDurationPercentage = $this->getMinOrMaxPercentage("DURATION", "MIN_WITHOUT_COUPON");

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
    public function getMaxTotalPercentageWithoutCoupon()
    {
        $maxDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MAX_WITHOUT_COUPON");
        $maxDiscountOnDurationPercentage = $this->getMinOrMaxPercentage("DURATION", "MAX_WITHOUT_COUPON");

        $maxTotalDiscountPercentage = $maxDiscountOnDurationPercentage + $maxDiscountInAdvancePercentage;

        // Protect system from max total discounts bigger than 100%
        if($maxTotalDiscountPercentage > 100)
        {
            $maxTotalDiscountPercentage = 100;
        }

        return $maxTotalDiscountPercentage;
    }

    public function getTotalPercentageWithMinAdvanceWithoutCoupon($paramDiscountPeriod)
    {
        $minDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MIN_WITHOUT_COUPON");
        $discountOnDurationPercentage = $this->getPercentage("DURATION", $paramDiscountPeriod);

        $totalDiscountPercentage = $discountOnDurationPercentage + $minDiscountInAdvancePercentage;

        // Protect system from total discounts bigger than 100%
        if($totalDiscountPercentage > 100)
        {
            $totalDiscountPercentage = 100;
        }

        if($this->debugMode)
        {
            echo "<br />[Price Plan Discount] Period Until pickup: MINIMUM, Reservation Period: ".intval($paramDiscountPeriod);
            echo "<br />[Price Plan Discount] Min. booking in advance + period duration: {$minDiscountInAdvancePercentage} % + ";
            echo "{$discountOnDurationPercentage} % = {$totalDiscountPercentage} %";
        }

        return $totalDiscountPercentage;
    }

    public function getTotalPercentageWithMaxAdvanceWithoutCoupon($paramDiscountPeriod)
    {
        $maxDiscountInAdvancePercentage = $this->getMinOrMaxPercentage("IN_ADVANCE", "MAX_WITHOUT_COUPON");
        $discountOnDurationPercentage = $this->getPercentage("DURATION", $paramDiscountPeriod);

        $totalDiscountPercentage = $discountOnDurationPercentage + $maxDiscountInAdvancePercentage;

        // Protect system from total discounts bigger than 100%
        if($totalDiscountPercentage > 100)
        {
            $totalDiscountPercentage = 100;
        }

        if($this->debugMode)
        {
            echo "<br />[Price Plan Discount] Period Until pickup: MAXIMUM, Reservation Period: ".intval($paramDiscountPeriod);
            echo "<br />[Price Plan Discount] Max. booking in advance + period duration: {$maxDiscountInAdvancePercentage} % + ";
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
            echo "<br />[Price Plan Discount] Period Until pickup: ".intval($paramPeriodUntilPickup).", Reservation Period: ".intval($paramDiscountPeriod);
            echo "<br />[Price Plan Discount] Discount on booking in advance + duration: {$discountInAdvancePercentage} % + ";
            echo "{$discountOnDurationPercentage} % = {$totalDiscountPercentage} %";
        }

        return $totalDiscountPercentage;
    }

    /**
     * We are making sure that we ALWAYS GET A RESULT, EVENT if there is no exact match for that period
     * Returns discount for specific period
     * @param string $paramDiscountType - "DURATION", "IN_ADVANCE"
     * @param int $paramPeriod
     * @return float
     */
    private function getPercentage($paramDiscountType, $paramPeriod)
    {
        $discount = 0;

        $validPricePlanId = StaticValidator::getValidPositiveInteger($this->pricePlanId, 0);
        $validPeriod = StaticValidator::getValidPositiveInteger($paramPeriod, 0);

        // Defaults
        $discountType = 1;
        // SPEED OPTIMIZATION NOTE: it is IMPORTANT to keep the WHERE columns in period_from, period_till order to speed up SQL search,
        // because we have an INDEX in database called 'period (period_from, period_to)'.
        // And we have to invert the sign, but not the order, if we are looking for discounts in advance
        $betweenPeriodSQL = "period_from AND period_till";

        if($paramDiscountType == "DURATION")
        {
            $discountType = 1;
            $betweenPeriodSQL = "period_from AND period_till";
        } else if($paramDiscountType == "IN_ADVANCE")
        {
            $discountType = 2;
            $betweenPeriodSQL = "period_till AND period_from";
        }

        // SPEED OPTIMIZATION NOTE: For better system speed - we merge zero and regular ids discounts to one query
        // There can more than 1 discount_id assigned to same price_plan_id, because discounts can be for different periods
        // ID=0,1,2...
        $sqlForZeroOrBiggerValue = "
            SELECT discount_percentage
            FROM {$this->conf->getPrefix()}discounts
            WHERE discount_type='{$discountType}' AND price_plan_id IN ('0', '{$validPricePlanId}') AND price_plan_id>='0' AND (
              '{$validPeriod}' BETWEEN {$betweenPeriodSQL}
            ) AND blog_id='{$this->conf->getBlogId()}'
            ORDER BY price_plan_id DESC, discount_percentage DESC LIMIT 1
        ";

        if($this->debugMode == 2)
        {
            // Deep debug:
            echo "<br />";
            echo "<br /><strong>FUNCTION:</strong> getDiscount(PRICE PLAN ID={$validPricePlanId}, DISCOUNT TYPE=".esc_html(sanitize_text_field($paramDiscountType));
            echo "<br />SQL (ID=0,1,2...): ".nl2br($sqlForZeroOrBiggerValue);
        }

        // Search for exact or nearest alternative discount
        $dataExactOrNearestZeroOrBigger = $this->conf->getInternalWPDB()->get_row($sqlForZeroOrBiggerValue, ARRAY_A);
        if(!is_null($dataExactOrNearestZeroOrBigger))
        {
            // Same nearest discounts for each price plan
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
     * @param string $paramDiscountType - "DURATION", "IN_ADVANCE"
     * @param $paramLimit - "MIN_WITHOUT_COUPON", "MAX_WITHOUT_COUPON"
     * @return int
     */
    private function getMinOrMaxPercentage($paramDiscountType, $paramLimit)
    {
        $maxDiscountAmountOrPercentage = 0;

        // Defaults
        $validDiscountType = 1;
        if($paramDiscountType == "DURATION")
        {
            $validDiscountType = 1;
        } else if($paramDiscountType == "IN_ADVANCE")
        {
            $validDiscountType = 2;
        }
        $validDiscountFieldOrderBy = $paramLimit == "MAX_WITHOUT_COUPON" ? "DESC" : "ASC";
        $validPricePlanId = StaticValidator::getValidPositiveInteger($this->pricePlanId, 0);

        // There can more than 1 discount_id assigned to same price_plan_id, because discounts can be for different periods
        $sqlForZeroOrBiggerValue = "
            SELECT discount_percentage
            FROM {$this->conf->getPrefix()}discounts
            WHERE discount_type='{$validDiscountType}' AND price_plan_id IN ('0', '{$validPricePlanId}') AND coupon_discount='0'
            AND blog_id='{$this->conf->getBlogId()}'
            ORDER BY price_plan_id DESC, discount_percentage {$validDiscountFieldOrderBy} LIMIT 1
        ";

        // Search for exact discount
        $rowZeroOrBigger = $this->conf->getInternalWPDB()->get_row($sqlForZeroOrBiggerValue, ARRAY_A);

        if(!is_null($rowZeroOrBigger))
        {
            // Use different or same discounts for each price plan (unless 0 was passed, than it will return default here)
            $maxDiscountAmountOrPercentage = $rowZeroOrBigger['discount_percentage'];
        }

        return $maxDiscountAmountOrPercentage;
    }
}