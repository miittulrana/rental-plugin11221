<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
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
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div class="order-details">
    <span class="title"><?=esc_html($lang['LANG_VIEW_DETAILS_TEXT']).' - '.esc_html($lang['LANG_LOG_ID_TEXT']);?>: <?=$log['log_id'];?>
    </span>
    <input type="submit" value="<?=esc_attr($lang['LANG_LOG_BACK_TO_LIST_TEXT']);?>" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back" />
    <hr />
    <table class="log-table" cellpadding="5" cellspacing="1">
        <tbody>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_LOG_ACTION_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['action_text']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_LOG_DATE_AND_TIME_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['log_date_i18n'].' '.$log['log_time_i18n']);?></td>
        </tr>



        <!-- 1-10 -->
        <?php if($log['dimension_1'] != "" || $log['value_1'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_1']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_1']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_2'] != "" || $log['value_2'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_2']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_2']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_3'] != "" || $log['value_3'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_3']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_3']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_4'] != "" || $log['value_4'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_4']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_4']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_5'] != "" || $log['value_5'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_5']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_5']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_6'] != "" || $log['value_6'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_6']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_6']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_7'] != "" || $log['value_7'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_7']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_7']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_8'] != "" || $log['value_8'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_8']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_8']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_9'] != "" || $log['value_9'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_9']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_9']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_10'] != "" || $log['value_10'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_10']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_10']);?></td>
            </tr>
        <?php endif; ?>



        <!-- 11-20 -->
        <?php if($log['dimension_11'] != "" || $log['value_11'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_11']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_11']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_12'] != "" || $log['value_12'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_12']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_12']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_13'] != "" || $log['value_13'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_13']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_13']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_14'] != "" || $log['value_14'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_14']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_14']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_15'] != "" || $log['value_15'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_15']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_15']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_16'] != "" || $log['value_16'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_16']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_16']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_17'] != "" || $log['value_17'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_17']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_17']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_18'] != "" || $log['value_18'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_18']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_18']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_19'] != "" || $log['value_19'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_19']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_19']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_20'] != "" || $log['value_20'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_20']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_20']);?></td>
            </tr>
        <?php endif; ?>



        <!-- 21-30 -->
        <?php if($log['dimension_21'] != "" || $log['value_21'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_21']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_21']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_22'] != "" || $log['value_22'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_22']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_22']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_23'] != "" || $log['value_23'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_23']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_23']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_24'] != "" || $log['value_24'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_24']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_24']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_25'] != "" || $log['value_25'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_25']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_25']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_26'] != "" || $log['value_26'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_26']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_26']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_27'] != "" || $log['value_27'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_27']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_27']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_28'] != "" || $log['value_28'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_28']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_28']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_29'] != "" || $log['value_29'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_29']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_29']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_30'] != "" || $log['value_30'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_30']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_30']);?></td>
            </tr>
        <?php endif; ?>



        <!-- 31-40 -->
        <?php if($log['dimension_31'] != "" || $log['value_31'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_31']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_31']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_32'] != "" || $log['value_32'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_32']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_32']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_33'] != "" || $log['value_33'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_33']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_33']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_34'] != "" || $log['value_34'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_34']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_34']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_35'] != "" || $log['value_35'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_35']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_35']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_36'] != "" || $log['value_36'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_36']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_36']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_37'] != "" || $log['value_37'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_37']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_37']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_38'] != "" || $log['value_38'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_38']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_38']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_39'] != "" || $log['value_39'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_39']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_39']);?></td>
            </tr>
        <?php endif; ?>
        <?php if($log['dimension_40'] != "" || $log['value_40'] != ""): ?>
            <tr>
                <td align="left" class="log-td"><?=esc_html($log['dimension_40']);?></td>
                <td align="left" class="log-td"><?=esc_html($log['value_40']);?></td>
            </tr>
        <?php endif; ?>



        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_IP_ADDRESS_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['ip']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_REAL_IP_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['real_ip']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_HOST_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['host']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_BROWSER_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['browser']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_OS_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['os']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_AGENT_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['agent']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_IS_ROBOT_TEXT']);?></td>
            <td align="left" class="log-td"><?=esc_html($log['is_robot_text']);?></td>
        </tr>
        <tr>
            <td align="left" class="log-td"><?=esc_html($lang['LANG_STATUS_TEXT']);?></td>
            <td align="left" class="log-td-long">
                <span style="color:<?=$log['status_color'];?>;"><?=esc_html($log['status_text']);?></span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="log-td-long">
                <?=esc_html(sprintf($lang['LANG_LOG_D_RESULTS_FOUND_TEXT'], $log['results_found']));?>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="log-td-long">
                <?=esc_html($lang['LANG_LOG_ERRORS_TEXT']);?>:<br />
                <?=esc_br_html($log['errors']);?>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="log-td-long">
                <?=esc_html($lang['LANG_LOG_DEBUG_TEXT']);?>:<br />
                <?=esc_br_html($log['debug_log']);?>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</div>