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
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-item-models-availability-calendar <?=esc_attr($extCSS_Prefix);?>item-models-availability-calendar">
    <span class="title">
        <?=esc_html($lang['LANG_ITEMS_AVAILABILITY_IN_NEXT_30_DAYS_TEXT']);?>
    </span>
    <hr/>
    <table class="availability-table">
        <thead>
        <tr class="classified-table-labels">
            <th colspan="2" class="classified-month-label">
                <?php
                if($itemModelsAvailabilityCalendar['2_months']):
                    print(esc_html($lang['LANG_ITEM_MODEL_TEXT']).' / '.$itemModelsAvailabilityCalendar['print_month_names'].' '.esc_html($lang['LANG_MONTH_DAYS_TEXT']).':');
                else:
                    print(esc_html($lang['LANG_ITEM_MODEL_TEXT']).' / '.$itemModelsAvailabilityCalendar['print_month_name'].' '.esc_html($lang['LANG_MONTH_DAY_TEXT']).':');
                endif;
                ?>
            </th>
            <?php
            foreach($itemModelsAvailabilityCalendar['print_days'] AS $dayName):
                print('<th class="one-day">'.esc_html($dayName).'</th>');
            endforeach;
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($itemModelsAvailabilityCalendar['classes'] AS $class):
            if($class['got_search_result']):
                print('<tr class="class-label">');
                print('<td class="class-name" colspan="'.($itemModelsAvailabilityCalendar['total_days']+2).'">'.$class['print_translated_class_name'].'</td>');
                print('</tr>');
                foreach($class['item_models'] AS $itemModel):
                    include 'Shared/ItemModelsAvailabilityCalendarOneClassPartial.php';
                endforeach;
            endif;
        endforeach;

        if($itemModelsAvailabilityCalendar['got_search_result'] === false):
            print('<tr class="class-label">');
            print('<td class="class-name" colspan="'.($itemModelsAvailabilityCalendar['total_days']+2).'">'.esc_html($lang['LANG_OTHER_TEXT']).'</td>');
            print('</tr>');
            print('<tr class="item-model-row">');
            print('<td class="no-item-models-in-category" colspan="'.($itemModelsAvailabilityCalendar['total_days']+2).'">'.esc_html($lang['LANG_ITEM_MODELS_NONE_AVAILABLE_TEXT']).'</td>');
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