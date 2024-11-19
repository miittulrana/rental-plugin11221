<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_SETTINGS_ORDER_TEXT']);?></span>
</h1>
<form name="order_settings_form" action="<?=esc_url($orderSettingsTabFormAction);?>" method="POST" class="order-settings-form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%" class="settings-table order-settings-table">
        <tbody>
        <tr>
            <td width="20%"><strong><?=esc_html($lang['LANG_SETTINGS_SHOW_LOGIN_FORM_WITH_WP_USER_TEXT']);?>:</strong></td>
            <td width="80%">
                <select name="conf_show_login_form" title="<?=esc_attr($lang['LANG_SETTINGS_SHOW_LOGIN_FORM_WITH_WP_USER_TEXT']);?>">
                    <?=$arrOrderSettings['trusted_show_login_form_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTINGS_CUSTOMER_LOOKUP_FOR_GUESTS_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_guest_customer_lookup_allowed" title="<?=esc_attr($lang['LANG_SETTINGS_CUSTOMER_LOOKUP_FOR_GUESTS_TEXT']);?>">
                    <?=$arrOrderSettings['trusted_guest_customer_lookup_allowed_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_br_html($lang['LANG_SETTINGS_AUTOMATICALLY_CREATE_ACCOUNT_NEW_WP_USER_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_automatically_create_account" title="<?=esc_attr($lang['LANG_SETTINGS_AUTOMATICALLY_CREATE_ACCOUNT_NEW_WP_USER_TEXT']);?>">
                    <?=$arrOrderSettings['trusted_automatically_create_account_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Reservation Period (Min):</strong></td>
            <td>
                <select name="conf_minimum_booking_period" title="Reservation Period (Min)">
                    <?=$arrOrderSettings['trusted_min_order_period_dropdown_options_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Reservation Period (Max):</strong></td>
            <td>
                <select name="conf_maximum_booking_period" title="Reservation Period (Max)">
                    <?=$arrOrderSettings['trusted_max_order_period_dropdown_options_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Cleaning Period:</strong></td>
            <td>
                <select name="conf_minimum_block_period_between_bookings" title="<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Cleaning Period">
                    <?=$arrOrderSettings['trusted_item_cleaning_period_dropdown_options_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Period Until Pick-up (Min):</strong></td>
            <td>
                <select name="conf_minimum_period_until_pickup" title="Period Until Pick-up (Min)">
                    <?=$arrOrderSettings['trusted_min_period_until_pickup_dropdown_options_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="<?=esc_attr($lang['LANG_SETTINGS_UPDATE_ORDER_SETTINGS_TEXT']);?>" name="update_order_settings"/>
            </td>
        </tr>
    </table>
</form>
<p><?=esc_html($lang['LANG_PLEASE_KEEP_IN_MIND_THAT_TEXT']);?>:</p>
<ol>
    <li>Non-editable settings can be edited via \'Models\Configuration\ConfigurationInterface\' file.</li>
    <li><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> cleaning period - is the shortest time period, required to clean or process the <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> after return,
        until it will get back online as available for next reservation.</li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.order-settings-form').validate();
});
</script>