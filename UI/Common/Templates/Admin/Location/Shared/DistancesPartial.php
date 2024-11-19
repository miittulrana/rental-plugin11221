<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Distance List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Distance" onclick="window.location.href='<?=esc_js($addNewDistanceURL);?>'" />
</h1>
<table class="display distance-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th style="width:1%">#</th>
		<th>Pick-up Location</th>
		<th>Return Location</th>
        <th>Distance</th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody>
	<?=$trustedAdminDistancesListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.distance-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
        "iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[0,'asc']],
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