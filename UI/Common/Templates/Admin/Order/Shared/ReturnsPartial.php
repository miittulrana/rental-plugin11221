<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<div class="clear">
	<table class="display returns-datatable" border="0" style="width:100%">
		<thead>
		<tr>
			<th width="1%">#</th>
			<th width="15%" >Reservation Code, Name &amp; <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE_UPPERCASE']);?></th>
			<th>Pick-Up Date, Time &amp; Location</th>
			<th>Return Date, Time &amp; Location</th>
			<th width="18%">Reservation Date &amp; Status</th>
			<th width="17%">Amount</th>
			<th width="14%">Actions</th>
		</tr>
		</thead>
		<tbody>
		 <?=$trustedAdminReturnListHTML;?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.returns-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"bSortClasses": false,
		"iDisplayLength": 25,
		"aaSorting": [[0,'asc']],
		"bAutoWidth": true,
		"aoColumns": [
			{ "sWidth": "1%" },
			{ "sWidth": "15%" },
			{ "sWidth": "18%" },
			{ "sWidth": "18%" },
			{ "sWidth": "17%" },
			{ "sWidth": "17%" },
			{ "sWidth": "14%" }
		],
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