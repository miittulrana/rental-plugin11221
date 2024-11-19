<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=$emailSubject;?></span>
    <input class="back-to" type="button" value="Back to E-mail Notifications" onclick="window.location.href='<?=esc_js($backToListURL);?>'" />
</h1>
<div class="clear">
<?=$emailBody;?>
</div>