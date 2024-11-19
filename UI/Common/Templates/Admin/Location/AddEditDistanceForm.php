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
<div id="container-inside">
    <span class="title">Distance Add/Edit</span>
    <input type="button" value="Back To Distance List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
    <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
        <table cellpadding="5" cellspacing="2" border="0">
            <input type="hidden" name="distance_id" value="<?=esc_attr($distanceId);?>"/>
            <tr>
                <td class="label"><strong>Pick-up Location:</strong></td>
                <td>
                    <select name="pickup_location_id" id="pickup_location_id">
                        <?=$trustedPickupLocationsDropdownOptionsHTML;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><strong>Return Location:</strong></td>
                <td>
                    <select name="return_location_id" id="return_location_id">
                        <?=$trustedReturnLocationsDropdownOptionsHTML;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><strong>Distance:</strong></td>
                <td>
                    <input type="text" name="distance" value="<?=esc_attr($distance);?>" id="distance" class="required number" />
                    &nbsp;<strong><?=esc_html($settings['conf_distance_measurement_unit']);?></strong> &nbsp;
                    <input type="checkbox" id="show_distance" name="show_distance" value="yes"<?=$showDistance;?>/> Show
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
                <td><input type="submit" value="Save Distance" name="save_distance" class="save-button"/></td>
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