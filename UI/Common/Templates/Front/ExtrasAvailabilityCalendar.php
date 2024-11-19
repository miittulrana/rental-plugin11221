<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper  fleet-management-extras-availability-calendar <?=esc_attr($extCSS_Prefix);?>extras-availability-calendar">
    <span class="title">
        <?=esc_html($lang['LANG_EXTRAS_AVAILABILITY_IN_NEXT_30_DAYS_TEXT']);?>
    </span>
    <hr/>
    <table class="availability-table">
        <thead>
        <tr class="table-labels">
            <th class="month-label">
                <?php
                if($extrasAvailabilityCalendar['2_months']):
                    print(esc_html($lang['LANG_EXTRAS_TEXT']).' / '.$extrasAvailabilityCalendar['print_month_names'].' '.esc_html($lang['LANG_MONTH_DAYS_TEXT']));
                else:
                    print(esc_html($lang['LANG_EXTRAS_TEXT']).' / '.$extrasAvailabilityCalendar['print_month_name'].' '.esc_html($lang['LANG_MONTH_DAY_TEXT']));
                endif;
                ?>
            </th>
            <?php
            foreach($extrasAvailabilityCalendar['print_days'] AS $oneDay):
                print('<th class="one-day">'.$oneDay.'</th>');
            endforeach;
            ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach($extrasAvailabilityCalendar['extras'] AS $extra): ?>
            <tr class="extra-row">
                <td class="extra-details">
                    <span class="extra-name"><?=esc_html($extra['translated_extra_name_with_dependant_item_model']);?></span>
                    <?php if($extra['partner_profile_url']): ?>
                        <br /><?=(esc_html($lang['LANG_PARTNER_TEXT']).': '.$extra['trusted_partner_link_html']);?>
                    <?php endif; ?>
                </td>
                <?php
                foreach($extra['day_list'] AS $day):
                    print('<td class="quantity-left-in-day '.$day['print_quantity_class'].'">');
                    print('<div class="quantity-hover"
                        title="'.esc_attr($lang['LANG_EXTRAS_AVAILABILITY_FOR_TEXT']).' '.esc_html($lang['LANG_ALL_DAY_TEXT']).'
                        '.esc_html($lang['LANG_ON_TEXT']).' '.$extrasAvailabilityCalendar['print_month_name'].' '.$day['print_day'].',
                        '.esc_html($lang['LANG_TOTAL_EXTRAS_TEXT']).' '.$day['units_in_stock'].'">'.$day['print_units_available'].'</div>');
                    print('<div class="partial-quantity-hover"
                        title="'.esc_attr($lang['LANG_EXTRAS_PARTIAL_AVAILABILITY_FOR_TEXT']).'
                        '.sprintf(esc_html($lang['LANG_S_PARTIAL_DAY_TEXT']), $noonTime).'">'.$day['print_partial_units_available'].'</div>');
                    print('</td>');
                endforeach;
                ?>
            </tr>
        <?php endforeach; ?>
        <?php if($extrasAvailabilityCalendar['got_search_result'] === false): ?>
            <tr class="item-model-row">
                <td class="no-extras" colspan="<?=($extrasAvailabilityCalendar['total_days']+1);?>"><?=esc_html($lang['LANG_EXTRAS_NONE_AVAILABLE_TEXT']);?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>