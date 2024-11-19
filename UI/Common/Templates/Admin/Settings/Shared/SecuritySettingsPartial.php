<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Security Settings</span>
</h1>
<form name="security_settings_form" action="<?=esc_url($securitySettingsTabFormAction);?>" method="POST" id="security_settings_form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%">
        <tr>
            <td width="20%"><strong>ReCaptcha Site Key:</strong></td>
            <td width="80%"><input type="text" value="<?=esc_attr($settings['conf_recaptcha_site_key']);?>" name="conf_recaptcha_site_key" id="conf_recaptcha_site_key" /></td>
        </tr>
        <tr>
            <td><strong>ReCaptcha Secret Key:</strong></td>
            <td><input type="text" value="<?=esc_attr($settings['conf_recaptcha_secret_key']);?>" name="conf_recaptcha_secret_key" id="conf_recaptcha_secret_key" /></td>
        </tr>
        <tr>
            <td><strong>ReCaptcha Validation:</strong></td>
            <td>
                <select name="conf_recaptcha_enabled" id="conf_recaptcha_enabled">
                    <?=$arrSecuritySettings['select_recaptcha_enabled'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>API requests limit:</strong></td>
            <td>
                <select name="conf_api_max_requests_per_period" id="conf_api_max_requests_per_period">
                    <?=$arrSecuritySettings['select_api_max_requests_per_period'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Failed API requests limit:</strong></td>
            <td>
                <select name="conf_api_max_failed_requests_per_period" id="conf_api_max_failed_requests_per_period">
                    <?=$arrSecuritySettings['select_api_max_failed_requests_per_period'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="Update security settings" name="update_security_settings"/>
            </td>
        </tr>
    </table>
</form>
<p>Please keep in mind that:</p>
<ol>
    <li>If enabled, ReCaptcha validation box is displayed on last step of reservation - in reservation summary, after customer details.</li>
    <li>To use ReCaptcha validation method, you must enter valid site and secret keys. If you don&#39;t have them - you can generate them at
        <a href = "https://www.google.com/recaptcha/admin">Google ReCaptcha Admin</a>.</li>
    <li>If you have set 50 api requests limit, this means that customer, who will lookup customer details for more than 50 times in an hour
        from same ip address will be withheld from fetching any customer details in reservation summary step for 1 hours period
        on his IP address.</li>
    <li>If you have set 3 failed api requests limit, this means that customer, who will fail 3 times in 1 hour to find his customer details
        from same ip address will be withheld from fetching any customer details in reservation summary step for 1 hours period
        on his IP address (or for that email address).</li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery("#security_settings_form").validate();
});
</script>