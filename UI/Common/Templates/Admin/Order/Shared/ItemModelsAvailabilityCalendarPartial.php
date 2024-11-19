<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<div class="fleet-management-item-models-availability-calendar <?=esc_attr($extCSS_Prefix);?>item-models-availability-calendar">
    <div class="calendar-label">
        <?php
        if($itemModelsAvailabilityCalendar['30_days']):
            print(esc_html($lang['LANG_ITEMS_AVAILABILITY_IN_NEXT_30_DAYS_TEXT']));
        else:
            print(esc_html($lang['LANG_ITEMS_AVAILABILITY_FOR_TEXT']).' '.$itemModelsAvailabilityCalendar['print_month_name'].', '.$itemModelsAvailabilityCalendar['print_year']);
        endif;
        ?>
    </div>
    <table class="availability-table" cellpadding="0" cellspacing="0">
        <thead>
        <tr class="table-labels">
            <th class="month-label">
                <?php
                if($itemModelsAvailabilityCalendar['2_months']):
                    print(esc_html($lang['LANG_ITEM_MODEL_TEXT']).' / '.$itemModelsAvailabilityCalendar['print_month_names'].' '.esc_html($lang['LANG_MONTH_DAYS_TEXT']));
                else:
                    print(esc_html($lang['LANG_ITEM_MODEL_TEXT']).' / '.$itemModelsAvailabilityCalendar['print_month_name'].' '.esc_html($lang['LANG_MONTH_DAY_TEXT']));
                endif;
                ?>
            </th>
            <?php
            foreach($itemModelsAvailabilityCalendar['print_days'] AS $oneDay):
                print('<th class="one-day">'.$oneDay.'</th>');
            endforeach;
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        if($itemModelsAvailabilityCalendar['classes'][0]['got_search_result']):
            foreach($itemModelsAvailabilityCalendar['classes'][0]['item_models'] AS $itemModel):
                include 'ItemModelsAvailabilityCalendarOneClassPartial.php';
            endforeach;
        else:
            print('<tr class="item-model-row">');
            print('<td class="no-item-models-in-category" colspan="'.($itemModelsAvailabilityCalendar['total_days']+1).'">'.esc_html($lang['LANG_ITEM_MODELS_NONE_AVAILABLE_TEXT']).'</td>');
            print('</tr>');
        endif;
        ?>
        </tbody>
    </table>
</div>