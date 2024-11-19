<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_SETTINGS_GLOBAL_TEXT']);?></span>
</h1>
<form name="global_settings_form" action="<?=esc_url($globalSettingsTabFormAction);?>" method="POST" class="global-settings-form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%" class="global-settings">
        <tr>
            <td width="20%"><strong><?=esc_html($lang['LANG_SETTINGS_USE_SESSIONS_TEXT']);?>:</strong></td>
            <td width="80%" valign="middle">
                <select name="conf_use_sessions" title="<?=esc_attr($lang['LANG_SETTINGS_USE_SESSIONS_TEXT']);?>">
                    <?=$arrGlobalSettings['trusted_use_sessions_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Payment Cancelled Page:</strong></td>
            <td valign="middle"><?=$arrGlobalSettings['trusted_payment_cancelled_page_select_html'];?></td>
        </tr>
        <tr>
            <td><strong>Reservation Confirmed Page:</strong></td>
            <td valign="middle"><?=$arrGlobalSettings['trusted_order_confirmed_page_select_html'];?></td>
        </tr>
        <tr>
            <td><strong>Terms &amp; Conditions Page:</strong></td>
            <td valign="middle"><?=$arrGlobalSettings['trusted_terms_and_conditions_page_select_html'];?></td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTINGS_FRONTEND_STYLE_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_system_style" title="<?=esc_attr($lang['LANG_SETTINGS_FRONTEND_STYLE_TEXT']);?>">
                    <?=$trustedSystemStylesDropdownOptionsHTML;?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTING_DROPDOWN_STYLE_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_dropdown_style" title="<?=esc_attr($lang['LANG_SETTING_DROPDOWN_STYLE_TEXT']);?>">
                    <?=$arrGlobalSettings['trusted_dropdown_style_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTING_INPUT_STYLE_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_input_style" title="<?=esc_attr($lang['LANG_SETTING_INPUT_STYLE_TEXT']);?>">
                    <?=$arrGlobalSettings['trusted_input_style_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Short Date Format:</strong></td>
            <td>
                <select name="conf_short_date_format" title="Short Date Format">
                    <?=$arrGlobalSettings['select_short_date_format'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Time Interval (Min):</strong></td>
            <td>
                <select name="conf_time_interval" title="Time Interval (Min)">
                    <?=$arrGlobalSettings['select_time_interval'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Time Ceiling:</strong></td>
            <td>
                <select name="conf_time_ceiling" title="Time Ceiling">
                    <?=$arrGlobalSettings['select_time_ceiling'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Weekend:</strong></td>
            <td>
                <select name="conf_weekend" title="Weekend">
                    <?=$arrGlobalSettings['select_weekend'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Noon time:</strong></td>
            <td>
                <select name="conf_noon_time" title="Noon time">
                    <?=$arrGlobalSettings['select_noon_time'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Distance Measurement Unit:</strong></td>
            <td><input type="text" name="conf_distance_measurement_unit" maxlength="20" value="<?=esc_attr($settings['conf_distance_measurement_unit']);?>" class="required page-slugs" title="Distance Measurement Unit" /></td>
        </tr>
        <tr>
            <td><strong>Page Slug:</strong></td>
            <td><strong>DOMAIN/</strong><input type="text" name="conf_page_url_slug" maxlength="20" value="<?=esc_attr($settings['conf_page_url_slug']);?>" class="required page-slugs" title="Page Slug" /><strong>/CONFIRMED/</strong></td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Slug:</strong></td>
            <td><strong>DOMAIN/</strong><input type="text" name="conf_item_url_slug" maxlength="20"  value="<?=esc_attr($settings['conf_item_url_slug']);?>" class="required page-slugs" title="<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Slug" /><strong>/GREAT-<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_FULL_UPPERCASE']);?>-MODEL/</strong></td>
        </tr>
        <tr>
            <td><strong>Location Slug:</strong></td>
            <td><strong>DOMAIN/</strong><input type="text" name="conf_location_url_slug" maxlength="20" value="<?=esc_attr($settings['conf_location_url_slug']);?>" class="required page-slugs" title="Location Slug" /><strong>/GREAT-AIRPORT/</strong></td>
        </tr>
        <tr>
            <td><strong>Classify <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Models:</strong></td>
            <td>
                <select name="conf_classify_items" title="Classify <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Models">
                    <?=$arrGlobalSettings['select_classify_item_models'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Reveal Partners:</strong></td>
            <td>
                <select name="conf_reveal_partner" title="Reveal Partners">
                    <?=$arrGlobalSettings['select_reveal_partner'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Datepicker Assets:</strong></td>
            <td>
                <select name="conf_load_datepicker_from_plugin" title="Datepicker Assets">
                    <?=$arrGlobalSettings['select_load_datepicker_from_plugin'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>fancyBox Assets:</strong></td>
            <td>
                <select name="conf_load_fancybox_from_plugin" title="fancyBox Assets">
                    <?=$arrGlobalSettings['select_load_fancybox_from_plugin'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTINGS_FONT_AWESOME_ICONS_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_load_font_awesome_from_plugin" title="<?=esc_attr($lang['LANG_SETTINGS_FONT_AWESOME_ICONS_TEXT']);?>">
                    <?=$arrGlobalSettings['select_load_font_awesome_from_plugin'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_SETTINGS_SLICK_SLIDER_ASSETS_TEXT']);?>:</strong></td>
            <td>
                <select name="conf_load_slick_slider_from_plugin" title="<?=esc_html($lang['LANG_SETTINGS_SLICK_SLIDER_ASSETS_TEXT']);?>">
                    <?=$arrGlobalSettings['select_load_slick_slider_from_plugin'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="<?=esc_attr($lang['LANG_SETTINGS_UPDATE_GLOBAL_SETTINGS_TEXT']);?>" name="update_global_settings"/>
            </td>
        </tr>
    </table>
</form>
<p><?=esc_html($lang['LANG_PLEASE_KEEP_IN_MIND_THAT_TEXT']);?>:</p>
<ol>
    <li>Non-editable settings can be edited via \'Models\Configuration\ConfigurationInterface\' file.</li>
    <li><?=esc_html($lang['LANG_SETTINGS_NOTE_FOR_SESSIONS_USAGE_TEXT']);?></li>
    <li>Weekend days setting is used in <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> models and extras availability calendar, to highlight the weekend dates.</li>
    <li>Noon time is used in <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE']);?> and extras availability calendar as a partial <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>/extra availability
        from NOON till MIDNIGHT.</li>
    <li>Page, <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model and location slugs has to be different.</li>
    <li>Revealing the partners means, that if enabled, all customers will see, on which partner there is a <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> created.
        This applies only when partners are used.</li>
    <li><?=esc_html($lang['LANG_SETTINGS_NOTE_FOR_ASSETS_LOADING_PLACE_TEXT']);?></li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.global-settings-form').validate();
});
</script>