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
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-manufacturers-slider <?=esc_attr($extCSS_Prefix);?>manufacturers-slider">
<?php if($gotResults): ?>
    <div class="responsive-manufacturers-slider">
    <?php foreach($manufacturers AS $manufacturer): ?>
        <div>
            <?php if($manufacturer['manufacturer_thumb_url'] != ""): ?>
                <div class="manufacturer-logo">
                    <img src="<?=esc_url($manufacturer['manufacturer_thumb_url']);?>" title="<?=$manufacturer['print_translated_manufacturer_name'];?>" alt="<?=$manufacturer['print_translated_manufacturer_name'];?>">
                </div>
            <?php else: ?>
                <div class="manufacturer-title">
                    <?=$manufacturer['print_translated_manufacturer_name'];?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-manufacturers-available"><?=esc_html($lang['LANG_MANUFACTURERS_NONE_AVAILABLE_TEXT']);?></div>
<?php endif; ?>
</div>
<?php
// Global variable are needed here, because only then we will be able to access them inside the 'add_action' hook
$GLOBALS['LANG_PREVIOUS_TEXT'] = $lang['LANG_PREVIOUS_TEXT'];
$GLOBALS['LANG_NEXT_TEXT'] = $lang['LANG_NEXT_TEXT'];
?>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.responsive-manufacturers-slider').slick({
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 5,
        prevArrow: '<button type="button" class="slider-prev-icon"><?=esc_html($GLOBALS['LANG_PREVIOUS_TEXT']);?></button>',
        nextArrow: '<button type="button" class="slider-next-icon"><?=esc_html($GLOBALS['LANG_NEXT_TEXT']);?></button>',
        responsive: [
            {
                breakpoint: 1280,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 640,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 360,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    dots: false
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });
});
</script>
<?php }, 100); ?>