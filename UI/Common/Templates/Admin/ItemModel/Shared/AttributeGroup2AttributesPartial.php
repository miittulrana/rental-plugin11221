<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Transmission Type List</span>&nbsp;&nbsp;
	<input class="add-new" type="button" value="Add New Attribute" onclick="window.location.href='<?=esc_js($addNewAttributeGroup2AttributeURL);?>'" />
</h1>
<table class="display attribute-group2-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th>ID</th>
		<th><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME2_TEXT']);?></th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody>
	<?=$trustedAdminAttributeGroup2AttributesListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.attribute-group2-datatable').dataTable( {
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