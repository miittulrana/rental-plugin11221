<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-order-cancelled <?=esc_attr($extCSS_Prefix);?>order-cancelled">
    <h2><?=esc_html($lang['LANG_ORDER_FAILURE_TEXT']);?></h2>
    <div class="failure-title">
        <?=esc_br_html($errorMessages);?>
        <div class="buttons">
            <button type="submit" class="back-button" onclick="window.location.href='<?=esc_js($goBackURL);?>'"><?=esc_html($lang['LANG_BACK_TEXT']);?></button>
        </div>
    </div>
    <div class="clear">&nbsp;</div>
</div>