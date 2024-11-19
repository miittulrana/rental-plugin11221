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
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-item-models-slider <?=esc_attr($extCSS_Prefix);?>item-models-slider">
<?php if($gotResults): ?>
    <div class="responsive-item-models-slider">
    <?php foreach($itemModels AS $itemModel): ?>
       <div>
            <?php
            print('<div class="item-model-image">');
            if($itemModel['item_model_page_url']):
                print('<a href="'.esc_url($itemModel['item_model_page_url']).'" title="'.esc_attr($lang['LANG_ITEM_MODEL_SHOW_TEXT']).'">');
            endif;
            if($itemModel['item_model_thumb_1_url'] != ""):
                print('<img src="'.esc_url($itemModel['item_model_thumb_1_url']).'" title="'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'" alt="'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'">');
            endif;
            if($itemModel['item_model_page_url']):
                print('</a>');
            endif;
            print('</div>');
            print('<div class="item-model-details">');
                print('<div class="'.($itemModel['partner_profile_url'] ? 'item-model-title-with-partner' : 'item-model-title').'">');
                    if($itemModel['item_model_page_url'])
                    {
                        print('<a href="'.esc_url($itemModel['item_model_page_url']).'" title="'.esc_attr($lang['LANG_ITEM_MODEL_SHOW_TEXT']).'">');
                    }
                    print($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name']);
                    if($itemModel['item_model_page_url'])
                    {
                        print('</a>');
                    }
                print('</div>');
                if($itemModel['partner_profile_url']):
                    print('<div class="slider-partner-title">'.$itemModel['trusted_via_partner_link_html'].'</div>');
                endif;
                if($itemModel['price_group_id'] == 0):
                    print('<div class="item-model-prefix">');
                        print(esc_html($lang['LANG_PRICING_GET_A_QUOTE_TEXT']));
                    print('</div>');
                else:
                    print('<div class="item-model-prefix">');
                        print(esc_html($lang['LANG_FROM_TEXT']));
                    print('</div>');
                    print('<div class="item-model-price">');
                        print($itemModel['unit_tiny_without_fraction_print']['discounted_total_dynamic']);
                    print('</div>');
                    print('<div class="item-model-suffix">');
                        print($itemModel['time_ext_long_print']);
                    print('</div>');
                endif;
            print('</div>');
            ?>
       </div>
    <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-item-models-available"><?=esc_html($lang['LANG_ITEM_MODELS_NONE_AVAILABLE_TEXT']);?></div>
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
    jQuery('.responsive-item-models-slider').slick({
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 4,
        prevArrow: '<button type="button" class="slider-prev-icon"><?=esc_html($GLOBALS['LANG_PREVIOUS_TEXT']);?></button>',
        nextArrow: '<button type="button" class="slider-next-icon"><?=esc_html($GLOBALS['LANG_NEXT_TEXT']);?></button>',
        responsive: [
            {
                breakpoint: 1280,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 842,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2,
                    arrows: true
                }
            },
            {
                breakpoint: 570,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    arrows: false,
                    dots: true
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