<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Class List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Class" onclick="window.location.href='<?=esc_js($addNewClassURL);?>'" />
</h1>
<table class="display classes-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th style="width: 1%">ID</th>
		<th>Class</th>
        <th style="text-align: center; width: 4%">Order</th>
		<th style="width: 15%">Actions</th>
	</tr>
	</thead>
	<tbody>
	<?=$trustedAdminClassesListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.classes-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[2,'asc'],[1,'asc']],
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