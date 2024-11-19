<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Prepayment Plans</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Prepayment Plan" onclick="window.location.href='<?=esc_js($addNewPrepaymentURL);?>'" />
</h1>
<table class="display prepayment-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th>Includes</th>
		<th>Not Includes</th>
		<th>Reservation From</th>
		<th>Reservation To</th>
    	<th>Prepayment (%)</th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody><?=$trustedAdminPrepaymentsListHTML;?></tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.prepayment-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[0,'asc']],
		"aoColumns": [
			{ "width": "15%" },
			{ "width": "15%" },
			{ "width": "20%" },
			{ "width": "20%" },
			{ "width": "15%" },
			{ "width": "15%" }
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