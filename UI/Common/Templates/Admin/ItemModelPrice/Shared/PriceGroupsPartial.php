<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Price Groups</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Price Group" onclick="window.location.href='<?=esc_js($addNewPriceGroupURL);?>'"	/>
</h1>
<table class="display price-group-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th>ID</th>
		<th>Price Group Name</th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody><?=$trustedAdminPriceGroupsListHTML;?></tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.price-group-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[1,'asc']],
		"aoColumns": [
			{ "width": "5%" },
			{ "width": "70%" },
			{ "width": "25%" }
		],
		"bAutoWidth": false,
		"bInfo": true,
		"sScrollY": "100%",
		"sScrollX": "100%",
		"bScrollCollapse": true,
		"sPaginationType": "full_numbers",
		"bRetrieve": true,
        "language": {
            "url": FleetManagementVars['<?=esc_js($extCode);?>']['DATATABLES_LANG_URL']
        }
	} );
} );
</script>