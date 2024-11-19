<?php
/**
 * Extra Discounts Observer (no setup for single extra discount)
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Extra;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Extra\Extra;

final class ExtraDiscountsObserver implements ObserverInterface
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
     * @param int $paramExtraId
     * @return array
     */
    public function getAllIds($paramDiscountType = "", $paramExtraId = -1)
    {
        $validExtraId = StaticValidator::getValidInteger($paramExtraId, -1);

        $sqlAdd = "";
        if($paramDiscountType != "")
        {
            $validSQLDiscountType = $paramDiscountType == "DURATION" ? 1 : 2;
            $sqlAdd .= " AND discount_type='{$validSQLDiscountType}'";
        }
        if($validExtraId > 0)
        {
            $sqlAdd .= " AND extra_id='{$validExtraId}'";
        }
        $validSqlOrder = $paramDiscountType == "IN_ADVANCE" ? "DESC" : "ASC";

        $sqlQuery = "
            SELECT discount_id
            FROM {$this->conf->getPrefix()}discounts
            WHERE blog_id='{$this->conf->getBlogId()}' AND price_plan_id='0'{$sqlAdd}
            ORDER BY period_from {$validSqlOrder}, period_till {$validSqlOrder}
        ";

        $ids = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        return $ids;
    }

    /**
     * @param string $paramDiscountType - "", "DURATION" or "IN_ADVANCE"
     * @param int $paramExtraId
     * @param int $paramPartnerId
     * @return array
     */
    public function getGroupedIds($paramDiscountType = "", $paramExtraId = -1, $paramPartnerId = -1)
    {
        $validExtraId = StaticValidator::getValidInteger($paramExtraId, -1);
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1);

        $sqlAdd = "";
        if($paramDiscountType != "")
        {
            $validSQLDiscountType = $paramDiscountType == "DURATION" ? 3 : 4;
            $sqlAdd .= " AND di.discount_type='{$validSQLDiscountType}'";
        }
        if($validExtraId > 0)
        {
            $sqlAdd .= " AND di.extra_id='{$validExtraId}'";
        }
        $validSqlOrder = $paramDiscountType == "IN_ADVANCE" ? "DESC" : "ASC";

        if($validPartnerId >= 0)
        {
            $sqlQuery = "
                SELECT discount_id
                FROM {$this->conf->getPrefix()}discounts di
                JOIN {$this->conf->getPrefix()}extras ex ON ex.extra_id=di.extra_id
                WHERE di.blog_id='{$this->conf->getBlogId()}' AND ex.partner_id='{$validPartnerId}'
                AND di.price_plan_id='0'{$sqlAdd}
                GROUP BY period_from, period_till
                ORDER BY period_from {$validSqlOrder}, period_till {$validSqlOrder}
            ";
        } else
        {
            $sqlQuery = "
                SELECT discount_id
                FROM {$this->conf->getPrefix()}discounts di
                WHERE di.blog_id='{$this->conf->getBlogId()}' AND di.price_plan_id='0'{$sqlAdd}
                GROUP BY period_from, period_till
                ORDER BY period_from {$validSqlOrder}, period_till {$validSqlOrder}
            ";
        }

        // Debug
        //echo "<br />".nl2br($sqlQuery);

        $ids = $this->conf->getInternalWPDB()->get_col($sqlQuery);

        return $ids;
    }

    public function canShowOnlyPartnerOwned()
    {
        $canEditOwnExtras = current_user_can('manage_'.$this->conf->getExtPrefix().'own_extras');
        $canEditAllExtras = current_user_can('manage_'.$this->conf->getExtPrefix().'all_extras');
        $onlyPartnerOwned = $canEditOwnExtras == true && $canEditAllExtras == false;

        return $onlyPartnerOwned;
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

        $discountIds = $this->getGroupedIds($paramDiscountType, -1, ($this->canShowOnlyPartnerOwned() ? get_current_user_id() : -1));
        $i = 0;
        foreach($discountIds AS $discountId)
        {
            $i++;
            $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->settings, $discountId);
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
     */
    private function getTrustedAdminDiscountListByPeriodHTML($paramDiscountType = "DURATION", $paramPeriodFrom, $paramPeriodTill, $rowNumbersPrefix = "0.")
    {
        $discountList = '';
        $validPeriodFrom = StaticValidator::getValidPositiveInteger($paramPeriodFrom, 0);
        $validPeriodTill = StaticValidator::getValidPositiveInteger($paramPeriodTill, 0);
        $validDiscountType = $paramDiscountType == "IN_ADVANCE" ? 4 : 3;

        $discountIds = $this->conf->getInternalWPDB()->get_col("
            SELECT di.discount_id
            FROM {$this->conf->getPrefix()}discounts di
            LEFT JOIN {$this->conf->getPrefix()}extras ext ON ext.extra_id=di.extra_id
            WHERE di.period_from='{$validPeriodFrom}' AND di.period_till='{$validPeriodTill}' AND di.blog_id='{$this->conf->getBlogId()}'
            AND di.discount_type='{$validDiscountType}'
            ORDER BY ext.extra_name ASC
        ");

        $i = 0;
        foreach($discountIds AS $discountId)
        {
            $i++;
            $objDiscount = new ExtraDiscount($this->conf, $this->lang, $this->settings, $discountId);
            $discountDetails = $objDiscount->getDetails();
            $objExtra = new Extra($this->conf, $this->lang, $this->settings, $discountDetails['extra_id']);
            $extraDetails = $objExtra->getDetailsWithItemAndPartner();
            $printExtraName = "";
            $partnerId = 0;
            if(!is_null($extraDetails))
            {
                $partnerId = $extraDetails['partner_id'];
                $printExtraName = esc_html($extraDetails['translated_extra_name_with_dependant_item_model']).' '.esc_html($extraDetails['via_partner']);
            }

            $printDurationFrom = $this->lang->getPrintFloorDurationByPeriod($this->priceCalculationType, $validPeriodFrom);
            $printDurationTill = $this->lang->getPrintFloorDurationByPeriod($this->priceCalculationType, $validPeriodTill);

            // HTML OUTPUT
            $discountList .= '<tr>';
            $discountList .= '<td>'.$rowNumbersPrefix.sprintf('%02d', $i).'</td>';
            if($discountDetails['extra_id'] == 0)
            {
                $discountList .= '<td>---</td>';
            } else
            {
                $discountList .= '<td>'.$printExtraName.'</td>';
            }
            $discountList .= '<td>'.$discountDetails['discount_percentage'].' %';
            $discountList .= '&nbsp;<strong>('.$printDurationFrom.' - '.$printDurationTill.')</strong>';
            $discountList .= '</td>';
            $discountList .= '<td align="right">';
            if($objDiscount->canEdit($partnerId))
            {
                $discountList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-discount&amp;discount_type='.$validDiscountType.'&amp;discount_id='.$discountId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a> || ';
                $discountList .= '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-extra-discount&amp;discount_type='.$validDiscountType.'&amp;delete_discount='.$discountId.'&amp;noheader=true')).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
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