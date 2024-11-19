<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<div class="clear">
	<table class="display customer-datatable" border="0" style="width:100%">
		<thead>
			<tr>
				<th width="2%">ID</th>
				<th width="13%">Customer Name</th>
				<th width="8%">Date of Birth</th>
				<th width="20%">Street Address</th>
				<th width="10%">Phone Number</th>
				<th width="16%">E-mail</th>
				<th width="8%">Registered</th>
				<th width="8%">Visited</th>
				<th width="15%">Actions</th>
			</tr>
		</thead>
		<tbody class="customer-list">
			<?=$trustedAdminCustomerListHTML;?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.customer-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[1,'asc']],
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
