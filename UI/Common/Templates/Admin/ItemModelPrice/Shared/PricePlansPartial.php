<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span><?=esc_html($lang['LANG_ITEM_PRICE_PLANS_TITLE']);?></span>
</h1>
<p class="big-text padded">Please select a price group:</p>
<form action="<?=esc_url(admin_url('admin.php'));?>" method="GET" class="big-labels form-new-price-plan">
    <input type="hidden" name="page" value="<?=esc_attr($addNewPricePlanPage);?>" />
    <select name="price_group_id" class="price-groups-dropdown">
        <?=$trustedPriceGroupDropdownOptionsHTML;?>
    </select>
    <input type="hidden" name="price_plan_id" value="0" />
    <input type="submit" value="Add New Price Plan" name="add_new_price_plan" class="add-new-price-plan" disabled="disabled" />
</form>
<hr />
<table class="price-plans-table" cellpadding="5" cellspacing="0">
	<tbody class="price-group-html">
	<tr>
		<td colspan="9"><?=esc_html($lang['LANG_PRICE_GROUP_PLEASE_SELECT_TEXT']);?></td>
	</tr>
	</tbody>
</table>

<script type="text/javascript">
jQuery(document).ready(function()
{
    'use strict';
	var objPriceGroupsDropdown = jQuery('.price-groups-dropdown');

    objPriceGroupsDropdown.on('change', function()
    {
        //alert('test');
        var objAddNew = jQuery('input[type="submit"].add-new-price-plan');
        if(this.value > 0)
        {
            objAddNew.removeAttr('disabled');
        } else
        {
            objAddNew.attr('disabled', true);
        }
        FleetManagementAdmin.setPricePlans('<?=esc_js($extCode);?>', this.value);
    });
    // If it's already chosen
    if(objPriceGroupsDropdown.val() > 0)
    {
        //alert('test val:' + jQuery('.price-groups-dropdown').val());
        jQuery('input[type="submit"].add-new-price-plan').removeAttr('disabled');
        FleetManagementAdmin.setPricePlans('<?=esc_js($extCode);?>', objPriceGroupsDropdown.val());
    }
});
</script>