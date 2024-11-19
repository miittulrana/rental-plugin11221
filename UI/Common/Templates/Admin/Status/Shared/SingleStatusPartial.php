<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html(sprintf($lang['LANG_STATUS_S_SYSTEM_TEXT'], $lang['EXT_NAME']));?></span>
</h1>
<form name="status_form" action="<?=esc_url($statusTabFormAction);?>" method="POST" class="status-form">
    <div class="big-text text-with-padding">
        <strong><?=esc_html($lang['LANG_STATUS_NETWORK_ENABLED_TEXT']);?>:</strong> <?=esc_html($lang[$networkEnabled ? 'LANG_YES_TEXT' : 'LANG_NO_TEXT']);?><br />
        <br />
        <strong><?=esc_html($lang['LANG_STATUS_DATABASE_VERSION_TEXT']);?>:</strong> <?=esc_html($databaseSemver);?><br />
        <br />
        <strong><?=esc_html($lang['LANG_STATUS_MICROFRAMEWORK_NAME_TEXT']);?>:</strong> <?=esc_html($microframeworkName);?><br />
        <br />
        <strong><?=esc_html($lang['LANG_STATUS_MICROFRAMEWORK_VERSION_TEXT']);?>:</strong> <?=esc_html($microframeworkSemver);?><br />
        <br />
        <?php if($updateAvailable): ?>
            <?php if($majorUpgradeAvailable): ?>
                <span class="major-update-text"><?=esc_html($lang['LANG_STATUS_MAJOR_UPGRADE_AVAILABLE_TEXT']);?></span><br />
            <?php else: ?>
                <span class="minor-update-text"><?=esc_html($lang['LANG_STATUS_MINOR_UPDATE_AVAILABLE_TEXT']);?></span><br />
            <?php endif; ?>
            <br />
            <strong><?=esc_html($lang['LANG_STATUS_NEWEST_VERSION_AVAILABLE_TEXT']);?>:</strong> <?=esc_html($newestSemverAvailable);?><br />
            <br />
            <?=esc_html($lang['LANG_STATUS_UPDATE_FOLLOW_STEPS_TEXT']);?>:
            <ol>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_MAKE_A_COPY_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_DOWNLOAD_NEW_VERSION_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_UPLOAD_VIA_FTP_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_UPLOAD_NEW_VERSION_TEXT']);?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_ACTIVATE_NEW_VERSION_TEXT']);?>,</li>
                <li><?=esc_html(sprintf($lang['LANG_STATUS_UPDATE_STEP_S_CLICK_UPDATE_TEXT'], $lang['EXT_NAME']));?>,</li>
                <li><?=esc_html($lang['LANG_STATUS_UPDATE_STEP_DONE_TEXT']);?>.</li>
            </ol>
        <?php elseif($updateAvailable === false && $updateExists === false): ?>
            <?php printf($lang['LANG_STATUS_YOU_HAVE_S_NO_UPDATE_AVAILABLE_TEXT'], '<span class="no-update-text">'.esc_html($lang['LANG_STATUS_THE_NEWEST_VERSION_TEXT']).'</span>'); ?>
        <?php elseif($updateAvailable === false && $updateExists): ?>
            <!-- Update exists, but system is not compatible to update -->
            <strong><?=esc_html($lang['LANG_STATUS_NEWEST_EXISTING_VERSION_TEXT']);?>:</strong> <?=esc_html($newestExistingSemver);?>
        <?php endif; ?>
    </div>
    <?php if($databaseMatchesCodeSemver === false): ?>
        <?php if($canMajorlyUpgrade): ?>
            <div class="big-text text-with-padding">
                <strong><?=esc_html(sprintf($lang['LANG_STATUS_S_SYSTEM_READY_FOR_UPGRADE_TEXT'], $lang['EXT_NAME']));?></strong>
            </div>
            <div class="big-text input-center">
                <input type="submit" value="<?=esc_attr($lang['LANG_STATUS_UPGRADE_TO_NEXT_VERSION_TEXT']);?>" name="update" />
            </div>
        <?php elseif($canUpdate): ?>
            <div class="big-text text-with-padding">
                <strong><?=esc_html(sprintf($lang['LANG_STATUS_S_SYSTEM_READY_FOR_UPDATE_TEXT'], $lang['EXT_NAME']));?></strong>
            </div>
            <div class="big-text input-center">
                  <input type="submit" value="<?=esc_attr($lang['LANG_STATUS_UPDATE_TO_NEXT_VERSION_TEXT']);?>" name="update" />
            </div>
        <?php else: ?>
            <?=esc_html($lang['LANG_STATUS_UPDATE_NOT_ALLOWED_ERROR_TEXT']);?>
        <?php endif; ?>
    <?php endif; ?>
</form>