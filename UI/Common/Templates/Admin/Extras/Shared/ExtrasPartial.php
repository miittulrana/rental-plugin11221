<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Extras List</span>&nbsp;&nbsp;
    <input class="add-new" type="button" value="Add New Extra" onclick="window.location.href='<?=esc_js($addNewExtraURL);?>'" />
</h1>
<table class="display extras-datatable" border="0" style="width:100%">
    <thead>
    <tr>
        <th>ID</th>
        <th>SKU</th>
        <th>Extra Name</th>
        <th>Quantity in Stock</th>
        <th>Unit Price (<?=esc_html($lang['LANG_TAX_WITHOUT_TEXT']);?>)</th>
        <?php if($settings['conf_deposit_enabled'] == 1): ?>
            <th>Fixed Deposit</th>
        <?php endif; ?>
        <th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
    </tr>
    </thead>
    <tbody>
    <?=$trustedAdminExtrasListHTML;?>
    </tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.extras-datatable').dataTable( {
        "responsive": true,
        "bJQueryUI": true,
        "iDisplayLength": 25,
        "bSortClasses": false,
        "aaSorting": [[2,'asc']],
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