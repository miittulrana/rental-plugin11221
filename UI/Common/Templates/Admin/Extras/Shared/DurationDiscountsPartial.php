<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Extra Discount Plans for Reservation Duration</span>&nbsp;&nbsp;
    <input class="add-new" type="button" value="Add New Discount Plan" onclick="window.location.href='<?=esc_js($addNewDurationDiscountURL);?>'" />
</h1>
<table class="display duration-datatable" border="0" style="width:100%">
    <thead>
    <tr>
        <th>#</th>
        <th>Reservation From - To / Extra Name</th>
        <th>Discount (%) Of Total Extra Price</th>
        <th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
    </tr>
    </thead>
    <tbody><?=$trustedAdminDurationDiscountsGroupsHTML;?></tbody>
</table>
<p>
    Total extra discount that will be applied in reservation process is a sum of two extra discounts - reservation duration discount and reservation in advance discounts.<br />
    If both - reservation duration and reservation in advance discounts are used, the in price table you will see price details
    for specific reservation duration period as a range from-to, example:
</p>
<ul style="margin-top: 0; padding-top: 0;">
    <li><strong>Price text:</strong> 20-25 <?=($settings['conf_currency_code'].' / '.$objSettings->getPeriodWord('LONG'));?></li>
    <li><strong>Hover text:</strong> <?=($objSettings->getPeriodWord('LONG'));?> price from with 50-60% discount applied</li>
</ul>
<p style="margin-top: 5px;padding-top: 0;">
    Price &quot;from&quot; word is used there because in price table system takes the lowest <?=($objSettings->getPeriodWord('LONG'));?> price used for that extra in week.
</p>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.duration-datatable').dataTable( {
        "responsive": true,
        "bJQueryUI": true,
        "iDisplayLength": 100,
        "bSortClasses": false,
        "aaSorting": [[0,'asc']],
        "aoColumns": [
            { "width": "10%" },
            { "width": "50%" },
            { "width": "25%" },
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