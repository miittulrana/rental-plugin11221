<?php
/**
 * Price Plan Discounts Observer (no setup for single price plan discount)

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PriceGroup;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PriceGroup\PriceGroup;
use FleetManagement\Models\PriceGroup\PricePlan;

final class PricePlanDiscountsObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings 	            = array();
    // Price calculation type: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
    protected $priceCalculationType 	= 1;
    protected $currencySymbol		    = '$';
    protected $currencyCode		        = 'USD';
    protected $currencySymbolLocation	= 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        $this->priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1, array(1, 2, 3));
        $this->currencySymbol = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol', "textval", "$");
        $this->currencyCode = StaticValidator::getValidSetting($paramSettings, 'conf_currency_code', "textval", "USD");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @param string $paramDiscountType - "", "DURATION" or "IN_ADVANCE"
     * @param int $paramPricePlanId
     * @return array
     */
    public function getAllIds($paramDiscountType = "", $paramPricePlanId = -1)
    {
        $validPricePlanId = StaticValidator::getValidInteger($paramPricePlanId, -1);

        $sqlAdd = "";
        if($paramDiscountType != "")
        {
            $validSQLDiscountType = $paramDiscountType == "DURATION" ? 1 : 2;
            $sqlAdd .= " AND discount_type='{$validSQLDiscountType}'";
        }
        if($validPricePlanId > 0)
        {
            $sqlAdd .= " AND price_plan_id='{$validPricePlanId}'";
        }
        $validSqlOrder = $paramDiscountType == "IN_ADVANCE" ? "DESC" : "ASC";

        $sqlQuery = "
            SELECT discount_id
            FROM {$this->conf->getPrefix()}discounts
            WHERE blog_id='{$this->conf->getBlogId()}' AND extra_id='0'{$sqlAdd}
            ORDER BY period_from {$validSqlOrder}, period_till {$validSqlOrder}
        ";

        $ids = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        return $ids;
    }

    /**
     * @param string $paramDiscountType - "", "DURATION" or "IN_ADVANCE"
     * @param bool $paramExcludeCouponPrices
     * @param int $paramPricePlanId
     * @param int $paramPartnerId
     * @return array
     */
    public function getGroupedIds($paramDiscountType = "", $paramExcludeCouponPrices = true, $paramPricePlanId = -1, $paramPartnerId = -1)
    {
        $validPricePlanId = StaticValidator::getValidInteger($paramPricePlanId, -1);
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1);

        $sqlAdd = "";
        if($paramDiscountType != "")
        {
            $validSQLDiscountType = $paramDiscountType == "DURATION" ? 1 : 2;
            $sqlAdd .= " AND di.discount_type='{$validSQLDiscountType}'";
        }
        if($paramExcludeCouponPrices)
        {
            $sqlAdd .= " AND di.coupon_discount='0'";
        }
        if($validPricePlanId > 0)
        {
            $sqlAdd .= " AND di.price_plan_id='{$validPricePlanId}'";
        }

        $validSqlOrder = $paramDiscountType == "IN_ADVANCE" ? "DESC" : "ASC";

        if($validPartnerId >= 0)
        {
            $sqlQuery = "
                SELECT di.discount_id
                FROM {$this->conf->getPrefix()}discounts di
                JOIN {$this->conf->getPrefix()}price_plans pp ON pp.price_plan_id=di.price_plan_id
                JOIN {$this->conf->getPrefix()}price_groups pg ON pg.price_group_id=pp.price_group_id
                WHERE di.blog_id='{$this->conf->getBlogId()}' AND di.extra_id='0'
                AND pg.partner_id='{$validPartnerId}'{$sqlAdd}
                GROUP BY period_from, period_till
                ORDER BY period_from {$validSqlOrder}, period_till {$validSqlOrder}
            ";
        } else
        {
            $sqlQuery = "
                SELECT di.discount_id
                FROM {$this->conf->getPrefix()}discounts di
                WHERE di.blog_id='{$this->conf->getBlogId()}' AND di.extra_id='0'{$sqlAdd}
                GROUP BY period_from, period_till
                ORDER BY period_from {$validSqlOrder}, period_till {$validSqlOrder}
            ";
        }

        // Debug
        // echo "<br />".nl2br($sqlQuery);

        $ids = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        return $ids;
    }

    public function canShowOnlyPartnerOwned()
    {
        $canEditOwnItems = current_user_can('manage_'.$this->conf->getExtPrefix().'own_items');
        $canEditAllItems = current_user_can('manage_'.$this->conf->getExtPrefix().'all_items');
        $onlyPartnerOwned = $canEditOwnItems == true && $canEditAllItems == false;

        return $onlyPartnerOwned;
    }


    /**
     * Update discount coupon status in discounts table based on price_plan_id for specific blog_id
     * @param int $paramPricePlanId
     * @param bool $paramHasCouponCode
     */
    public function changeCouponStatus($paramPricePlanId, $paramHasCouponCode)
    {
        $validPricePlanId = StaticValidator::getValidPositiveInteger($paramPricePlanId, 0);
        $validIsCouponDiscount = $paramHasCouponCode == true ? 1 : 0;

        if($validPricePlanId > 0)
        {
            $updateQuery = "
                UPDATE {$this->conf->getPrefix()}discounts SET coupon_discount='{$validIsCouponDiscount}'
                WHERE price_plan_id='{$validPricePlanId}' AND blog_id='{$this->conf->getBlogId()}'
            ";

            $this->conf->getInternalWPDB()->query($updateQuery);
        }
    }


    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    public function getTrustedAdminListForDiscountDurationHTML()
    {
        return $this->getTrustedAdminListHTML("DURATION");
    }

    public function getTrustedAdminListForOrderInAdvanceHTML()
    {
        return $this->getTrustedAdminListHTML("IN_ADVANCE");
    }

    /**
     * DIFFERENT DURATION DISCOUNTS FOR DIFFERENT ITEMS
     * DEPTH: 1
     * @param string $paramDiscountType - "DURATION" or "IN_ADVANCE"
     * @return string
     * @internal param int $paramPartnerId
     */
    private function getTrustedAdminListHTML($paramDiscountType = "DURATION")
    {
        $discountList = '';

        // Include discounts for price plans with coupon codes
        $discountIds = $this->getGroupedIds($paramDiscountType, false, -1, ($this->canShowOnlyPartnerOwned() ? get_current_user_id() : -1));
        $i = 0;
        foreach($discountIds AS $discountId)
        {
            $i++;
            $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->settings, $discountId);
            $discountDetails = $objDiscount->getDetails();
            $printDurationFrom = $this->lang->getPrintFloorDurationByPeriod($this->priceCalculationType, $discountDetails['period_from']);
            $printDurationTill = $this->lang->getPrintFloorDurationByPeriod($this->priceCalculationType, $discountDetails['period_till']);

            // HTML OUTPUT
            $discountList .= '<tr>';
            $discountList .= '<td>'.sprintf('%02d', $i).'</td>';
            $discountList .= '<td><strong>'.$printDurationFrom.' - '.$printDurationTill.'</strong></td>';
            $discountList .= '<td>&nbsp;</td>';
            $discountList .= '<td>&nbsp;</td>';
            $discountList .= '</tr>';
            $discountList .= $this->getTrustedAdminDiscountListByPeriodHTML(
                $paramDiscountType, $discountDetails['period_from'], $discountDetails['period_till'], sprintf('%02d', $i) . "."
            );
        }

        return  $discountList;
    }

    /**
     * DIFFERENT DISCOUNTS FOR DIFFERENT ITEMS IN SPECIFIC PERIOD FROM-TO
     * DEPTH: 2
     * @param string $paramDiscountType - "DURATION" or "IN_ADVANCE"
     * @param int $paramPeriodFrom
     * @param int $paramPeriodTill
     * @param string $rowNumbersPrefix
     * @return string
     * @internal param bool $showRowNumbers
     */
    private function getTrustedAdminDiscountListByPeriodHTML($paramDiscountType, $paramPeriodFrom, $paramPeriodTill, $rowNumbersPrefix = "0.")
    {
        $discountList = '';
        $validPeriodFrom = StaticValidator::getValidPositiveInteger($paramPeriodFrom, 0);
        $validPeriodTill = StaticValidator::getValidPositiveInteger($paramPeriodTill, 0);
        $validDiscountType = $paramDiscountType == "IN_ADVANCE" ? 2 : 1;

        $discountIds = $this->conf->getInternalWPDB()->get_col("
            SELECT di.discount_id
            FROM {$this->conf->getPrefix()}discounts di
            LEFT JOIN {$this->conf->getPrefix()}price_plans pp ON pp.price_plan_id=di.price_plan_id
            LEFT JOIN {$this->conf->getPrefix()}price_groups pg ON pg.price_group_id=pp.price_group_id
            WHERE di.period_from='{$validPeriodFrom}' AND di.period_till='{$validPeriodTill}'
            AND di.discount_type='{$validDiscountType}' AND di.blog_id='{$this->conf->getBlogId()}'
            ORDER BY pg.price_group_name ASC, pp.coupon_code ASC, pp.seasonal_price ASC, pp.start_timestamp ASC, pp.end_timestamp ASC
        ");

        $i = 0;
        foreach($discountIds AS $discountId)
        {
            $i++;
            $objDiscount = new PricePlanDiscount($this->conf, $this->lang, $this->settings, $discountId);
            $discountDetails = $objDiscount->getDetails();
            $objPricePlan = new PricePlan($this->conf, $this->lang, $this->settings, $discountDetails['price_plan_id']);
            $pricePlanDetails = $objPricePlan->getDetails();
            $printFullPriceLabel = "";
            $partnerId = 0;
            if(!is_null($pricePlanDetails))
            {
                $printFullPriceLabel = $pricePlanDetails['print_label'];
                $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->settings, $pricePlanDetails['price_group_id']);
                $priceGroupDetails = $objPriceGroup->getDetailsWithPartner();
                if(!is_null($priceGroupDetails))
                {
                    $partnerId = $priceGroupDetails['partner_id'];
                    $printTranslatedPriceGroupName = $priceGroupDetails['print_translated_price_group_name'].' '.esc_html($priceGroupDetails['via_partner']);
                    $printFullPriceLabel = $printTranslatedPriceGroupName.' - '.$printFullPriceLabel;
                }
            }

            $printDurationFrom = $this->lang->getPrintFloorDurationByPeriod($this->priceCalculationType, $validPeriodFrom);
            $printDurationTill = $this->lang->getPrintFloorDurationByPeriod($this->priceCalculationType, $validPeriodTill);

            // HTML OUTPUT
            $discountList .= '<tr>';
            $discountList .= '<td>'.$rowNumbersPrefix.sprintf('%02d', $i).'</td>';
            if($discountDetails['price_plan_id'] == 0)
            {
                $discountList .= '<td>---</td>';
            } else
            {
                $discountList .= '<td>'.$printFullPriceLabel.'</td>';
            }
            $discountList .= '<td>'.$discountDetails['discount_percentage'].' %';
            $discountList .= '&nbsp;<strong>('.$printDurationFrom.' - '.$printDurationTill.')</strong>';
            $discountList .= '</td>';
            $discountList .= '<td align="right">';
            if($objDiscount->canEdit($partnerId))
            {
                $discountList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan-discount&amp;discount_type='.$validDiscountType.'&amp;discount_id='.$discountId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $discountList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan-discount&amp;discount_type='.$validDiscountType.'&amp;delete_discount='.$discountId.'&amp;noheader=true')).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            } else
            {
                $discountList = '--';
            }
            $discountList .= '</td>';
            $discountList .= '</tr>';
        }

        return  $discountList;
    }
}