<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery.mousewheel'); // Optional for fancyBox
if($settings['conf_load_fancybox_from_plugin'] == 1):
    wp_enqueue_script('fancybox');
endif;

// Styles
if($settings['conf_load_fancybox_from_plugin'] == 1):
    wp_enqueue_style('fancybox');
endif;
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-item-models-price-table <?=esc_attr($extCSS_Prefix);?>item-models-price-table">
    <table class="price-table">
    <thead>
        <tr class="classified-table-labels">
            <th colspan="2" class="classified-item-label">
                <?=(esc_html($lang['LANG_ITEM_MODEL_TEXT']).' / '. $priceTable['print_dynamic_period_label']);?>:
            </th>
            <?php
            foreach($priceTable['print_periods'] AS $period):
                print('<th class="item-model-price-on-duration">');
                print('<span title="'.esc_attr($lang['LANG_PERIOD_TEXT']).'">'.$period['print_dynamic_period_label'].'</span>');
                print('</th>');
            endforeach;
            if($settings['conf_deposit_enabled'] == 1):
                print('<th class="item-model-deposit">'.esc_html($lang['LANG_DEPOSIT_TEXT']).'</th>');
            endif;
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($priceTable['classes'] AS $class):
            if($class['got_search_result']):
                $colspan = $settings['conf_deposit_enabled'] == 1 ? $priceTable['total_periods']+3 : $priceTable['total_periods']+2;
                print('<tr class="class-label">');
                print('<td class="class-name" colspan="'.$colspan.'">'.$class['print_translated_class_name'].'</td>');
                print('</tr>');
                foreach($class['item_models'] AS $itemModel):
                    include 'Shared/ItemModelsPriceTableOneClassPartial.php';
                endforeach;
            endif;
        endforeach;
        if($priceTable['got_search_result'] === false):
            $colspan = $settings['conf_deposit_enabled'] == 1 ? $priceTable['total_periods']+3 : $priceTable['total_periods']+2;
            print('<tr class="class-label">');
            print('<td class="class-name" colspan="'.$colspan.'">'.esc_html($lang['LANG_OTHER_TEXT']).'</td>');
            print('</tr>');
            print('<tr class="item-model-row">');
            print('<td class="no-item-models-in-category" colspan="'.$colspan.'">'.esc_html($lang['LANG_ITEM_MODELS_NONE_AVAILABLE_TEXT']).'</td>');
            print('</tr>');
        endif;
        ?>
    </tbody>
    </table>
</div>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.fancybox').fancybox();
});
</script>
<?php }, 100); ?>