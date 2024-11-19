<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Location List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Location" onclick="window.location.href='<?=esc_js($addNewLocationURL);?>'" />
</h1>
<table class="display location-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th width="5%">Id / Code</th>
		<th>Location / Contacts</th>
		<th style="width:110px;">Regular Fees</th>
		<th>Business Hours</th>
		<th>After Hours Locations</th>
		<th style="width:110px;">After Hours Fees</th>
		<th style="width: 4%; text-align: center">Order</th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody>
	<?=$trustedAdminLocationsListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.location-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
        "iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[6,'asc'],[1,'asc']],
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