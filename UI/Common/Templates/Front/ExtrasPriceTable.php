<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-extras-price-table <?=esc_attr($extCSS_Prefix);?>extras-price-table">
    <table class="price-table">
    <thead>
        <tr class="table-labels">
            <th class="extra-label">
                <?=(esc_html($lang['LANG_RENTAL_OPTION_TEXT']).' / '. $priceTable['print_dynamic_period_label']);?>
            </th>
            <?php
            foreach($priceTable['print_periods'] AS $period):
                print('<th class="extra-price-on-duration">');
                print('<span title="'.esc_attr($lang['LANG_PERIOD_TEXT']).'">'.$period['print_dynamic_period_label'].'</span>');
                print('</th>');
            endforeach;
            if($settings['conf_deposit_enabled'] == 1):
                print('<th class="extra-deposit">'.esc_html($lang['LANG_DEPOSIT_TEXT']).'</th>');
            endif;
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($priceTable['extras'] AS $extra):
        ?>
            <tr class="extra-row">
                <td class="extra-details">
                    <span class="extra-name"><?=esc_html($extra['translated_extra_name_with_dependant_item_model']);?></span>
                    <?php if($extra['partner_profile_url']): ?>
                        <br /><?=(esc_html($lang['LANG_PARTNER_TEXT']).': '.$extra['trusted_partner_link_html']);?>
                    <?php endif; ?>
                </td>
                <?php
                foreach($extra['period_list'] AS $period):
                    print('<td class="extra-price-on-duration">');
                        print('<span title="'.$period['print_price_description'].'">'.$period['print_price'].'</span>');
                    print('</td>');
                endforeach;
                ?>
                <?php if($settings['conf_deposit_enabled'] == 1): ?>
                    <td class="extra-deposit">
                        <strong><?=$extra['unit_long_without_fraction_print']['fixed_deposit'];?></strong>
                    </td>
                <?php endif; ?>
            </tr>
        <?php
        endforeach;
        if($priceTable['got_search_result'] === false):
            $colspan = $settings['conf_deposit_enabled'] == 1 ? $priceTable['total_periods']+2 : $priceTable['total_periods']+1;
            print('<tr class="extra-row">');
            print('<td class="no-extras-available" colspan="'.$colspan.'">'.esc_html($lang['LANG_EXTRAS_NONE_AVAILABLE_TEXT']).'</td>');
            print('</tr>');
        endif;
        ?>
    </tbody>
    </table>
</div>