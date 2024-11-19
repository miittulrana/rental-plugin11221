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
<div id="container-inside" class="fleet-management-add-additional-fee">
    <span class="title">Additional Fee Add/Edit</span>
    <input type="button" class="button-back" value="Back To Additional Fee List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" />
    <hr/>
    <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
        <table cellpadding="5" cellspacing="2" border="0">
            <input type="hidden" name="additional_fee_id" value="<?=esc_attr($additionalFeeId);?>"/>
            <tr>
                <td class="label"><strong>Pick-up Location:</strong></td>
                <td>
                    <select name="pickup_location_id" id="pickup_location_id" disabled="disabled">
                        <?=$trustedPickupLocationsDropdownOptionsHTML;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><strong>Return Location:</strong></td>
                <td>
                    <select name="return_location_id" id="return_location_id" disabled="disabled">
                        <?=$trustedReturnLocationsDropdownOptionsHTML;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><strong>Additional Fee:</strong></td>
                <td>
                    <?=esc_html($settings['conf_currency_symbol']);?>
                    <input type="text" name="additional_fee" value="<?=esc_attr($additionalFee);?>" id="additional_fee" class="required number" size="4" />
                    (excl. <?=esc_html($lang['LANG_TAX_SHORT_TEXT']);?>)
                </td>
            </tr>
            <tr>
                <td class="label"></td>
                <td><input type="submit" value="<?=esc_attr($lang['LANG_ADDITIONAL_FEE_SAVE_TEXT']);?>" name="save_additional_fee" class="save-button"/></td>
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