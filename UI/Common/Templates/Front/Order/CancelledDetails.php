<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-order-cancelled <?=esc_attr($extCSS_Prefix);?>order-cancelled">
    <h2><?=esc_html($lang['LANG_THANK_YOU_TEXT']);?></h2>
    <div class="info-content">
        <?=esc_html(sprintf($lang['LANG_ORDER_S_CANCELLED_SUCCESSFULLY_TEXT'], $orderCode));?>
        <?=esc_br_html($errorMessages);?>
        <div class="buttons">
            <button type="submit" class="home-button" onclick="window.location.href='<?=esc_js($goToHomePageURL);?>'"><?=esc_html($lang['LANG_GO_TO_HOME_PAGE_TEXT']);?></button>
        </div>
    </div>
    <div class="clear">&nbsp;</div>
</div>