<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Blocked <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Models List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Block" onclick="window.location.href='<?=esc_js($addNewBlockURL);?>'" />
</h1>
<table class="display blocks-datatable" border="0" style="width:100%">
	<thead>
		<tr>
			<th>Label</th>
			<th><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> ID / SKU</th>
			<th><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Type</th>
			<th>Blocked <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?></th>
			<th>Units</th>
			<th>Date Range</th>
			<th>Specific Location</th>
			<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
		</tr>
	</thead>
    <tbody>
	<?=$trustedAdminBlockedListHTML;?>
    </tbody>
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
            { "width": "14%" },
            { "width": "9%" },
            { "width": "7%" },
            { "width": "20%" },
            { "width": "5%" },
            { "width": "21%" },
            { "width": "17%" },
            { "width": "7%" }
        ],
		"bAutoWidth": true, // moves column names along the content
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