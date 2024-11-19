<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model" onclick="window.location.href='<?=esc_js($addNewItemModelURL);?>'" />
</h1>
<table class="display datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th>ID</th>
		<th>SKU</th>
		<th>Type</th>
		<th>Transmission</th>
		<th><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?></th>
		<th>Qty.</th>
		<th>Fuel</th>
		<th>Price Group</th>
        <?php if($settings['conf_deposit_enabled'] == 1): ?>
            <th>Deposit</th>
        <?php endif; ?>
		<th title="Minimum age">Age</th>
		<th>In Slider</th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody>
	<?=$trustedAdminItemModelListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[4,'asc'],[2,'asc']],
		"bAutoWidth": true,
		"bInfo": true,
		"sScrollY": "100%",
		"sScrollX": "100%",
		"bScrollCollapse": true,
		"sPaginationType": "full_numbers",
		"bRetrieve": true,
        "language": {
            "url": FleetManagementVars['<?=esc_js($extCode);?>']['DATATABLES_LANG_URL']
        }
	});
});
</script>