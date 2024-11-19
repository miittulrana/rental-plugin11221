<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('fleet-management-admin');
?>

<?php if ($errorMessage != ""): ?>
    <div class="admin-info-message admin-wide-message admin-error-message"><?=esc_br_html($errorMessage);?></div>
<?php elseif ($okayMessage != ""): ?>
    <div class="admin-info-message admin-wide-message admin-okay-message"><?=esc_br_html($okayMessage);?></div>
<?php endif; ?>
<?php if ($ksesedDebugHTML != ""): ?>
    <div class="admin-info-message admin-wide-message admin-debug-html"><?=$ksesedDebugHTML;?></div>
<?php endif; ?>
<p> </p>
<div class="fleet-management-tabbed-admin">
<div class="order-details">
    <span class="title"><?=(esc_html($lang['LANG_ORDER_CODE2_TEXT']));?>:


    <?=(esc_html($order['booking_code']).($order['coupon_code'] ? ' ('.esc_html($lang['LANG_COUPON_TEXT']).': '.esc_html($order['coupon_code']).')' : ''));?>
    </span>
	<br>
	<span class='title' id="vnumber"></span>
    <input type="submit" value="<?=esc_attr($lang['LANG_CUSTOMER_BACK_TO_ORDERS_LIST_TEXT']);?>" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back" />
	<button onclick ="pushDataToDB();" id ="removeLinkJS" class = 'button-back'>Update Invoice</button>
    <br />
    <hr  />
    <table class="view-order-table" cellpadding="4" cellspacing="1">
        <tbody>
        <tr>
          <td align="left" class="table-title" colspan="2">
              <strong><?=esc_html($lang['LANG_CUSTOMER_DETAILS_FROM_DB_TEXT']);?></strong>
          </td>
        </tr>
        <?php if($customerTitleVisible || $customerFirstNameVisible || $customerLastNameVisible): ?>
            <tr>
                <td width="160px"><?=esc_html($lang['LANG_CUSTOMER_TEXT']);?></td>
                <td><?=esc_html($customer['full_name_with_title']);?>

                </td>
            </tr>
        <?php endif; ?>
        <?php if($customerBirthdateVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_DATE_OF_BIRTH_TEXT']);?></td>
                <td><?=esc_html($customer['birthdate_i18n']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerStreetAddressVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_STREET_ADDRESS_TEXT']);?></td>
                <td><?=esc_html($customer['street_address']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerCityVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_CITY_TEXT']);?></td>
                <td><?=esc_html($customer['city']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerStateVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_STATE_TEXT']);?></td>
                <td><?=esc_html($customer['state']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerZIP_CodeVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_ZIP_CODE_TEXT']);?></td>
                <td><?=esc_html($customer['zip_code']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerCountryVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_COUNTRY_TEXT']);?></td>
                <td><?=esc_html($customer['country']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerPhoneVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_PHONE_TEXT']);?></td>
                <td><?=$customer['trusted_phone_html'];?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerEmailVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_EMAIL_TEXT']);?></td>
                <td><?=$customer['trusted_email_html'];?></td>
            </tr>
        <?php endif; ?>
        <?php if($customerCommentsVisible): ?>
            <tr>
                <td><?=esc_html($lang['LANG_ADDITIONAL_COMMENTS_TEXT']);?></td>
                <td><?=esc_br_html($customer['comments']);?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <br />


    <?=$trustedInvoiceHTML;?>

    <br />
    <table class="view-order-table" cellpadding="4" cellspacing="1">
        <tr>
            <td colspan="2" align="left" class="table-title"><strong><?=esc_html($lang['LANG_ORDER_STATUS_TEXT']);?></strong></td>
        </tr>
        <tr>
            <td colspan="2">
                <strong><span style="color:<?=esc_attr($order['payment_status_color']);?>;"><?=esc_html($order['payment_status_text']);?></span>,
                <span style="color:<?=esc_attr($order['status_color']);?>;"><?=esc_html($order['status_text']);?></span></strong>
            </td>
        </tr>
    </table>
</div>
</div>
<script>
	function addVehicleRegistrationNumber(){

		let vrn = prompt("Enter the Vehicle Registration Number")
		if(vrn == null){
			  alert("Please enter a valid value.");

		}
		else{
		let urlToRedirect = `https://larentalsmalta.com/wp-admin/admin.php?page=car-rental-add-edit-order&noheader=true&update_vehicle_registration=<?php echo $_GET["order_id"];
?>&order_id=<?php echo $_GET["order_id"]; ?>&number_vehicle_registration=${vrn}&back_page=car-rental-order-manager&back_tab=car-rental-order-manager`
		window.location.href = urlToRedirect;
		}

	}
if("<?php echo $order["vehicle_registration_number"]; ?>" == "none"){
	document.getElementById("vnumber").innerHTML = `Vehicle Registration Number : <a href ="javascript:;" onclick="javascript:addVehicleRegistrationNumber()">Add Now</a>`
}
	else{
		document.getElementById("vnumber").innerHTML = `Vehicle Registration Number : <?php echo $order["vehicle_registration_number"]; ?> <a href ="javascript:;" onclick="javascript:addVehicleRegistrationNumber()">Edit</a>`
	}
</script>
<script>
function updateTotal(){

    let updateTotalPrice = prompt("Enter the Price")
        if(updateTotalPrice == null || updateTotalPrice == ''){
              alert("Please enter a valid value.");

        }
    else{
        var itemPrice = parseFloat(document.getElementById("itemPrice").innerText.replace('€ ','').replace(/[^\d\.\-]/g, ""))
        itemPrice = parseFloat(itemPrice.toFixed(2))


        var itemTotal = parseFloat(document.getElementById("itemTotal").innerText.replace('€ ','').replace(/[^\d\.\-]/g, ""))
        itemTotal = parseFloat(itemTotal.toFixed(2))


        document.getElementById("itemPrice").innerText = `€ ${updateTotalPrice.toLocaleString()}`
        document.getElementById("itemTotal").innerText = `€ ${updateTotalPrice.toLocaleString()}`

        var netPriceDiff = parseFloat((updateTotalPrice - itemTotal).toFixed(2))

        var subTotal = parseFloat(document.getElementById("subTotal").innerText.replace('€ ','').replace(/[^\d\.\-]/g, "")) + netPriceDiff
        subTotal = parseFloat(subTotal.toFixed(2))


        document.getElementById("subTotal").innerText = `€ ${subTotal.toLocaleString()}`
    var  finalVatPercent = parseFloat(document.getElementById("vatTaxPercent").innerText)/100
        var finalVat = finalVatPercent * subTotal
        finalVat = parseFloat(finalVat.toFixed(2))


        document.getElementById("vatTax").innerText = `€ ${finalVat.toLocaleString()}`



    var grandTotalBefore = parseFloat(document.getElementById("grandTotal").innerText.replace('€ ','').replace(/[^\d\.\-]/g, ""))



    var grandTotal = parseFloat((subTotal + finalVat).toFixed(2))
    document.getElementById("grandTotal").innerText = `€ ${grandTotal.toLocaleString()}`




    var payLater = grandTotal - grandTotalBefore + parseFloat(document.getElementById("payLater").innerText.replace('€ ','').replace(/[^\d\.\-]/g, ""))



    payLater = parseFloat(payLater.toFixed(2))
    document.getElementById("payLater").innerText = `€ ${payLater.toLocaleString()}`




    }

    }




    function pushDataToDB(){
    var removeJS = document.querySelectorAll("#removeLinkJS")

    for(var i = 0; i< removeJS.length;i++){

        removeJS[i].remove();

    }
    const data = new FormData();
    var grandTotal = parseFloat(document.getElementById("grandTotal").innerText.replace('€ ','').replace(/[^\d\.\-]/g, ""))
    var payLater = parseFloat(document.getElementById("payLater").innerText.replace('€ ','').replace(/[^\d\.\-]/g, ""))

    var return_timestamp = new Date(document.querySelector(".duration-details").children[1].innerText.reduceWhiteSpace())

    data.append("order_id", "<?php echo $_GET["order_id"]; ?>");
    data.append("back_url", window.location.href);
    data.append("invoice", `<!-- We put all content in a div, so that center and other tags looks well everywhere, especially in e-mails -->
    <div style="width:840px;" id = "invoiceFinal">${document.getElementById("invoiceFinal").innerHTML}</div>`);
    data.append("grand_total", grandTotal);
    data.append("total_pay_later", payLater);
    data.append("return_timestamp", return_timestamp.valueOf()/1000);


    const xhr = new XMLHttpRequest();
    xhr.withCredentials = true;
    xhr.addEventListener("readystatechange", function () {
      if (this.readyState === this.DONE) {
    window.location.href = window.location.href
      }
    });

    xhr.open("POST", "https://larentalsmalta.com/wp-admin/admin.php?page=car-rental-add-edit-order&noheader=true");
    xhr.setRequestHeader("Accept", "*/*");
    xhr.send(data);

    }
    function updateDays(){
        let updateType = prompt("Do you want to update Pickup or Return date & time? Enter 'P' for Pickup and 'R' for Return.");

        if (updateType === 'P' || updateType === 'p') {
            let updatePeriod = prompt("Add number of days to Pickup date. Leave blank for no change.");

            if (updatePeriod == null || updatePeriod == ''){
                updatePeriod = 0;
            }

            let updateHours = prompt("Add number of hours to Pickup date. Leave blank for no change.");

            if (updateHours == null || updateHours == '' ){
                updateHours = 0;
            }

            var oldPickupDate = new Date(document.querySelector(".duration-details").children[0].innerText.reduceWhiteSpace());

            var newPickupDate = oldPickupDate.addDays(Number(updatePeriod));
            newPickupDate = addHours(Number(updateHours), newPickupDate);
            document.querySelector(".duration-details").children[0].innerText = `${newPickupDate.getMonthName()} ${newPickupDate.getDate()}, ${newPickupDate.getFullYear()}    ${newPickupDate.toLocaleString('en-US', { hour:'numeric', hour12: false, hour: '2-digit',minute: '2-digit'})}`;

        } else if (updateType === 'R' || updateType === 'r') {
            let updatePeriod = prompt("Add number of days to Return date. Leave blank for no change.");

            if (updatePeriod == null || updatePeriod == ''){
                updatePeriod = 0;
            }

            let updateHours = prompt("Add number of hours to Return date. Leave blank for no change.");

            if (updateHours == null || updateHours == '' ){
                updateHours = 0;
            }

            var oldReturnDate = new Date(document.querySelector(".duration-details").children[1].innerText.reduceWhiteSpace());

            var newReturnDate = oldReturnDate.addDays(Number(updatePeriod));
            newReturnDate = addHours(Number(updateHours), newReturnDate);
            document.querySelector(".duration-details").children[1].innerText = `${newReturnDate.getMonthName()} ${newReturnDate.getDate()}, ${newReturnDate.getFullYear()}    ${newReturnDate.toLocaleString('en-US', { hour:'numeric', hour12: false, hour: '2-digit',minute: '2-digit'})}`;

            var pickupDate = new Date(document.querySelector(".duration-details").children[0].innerText.reduceWhiteSpace());
            document.querySelector("#period").innerText = `${Math.ceil((newReturnDate - pickupDate )/3600/1000/24)} days `;
        } else {
            alert("Invalid input. Please enter 'P' for Pickup or 'R' for Return.");
        }
    }




    String.prototype.killWhiteSpace = function() {
        return this.replace(/\s/g, '');
    };

    String.prototype.reduceWhiteSpace = function() {
        return this.replace(/\s+/g, ' ');
    };

    Date.prototype.getMonthName = function() {
        var monthNames = [ "January", "February", "March", "April", "May", "June",
                           "July", "August", "September", "October", "November", "December" ];
        return monthNames[this.getMonth()];
    };

    Date.prototype.addDays = function (days) {
        const date = new Date(this.valueOf());
        date.setDate(date.getDate() + days);
        return date;
    };

    function addHours(numOfHours, date = new Date()) {
        date.setTime(date.getTime() + numOfHours * 60 * 60 * 1000);
        return date;
    }



    document.getElementById("itemTotal").parentNode.innerHTML = document.getElementById("itemTotal").parentNode.innerHTML + `<a href="javascript:;" onclick="javascript:updateTotal()" id ="removeLinkJS">Edit</a>`
    document.querySelector(".duration-details").children[2].innerHTML = `<span id="period">${document.querySelector(".duration-details").children[2].innerHTML}</span><a href="javascript:;" onclick="javascript:updateDays()" id="removeLinkJS">Edit</a>`;
    document.getElementById("vatTaxPercent").style.display = "none"












</script>
