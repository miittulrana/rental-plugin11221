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
if($settings['conf_load_font_awesome_from_plugin'] == 1):
    wp_enqueue_style('font-awesome');
endif;
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-item-models-list <?=esc_attr($extCSS_Prefix);?>item-models-list">
<?php
if($gotResults):
    foreach($classesWithItemModels AS $classWithItemModels):
        print('<div class="class-label">'.$classWithItemModels['print_translated_class_name'].'</div>');
        foreach($classWithItemModels['item_models'] AS $itemModel):
            include 'Shared/ItemModelsListOneClassPartial.php';
        endforeach;
    endforeach;
else:
    print('<div class="no-item-models-available">'.esc_html($lang['LANG_ITEM_MODELS_NONE_AVAILABLE_TEXT']).'</div>');
endif;
?>
</div>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.fancybox').fancybox();
});
</script>
<?php }, 100); ?>