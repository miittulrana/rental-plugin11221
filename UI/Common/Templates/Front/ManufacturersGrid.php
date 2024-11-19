<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
if($settings['conf_load_slick_slider_from_plugin'] == 1):
    wp_enqueue_script('slick-slider');
endif;

// Styles
if($settings['conf_load_slick_slider_from_plugin'] == 1):
    wp_enqueue_style('slick-slider');
    wp_enqueue_style('slick-theme');
endif;
wp_enqueue_style('fleet-management-main');

?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-manufacturers-grid <?=esc_attr($extCSS_Prefix);?>manufacturers-grid">
<?php if($gotResults): ?>
    <?php foreach($manufacturers AS $manufacturer): ?>
        <div class="manufacturer-box">
            <?php if($manufacturer['manufacturer_thumb_url'] != ""): ?>
                <img src="<?=esc_url($manufacturer['manufacturer_thumb_url']);?>" title="<?=$manufacturer['print_translated_manufacturer_name'];?>" alt="<?=$manufacturer['print_translated_manufacturer_name'];?>">
            <?php else: ?>
                <div class="manufacturer-title">
                    <?=$manufacturer['print_translated_manufacturer_name'];?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <div class="clear">&nbsp;</div>
<?php else: ?>
    <div class="no-manufacturers-available"><?=esc_html($lang['LANG_MANUFACTURERS_NONE_AVAILABLE_TEXT']);?></div>
<?php endif; ?>
</div>