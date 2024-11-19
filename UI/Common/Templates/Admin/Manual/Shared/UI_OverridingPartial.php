<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_MANUAL_TUTORIAL_HOW_TO_OVERRIDE_UI_TEXT']);?></span>
</h1>

<h3>For very beginners:</h3>
<ol>
    <li>
        Just open your plugin folder. And copy &#39;UI&#39; folder to your current theme as &#39;FleetManagementUI&#39; folder.
    </li>
    <li>
        Then open any template file you want to change, i.e.<br />
        <pre>
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/Common/Templates/Front/ItemModelsList.php</pre>
        and edit it however you want.
    </li>
    <li>
        Save it. That&#39;s it - all done.
    </li>
</ol>

<h3>For professionals:</h3>
<ol>
    <li>
        To maintain the maximum compatibility with the future plugin updates, you should never copy all &#39;UI&#39; folder sub-folders.
        Instead of that you should copy and change only those exact folders/files which you want to override.
        It is recommended to start by copying &#39;UI/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?>Rental/&#39;' folder to your theme as &#39;FleetManagementUI/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?>Rental/&#39;' folder,
        but without it&#39;s &#39;SQL&#39; and &#39;DemoGallery&#39; sub-folders as these folders are pretty much just
        a folder-structure only prepared for extension-specific overriding.<br />
        <br />
        Examples:<br />
        <ul>
            <li>
                Copy <strong>template</strong> file from:
                <pre>
/wp-content/plugins/FleetManagement/UI/Common/Templates/Front/ItemModelsList.php</pre>
                To (if it is extension-specific template):<br />
                <pre>
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?>Rental/Templates/Front/ItemModelsList.php</pre>
                To (if that template can be applied to any extension):<br />
                <pre>
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/Common/Templates/Front/ItemModelsList.php</pre>
                And then edit the copied file however you want.
            </li>
            <li>
                Copy these three <strong>style-sheet</strong> files from:
                <pre>
/wp-content/plugins/FleetManagement/UI/Common/Assets/Front/CSS/Local/Shared/CrimsonRedColorsPartial.css
/wp-content/plugins/FleetManagement/UI/Common/Assets/Front/CSS/Local/Shared/LayoutPartial.css
/wp-content/plugins/FleetManagement/UI/Common/Assets/Front/CSS/Local/CrimsonRed.css</pre>
                To (if they are extension-specific style-sheets):<br />
                <pre>
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?>Rental/Assets/Front/CSS/Local/Shared/CrimsonRedColorsPartial.css
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?>Rental/Assets/Front/CSS/Local/Shared/LayoutPartial.css
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?>Rental/Assets/Front/CSS/Local/CrimsonRed.css</pre>
                To (if these style-sheets can be applied to any extension):<br />
                <pre>
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/Common/Assets/Front/CSS/Local/Shared/CrimsonRedColorsPartial.css
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/Common/Assets/Front/CSS/Local/Shared/LayoutPartial.css
/wp-content/themes/&lt;MY_THEME&gt;/FleetManagementUI/Common/Assets/Front/CSS/Local/CrimsonRed.css</pre>
                And then edit the copied files however you want.
            </li>
        </ul>
    </li>
    <li>
        Save all your edits. That&#39;s it - all done.
    </li>
</ol>