<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('jquery-ui-datepicker', array('jquery','jquery-ui-core'));
wp_enqueue_script('jquery-ui-datepicker-locale');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-ui-theme');
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside">
    <span class="title">Search for an Extra to Block</span>
    <input type="button" value="Back To Blocked Extras List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
    <table cellpadding="4" width="100%">
        <tr>
            <td valign="top" style="width: 50%">
                <?php
                // Load extra block search form admin template
                include 'Shared/ExtraBlockInputPartial.php';
                ?>
            </td>
            <td valign="top">
                <?php
                if(isset($_POST['search_to_block']))
                {
                    // Include admin extra search results template
                    include 'Shared/ExtraBlockResultsPartial.php';
                }
                ?>
            </td>
        </tr>
    </table>
</div>
</div>