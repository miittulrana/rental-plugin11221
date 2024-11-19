<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
if($newOrder == true && $settings['conf_universal_analytics_enhanced_ecommerce'] == 1):
    include 'Shared/CompletedEnhancedEcommercePartial.php';
endif;
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-order-updated <?=esc_attr($extCSS_Prefix);?>order-updated">
    <h2><?=esc_html($lang['LANG_THANK_YOU_TEXT']);?></h2>
    <div class="info-content">
        <?=esc_html(sprintf($lang['LANG_ORDER_UPDATED_YOUR_CODE_S_TEXT'], $orderCode));?>
        <?php
        if($settings['conf_send_emails']):
            print(' '.esc_html($lang['LANG_INVOICE_SENT_TO_YOUR_EMAIL_ADDRESS_TEXT']).'.');
        endif;

        if(!empty($errorMessages)):
            print('<br /><br />'.esc_br_html($errorMessages));
        endif;
        ?>
    </div>
</div>
<div class="clear">&nbsp;</div>


