<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside">
  <span class="title"><?=esc_html($lang['LANG_PAYMENT_METHOD_ADD_EDIT_TEXT']);?></span>
  <input type="button" value="<?=esc_attr($lang['LANG_PAYMENT_METHOD_BACK_TO_LIST_TEXT']);?>" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
  <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
    <table cellpadding="5" cellspacing="2" border="0">
        <input type="hidden" name="payment_method_id" value="<?=esc_attr($paymentMethodId);?>"/>
        <tr>
            <td width="20%"><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_NAME_TEXT']);?>:<span class="is-required">*<span></strong></td>
            <td width="80%">
                <input type="text" name="payment_method_name" maxlength="100" value="<?=esc_attr($paymentMethodName);?>" class="payment-method-name required" title="<?=esc_attr($lang['LANG_PAYMENT_METHOD_NAME_TEXT']);?>" />
            </td>
        </tr>
        <?php if($networkEnabled): ?>
            <tr>
                <td><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_CODE_TEXT']);?>:<span class="is-required">*<span></strong></td>
                <td>
                    <input type="text" name="payment_method_code" maxlength="20" value="<?=esc_attr($paymentMethodCode);?>" class="payment-method-code number required" title="<?=esc_attr($lang['LANG_PAYMENT_METHOD_CODE_TEXT']);?>" />
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_CLASS_TEXT']);?>:</strong></td>
            <td>
                <select name="payment_method_class" class="payment-method-class" title="<?=esc_attr($lang['LANG_PAYMENT_METHOD_CLASS_TEXT']);?>">
                    <?=$trustedPaymentMethodClassesDropdownOptionsHTML;?>
                </select><br />
                (Optional, leave blank for payment methods without class integration)
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_EMAIL_TEXT']);?>:</strong></td>
            <td>
                <input type="text" name="payment_method_email" maxlength="50" value="<?=esc_attr($paymentMethodEmail);?>" class="payment-method-email" title="<?=esc_attr($lang['LANG_PAYMENT_METHOD_EMAIL_TEXT']);?>" />
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_DESCRIPTION_TEXT']);?>:</strong></td>
            <td>
                <textarea name="payment_method_description" rows="3" cols="50" title="<?=esc_attr($lang['LANG_PAYMENT_METHOD_DESCRIPTION_TEXT']);?>"><?=$paymentMethodDescription;?></textarea><br />
                (I.e. &quot;Credit Card Required&quot;, &quot;Cash Payment Only&quot;)
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_PUBLIC_KEY_TEXT']);?>:</strong></td>
            <td>
                <input type="text" name="public_key" maxlength="255" value="<?=esc_attr($publicKey);?>" class="public-key" title="<?=esc_attr($lang['LANG_PAYMENT_METHOD_PUBLIC_KEY_TEXT']);?>" />
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_PRIVATE_KEY_TEXT']);?>:</strong></td>
            <td>
                <input type="text" name="private_key" maxlength="255" value="<?=esc_attr($privateKey);?>" class="private-key" title="<?=esc_attr($lang['LANG_PAYMENT_METHOD_PRIVATE_KEY_TEXT']);?>" />
            </td>
        </tr>
        <tr>
            <td class="label"><strong>Pay in Other Currency:</strong></td>
            <td>
                <strong>1 <?=$settings['conf_currency_code'];?> =</strong>
                <input type="text" name="pay_in_currency_rate" value="<?=esc_attr($payInCurrencyRate);?>" class="pay-in-currency-rate required number" size="4" disabled="disabled" />
                <input type="text" name="pay_in_currency_code" maxlength="10" value="<?=esc_attr($payInCurrencyCode);?>" class="pay-in-currency-code number" size="4" disabled="disabled" />
                <em>(optional, leave currency code field empty to pay in site&#39;s currency)</em>
            </td>
        </tr>
        <tr>
            <td class="label"><strong>Other Currency Symbol:</strong></td>
            <td>
                <input type="text" name="pay_in_currency_symbol" maxlength="10" value="<?=esc_attr($payInCurrencyCode);?>" class="pay-in-currency-symbol number" size="4" disabled="disabled" />
                <em>(optional, leave field empty to pay in site&#39;s currency)</em>
            </td>
        </tr>
        <tr>
            <td><strong>Expiration Time:</strong></td>
            <td>
                <select name="expiration_time" id="expiration_time">
                    <?=$trustedExpirationTimeDropdownOptionsHTML;?>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Work in Sandbox Mode:</strong></td>
            <td>
                <input type="checkbox" name="sandbox_mode" value="yes" id="sandbox_mode"<?=($inSandboxMode ? ' checked="checked"' : '');?> />
            </td>
        </tr>
        <tr>
            <td><strong>Check Certificate:</strong></td>
            <td>
                <input type="checkbox" name="check_certificate" value="yes" id="check_certificate"<?=($checkCertificate ? ' checked="checked"' : '');?> />
            </td>
        </tr>
        <tr>
            <td><strong>SSL Only (https://):</strong></td>
            <td>
                <input type="checkbox" name="ssl_only" value="yes" id="ssl_only"<?=($sslOnly ? ' checked="checked"' : '');?> />
            </td>
        </tr>
        <tr>
            <td><strong>Is Online Payment:</strong></td>
            <td>
                <input type="checkbox" name="online_payment" value="yes" id="online_payment"<?=($isOnlinePayment ? ' checked="checked"' : '');?> />
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_ENABLED_TEXT']);?>:</strong></td>
            <td>
                <input type="checkbox" name="payment_method_enabled" value="yes" id="payment_method_enabled"<?=($paymentMethodEnabled ? ' checked="checked"' : '');?> title="<?=esc_attr($lang['LANG_ENABLED_TEXT']);?>" />
            </td>
        </tr>
        <tr>
            <td><strong><?=esc_html($lang['LANG_PAYMENT_METHOD_ORDER_TEXT']);?>:</strong></td>
            <td>
                <input type="text" name="payment_method_order" maxlength="11" value="<?=esc_attr($paymentMethodOrder);?>" class="payment-method-order"  title="<?=esc_attr($lang['LANG_LIST_ORDER_TEXT']);?>"/>
                <em><?=$paymentMethodId > 0 ? '' : '('.esc_html($lang['LANG_PAYMENT_METHOD_ORDER_OPTIONAL_TEXT']).')';?></em>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="submit" value="<?=esc_attr($lang['LANG_PAYMENT_METHOD_SAVE_TEXT']);?>" name="save_payment_method" class="save-button"/>
            </td>
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