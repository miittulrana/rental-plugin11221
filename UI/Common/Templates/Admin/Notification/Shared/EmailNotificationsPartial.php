<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Email Notifications</span>
</h1>
<form name="email_settings_form" action="<?=esc_url($emailNotificationsTabFormAction);?>" method="POST" class="email-notifications-form">
    <table cellpadding="5" cellspacing="2" border="0">
        <tr>
            <td class="td-title">
                <strong>Email Type:</strong>
            </td>
            <td class="td-second">
                <select name="email_id" class="email-dropdown"><?=$emailList;?></select>
                <input class="back-to" type="button" name="email_preview" value="Preview"
                       onclick="" disabled="disabled"
                    />
            </td>
        </tr>
        <tr>
            <td class="td-title"><strong>Email Subject:</strong></td>
            <td class="td-second">
                <input type="text" name="email_subject" class="email-subject required" /><br />
                <span class="span-supported-codes">
                    <span class="span-supported-codes-title">
                        <strong>Supported Codes:</strong>
                    </span>
                    <span class="span-supported-codes-green">
                        <?=esc_html($orderBBCode);?>, [CUSTOMER_NAME], [COMPANY_NAME]<?php if($showLocationBBCodes === true): ?>, [LOCATION_NAME]<?php endif; ?>
                    </span>
                </span>
            </td>
        </tr>
        <tr>
            <td class="td-title">
                <strong>Email Body:</strong>
            </td>
            <td>
                <textarea name="email_body" class="email-body required"></textarea>
                <br />
                <span class="span-supported-codes">
                    <span class="span-supported-codes-title">
                        <strong>Supported Codes:</strong>
                    </span><br />
                    <span class="span-supported-codes-green">
                        [S]<strong><span class="span-supported-codes-black">STRONG</span></strong>[/S],
                        [EM]<em><span class="span-supported-codes-black">EMPHASIZED</span></em>[/EM],
                        [CENTER]<span class="span-supported-codes-black">CENTERED</span>[/CENTER], [HR],
                        [IMG]<span class="span-supported-codes-black">URL</span>[/IMG],
                        <?=esc_html($orderBBCode);?>, <?=esc_html($changeOrderURL_BBCode);?><br />
                        [CUSTOMER_ID], [CUSTOMER_NAME], [CUSTOMER_PHONE], [CUSTOMER_EMAIL], [INVOICE],
                        [SITE_URL], [COMPANY_NAME], [COMPANY_PHONE], [COMPANY_EMAIL]
                        <?php if($showLocationBBCodes === true): ?>
                            <br />
                            [LOCATION_NAME], [LOCATION_PHONE], [LOCATION_EMAIL]
                        <?php endif; ?>
                    </span>
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input name="update_email" type="submit" value="Update" disabled="disabled" />
            </td>
        </tr>
    </table>
</form>
<p>Please keep in mind that:</p>
<ol>
    <li>Email content, invoice and customer details width is 840px.</li>
    <li>URLs and email addresses in email content will be translated to links automatically.</li>
    <li>You have to be very careful with link emails - don&#39;t use the or use as few as possible to avoid emails going to SPAM folder.</li>
    <li>Email recipient has to allow images in emails to make pixel tracking work.</li>
    <li>You want to be consistent with your names for your campaign source, medium and content.<br />
        Any links in your email that go to your site should be tagged with the same source, medium and campaign as above.<br />
        Following the example bellow, your links should be formatted in this structure:<br />
        <span class="email-information-span">
        <?=(site_url());?>/landing-page/?utm_source=invoice&utm_medium=email&utm_campaign=061215
        </span>
    </li>
</ol>
<p>How to use in emails Google Analytics tracking pixel (image) via MailChimp STMP server:</p>
<ol>
    <li>Your demo shortcode will look like this:<br />
        <span class="email-information-span">
            <span class="email-information-span-green">[IMG]</span>https://www.google-analytics.com/collect?v=1&tid=UA-XXXXXXX-YY&cid=*|UNIQID|*&t=event&ec=email&ea=open&el=*|UNIQID|*&cs=invoice&cm=email&cn=061215&cm1=1<span class="email-information-span-green">[/IMG]</span>
        </span>
    </li>
    <li>The breakdown of parameters above:<br />
        <ul class="email-information-span">
            <li>
                <span><strong>https://www.google-analytics.com/collect?</strong></span>
                - This is the API endpoint for the Measurement Protocol.<br />
                In layman’s terms, this is where we’re sending the data. The data that’s being sent comes next, in the form of query parameters.
            </li>
            <li>
                <span><strong>v = 1</strong></span>
                - Protocol Version (required)
            </li>
            <li>
                <span><strong>tid = UA-XXXXXX-YY</strong></span>
                - Tracking ID / Web Property ID (required)
            </li>
            <li>
                <span><strong>cid = *|UNIQID|*</strong></span>
                - Client ID (required). This anonymously identifies a particular user, device, or browser.<br />
                The value – *|UNIQID|* – is a dynamic parameter (aka merge tag) in MailChimp that will fill in the user’s MailChimp ID.<br />
                If you are not using MailChimp, or if you want to track users by customer id, you can use [CUSTOMER_ID] shortcode instead.
            </li>
            <li>
                <span><strong>t = event</strong></span>
                - Hit type (required). We’re tracking this with event tracking, hence the event hit type.
            </li>
            <li>
                <span><strong>ec = email</strong></span>
                - Event Category
            </li>
            <li>
                <span><strong>ea = open</strong></span>
                - Event Action
            </li>
            <li>
                <span><strong>el = *|UNIQID|*</strong></span>
                - Event Label
            </li>
            <li>
                <span><strong>cs = invoice</strong></span>
                - Campaign Source
            </li>
            <li>
                <span><strong>cm = email</strong></span>
                - Campaign Medium
            </li>
            <li>
                <span><strong>cn = 061215</strong></span>
                - Campaign Name
            </li>
            <li>
                <span><strong>cm1 = 1</strong></span>
                - Custom Metric 1
            </li>
        </ul>
    </li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function()
{
    'use strict';
    var emailDropdown = jQuery('.email-notifications-form .email-dropdown');

    emailDropdown.on('change', function()
    {
        FleetManagementAdmin.setEmailContent('<?=esc_js($extCode);?>', this.value);
    });
    FleetManagementAdmin.setEmailContent('<?=esc_js($extCode);?>', emailDropdown.val());

    jQuery('.email-notifications-form').validate();
});
</script>