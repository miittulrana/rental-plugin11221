<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Customer data lookups in last 30 days</span>
</h1>
<table class="display lookups-datatable" border="0" style="width:100%">
	<thead>
		<tr>
			<th width="1%" nowrap="nowrap"><?=esc_html($lang['LANG_ID_TEXT']);?></th>
            <th width="15%" nowrap="nowrap">Date &amp; Time</th>
            <th width="5%" nowrap="nowrap">Action</th>
			<th width="5%">Dimensions &amp; Values</th>
			<th width="10%" nowrap="nowrap">Errors</th>
			<th width="10%" nowrap="nowrap">Debug Log</th>
			<th width="10%" nowrap="nowrap">Details</th>
            <th width="4%" nowrap="nowrap">Robot</th>
			<th width="5%" nowrap="nowrap"><?=esc_html($lang['LANG_STATUS_TEXT']);?></th>
            <th width="5%" nowrap="nowrap"><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
        </tr>
	</thead>
	<tbody class="lookup-list">
		<?=$trustedCustomerLookupLogListHTML;?>
	</tbody>
</table>
<script type="text/javascript">

jQuery(document).ready(function() {
    'use strict';
	jQuery('.lookups-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[0,'desc']],
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
	} );
} );
</script> 
