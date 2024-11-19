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
<div id="container-inside"> <span class="title"">Add/Edit Extra</span>
  <input type="button" value="Back to Extras List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
  <hr/>
  <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
    <table cellpadding="5" cellspacing="2" border="0">
      <input type="hidden" name="extra_id" value="<?=esc_attr($extraId);?>"/>
        <tr>
            <td><strong>Extra Name:<span class="is-required">*</span></strong></td>
            <td>
                <input type="text" name="extra_name" value="<?=esc_attr($extraName);?>" id="extra_name" class="required" />
            </td>
        </tr>
        <?php if($networkEnabled): ?>
            <tr>
                <td><strong>Stock Keeping Unit:<span class="is-required">*</span></strong></td>
                <td><input type="text" name="extra_sku" maxlength="50" value="<?=esc_attr($extraSKU);?>" id="extra_sku" class="required" /><br />
                    &nbsp;&nbsp;&nbsp; <em>(Used for Google Enhanced Ecommerce tracking<br />
                        and when plugin is network-enabled in multisite mode)</em>
                </td>
            </tr>
        <?php endif; ?>
        <?php if($isManager): ?>
            <tr>
                <td><strong>Partner:</strong></td>
                <td>
                    <select name="partner_id" id="partner_id">
                        <?=$trustedPartnersDropdownOptionsHTML;?>
                    </select>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td><strong>Select a <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?></strong>:</td>
            <td>
                <select name="item_model_id">
                    <?=$trustedItemModelDropdownOptionsHTML;?>
                </select>
                &nbsp;&nbsp;&nbsp; <em>(optional, can be left blank. Use it to show this extra only to specific <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>)</em>
            </td>
        </tr>
        <tr>
            <td><strong>Total Units in Stock:<span class="is-required">*</span></strong></td>
            <td>
                <select name="units_in_stock" id="units_in_stock" class="required">
                    <?=$trustedUnitsInStockDropdownOptionsHTML;?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Max. Units per Reservation:<span class="is-required">*</span></strong></td>
            <td>
                <select name="max_units_per_booking" id="max_units_per_booking" class="required">
                    <?=$trustedMaxUnitsPerOrderDropdownOptionsHTML;?>
                </select>
                &nbsp;&nbsp;&nbsp; <em>(Can&#39;t be more than total extra units in stock)</em>
            </td>
        </tr>
        <tr>
            <td><strong>Price:<span class="is-required">*</span></strong></td>
            <td>
                <input type="text" name="price" value="<?=esc_attr($extraPrice);?>" id="price" class="required number"/>
                &nbsp;
                <?=esc_html($settings['conf_currency_code']);?>
                &nbsp;&nbsp;&nbsp; <em>(Without <?=esc_html($lang['LANG_TAX_SHORT_TEXT']);?>)</em>
            </td>
        </tr>
        <tr>
            <td><strong>Price Type:</strong></td>
            <td>
                <select name="price_type" id="price_type" class="required">
                    <?=$trustedPriceTypeDropdownOptionsHTML;?>
                </select>
            </td>
        </tr>
        <?php if($settings['conf_deposit_enabled'] == 1): ?>
            <tr>
                <td><strong>Fixed Rental Deposit:<span class="is-required">*</span></strong></td>
                <td>
                    <input type="text" name="fixed_deposit" value="<?=esc_attr($fixedDeposit);?>" id="fixed_deposit" class="required number" />
                    &nbsp;
                    <?=esc_html($settings['conf_currency_code']);?>
                    &nbsp;&nbsp;&nbsp; <em>(<?=esc_html($lang['LANG_TAX_SHORT_TEXT']);?> is not applicable for deposit - it is a refundable amount with no <?=esc_html($lang['LANG_TAX_SHORT_TEXT']);?> applied to it)</em>
                </td>
            </tr>
        <?php else: ?>
            <input type="hidden" name="fixed_deposit" value="<?=esc_attr($fixedDeposit);?>" />
        <?php endif; ?>
        <tr>
            <td></td>
            <td><input type="submit" value="Save extra" name="save_extra" class="save-button"/></td>
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