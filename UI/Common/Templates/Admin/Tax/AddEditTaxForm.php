<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside" >
  <span class="title">Add/Edit Tax</span>
  <input type="button" value="Back to tax list" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
  <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
    <table cellpadding="5" cellspacing="2" border="0">
        <input type="hidden" name="tax_id" value="<?=esc_attr($taxId);?>"/>
        <tr>
            <td width="20%"><strong>Tax Name:<span class="is-required">*<span></strong></td>
            <td width="80%">
                <input type="text" name="tax_name" value="<?=esc_attr($taxName);?>" id="tax_name" class="required tax-name" />
            </td>
        </tr>
        <tr>
            <td><strong>Select Location</strong>:</td>
            <td>
                <select name="location_id" class="">
                    <?=$trustedLocationsDropdownOptionsHTML;?>
                </select> (optional, leave blank to apply same tax % to all locations)
            </td>
        </tr>
        <tr>
            <td><strong>Location Type:</strong><br /></td>
            <td>
                <input type="radio" name="location_type" value="1"<?=$pickupTypeChecked;?> /> <?=esc_html($lang['LANG_PICKUP_TEXT']);?>
                <input type="radio" name="location_type" value="2"<?=$returnTypeChecked;?> /> <?=esc_html($lang['LANG_RETURN_TEXT']);?>
            </td>
        </tr>

        <tr>
            <td><strong>Tax Percentage:</strong></td>
            <td>
                <input type="text" name="tax_percentage" value="<?=esc_attr($taxPercentage);?>" id="tax_percentage" class="required number" />&nbsp;%
            </td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" value="Save tax" name="save_tax" class="save-button"/></td>
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