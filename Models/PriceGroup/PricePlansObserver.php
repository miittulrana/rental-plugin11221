<?php
/**
 * Price Plans Observer (no setup for single price plan)
 *
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

final class PricePlansObserver implements ObserverInterface
{
    protected $conf 	                = null;
    protected $lang 		            = null;
    protected $debugMode 	            = 0;
    protected $settings 	            = array();

    /**
     * @var int - price calculation: 1 - daily, 2 - hourly, 3 - mixed (daily+hourly)
     */
    protected $priceCalculationType		= 1;
    protected $currencySymbol		    = '$';
    /**
     * @var int - currency symbol location: 0 - left, 1 - right
     */
    protected $currencySymbolLocation	= 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        // Set currency symbol location
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
        // Set price calculation type
        $this->priceCalculationType = StaticValidator::getValidSetting($paramSettings, 'conf_price_calculation_type', 'positive_integer', 1, array(1, 2, 3));

        if(isset($paramSettings['conf_currency_symbol']))
        {
            // Set currency symbol
            $this->currencySymbol = sanitize_text_field($paramSettings['conf_currency_symbol']);
        }

    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getAllIds($paramPriceGroupId, $paramPartnerId = -1)
    {
        $validPriceGroupId = StaticValidator::getValidPositiveInteger($paramPriceGroupId, 0);
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1);

        if($validPartnerId >= 0)
        {
            $sqlQuery = "
                SELECT price_plan_id
                FROM {$this->conf->getPrefix()}price_plans pp
                JOIN {$this->conf->getPrefix()}price_groups pg ON pg.price_group_id=pp.price_group_id
                WHERE pp.price_group_id='{$validPriceGroupId}' AND pg.partner_id='{$validPartnerId}' AND pp.blog_id='{$this->conf->getBlogId()}'
                ORDER BY seasonal_price ASC, start_timestamp ASC, end_timestamp ASC, coupon_code ASC
            ";
        } else
        {
            $sqlQuery = "
                SELECT price_plan_id
                FROM {$this->conf->getPrefix()}price_plans
                WHERE price_group_id='{$validPriceGroupId}' AND blog_id='{$this->conf->getBlogId()}'
                ORDER BY seasonal_price ASC, start_timestamp ASC, end_timestamp ASC, coupon_code ASC
            ";
        }

        // DEBUG
        // echo nl2br($sqlQuery);

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

    public function getTrustedTranslatedDropdownOptionsHTML_ByPartnerId($paramPartnerId = -1, $paramSelectedPricePlanId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramShowPriceGroupId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedPricePlanId, $paramDefaultValue, $paramDefaultLabel, $paramShowPriceGroupId, true, $paramPartnerId);
    }

    public function getTrustedDropdownOptionsHTML_ByPartnerId($paramPartnerId = -1, $paramSelectedPricePlanId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramShowPriceGroupId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedPricePlanId, $paramDefaultValue, $paramDefaultLabel, $paramShowPriceGroupId, false, $paramPartnerId);
    }

    public function getTrustedTranslatedDropdownOptionsHTML($paramSelectedPricePlanId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramShowPriceGroupId = true)
    {
        return $this->getTrustedDropdownOptionsHTML($paramSelectedPricePlanId, $paramDefaultValue, $paramDefaultLabel, $paramShowPriceGroupId, true, -1);
    }

    /**
     * @none - No translation for price plans needed!
     * @param int $paramSelectedPricePlanId
     * @param int $paramDefaultValue
     * @param string $paramDefaultLabel
     * @param bool $paramShowPriceGroupId
     * @param bool $paramTranslated
     * @param int $paramPartnerId
     * @return string
     */
    public function getTrustedDropdownOptionsHTML($paramSelectedPricePlanId = 0, $paramDefaultValue = 0, $paramDefaultLabel = "", $paramShowPriceGroupId = true, $paramTranslated = false, $paramPartnerId = -1)
    {
        $validDefaultValue = StaticValidator::getValidPositiveInteger($paramDefaultValue, 0);
        $sanitizedDefaultLabel = sanitize_text_field($paramDefaultLabel);
        $defaultSelected = $paramSelectedPricePlanId == $validDefaultValue ? ' selected="selected"' : '';

        $priceGroupsAndPlansHTML = '';
        $priceGroupsAndPlansHTML .= '<option value="'.$validDefaultValue.'"'.$defaultSelected.'>'.$sanitizedDefaultLabel.'</option>';
        $objPriceGroupsObserver = new PriceGroupsObserver($this->conf, $this->lang, $this->settings);
        $priceGroupIds = $objPriceGroupsObserver->getAllIds($paramPartnerId);
        foreach ($priceGroupIds AS $priceGroupId)
        {
            $objPriceGroup = new PriceGroup($this->conf, $this->lang, $this->settings, $priceGroupId);
            $priceGroupDetails = $objPriceGroup->getDetailsWithPartner();
            $pricePlanIds = $this->getAllIds($priceGroupId, $paramPartnerId);

            $pricePlansHTML = '';
            foreach ($pricePlanIds AS $pricePlanId)
            {
                // Process full item details
                $objPricePlan = new PricePlan($this->conf, $this->lang, $this->settings, $pricePlanId);
                $pricePlanDetails = $objPricePlan->getDetails();
                $selected = $paramSelectedPricePlanId == $pricePlanDetails['price_plan_id'] ? ' selected="selected"' : '';
                $printTitle = $priceGroupDetails[$paramTranslated ? 'print_translated_price_group_name' : 'print_price_group_name'];
                $printTitle .= ' - '.$pricePlanDetails['print_label'];
                $pricePlansHTML .= '<option value="'.$pricePlanDetails['price_plan_id'].'"'.$selected.'>'.$printTitle.'</option>';
            }
            if($pricePlansHTML != '')
            {
                $printPriceGroupTitle = $priceGroupDetails[$paramTranslated ? 'print_translated_price_group_name' : 'print_price_group_name'];
                $printPriceGroupTitle .= ' '.esc_html($priceGroupDetails['via_partner']);
                if($paramShowPriceGroupId)
                {
                    $printPriceGroupTitle .= " (ID=".$priceGroupDetails['price_group_id'].")";
                }
                $priceGroupsAndPlansHTML .= '<optgroup label="'.$printPriceGroupTitle.'">';
                $priceGroupsAndPlansHTML .= $pricePlansHTML;
                $priceGroupsAndPlansHTML .= '</optgroup>';
            }
        }

        return $priceGroupsAndPlansHTML;
    }


    /*******************************************************************************/
    /********************** METHODS FOR ADMIN ACCESS ONLY **************************/
    /*******************************************************************************/

    /**
     * @param $paramPriceGroupId
     * @param $paramTaxPercentage
     * @return string
     */
    public function getTrustedAdminListHTML($paramPriceGroupId, $paramTaxPercentage)
    {
        $pricePlansHTML = '';
        $validPriceGroupId = StaticValidator::getValidPositiveInteger($paramPriceGroupId, 0);
        $validTaxPercentage = floatval($paramTaxPercentage);
        if($this->currencySymbolLocation == 0)
        {
            $printLeftCurrencySymbol = esc_html(sanitize_text_field($this->currencySymbol)).' ';
            $printRightCurrencySymbol = '';
        } else
        {
            $printLeftCurrencySymbol = '';
            $printRightCurrencySymbol = ' '.esc_html(sanitize_text_field($this->currencySymbol));
        }

        $pricePlanIds = $this->getAllIds($paramPriceGroupId);
        foreach($pricePlanIds AS $pricePlanId)
        {
            $objPricePlan = new PricePlan($this->conf, $this->lang, $this->settings, $pricePlanId);
            $pricePlanDetails = $objPricePlan->getDetails();

            if($pricePlanDetails['seasonal_price'] == 0)
            {
                // Regular prices
                $trustedPricePlanEditLinkHTML = '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan&amp;item_model_id='.$validPriceGroupId.'&amp;price_plan_id='.$pricePlanId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a>';
                $trustedPricePlanDeleteLinkHTML = '';
            } else
            {
                // Seasonal prices
                $trustedPricePlanEditLinkHTML = '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan&amp;price_group_id='.$validPriceGroupId.'&amp;price_plan_id='.$pricePlanId)).'">'.$this->lang->escHTML('LANG_EDIT_TEXT').'</a>';
                $trustedPricePlanDeleteLinkHTML = '<a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-price-plan&amp;noheader=true&amp;delete_price_plan='.$pricePlanId)).'">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
            }

            $dailyPriceTypeTitle = $this->lang->getText('LANG_PRICE_TEXT').' / '.$this->lang->getText('LANG_PRICING_PER_DAY_SHORT_TEXT').'<br />';
            $hourlyPriceTypeTitle = $this->lang->getText('LANG_PRICE_TEXT').' / '.$this->lang->getText('LANG_PRICING_PER_HOUR_SHORT_TEXT').'<br />';
            if($validTaxPercentage > 0)
            {
                $dailyPriceTypeTitle .= $this->lang->getText('LANG_PRICE_TEXT').' + '.$this->lang->getText('LANG_TAX_SHORT_TEXT').' / '.$this->lang->getText('LANG_PRICING_PER_DAY_SHORT_TEXT');
                $hourlyPriceTypeTitle .= $this->lang->getText('LANG_PRICE_TEXT').' + '.$this->lang->getText('LANG_TAX_SHORT_TEXT').' / '.$this->lang->getText('LANG_PRICING_PER_HOUR_SHORT_TEXT');
            }


            $dailyPrices = array();
            $hourlyPrices = array();
            foreach($objPricePlan->getDaysOfTheWeek() AS $dayOfTheWeek => $dayName)
            {

                $dailyPrice = $printLeftCurrencySymbol.number_format_i18n($pricePlanDetails['daily_rate_'.$dayOfTheWeek], 2).$printRightCurrencySymbol;
                $dailyPrice .= $validTaxPercentage > 0 ? '<br />'.$printLeftCurrencySymbol.number_format_i18n($pricePlanDetails['daily_rate_'.$dayOfTheWeek]*( 1 + $validTaxPercentage / 100), 2).$printRightCurrencySymbol : '';
                $dailyPrices[] = $dailyPrice;

                $hourlyPrice = $printLeftCurrencySymbol.number_format_i18n($pricePlanDetails['hourly_rate_'.$dayOfTheWeek], 2);
                $hourlyPrice .= $validTaxPercentage > 0 ? '<br />'.$printLeftCurrencySymbol.number_format_i18n($pricePlanDetails['hourly_rate_'.$dayOfTheWeek]*( 1 + $validTaxPercentage / 100), 2).$printRightCurrencySymbol : '';
                $hourlyPrices[] = $hourlyPrice;
            }

            // HTML OUTPUT: START
            $pricePlansHTML .= '<tr class="price-plan-heading">';
            $pricePlansHTML .= '<td colspan="9" class="price-plan-big-label">'.$pricePlanDetails['print_label'].'</td>';
            $pricePlansHTML .= '</tr>';
            $pricePlansHTML .= '<tr>';
            $pricePlansHTML .= '<td class="price-plan-label">'.$this->lang->escHTML('LANG_PRICE_TYPE_TEXT').'</td>';
            foreach($objPricePlan->getDaysOfTheWeek() AS $dayOfTheWeek => $dayName)
            {
                $pricePlansHTML .= '<td class="price-plan-label">'.esc_html($dayName).'</td>';
            }
            $pricePlansHTML .= '<td class="price-plan-label">&nbsp;</td>';
            $pricePlansHTML .= '</tr>';

            if(in_array($this->priceCalculationType, array(1, 3)))
            {
                // Price by days
                $pricePlansHTML .= '<tr class="odd">';
                $pricePlansHTML .= '<td class="price-plan-description">'.$dailyPriceTypeTitle.'</td>';
                foreach($dailyPrices AS $dailyPrice)
                {
                    $pricePlansHTML .= '<td class="price-plan-label">'.$dailyPrice.'</td>';
                }
                $pricePlansHTML .= '<td class="price-plan-links">';
                if(in_array($this->priceCalculationType, array(1, 3)))
                {
                    $pricePlansHTML .= '<span class="price-plan-link">'.$trustedPricePlanEditLinkHTML.'</span>';
                    if($trustedPricePlanDeleteLinkHTML)
                    {
                        $pricePlansHTML .= ' &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp; ';
                        $pricePlansHTML .= '<span class="price-plan-link">'.$trustedPricePlanDeleteLinkHTML.'</span>';
                    }
                } else
                {
                    $pricePlansHTML .= "&nbsp;";
                }
                $pricePlansHTML .= '</td>';
                $pricePlansHTML .= '</tr>';
            }

            if(in_array($this->priceCalculationType, array(2, 3)))
            {
                // Price by hours
                $pricePlansHTML .= '<tr class="even">';
                $pricePlansHTML .= '<td class="price-plan-description">'.$hourlyPriceTypeTitle.'</td>';
                foreach($hourlyPrices AS $hourlyPrice)
                {
                    $pricePlansHTML .= '<td class="price-plan-label">'.$hourlyPrice.'</td>';
                }
                $pricePlansHTML .= '<td class="price-plan-links">';
                if($this->priceCalculationType == 2)
                {
                    $pricePlansHTML .= '<span class="price-plan-link">'.$trustedPricePlanEditLinkHTML.'</span>';
                    if($trustedPricePlanDeleteLinkHTML)
                    {
                        $pricePlansHTML .= ' &nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp; ';
                        $pricePlansHTML .= '<span class="price-plan-link">'.$trustedPricePlanDeleteLinkHTML.'</span>';
                    }
                } else
                {
                    $pricePlansHTML .= "&nbsp;";
                }
                $pricePlansHTML .= '</td>';
                $pricePlansHTML .= '</tr>';
            }
            // HTML OUTPUT: END
        }

        return $pricePlansHTML;
    }
}