<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Taxes</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Tax" onclick="window.location.href='<?=esc_js($addNewTaxURL);?>'"/>
</h1>
<table class="display taxes-datatable" border="0" style="width:100%">
	<thead>
	<tr>
        <th>Tax Name</th>
		<th>Location Name</th>
		<th>Location Type</th>
    	<th>Tax (%)</th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody><?=$trustedAdminTaxesListHTML;?></tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.taxes-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[0,'asc']],
		"aoColumns": [
			{ "width": "30%" },
			{ "width": "30%" },
			{ "width": "20%" },
			{ "width": "10%" },
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