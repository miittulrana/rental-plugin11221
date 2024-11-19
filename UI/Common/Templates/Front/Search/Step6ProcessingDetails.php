<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
if($newOrder == true && $settings['conf_universal_analytics_enhanced_ecommerce'] == 1):
    include 'Shared/Step6EnhancedEcommercePartial.php';
endif;
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-payment-processing <?=esc_attr($extCSS_Prefix);?>payment-processing">
    <?=$processingPageOutput;?>
    <div class="clear">&nbsp;</div>
</div>


