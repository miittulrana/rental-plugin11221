<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Notification Settings</span>
</h1>
<form name="notification_settings_form" action="<?=esc_url($notificationSettingsTabFormAction);?>" method="POST" id="notification_settings_form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%">
        <tr>
            <td width="20%"><strong>Send E-mails:</strong></td>
            <td width="80%">
                <select name="conf_send_emails" id="conf_send_emails">
                    <?=$arrNotificationSettings['trusted_send_notifications_dropdown_options_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Company Notification E-mails:</strong></td>
            <td>
                <select name="conf_company_notification_emails" id="conf_company_notification_emails">
                    <?=$arrNotificationSettings['trusted_send_company_notifications_dropdown_options_html'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="Update notification settings" name="update_notification_settings"/>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery("#notification_settings_form").validate();
});
</script>