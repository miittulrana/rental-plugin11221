<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Manufacturer List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Manufacturer" onclick="window.location.href='<?=esc_js($addNewManufacturerURL);?>'" />
</h1>
<table class="display manufacturers-datatable" border="0" style="width:100%">
	<thead>
	  <tr>
		  <th>ID</th>
		  <th>Manufacturer</th>
          <th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	  </tr>
	</thead>
	<tbody>
	 <?=$trustedAdminManufacturersListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.manufacturers-datatable').dataTable( {
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
	});
});
</script>