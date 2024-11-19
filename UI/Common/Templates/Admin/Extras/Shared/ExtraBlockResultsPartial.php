<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<form name="admin-block-results" action="<?=esc_url($blockResultsFormAction);?>" method="POST" id="admin-block-results">
    <table cellpadding="4" cellspacing="2" border="0" class="extra-block-result-table" >
        <input type="hidden" name="start_date" value="<?=esc_attr($startDate);?>" />
        <input type="hidden" name="start_time" value="<?=esc_attr($startTime);?>" />
        <input type="hidden" name="end_date" value="<?=esc_attr($endDate);?>" />
        <input type="hidden" name="end_time" value="<?=esc_attr($endTime);?>" />
        <tr>
            <td align="left" colspan="2" class="td-bold">
                <strong><?=esc_html($lang['LANG_PERIOD_TEXT']);?></strong><br />
                <?=($startDateTimeLabel.' - '.$endDateTimeLabel);?>
            </td>
        </tr>
        <tr>
            <td align="left" colspan="2"><strong>Name/Description</strong><br />
                <input type="text" name="block_name" id="block_name" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <hr />
            </td>
        </tr>
        <tr>
            <th align="left">Extra Name</th>
            <th align="left">Units</th>
        </tr>
        <tr>
             <td colspan="2">
                 <hr />
             </td>
        </tr>
        <?php foreach($extras as $extra): ?>
            <tr>
            <td><?=(esc_html($extra['translated_extra_name_with_dependant_item_model']).' '.esc_html($extra['via_partner']));?></td>
            <td>
                <input type="hidden" name="extra_ids[]" value="<?=esc_attr($extra['extra_id']);?>" />
                <select name="extra_units[<?=esc_attr($extra['extra_id']);?>]" id="extra_units_<?=esc_attr($extra['extra_id']);?>" class="required">
                    <?=$extra['quantity_dropdown_options'];?>
                </select>
            </td>
            </tr>
        <?php endforeach; ?>
        <?php if($gotBlockResults): ?>
            <tr>
                <td colspan="2" class="td-center"><input type="submit" value="Block selected extras" name="block" class="save-button"/></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="2" class="td-center"><strong>No extras found.</strong></td>
            </tr>
        <?php endif; ?>
   </table>
</form>