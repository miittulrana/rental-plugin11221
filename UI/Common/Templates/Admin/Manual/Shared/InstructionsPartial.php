<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_MANUAL_INSTRUCTIONS_TEXT']);?></span>
</h1>
<p><strong>Step 1</strong> - You already have the plugin installed in your system.</p>
<ul>
    <li><em>(Optional) Step 1.1</em> - If your theme <span class="success"><strong>already supports</strong></span> full screen <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model images preview,
        please <span class="failed"><strong>disable</strong></span> FancyBox in <?=esc_html($lang['EXT_NAME']);?> -&gt; Settings -&gt; &quot;Global&quot; tab.</li>
    <li><em>(Optional) Step 1.2</em> - If your theme <span class="success"><strong>already supports</strong></span> FontAwesome icons,
        please <span class="failed"><strong>disable</strong></span> FontAwesome in <?=esc_html($lang['EXT_NAME']);?> -&gt; Settings -&gt; &quot;Global&quot; tab.</li>
    <li><em>(Optional) Step 1.3</em> - If your theme <span class="success"><strong>already supports</strong></span> Slick Slider,
        please <span class="failed"><strong>disable</strong></span> Slick Slider in <?=esc_html($lang['EXT_NAME']);?> -&gt; Settings -&gt; &quot;Global&quot; tab.</li>
</ul>
<p><strong>Step 2</strong> - Now create a page by clicking the [Add New] button under the page menu.</p>
<p><strong>Step 3</strong> - Add <strong>[<?=esc_html($shortcode);?> display=&quot;search&quot; layouts=&quot;form,list,list,list,table,details,details,details,details&quot;]</strong> shortcode to page content and click on [Publish] button.</p>
<ul>
    <li><em>(Optional) Step 3.1</em> - For <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> models slider, use <strong>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models&quot; layout=&quot;slider&quot;]</strong> shortcode.</li>
    <li><em>(Optional) Step 3.2</em> - For reservation changes, use <strong>[<?=esc_html($shortcode);?> display=&quot;change-reservation&quot; layouts=&quot;form,form,list,list,list,table,details,details,details,details,details&quot;]</strong> shortcode.</li>
    <li><em>(Optional) Step 3.3</em> - For <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> models list, use <strong>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models&quot; layout=&quot;list&quot;]</strong> shortcode.</li>
    <li><em>(Optional) Step 3.4</em> - For location list, use <strong>[<?=esc_html($shortcode);?> display=&quot;locations&quot; layout=&quot;list&quot;]</strong> shortcode.</li>
    <li><em>(Optional) Step 3.5</em> - For <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model prices table, use <strong>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-model-prices&quot; layout=&quot;table&quot;]</strong> shortcode.</li>
    <li><em>(Optional) Step 3.6</em> - For <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> models availability calendar, use <strong>[<?=esc_html($shortcode);?> display=&quot;<?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?>-models-availability&quot; layout=&quot;calendar&quot;]</strong> shortcode.</li>
    <li><em>(Optional) Step 3.7</em> - For manufacturers grid, use <strong>[<?=esc_html($shortcode);?> display=&quot;manufacturers&quot; layout=&quot;grid&quot;]</strong> shortcode.</li>
</ul>
<p><strong>Step 4</strong> - In WordPress front-end page, where you added search shortcode, you will see reservation engine.</p>
<p><strong>Step 5</strong> - Congratulations, you&#39;re done! We wish you to have a pleasant work with our <?=esc_html($lang['EXT_NAME']);?> System for WordPress.</p>
<h3>Additional Notes</h3>
<ol>
    <li>Make sure that your &quot;/wp-content/uploads/&quot; directory is writable.</li>
    <li>If server is using not apache user to write to folder, CHMOD 0755 is not enough - you need to set permissions (CHMOD) to 0777.</li>
    <li>If you have a multisite setup, you need to do the same CHMOD to 0777 to &quot;/wp-content/uploads/sites/2/&quot;,
&quot;/wp-content/uploads/sites/3/&quot; etc. folders.</li>
    <li><?=esc_html($lang['LANG_INSTRUCTIONS_PARAGRAPH1']);?></li>
</ol>