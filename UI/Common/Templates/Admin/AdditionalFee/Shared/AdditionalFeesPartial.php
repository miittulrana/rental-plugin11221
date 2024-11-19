<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_ADDITIONAL_FEE_LIST_TEXT']);?></span>&nbsp;&nbsp;
</h1>
<table class="display additional-fee-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th style="width:1%">#</th>
        <th><?=esc_html($lang['LANG_ADDITIONAL_FEE_NAME_SHORT_TEXT']);?></th>
        <th><?=esc_html($lang['LANG_LOCATION_PICKUP_TEXT']);?></th>
        <th><?=esc_html($lang['LANG_LOCATION_RETURN_TEXT']);?></th>
        <th><?=esc_html($lang['LANG_ADDITIONAL_FEE_SHORT_TEXT']);?> (<?=esc_html($lang['LANG_TAX_WITHOUT_TEXT']);?>)</th>
        <th><?=esc_html($lang['LANG_ADDITIONAL_FEE_SHORT_TEXT']);?> (<?=esc_html($lang['LANG_TAX_WITH_TEXT']);?>)</th>
        <th><?=esc_html($lang['LANG_ADDITIONAL_FEE_BENEFICIAL_ENTITY_TEXT']);?></th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody>
	<?=$trustedAdminAdditionalFeesListHTML;?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.additional-fee-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
        "iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[0,'asc']],
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