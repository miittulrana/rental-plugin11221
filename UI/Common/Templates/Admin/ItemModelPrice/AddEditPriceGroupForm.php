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
  <span class="title">Add/Edit Price Group</span>
  <input type="button" value="Back to price group list" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr />
  <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
    <table cellpadding="5" cellspacing="2" border="0">
        <input type="hidden" name="price_group_id" value="<?=esc_attr($priceGroupId);?>"/>
        <tr>
            <td><strong>Price Group Name:</strong></td>
            <td>
                <input type="text" name="price_group_name" value="<?=esc_attr($priceGroupName);?>" id="price_group_name" class="required form-input"  />
            </td>
        </tr>
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
            <td></td>
            <td><input type="submit" value="Save price group" name="save_price_group" class="save-button"/></td>
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