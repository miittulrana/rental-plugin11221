<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Customer Settings</span>
</h1>
<form name="customer_settings_form" action="<?=esc_url($customerSettingsTabFormAction);?>" method="POST" id="customer_settings_form" class="customer_settings_form">
    <table class="big-text" cellpadding="5" cellspacing="2" border="0" width="100%">
        <thead>
        <tr>
            <th>Customer Field</th>
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
                <?=esc_html($lang['LANG_TITLE_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_title_visible" value="yes"<?=$titleVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_title_required" value="yes"<?=$titleRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_FIRST_NAME_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_first_name_visible" value="yes"<?=$firstNameVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_first_name_required" value="yes"<?=$firstNameRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_LAST_NAME_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_last_name_visible" value="yes"<?=$lastNameVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_last_name_required" value="yes"<?=$lastNameRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_DATE_OF_BIRTH_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_birthdate_visible" value="yes"<?=$birthdateVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_birthdate_required" value="yes"<?=$birthdateRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_STREET_ADDRESS_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_street_address_visible" value="yes"<?=$streetAddressVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_street_address_required" value="yes"<?=$streetAddressRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_CITY_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_city_visible" value="yes"<?=$cityVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_city_required" value="yes"<?=$cityRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_STATE_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_state_visible" value="yes"<?=$stateVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_state_required" value="yes"<?=$stateRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_ZIP_CODE_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_zip_code_visible" value="yes"<?=$zipCodeVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_zip_code_required" value="yes"<?=$zipCodeRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_COUNTRY_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_country_visible" value="yes"<?=$countryVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_country_required" value="yes"<?=$countryRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_PHONE_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_phone_visible" value="yes"<?=$phoneVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_phone_required" value="yes"<?=$phoneRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_EMAIL_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_email_visible" value="yes"<?=$emailVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_email_required" value="yes"<?=$emailRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>
                <?=esc_html($lang['LANG_ADDITIONAL_COMMENTS_TEXT']);?>
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_comments_visible" value="yes"<?=$commentsVisibleChecked;?> />
            </td>
            <td align="center">
                <input type="checkbox" name="conf_customer_comments_required" value="yes"<?=$commentsRequiredChecked;?> />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" align="center">
                <br />
                <input type="submit" value="Update customer settings" name="update_customer_settings"/>
            </td>
        </tr>
    </table>
</form>
<p>Please keep in mind that:</p>
<ol>
    <li>If you will hide or make optional the &quot;Customer email&quot; field, you will also disable Ajax user data pre-fill box, where there is a search by email. All data will have to be then entered manually on every new booking.</li>
    <li>For reservation edit, it will still pull customer&#39;s data, because system will use customer id, which is attached to reservation id.</li>
    <li>If you mark customer birth date as a mandatory field, it will be used as a 2nd parameter for customer details lookup to make security stronger.</li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery("#customer_settings_form").validate();
});
</script>