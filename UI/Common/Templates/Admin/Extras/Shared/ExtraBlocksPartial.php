<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Blocked Extras List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Block" onclick="window.location.href='<?=esc_js($addNewBlockURL);?>'" />
</h1>
<table class="display blocks-datatable" border="0" style="width:100%">
	<thead>
		<tr>
			<th>Label</th>
			<th>Extra ID / SKU</th>
			<th>Blocked Extra</th>
			<th>Units</th>
			<th>Date Range</th>
			<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
		</tr>
	</thead>
	<?=$trustedAdminBlockedListHTML;?>
</table>
<script>
jQuery(document).ready(function() {
    'use strict';
	jQuery('.blocks-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[0,'asc']],
		"aoColumns": [
			{ "width": "15%" },
			{ "width": "10%" },
			{ "width": "25%" },
			{ "width": "5%" },
			{ "width": "35%" },
			{ "width": "10%" }
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