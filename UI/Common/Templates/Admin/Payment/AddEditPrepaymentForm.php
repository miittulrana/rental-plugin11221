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
<div id="container-inside">
  <span class="title">Add/Edit Prepayment</span>
  <input type="button" value="Back to prepayments list" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
  <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
    <table cellpadding="5" cellspacing="2" border="0">
        <input type="hidden" name="prepayment_id" value="<?=esc_attr($prepaymentId);?>"/>
        <tr>
            <td width="20%"><strong>Duration From:</strong></td>
            <td width="80%">
                <?php
                if (in_array($settings['conf_price_calculation_type'], array("1", "3")))
                {
                    print('<input type="text" name="days_from" value="'.$durationFromDays.'" class="required digits" />');
                    print(' '.esc_html($lang['LANG_DAYS2_TEXT']));
                }
                if (in_array($settings['conf_price_calculation_type'], array("2", "3")))
                {
                    print('<input type="text" name="hours_from" value="'.$durationFromHours.'" class="required digits" />');
                    print(' '.esc_html($lang['LANG_HOURS2_TEXT']).' (for minutes - use fraction, i.e. for 1 hour 15 minutes enter 1.25)');
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>Duration Till:</strong></td>
            <td>
                <?php
                if (in_array($settings['conf_price_calculation_type'], array("1", "3")))
                {
                    print('<input type="text" name="days_till" value="'.$durationTillDays.'" class="required digits" />');
                    print(' '.esc_html($lang['LANG_DAYS2_TEXT']));
                    if($settings['conf_price_calculation_type'] == 1) { print(', including full last day'); }
                }
                if (in_array($settings['conf_price_calculation_type'], array("2", "3")))
                {
                    print('<input type="text" name="hours_till" value="'.$durationTillHours.'" class="required number" />');
                    print(' '.esc_html($lang['LANG_HOURS2_TEXT']).', including full last hour');
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>Include:</strong></td>
            <td>
                <table width="100%">
                    <tr>
                        <td><input type="checkbox" id="item_prices_included" name="item_prices_included"<?=$itemPricesIncludedChecked;?>/> <?=esc_html($lang['LANG_PREPAYMENT_ITEMS_PRICE_TEXT']);?></td>
                        <td><input type="checkbox" id="item_deposits_included" name="item_deposits_included"<?=$itemDepositsIncludedChecked;?>/> <?=esc_html($lang['LANG_PREPAYMENT_ITEMS_DEPOSIT_TEXT']);?></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" id="extra_prices_included" name="extra_prices_included"<?=$extraPricesIncludedChecked;?>/> <?=esc_html($lang['LANG_PREPAYMENT_EXTRAS_PRICE_TEXT']);?></td>
                        <td><input type="checkbox" id="extra_deposits_included" name="extra_deposits_included"<?=$extraDepositsIncludedChecked;?>/> <?=esc_html($lang['LANG_PREPAYMENT_EXTRAS_DEPOSIT_TEXT']);?></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" id="pickup_fees_included" name="pickup_fees_included"<?=$pickupFeesIncludedChecked;?>/> <?=esc_html($lang['LANG_PREPAYMENT_PICKUP_FEES_TEXT']);?></td>
                        <td><input type="checkbox" id="additional_fees_included" name="additional_fees_included"<?=$additionalFeesIncludedCheckedChecked;?>/> <?=esc_html($lang['LANG_PREPAYMENT_ADDITIONAL_FEES_TEXT']);?></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" id="return_fees_included" name="return_fees_included"<?=$returnFeesIncludedChecked;?>/> <?=esc_html($lang['LANG_PREPAYMENT_RETURN_FEES_TEXT']);?></td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><strong>Prepayment:</strong></td>
            <td>
                <input type="text" name="prepayment_percentage" value="<?=esc_attr($prepaymentPercentage);?>" id="prepayment_percentage" class="required number" />&nbsp;%
            </td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" value="Save prepayment" name="save_prepayment" class="save-button"/></td>
        </tr>
    </table>
  </form>
</div>
</div>
<script type="text/javascript">
jQuery().ready(function() {
    'use strict';
    jQuery("#form1").validate();
});
</script>