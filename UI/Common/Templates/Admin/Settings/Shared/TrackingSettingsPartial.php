<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Tracking Settings</span>
</h1>
<form name="tracking_settings_form" action="<?=esc_url($trackingSettingsTabFormAction);?>" method="POST" id="tracking_settings_form">
    <table cellpadding="5" cellspacing="2" border="0" width="100%">
        <tr>
            <td width="20%"><strong>UA Event Tracking:</strong></td>
            <td width="80%">
                <select name="conf_universal_analytics_events_tracking" id="conf_universal_analytics_events_tracking">
                    <?=$arrTrackingSettings['select_universal_analytics_events_tracking'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>UA Enhanced Ecommerce:</strong></td>
            <td>
                <select name="conf_universal_analytics_enhanced_ecommerce" id="conf_universal_analytics_enhanced_ecommerce">
                    <?=$arrTrackingSettings['select_universal_analytics_enhanced_ecommerce'];?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <br />
                <input type="submit" value="Update tracking settings" name="update_tracking_settings"/>
            </td>
        </tr>
    </table>
</form>
<p>Please keep in mind that:</p>
<ol>
    <li>If you want to enable Universal Analytics event tracking or/and Enhanced Ecommerce,
        make sure you have standard Universal Analytics tracking code added to your site header
        or just after opening of &lt;body&gt; tag. Most themes has the header scripts part.<br />
        Default universal analytics tracking code looks like this:<br />
        <div class="tracking-codes">
            <pre>
&lt;script async src="https://www.googletagmanager.com/gtag/js?id=UA-<strong>YOUR-TRACKING-CODE</strong>&quot;&gt;&lt;/script&gt;
&lt;script&gt;
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-<strong>YOUR-TRACKING-CODE</strong>');
&lt;/script&gt;

            </pre>
        </div>
        Universal Analytics Event tracking will fire these onClick actions for new reservation:<br />
        <div class="tracking-codes">
            gtag('event', 'Click', { 'event_category': '<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Rental', 'event_label': '1. Search for all <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE']);?>'});<br />
            gtag('event', 'Click', { 'event_category': '<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Rental', 'event_label': '1. Search for single <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>'});<br />
            gtag('event', 'Click', { 'event_category': '<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Rental', 'event_label': '3. Continue to extras'});<br />
            gtag('event', 'Click', { 'event_category': '<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Rental', 'event_label': '4. Continue to summary'});<br />
            gtag('event', 'Click', { 'event_category': '<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Rental', 'event_label': '5. Confirm reservation'});
            });
        </div>
    </li>
    <li>With Enhanced Ecommerce we can track only those <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> models, which has &quot;Stock Keeping Unit&quot; (SKU) set.</li>
    <li>With Enhanced Ecommerce we can track only those extras, which has &quot;Stock Keeping Unit&quot; (SKU) set.</li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery("#tracking_settings_form").validate();
});
</script>