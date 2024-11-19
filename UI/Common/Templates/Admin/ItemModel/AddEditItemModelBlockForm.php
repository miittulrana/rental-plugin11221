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
    <span class="title">Search for a <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model To Block</span>
    <input type="button" value="Back To Blocked <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Models List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr />
    <table cellpadding="4" width="100%">
        <tr>
            <td valign="top" style="width: 47%">
                <?php
                // Include admin search form template
                include 'Shared/ItemModelBlockInputPartial.php';
                ?>
            </td>
            <td valign="top">
                <?php
                if(isset($_POST['search_for_block']))
                {
                    // Include admin search results template
                    include 'Shared/ItemModelBlockResultsPartial.php';
                }
                ?>
            </td>
        </tr>
    </table>
</div>
</div>