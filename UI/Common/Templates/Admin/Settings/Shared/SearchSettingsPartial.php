<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Search Settings</span>
</h1>
<form name="search_settings_form" action="<?=esc_url($searchSettingsTabFormAction);?>" method="POST" id="search_settings_form" class="search_settings_form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%">
    <tr>
        <td width="20%"><strong>Search:</strong></td>
        <td width="80%">
            <select name="conf_search_enabled" id="conf_search_enabled">
                <?=$arrSearchSettings['trusted_search_enabled_dropdown_options_html'];?>
            </select>
        </td>
    </tr>
    <tr>
        <td><strong>Select Multiple <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Models:</strong></td>
        <td>
            <select name="conf_booking_model" id="conf_booking_model">
                <?=$arrSearchSettings['trusted_search_multimode_dropdown_options_html'];?>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2"><hr />
        </td>
    </tr>
    </table>
    <table class="big-text" cellpadding="5" cellspacing="2" border="0" width="100%">
        <thead>
        <tr>
            <th>Search Field</th>
            <th>Visible</th>
            <th>Required</th>
            <th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
        </tr>
        <tr>
            <th colspan="4"><hr /></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <?=esc_html($lang['LANG_LOCATION_PICKUP_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_pickup_location_visible" value="yes"<?=$pickupLocationVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_pickup_location_required" value="yes"<?=$pickupLocationRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_PICKUP_DATE_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_pickup_date_visible" value="yes"<?=$pickupDateVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_pickup_date_required" value="yes"<?=$pickupDateRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_LOCATION_RETURN_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_return_location_visible" value="yes"<?=$returnLocationVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_return_location_required" value="yes"<?=$returnLocationRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_RETURN_DATE_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_return_date_visible" value="yes"<?=$returnDateVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_return_date_required" value="yes"<?=$returnDateRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_PARTNER_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_partner_visible" value="yes"<?=$partnerVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_partner_required" value="yes"<?=$partnerRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_MANUFACTURER_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_manufacturer_visible" value="yes"<?=$manufacturerVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_manufacturer_required" value="yes"<?=$manufacturerRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_CLASS_TEXT']);?> <span class="not-important">(Class)</span>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_body_type_visible" value="yes"<?=$classVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_body_type_required" value="yes"<?=$classRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL1_TEXT']);?> <span class="not-important">(<?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME1_TEXT']);?>)</span>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_fuel_type_visible" value="yes"<?=$attribute1VisibleChecked;?> />
            </td>
            <td align="center">
                ---
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT']);?> <span class="not-important">(<?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME2_TEXT']);?>)</span>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_transmission_type_visible" value="yes"<?=$attribute2VisibleChecked;?> />
            </td>
            <td align="center">
                ---
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_COUPON_CODE_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_coupon_code_visible" value="yes"<?=$couponCodeVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_search_coupon_code_required" value="yes"<?=$couponCodeRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="center">
                <br />
                <input type="submit" value="Update search settings" name="update_search_settings"/>
            </td>
        </tr>
    </table>
</form>
<p>Please keep in mind that:</p>
<ol>
    <li>Existing reservation code visibility/required setting is only applied to main search template, and is not applied for &quot;Edit Reservation&quot; template.</li>
    <li>If you don&#39;t want to allow edit reservations at all - then just do not install reservation editing shortcode.</li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery("#search_settings_form").validate();
});
</script>