<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?=esc_html($lang['LANG_INVOICE_PRINT_TEXT']);?></title>

</head>
<body>
    <div align="center">
        <table cellpadding="3" border="0" width="750" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
            <tr>
                <td width="400" align="left" valign="top">

                    <span style="font-size:24px; font-weight:bold;"><?=esc_html($settings['conf_company_name']);?></span><br />
                    <br />
                    <span><?=esc_html($settings['conf_company_street_address']);?></span><br />
                    <span><?=esc_html($settings['conf_company_city']);?></span><br />
                    <span><?=($settings['conf_company_state'].' '.$settings['conf_company_zip_code']);?></span><br />
                    <span><?=esc_html($settings['conf_company_country']);?></span><br />
                </td>
                
                <td width="200" align="right" valign="top">
                    <span>
                        <strong><?=esc_html($lang['LANG_PHONE_TEXT']);?>:</strong> <?=esc_html($settings['conf_company_phone']);?>
                    </span><br />
                    <span>
                        <strong><?=esc_html($lang['LANG_EMAIL_TEXT']);?>:</strong> <?=esc_html($settings['conf_company_email']);?> 
                    </span><br />
                </td>
            </tr>
            <tr ><td align="left">
                
                                                            <img alt="" src="https://larentalsmalta.com/wp-content/uploads/2023/12/LA-Rentals-New-Official-Logo.png" style="height:145px; width:150px" />

            </td>

            </tr>
        </table>
        <br />

    <?=$invoiceHTML;?>
    </div>
	<script type="text/javascript">
	try{
    if(window.location.href.includes('page=car-rental-print-invoice')){
      //  document.querySelector('body > div:nth-child(1) > table').remove();
	//	document.querySelector("#invoiceFinal > table:nth-child(1)").style["margin-top"] = '150px';
		
		document.querySelector('#vatTaxPercent').style.visibility = "hidden"
		var vNumber = "<?php echo $vehicleRegistrationNumber;?>"
		if(vNumber !== 'none' && vNumber !== '' && vNumber !== null){
document.querySelector("#invoiceFinal > table:nth-child(4) > tbody > tr.item-models > td.col1").innerText = document.querySelector("#invoiceFinal > table:nth-child(4) > tbody > tr.item-models > td.col1").innerText + ` [${vNumber}]`
		}
    }
		

}
catch(e){
    console.log(e)
}
window.print();
</script>
</body>
</html>