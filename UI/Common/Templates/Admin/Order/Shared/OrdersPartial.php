<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

// Add custom CSS for highlighting orders in admin
function fmo_enqueue_admin_styles() {
    echo '<style>
        .highlight-comment {
            background-color: yellow !important;
        }
    </style>';
}
add_action('admin_head', 'fmo_enqueue_admin_styles');

// Capture comments from the booking form and save them with the order
function fmo_capture_and_save_comments($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['customer_comments_nonce']) || !wp_verify_nonce($_POST['customer_comments_nonce'], 'save_customer_comments')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['customer_comments']) && !empty($_POST['customer_comments'])) {
        update_post_meta($post_id, '_customer_comments', sanitize_text_field($_POST['customer_comments']));
    }
}
add_action('save_post', 'fmo_capture_and_save_comments');

// Add a custom column to the admin order list
function fmo_add_custom_order_column($columns) {
    $columns['order_comments'] = __('Comments', 'fleet-management-order-highlight');
    return $columns;
}
add_filter('manage_order_posts_columns', 'fmo_add_custom_order_column');

// Render the content in the custom column
function fmo_render_custom_order_column($column, $post_id) {
    if ('order_comments' === $column) {
        $comments = get_post_meta($post_id, '_customer_comments', true);
        if (!empty($comments)) {
            echo '<span class="highlight-comment">' . esc_html($comments) . '</span>';
        } else {
            echo '<span>No Comments</span>';
        }
    }
}
add_action('manage_order_posts_custom_column', 'fmo_render_custom_order_column', 10, 2);

// Highlight orders with comments in the order list
function fmo_highlight_orders_with_comments($classes, $class, $order_id) {
    $comments = get_post_meta($order_id, '_customer_comments', true);
    if (!empty($comments)) {
        $classes[] = 'highlight-comment';
    }
    return $classes;
}
add_filter('post_class', 'fmo_highlight_orders_with_comments', 10, 3);

// Ensure comments are included in the form
function fmo_modify_order_form($order) {
    ?>
    <div class="form-row">
        <div class="customer-data-label">
            <strong><?php esc_html_e('Additional Comments', 'fleet-management-order-highlight'); ?>:</strong>
        </div>
        <div class="customer-data-input customer-textarea">
            <textarea name="customer_comments" class="customer-comments"><?php echo esc_textarea(get_post_meta($order->ID, '_customer_comments', true)); ?></textarea>
        </div>
    </div>
    <?php wp_nonce_field('save_customer_comments', 'customer_comments_nonce'); ?>
    <?php
}
add_action('your_order_form_hook', 'fmo_modify_order_form');

// Capture comments from the booking form
function fmo_capture_booking_form_comments() {
    if (isset($_POST['customer_comments']) && !empty($_POST['customer_comments'])) {
        update_post_meta(get_the_ID(), '_customer_comments', sanitize_text_field($_POST['customer_comments']));
    }
}
add_action('save_post_order', 'fmo_capture_booking_form_comments');
?>
<div class="clear">
    <table class="display bookings-datatable" border="0" style="width:100%">
        <thead>
        <tr>
            <th>#</th>
            <th>Code, Name & <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE_UPPERCASE']);?></th>
            <th>Pick-Up Date, Time & Location</th>
            <th>Return Date, Time & Location</th>
            <th>Reservation Date & Status</th>
            <th>Amount</th>
            <th><?=esc_html($lang['LANG_ACTIONS_TEXT']);?></th>
        </tr>
        </thead>
        <tbody>
        <?=$trustedAdminOrderListHTML;?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.bookings-datatable').dataTable( {
        "responsive": true,
        "bJQueryUI": true,
        "bSortClasses": false,
        "iDisplayLength": 25,
        "aaSorting": [[0,'asc']],
        "bAutoWidth": true,
        "aoColumns": [
            { "sWidth": "1%" },
            { "sWidth": "15%" },
            { "sWidth": "20%" },
            { "sWidth": "20%" },
            { "sWidth": "13%" },
            { "sWidth": "17%" },
            { "sWidth": "14%" }
        ],
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
