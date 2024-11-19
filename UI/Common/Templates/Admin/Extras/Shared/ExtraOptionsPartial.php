<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Extra Options List</span>&nbsp;&nbsp;
    <input class="add-new" type="button" value="Add New Option" onclick="window.location.href='<?=esc_js($addNewOptionURL);?>'" />
</h1>
<table class="display options-datatable" border="0" style="width:100%">
    <thead>
    <tr>
        <th>#</th>
        <th>Extra/Option Name</th>
        <th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
    </tr>
    </thead>
    <tbody>
    <?=$trustedAdminOptionsListHTML;?>
    </tbody>
</table>
<script>
jQuery(document).ready(function() {
    'use strict';
    jQuery('.options-datatable').dataTable( {
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
    } );
} );
</script>