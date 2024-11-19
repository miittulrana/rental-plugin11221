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
  <span class="title"><?=esc_html($pageTitle);?></span>
  <input type="button" value="Back To Price Plan Discounts List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
  <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
    <table cellpadding="5" cellspacing="2" border="0">
        <input type="hidden" name="discount_id" value="<?=esc_attr($discountId);?>" />
        <input type="hidden" name="discount_type" value="<?=esc_attr($discountType);?>" />
        <tr>
            <td width="20%"><strong>Select a Price Plan:</strong></td>
            <td width="80%">
                <select name="price_plan_id" class="select-price-plan">
                    <?=$trustedPricePlanDropdownOptionsHTML;?>
                </select> (optional, leave blank to apply same discount % to all price plans)
                <input type="hidden" name="extra_id" value="0" />
            </td>
        </tr>
        <tr>
          <td><strong><?=esc_html($fromTitle);?></strong></td>
          <td>
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
          <td><strong><?=esc_html($toTitle);?></strong></td>
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
          <td><strong>Price Discount:</strong><br /><em>(of total <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> price)</em></td>
          <td>
              <input type="text" name="discount_percentage" value="<?=esc_attr($discountPercentage);?>" class="required number" />&nbsp;%
          </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="submit" value="Save discount plan" name="save_discount" class="save-button"/>
            </td>
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