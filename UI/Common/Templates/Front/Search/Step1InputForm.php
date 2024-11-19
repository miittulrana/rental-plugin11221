<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('fleet-management-main');
if($settings['conf_load_datepicker_from_plugin'] == 1):
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-datepicker-locale');
endif;

// Styles
if($settings['conf_load_datepicker_from_plugin'] == 1):
    wp_enqueue_style('jquery-ui-theme');
endif;
wp_enqueue_style('fleet-management-main');
?>
<?php
// Global variable are needed here, because only then we will be able to access them inside the 'add_action' hook
$GLOBALS['EXT_CODE'] = $extCode;
$GLOBALS['EXT_CSS_PREFIX'] = $extCSS_Prefix;
$GLOBALS['PICKUP_CLOSED_DATES'] = $pickupClosedDates;
$GLOBALS['RETURN_CLOSED_DATES'] = $returnClosedDates;
$GLOBALS['MIN_DATE'] = $minDate;
$GLOBALS['CONF_DATEPICKER_DATE_FORMAT'] = $settings['conf_datepicker_date_format'];
$GLOBALS['COUPON_CODE_REQUIRED'] = $couponCodeRequired;
$GLOBALS['PICKUP_LOCATION_REQUIRED'] = $pickupLocationRequired;
$GLOBALS['RETURN_LOCATION_REQUIRED'] = $returnLocationRequired;
$GLOBALS['PICKUP_DATE_REQUIRED'] = $pickupDateRequired;
$GLOBALS['RETURN_DATE_REQUIRED'] = $returnDateRequired;
$GLOBALS['LANG_LOCATION_STATUS_CLOSED_TEXT'] = $lang['LANG_LOCATION_STATUS_CLOSED_TEXT'];
$GLOBALS['LANG_COUPON_CODE_INPUT_TEXT'] = $lang['LANG_COUPON_CODE_INPUT_TEXT'];
$GLOBALS['LANG_COUPON_CODE_INPUT2_TEXT'] = $lang['LANG_COUPON_CODE_INPUT2_TEXT'];
$GLOBALS['LANG_SEARCH_NO_COUPON_CODE_ERROR_TEXT'] = $lang['LANG_SEARCH_NO_COUPON_CODE_ERROR_TEXT'];
$GLOBALS['LANG_LOCATION_PICKUP_SELECT_ERROR_TEXT'] = $lang['LANG_LOCATION_PICKUP_SELECT_ERROR_TEXT'];
$GLOBALS['LANG_LOCATION_RETURN_SELECT_ERROR_TEXT'] = $lang['LANG_LOCATION_RETURN_SELECT_ERROR_TEXT'];
$GLOBALS['LANG_DATE_SELECT_TEXT'] = $lang['LANG_DATE_SELECT_TEXT'];
$GLOBALS['LANG_DATE_SELECT2_TEXT'] = $lang['LANG_DATE_SELECT2_TEXT'];
$GLOBALS['LANG_SEARCH_PICKUP_DATE_SELECT_ERROR_TEXT'] = $lang['LANG_SEARCH_PICKUP_DATE_SELECT_ERROR_TEXT'];
$GLOBALS['LANG_SEARCH_RETURN_DATE_SELECT_ERROR_TEXT'] = $lang['LANG_SEARCH_RETURN_DATE_SELECT_ERROR_TEXT'];
$GLOBALS['LANG_ORDER_NO_PERIOD_SELECTED_ERROR_TEXT'] = $lang['LANG_ORDER_NO_PERIOD_SELECTED_ERROR_TEXT'];
?>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function(){
    'use strict';
    jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').datepicker({
        minDate: "+<?=esc_js($GLOBALS['MIN_DATE']);?>D",
        maxDate: "+1035D",
        dateFormat: '<?=esc_js($GLOBALS['CONF_DATEPICKER_DATE_FORMAT']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>,
        beforeShowDay: function (date) {
            var closedDates = [<?=$GLOBALS['PICKUP_CLOSED_DATES'];?>];
            return FleetManagementMain.getUnavailableDates('<?=esc_js($GLOBALS['EXT_CODE']);?>', date, closedDates);
        },
        numberOfMonths: 2,
        onSelect: function(selected) {
            var date = jQuery(this).datepicker('getDate');
            if(date) {
                date.setDate(date.getDate());
            }
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').datepicker("option","minDate", date)
        }
    });
    jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').datepicker({
        minDate: 0,
        maxDate:"+1035D",
        dateFormat: '<?=esc_js($GLOBALS['CONF_DATEPICKER_DATE_FORMAT']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>,
        beforeShowDay:  function (date) {
            var closedDates = [<?=$GLOBALS['RETURN_CLOSED_DATES'];?>];
            return FleetManagementMain.getUnavailableDates('<?=esc_js($GLOBALS['EXT_CODE']);?>', date, closedDates);
        },
        numberOfMonths: 2,
        onSelect: function(selected) {
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').datepicker("option","maxDate", selected)
        }
    });
    if(jQuery(window).width() < 728){
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').datepicker("option", "numberOfMonths", 1);
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').addClass('is-minified');
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').datepicker("option", "numberOfMonths", 1);
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').addClass('is-minified');
    }
    jQuery(window).resize(function(){
        if(jQuery(window).width() < 728 && (!jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').hasClass('is-minified') || !jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').hasClass('is-minified'))){
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').datepicker("option", "numberOfMonths", 1);
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').addClass('is-minified');
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').datepicker("option", "numberOfMonths", 1);
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').addClass('is-minified');
        } else if(jQuery(window).width() > 728 && (jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').hasClass('is-minified') || jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').hasClass('is-minified'))){
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').datepicker("option", "numberOfMonths", 2);
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').removeClass('is-minified');
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').datepicker("option", "numberOfMonths", 2);
            jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').removeClass('is-minified');
        }
    });
    jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date-datepicker').on( "click", function()
    {
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date').datepicker('show');
    });
    jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date-datepicker').on( "click", function()
    {
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date').datepicker('show');
    });

    jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .do-search').on( "click", function()
    {
        var canProceed = true;
        var objCouponCode = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .coupon-code');
        var objPickupLocation = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-location');
        var objReturnLocation = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-location');
        var objPickupDate = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .pickup-date');
        var objReturnDate = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .return-date');
        var objOrderPeriod = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>wrapper .order-period');

        var couponCode = "SKIP";
        var pickupLocationId = "SKIP";
        var returnLocationId = "SKIP";
        var pickupDate = "SKIP";
        var returnDate = "SKIP";
        var orderPeriod = "SKIP";

        <?php if($GLOBALS['COUPON_CODE_REQUIRED']): ?>
            if(objCouponCode.length)
            {
                couponCode = objCouponCode.val();
            }
        <?php endif; ?>

        <?php if($GLOBALS['PICKUP_LOCATION_REQUIRED']): ?>
            if(objPickupLocation.length)
            {
                pickupLocationId = Number(objPickupLocation.val());
            }
        <?php endif; ?>

        <?php if($GLOBALS['RETURN_LOCATION_REQUIRED']): ?>
            if(objReturnLocation.length)
            {
                returnLocationId = Number(objReturnLocation.val());
            }
        <?php endif; ?>

        <?php if($GLOBALS['PICKUP_DATE_REQUIRED']): ?>
            if(objPickupDate.length)
            {
                pickupDate = objPickupDate.val();
            }
        <?php endif; ?>

        <?php if($GLOBALS['RETURN_DATE_REQUIRED']): ?>
            if(objReturnDate.length)
            {
                returnDate = objReturnDate.val();
            }
        <?php endif; ?>

        <?php if($GLOBALS['RETURN_DATE_REQUIRED']): ?>
            if(objOrderPeriod.length)
            {
                orderPeriod = Number(objOrderPeriod.val());
            }
        <?php endif; ?>
        //alert('couponCode[len]:' + objCouponCode.length + ', couponCode[val]:' + couponCode);

        if(couponCode === "" || couponCode === "<?=esc_js($GLOBALS['LANG_COUPON_CODE_INPUT_TEXT']);?>" || couponCode === "<?=esc_js($GLOBALS['LANG_COUPON_CODE_INPUT2_TEXT']);?>")
        {
            alert('<?=esc_js($GLOBALS['LANG_SEARCH_NO_COUPON_CODE_ERROR_TEXT']);?>');
            canProceed = false;
        } else if(pickupLocationId === 0)
        {
            alert('<?=esc_js($GLOBALS['LANG_LOCATION_PICKUP_SELECT_ERROR_TEXT']);?>');
            canProceed = false;
        } else if(returnLocationId === 0)
        {
            alert('<?=esc_js($GLOBALS['LANG_LOCATION_RETURN_SELECT_ERROR_TEXT']);?>');
            canProceed = false;
        } else if(pickupDate === "" || pickupDate === "<?=esc_js($GLOBALS['LANG_DATE_SELECT_TEXT']);?>" || pickupDate === "<?=esc_js($GLOBALS['LANG_DATE_SELECT2_TEXT']);?>")
        {
            alert('<?=esc_js($GLOBALS['LANG_SEARCH_PICKUP_DATE_SELECT_ERROR_TEXT']);?>');
            canProceed = false;
        } else if(returnDate === "" || returnDate === "<?=esc_js($GLOBALS['LANG_DATE_SELECT_TEXT']);?>" || returnDate === "<?=esc_js($GLOBALS['LANG_DATE_SELECT2_TEXT']);?>")
        {
            alert('<?=esc_js($GLOBALS['LANG_SEARCH_RETURN_DATE_SELECT_ERROR_TEXT']);?>');
            canProceed = false;
        } else if(orderPeriod === 0)
        {
            alert('<?=esc_js($GLOBALS['LANG_ORDER_NO_PERIOD_SELECTED_ERROR_TEXT']);?>');
            canProceed = false;
        }

        return canProceed;
    });
});
</script>
<?php }, 100); ?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-search-step1 <?=esc_attr($extCSS_Prefix);?>search-step1">
    <form id="formElem" name="formElem" action="<?=esc_url($formAction);?>" method="POST">
        <?php if($displaySearchBlock1): ?>
        <div class="search-field">
            <div class="search-field-header">
                <div class="search-field-title">
                   <i class="icon-pickup title-icon"></i> <p><?=esc_html($lang['LANG_PICKUP_TEXT']);?>
                    <?=esc_html($lang['LANG_INFORMATION_TEXT']);?></p>
                </div>
            </div>
            <div class="search-field-body">
                <?php if($pickupLocationVisible): ?>
                    <?php if($pickupLocationName != ""): ?>
                        <input type="hidden" name="pickup_location_id" class="pickup-location" value="<?=esc_attr($pickupLocationId);?>" />
                        <div class="location-title"><?=$pickupLocationName;?></div>
                    <?php else: ?>
                        <div class="styled-select-dropdown wide-dropdown">
                            <i class="icon-location select-icon"></i><select name="pickup_location_id" class="pickup-location home-select">
                                <?=$trustedPickupDropdownOptionsHTML;?>
                            </select>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($pickupDateVisible): ?>
                    <div class="inline-div">
                        <input type="text" name="pickup_date" value="<?=esc_attr($selectedPickupDate);?>" class="pickup-date" readonly="readonly" AUTOCOMPLETE=OFF />
                    </div>
                    <div class="styled-select-dropdown narrow-dropdown">
                        <i class="icon-clock select-icon"></i><select name="pickup_time" class="pickup-time">
                            <?=$trustedPickupTimeDropdownOptionsHTML;?>
                        </select>
                    </div>
                <?php endif; ?>
                <?php if($couponCodeVisible): ?>
                    <div class="top-padded">
                        <?php if($inputStyle == 1): ?>
                            <input type="text" name="coupon_code" value="<?=esc_attr($couponCode != "" ? $couponCode : $lang['LANG_COUPON_CODE_INPUT_TEXT']);?>" class="coupon-code" title="<?=esc_attr($lang['LANG_COUPON_CODE_TEXT']);?>"
                                   onfocus="if(this.value === '<?=esc_js($lang['LANG_COUPON_CODE_INPUT_TEXT']);?>') {this.value=''}"
                                   onblur="if(this.value === ''){this.value ='<?=esc_js($lang['LANG_COUPON_CODE_INPUT_TEXT']);?>'}" />
                        <?php else: ?>
                            <input type="text" name="coupon_code" value="<?=esc_attr($couponCode != "" ? $couponCode : $lang['LANG_COUPON_CODE_INPUT2_TEXT']);?>" class="coupon-code" title="<?=esc_attr($lang['LANG_COUPON_CODE_TEXT']);?>"
                                   onfocus="if(this.value === '<?=esc_js($lang['LANG_COUPON_CODE_INPUT2_TEXT']);?>') {this.value=''}"
                                   onblur="if(this.value === ''){this.value ='<?=esc_js($lang['LANG_COUPON_CODE_INPUT2_TEXT']);?>'}" />
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if($cancelButtonBlock == 1 && $newOrder == false): ?>
                    <div class="top-padded-cancel">
                        <input type="submit" name="<?=esc_attr($extPrefix);?>cancel_order" value="<?=esc_html($lang['LANG_CANCEL_ORDER_TEXT']);?>" class="btn-cancel-order" />
                    </div>
                <?php endif; ?>

                <?php if($searchButtonBlock == 1 && $newOrder == true): ?>
                    <div class="top-padded-submit">
                        <?php if($settings['conf_universal_analytics_events_tracking'] == 1): ?>
                            <!-- Note: Do not translate events to track well inter-language events -->
                            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_SEARCH_TEXT']);?>" class="do-search"
                                   onclick="ga('send', 'event', '<?=esc_js($extName);?>', 'Click', '1. Search for all item models');" />
                        <?php else: ?>
                            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_SEARCH_TEXT']);?>" class="do-search" />
                        <?php endif; ?>
                    </div>
                <?php elseif($searchButtonBlock == 1): ?>
                    <div class="top-padded-submit">
                        <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_CONTINUE_TEXT']);?>" class="do-search" />
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($displaySearchBlock2): ?>
            <div class="search-field">
                <div class="search-field-header">
                    <div class="search-field-title">
                        <i class="icon-return title-icon"></i><p><?=esc_html($lang['LANG_RETURN_TEXT']);?>
                        <?=esc_html($lang['LANG_INFORMATION_TEXT']);?></p></div>
                </div>
                <div class="search-field-body">
                    <?php if($returnLocationVisible): ?>
                        <?php if($returnLocationName != ""): ?>
                            <input type="hidden" name="return_location_id" class="return-location" value="<?=esc_attr($returnLocationId);?>" />
                            <div class="location-title"><?=$returnLocationName;?></div>
                        <?php else: ?>
                            <div class="styled-select-dropdown wide-dropdown">
                                <i class="icon-location select-icon"></i><select name="return_location_id" class="return-location home-select">
                                    <?=$trustedReturnDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($returnDateVisible): ?>
                        <?php if($settings['conf_price_calculation_type'] == 2): ?>
                            <div class="styled-select-dropdown wide-dropdown">
                                <select name="expected_order_period" class="order-period home-select">
                                    <?=$trustedOrderPeriodsDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="inline-div">
                                <input type="text" name="return_date" class="return-date" value="<?=esc_attr($selectedReturnDate);?>" readonly="readonly" AUTOCOMPLETE=OFF />
                            </div>
                            <div class="styled-select-dropdown narrow-dropdown">
                                <i class="icon-clock select-icon"></i><select name="return_time" class="return-time">
                                    <?=$trustedReturnTimeDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($cancelButtonBlock == 2 && $newOrder == false): ?>
                        <div class="top-padded-cancel">
                            <input type="submit" name="<?=esc_attr($extPrefix);?>cancel_order" value="<?=esc_attr($lang['LANG_CANCEL_ORDER_TEXT']);?>" class="btn-cancel-order" />
                        </div>
                    <?php endif; ?>

                    <?php if($searchButtonBlock == 2 && $newOrder == true): ?>
                        <div class="top-padded-submit">
                            <?php if($settings['conf_universal_analytics_events_tracking'] == 1): ?>
                                <!-- Note: Do not translate events to track well inter-language events -->
                                <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_SEARCH_TEXT']);?>" class="do-search"
                                       onclick="ga('send', 'event', '<?=esc_js($extName);?>', 'Click', '1. Search for all item models');" />
                            <?php else: ?>
                                <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_SEARCH_TEXT']);?>" class="do-search" />
                            <?php endif; ?>
                        </div>
                    <?php elseif($searchButtonBlock == 2): ?>
                        <div class="top-padded-submit">
                            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_CONTINUE_TEXT']);?>" class="do-search" />
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if($displaySearchBlock3): ?>
            <div class="search-field">
                <div class="search-field-header">
                    <div class="search-field-title">
                        <i class="icon-other title-icon"></i><p><?=esc_html($lang['LANG_OTHER_TEXT']);?>
                        <?=esc_html($lang['LANG_INFORMATION_TEXT']);?></p></div>
                </div>
                <div class="search-field-body">
                    <?php if($partnerVisible): ?>
                        <?php if($partnerId > 0): ?>
                            <input type="hidden" name="partner_id" class="partner" value="<?=esc_attr($partnerId);?>" />
                        <?php else: ?>
                            <div class="styled-select-dropdown wide-dropdown">
                                <select name="partner_id" class="partner">
                                    <?=$trustedPartnersDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($manufacturerVisible): ?>
                        <?php if($manufacturerId > 0): ?>
                            <input type="hidden" name="manufacturer_id" class="manufacturer" value="<?=esc_attr($manufacturerId);?>" />
                        <?php else: ?>
                            <div class="styled-select-dropdown wide-dropdown">
                                <select name="manufacturer_id" class="manufacturer">
                                    <?=$trustedManufacturersDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($classVisible): ?>
                        <?php if($classId > 0): ?>
                            <input type="hidden" name="class_id" value="<?=esc_attr($classId);?>" class="class" />
                        <?php else: ?>
                            <div class="styled-select-dropdown wide-dropdown">
                                <select name="class_id" class="class">
                                    <?=$trustedClassesDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($attribute2Visible): ?>
                        <?php if($attributeId2 > 0): ?>
                            <input type="hidden" name="attribute_id2" value="<?=esc_attr($attributeId2);?>" class="attribute2" />
                        <?php else: ?>
                            <div class="styled-select-dropdown wide-dropdown">
                                <select name="attribute_id2" class="attribute2">
                                    <?=$trustedAttributeGroup2AttributesDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($attribute1Visible): ?>
                        <?php if($attributeId1 > 0): ?>
                            <input type="hidden" name="attribute_id1" value="<?=esc_attr($attributeId1);?>" class="attribute1" />
                        <?php else: ?>
                            <div class="styled-select-dropdown wide-dropdown">
                                <select name="attribute_id1" class="attribute1">
                                    <?=$trustedAttributeGroup1AttributesDropdownOptionsHTML;?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($cancelButtonBlock == 3 && $newOrder == false): ?>
                        <div class="top-padded-cancel">
                            <input type="submit" name="<?=esc_attr($extPrefix);?>cancel_order" value="<?=esc_attr($lang['LANG_CANCEL_ORDER_TEXT']);?>" class="btn-cancel-order" />
                        </div>
                    <?php endif; ?>
		</div>
		</div>
		<div class="search-field">
		  <div class="search-field-body">
                    <input type="hidden" name="<?=esc_attr($extPrefix);?>came_from_step1" value="yes" />
                    <?php if($searchButtonBlock == 3 && $newOrder == true): ?>
                        <div class="top-padded-submit">
                            <?php if($settings['conf_universal_analytics_events_tracking'] == 1): ?>
                                <!-- Note: Do not translate events to track well inter-language events -->
                                <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_SEARCH_TEXT']);?>" class="do-search"
                                       onclick="ga('send', 'event', '<?=esc_js($extName);?>', 'Click', '1. Search for all item models');" />
                            <?php else: ?>
                                <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_SEARCH_TEXT']);?>" class="do-search" />
                            <?php endif; ?>
                        </div>
                    <?php elseif($searchButtonBlock == 3): ?>
                        <div class="top-padded-submit">
                            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_CONTINUE_TEXT']);?>" class="do-search" />
                        </div>
                    <?php endif; ?>
			</div>
		</div>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>