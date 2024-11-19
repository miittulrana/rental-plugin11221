<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Feature List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Feature" onclick="window.location.href='<?=esc_js($addNewFeatureURL);?>'" />
</h1>
<table class="display features-datatable" border="0" style="width:100%">
	<thead>
	  	<tr>
			<th><?=esc_html($lang['LANG_FEATURE_TEXT']);?></th>
		  	<th><?=esc_html($lang['LANG_FEATURE_KEY_TEXT']);?></th>
			<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	  	</tr>
	</thead>
	<tbody>
	 <?=$trustedAdminFeatureListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.features-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[0,'asc']],
		"bAutoWidth": true,
		"aoColumns": [
			null,
			{ "sWidth": "15%" },
			{ "sWidth": "15%" }
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