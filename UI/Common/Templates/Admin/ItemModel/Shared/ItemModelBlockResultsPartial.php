<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<form name="admin-block-results" action="<?=esc_url($blockResultsFormAction);?>" method="POST" id="admin-block-results">
    <table cellpadding="4" cellspacing="2" border="0" class="extra-block-result-table">
    <input type="hidden" name="location_id" value="<?=esc_attr($locationId);?>" />
    <input type="hidden" name="start_date" value="<?=esc_attr($startDate);?>" />
    <input type="hidden" name="start_time" value="<?=esc_attr($startTime);?>" />
    <input type="hidden" name="end_date" value="<?=esc_attr($endDate);?>" />
    <input type="hidden" name="end_time" value="<?=esc_attr($endTime);?>" />
    <tr>
        <td align="left" colspan="6">
            <strong><?=esc_html($lang['LANG_PERIOD_TEXT']);?></strong><br />
            <?=($startDateTimeLabel.' - '.$endDateTimeLabel);?>
        </td>
    </tr>
    <tr>
        <td align="left" colspan="6"><strong>Name/Description</strong><br />
          <input type="text" name="block_name" id="block_name"/>
        </td>
    </tr>
    <tr>
        <td colspan="6">
            <hr />
        </td>
    </tr>
    <tr>
        <th align="left" style="width: 200px"><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model</th>
        <th align="left">Class</th>
        <th align="left">Transmission</th>
        <th align="left">Fuel</th>
        <th align="left">Units</th>
    </tr>
    <tr>
         <td colspan="5">
             <hr />
         </td>
    </tr>
    <?php foreach($itemModels AS $itemModel): ?>
        <tr>
            <td><?=($itemModel['manufacturer_name'].' '.$itemModel['item_model_name'].' '.esc_html($itemModel['via_partner']));?></td>
            <td><?=$itemModel['class_name'];?></td>
            <td><?=$itemModel['attribute2_title'];?></td>
            <td><?=$itemModel['attribute1_title'];?></td>
            <td align="right">
                <input type="hidden" name="item_model_ids[]" value="<?=esc_attr($itemModel['item_model_id']);?>" />
                <select name="item_model_units[<?=esc_attr($itemModel['item_model_id']);?>]" id="item_model_units_<?=esc_attr($itemModel['item_model_id']);?>" class="required">
                    <?=$itemModel['quantity_dropdown_options'];?>
                </select>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if($gotBlockResults): ?>
        <tr>
            <td>&nbsp;</td>
            <td colspan="5"><input type="submit" value="Block selected <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE']);?>" name="block" class="save-button"/></td>
        </tr>
    <?php else: ?>
        <tr>
            <td colspan="5" align="center" style="color:red;"><strong>No <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE']);?> found.</strong></td>
        </tr>
    <?php endif; ?>
    </table>
</form>