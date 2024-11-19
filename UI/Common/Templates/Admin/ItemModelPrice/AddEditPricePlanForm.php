<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('jquery-ui-datepicker', array('jquery','jquery-ui-core'));
wp_enqueue_script('jquery-ui-datepicker-locale');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-ui-theme');
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside" >
    <span class="title">Add/Edit <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Price Plan</span>
    <input type="button" value="Back to Price Plan List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr />
    <strong>Note:</strong> All prices have to be entered without <?=esc_html($lang['LANG_TAX_SHORT_TEXT']);?>.<br />
    <hr />
    <form action="<?=esc_url($formAction);?>" method="POST" class="price-plan-form">
    <table width="100%" border="0" cellspacing="2" cellpadding="2">
        <input type="hidden" name="price_plan_id" value="<?=esc_attr($pricePlanId);?>" />
        <input type="hidden" name="price_group_id" value="<?=esc_attr($priceGroupId);?>" />
        <tr>
            <td width="10%"><strong>Price Group:</strong></td>
            <td width="90%">
                <?=$priceGroupName;?>
            </td>
        </tr>
        <tr>
            <td><strong>Coupon Code:</strong></td>
            <td><input type="text" name="coupon_code" maxlength="50" value="<?=esc_attr($couponCode);?>" id="coupon_code" class="form-input"  /></td>
        </tr>
        <tr>
            <td width="95px"><strong>Start Date:</strong></td>
            <td>
                <input name="start_date" type="text" size="10" value="<?=esc_attr($startDate);?>" class="start-date" />
                <img class="start-date-datepicker" src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" /></a>
                (optional, active from <?=$startTime;?>)
            </td>
        </tr>
        <tr>
            <td width="95px"><strong>End Date:</strong></td>
            <td>
                <input name="end_date" type="text" size="10" value="<?=esc_attr($endDate);?>" class="end-date"/>
                <img class="end-date-datepicker" src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" /></a>
                (optional, active till <?=$endTime;?>)
            </td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
        <td colspan="2">
        <table cellpadding="3" cellspacing="0" border="0" >
            <tr>
                <td class="td1"><?=esc_html($lang['LANG_PRICE_TYPE_TEXT']);?></td>
                <?php foreach($daysOfTheWeek AS $dayOfTheWeek => $dayName): ?>
                    <td class="td2"><?=esc_html($dayName);?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td colspan="8"><hr /></td>
            </tr>
            <?php
            if ($displayDailyRates):
                print('<tr>');
                    print('<td>');
                    print(esc_html($lang['LANG_PRICE_TEXT']).' / '.esc_html($lang['LANG_PRICING_PER_DAY_TEXT']));
                    print('</td>');
            endif;
                foreach($dailyRates AS $dayOfTheWeek => $dailyRate):
                    if ($displayDailyRates):
                        print('<td>'.$leftCurrencySymbol);
                        print('<input type="text" name="daily_rate_'.$dayOfTheWeek.'" value="'.$dailyRate.'" size="4" class="required number" />');
                        print($rightCurrencySymbol.'</td>');
                    else:
                        print('<input type="hidden" name="daily_rate_'.$dayOfTheWeek.'" value="'.$dailyRate.'" />');
                    endif;
                endforeach;
            if ($displayDailyRates):
                print('</tr>');
            endif;

            if ($displayHourlyRates):
                print('<tr>');
                    print('<td>');
                    print(esc_html($lang['LANG_PRICE_TEXT']).' / '.esc_html($lang['LANG_PRICING_PER_HOUR_TEXT']));
                    print('</td>');
            endif;
                foreach($hourlyRates AS $dayOfTheWeek => $hourlyRate):
                    if ($displayHourlyRates):
                        print('<td>'.$leftCurrencySymbol);
                        print('<input type="text" name="hourly_rate_'.$dayOfTheWeek.'" value="'.$hourlyRate.'" size="4" class="required number" />');
                        print($rightCurrencySymbol.'</td>');
                    else:
                        print('<input type="hidden" name="hourly_rate_'.$dayOfTheWeek.'" value="'.$hourlyRate.'" />');
                    endif;
                endforeach;
            if ($displayHourlyRates):
                print('</tr>');
            endif;
            ?>
        </table>
        </td>
        </tr>
        <tr>
            <td>
                <input type="submit" value="Save price plan" name="save_price_plan" class="save-button" />
            <td>
        </tr>
    </table>
    </form>
</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery(".start-date").datepicker({
        minDate: "-365D",
        maxDate: "+1095D",
        numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>,
        onSelect: function(selected) {
            var date = jQuery(this).datepicker('getDate');
            if(date)
            {
                date.setDate(date.getDate() + 1);
            }
            jQuery(".end-date").datepicker("option","minDate", date);
        }
    });

    jQuery(".end-date").datepicker({
        minDate: "-365D",
        maxDate:"+1095D",
        numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>,
        onSelect: function(selected) {
            jQuery(".start-date").datepicker("option","maxDate", selected)
        }
    });
    jQuery(".start-date-datepicker").on( "click", function() {
        jQuery(".start-date").datepicker("show");
    });
    jQuery(".end-date-datepicker").on( "click", function() {
        jQuery(".end-date").datepicker("show");
    });

    // Validator
    jQuery('.price-plan-form').validate();
 });
</script> 