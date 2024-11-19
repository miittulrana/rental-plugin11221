<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Company Settings</span>
</h1>
<form name="company_settings_form" action="<?=esc_url($companySettingsTabFormAction);?>" method="POST" id="company_settings_form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%">
        <tr>
            <td width="20%"><strong>Company Name:</strong></td>
            <td width="80%"><input type="text" value="<?=esc_attr($settings['conf_company_name']);?>" name="conf_company_name" class="required company-name" /></td>
        </tr>
        <tr>
            <td><strong>Company Street Address:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_company_street_address']);?>" name="conf_company_street_address" class="company-street-address" /></td>
        </tr>
        <tr>
            <td><strong>Company City:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_company_city']);?>" name="conf_company_city" class="company-city" /></td>
        </tr>
        <tr>
            <td><strong>Company State:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_company_state']);?>" name="conf_company_state" class="company-state" /></td>
        </tr>
        <tr>
            <td><strong>Company Country:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_company_country']);?>" name="conf_company_country" class="company-country" /></td>
        </tr>
        <tr>
            <td><strong>Company Zip Code:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_company_zip_code']);?>" name="conf_company_zip_code" class="company-zip-code" /></td>
        </tr>
        <tr>
            <td><strong>Company Phone:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_company_phone']);?>" name="conf_company_phone" class="conf_company_phone" /></td>
        </tr>
        <tr>
            <td><strong>Company E-mail:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_company_email']);?>" name="conf_company_email" class="email conf_company_email" /></td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="Update company settings" name="update_company_settings"/>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery("#company_settings_form").validate();
});
</script>