<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
	<span>Payment Methods</span>&nbsp;&nbsp;
    <input class="add-new" type="button" value="Add New Payment Method" onclick="window.location.href='<?=esc_js($addNewPaymentMethodURL);?>'" />
</h1>
<table class="display payment-method-datatable" border="0" style="width:100%">
	<thead>
	<tr>
		<th >ID / Code</th>
		<th>Name</th>
		<th>E-mail / Keys</th>
        <th title="Work in sandbox mode">Sandbox</th>
        <th title="Check certificate">Cert.</th>
        <th title="Work in https:// only">SSL</th>
        <th title="Is it an online payment">Online</th>
		<th>Status</th>
        <th style="text-align: center">Order</th>
		<th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
	</tr>
	</thead>
	<tbody><?=$trustedAdminPaymentMethodsListHTML;?></tbody>
</table>
<p>Please keep in mind that:</p>
<ol>
    <li>
        If you set a payment method as online-payment, then if the reservation will be edited and saved, the old reservation will not (!) going to be updated.
        Instead of it will be cancelled and the new reservation will be saved.
        This is made because you need to be able easily track the payment difference which you will need to return.
    </li>
</ol>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.payment-method-datatable').dataTable( {
		"responsive": true,
		"bJQueryUI": true,
		"iDisplayLength": 25,
		"bSortClasses": false,
		"aaSorting": [[8,'asc'],[1,'asc']],
        "aoColumns": [
            { "width": "7%" },
            { "width": "25%" },
            { "width": "40%" },
            { "width": "4%" },
            { "width": "4%" },
            { "width": "4%" },
            { "width": "4%" },
            { "width": "4%" },
            { "width": "4%" },
            { "width": "4%" }
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